<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const VENDOR_DEPENDENT_TABLES = [
        'vendor_documents',
        'vendor_bank_accounts',
        'vendor_status_histories',
        'products',
        'commissions',
        'settlements',
        'subscriptions',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->consolidateDuplicateOwners();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->unique('owner_user_id', 'vendors_owner_user_id_unique');
        });

        Schema::table('vendor_users', function (Blueprint $table) {
            $table->string('invited_email')->nullable()->after('user_id');
            $table->string('invitation_token_hash', 64)->nullable()->after('invited_email');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token_hash');
            $table->timestamp('accepted_at')->nullable()->after('invitation_expires_at');
            $table->timestamp('declined_at')->nullable()->after('accepted_at');
            $table->timestamp('revoked_at')->nullable()->after('declined_at');

            $table->unique('invitation_token_hash');
            $table->index(['invited_email', 'status']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('order_id')->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable()->after('quantity');
        });
    }

    private function consolidateDuplicateOwners(): void
    {
        $duplicateOwnerIds = DB::table('vendors')
            ->select('owner_user_id')
            ->whereNotNull('owner_user_id')
            ->groupBy('owner_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('owner_user_id')
            ->pluck('owner_user_id');

        foreach ($duplicateOwnerIds as $ownerId) {
            // Match the registration service's lock order and serialize this owner's merge.
            DB::table('users')->where('id', $ownerId)->lockForUpdate()->first();

            $vendors = DB::table('vendors')
                ->where('owner_user_id', $ownerId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($vendors->count() < 2) {
                continue;
            }

            $orderedVendors = $vendors->all();
            usort($orderedVendors, fn (object $left, object $right): int => $this->compareVendors($left, $right));

            $canonical = array_shift($orderedVendors);
            $duplicates = $orderedVendors;
            $duplicateIds = array_map(fn (object $vendor): int => (int) $vendor->id, $duplicates);
            $vendorIds = array_merge([(int) $canonical->id], $duplicateIds);

            // Prefer the canonical vendor's existing default; otherwise retain one
            // deterministic legacy default. No default is invented when none exists.
            $defaultAccountId = DB::table('vendor_bank_accounts')
                ->where('vendor_id', $canonical->id)
                ->where('is_default', true)
                ->orderBy('id')
                ->value('id');

            if ($defaultAccountId === null) {
                $defaultAccountId = DB::table('vendor_bank_accounts')
                    ->whereIn('vendor_id', $vendorIds)
                    ->where('is_default', true)
                    ->orderBy('id')
                    ->value('id');
            }

            $staffCollisions = [];

            foreach ($duplicates as $duplicate) {
                $staffCollisions[(int) $duplicate->id] = $this->mergeVendorUsers(
                    (int) $duplicate->id,
                    (int) $canonical->id,
                );
            }

            foreach (self::VENDOR_DEPENDENT_TABLES as $table) {
                DB::table($table)
                    ->whereIn('vendor_id', $duplicateIds)
                    ->update(['vendor_id' => $canonical->id]);
            }

            foreach ($duplicates as $duplicate) {
                $this->archiveVendorMerge(
                    $duplicate,
                    $canonical,
                    $staffCollisions[(int) $duplicate->id],
                );
            }

            DB::table('vendor_bank_accounts')
                ->whereIn('vendor_id', $vendorIds)
                ->update(['is_default' => false]);

            if ($defaultAccountId !== null) {
                DB::table('vendor_bank_accounts')
                    ->where('id', $defaultAccountId)
                    ->update(['is_default' => true]);
            }

            DB::table('vendors')->whereIn('id', $duplicateIds)->delete();
        }
    }

    private function compareVendors(object $left, object $right): int
    {
        $leftApproved = $left->verification_status === 'approved';
        $rightApproved = $right->verification_status === 'approved';

        if ($leftApproved !== $rightApproved) {
            return $leftApproved ? -1 : 1;
        }

        $leftActive = $left->status === 'active';
        $rightActive = $right->status === 'active';

        if ($leftActive !== $rightActive) {
            return $leftActive ? -1 : 1;
        }

        foreach (['approved_at', 'submitted_at', 'created_at'] as $column) {
            $comparison = strcmp((string) ($right->{$column} ?? ''), (string) ($left->{$column} ?? ''));

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return (int) $right->id <=> (int) $left->id;
    }

    /**
     * @return list<string>
     */
    private function mergeVendorUsers(int $duplicateVendorId, int $canonicalVendorId): array
    {
        $memberships = DB::table('vendor_users')
            ->where('vendor_id', $duplicateVendorId)
            ->orderBy('user_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $collisions = [];

        foreach ($memberships as $membership) {
            $canonicalMembership = DB::table('vendor_users')
                ->where('vendor_id', $canonicalVendorId)
                ->where('user_id', $membership->user_id)
                ->lockForUpdate()
                ->first();

            if ($canonicalMembership === null) {
                DB::table('vendor_users')
                    ->where('id', $membership->id)
                    ->update(['vendor_id' => $canonicalVendorId]);

                continue;
            }

            if ($this->isStrongerMembership($membership, $canonicalMembership)) {
                DB::table('vendor_users')
                    ->where('id', $canonicalMembership->id)
                    ->update([
                        'staff_role' => $membership->staff_role,
                        'status' => $membership->status,
                        'invited_by' => $membership->invited_by,
                        'joined_at' => $membership->joined_at,
                        'created_at' => $membership->created_at,
                        'updated_at' => $membership->updated_at,
                    ]);

                $retainedStatus = $membership->status;
            } else {
                $retainedStatus = $canonicalMembership->status;
            }

            DB::table('vendor_users')->where('id', $membership->id)->delete();

            $collisions[] = sprintf(
                'user #%d kept membership #%d (%s), removed membership #%d (%s)',
                $membership->user_id,
                $canonicalMembership->id,
                $retainedStatus,
                $membership->id,
                $membership->status,
            );
        }

        return $collisions;
    }

    private function isStrongerMembership(object $candidate, object $current): bool
    {
        $statusRanks = ['active' => 3, 'invited' => 2, 'declined' => 1, 'revoked' => 0];
        $roleRanks = ['manager' => 1, 'staff' => 0];

        $candidateStatusRank = $statusRanks[$candidate->status] ?? 0;
        $currentStatusRank = $statusRanks[$current->status] ?? 0;

        if ($candidateStatusRank !== $currentStatusRank) {
            return $candidateStatusRank > $currentStatusRank;
        }

        $candidateRoleRank = $roleRanks[$candidate->staff_role] ?? 0;
        $currentRoleRank = $roleRanks[$current->staff_role] ?? 0;

        if ($candidateRoleRank !== $currentRoleRank) {
            return $candidateRoleRank > $currentRoleRank;
        }

        $joinedComparison = strcmp((string) ($candidate->joined_at ?? ''), (string) ($current->joined_at ?? ''));

        if ($joinedComparison !== 0) {
            return $joinedComparison > 0;
        }

        return (int) $candidate->id > (int) $current->id;
    }

    /**
     * @param  list<string>  $staffCollisions
     */
    private function archiveVendorMerge(object $duplicate, object $canonical, array $staffCollisions): void
    {
        $reason = sprintf(
            'Legacy duplicate owner merge: vendor #%d (%s; %s; verification=%s; account=%s; owner_user_id=%d) merged into vendor #%d (%s; %s). Reason: enforce one vendor per owner.',
            $duplicate->id,
            $duplicate->vendor_code,
            $duplicate->business_name,
            $duplicate->verification_status,
            $duplicate->status,
            $duplicate->owner_user_id,
            $canonical->id,
            $canonical->vendor_code,
            $canonical->business_name,
        );

        if ($staffCollisions !== []) {
            $reason .= ' Staff collisions: '.implode('; ', $staffCollisions).'.';
        }

        DB::table('vendor_status_histories')->insert([
            'vendor_id' => $canonical->id,
            'previous_verification_status' => $duplicate->verification_status,
            'new_verification_status' => $canonical->verification_status,
            'previous_account_status' => $duplicate->status,
            'new_account_status' => $canonical->status,
            'changed_by' => null,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('note');
        });

        Schema::table('vendor_users', function (Blueprint $table) {
            $table->dropUnique(['invitation_token_hash']);
            $table->dropIndex(['invited_email', 'status']);
            $table->dropColumn([
                'invited_email', 'invitation_token_hash', 'invitation_expires_at',
                'accepted_at', 'declined_at', 'revoked_at',
            ]);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique('vendors_owner_user_id_unique');
        });
    }
};
