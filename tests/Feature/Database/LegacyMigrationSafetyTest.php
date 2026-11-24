<?php

namespace Tests\Feature\Database;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyMigrationSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        if (DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->fail('Legacy migration tests must use the SQLite :memory: connection.');
        }
    }

    public function test_cart_upgrade_merges_duplicate_user_carts_and_preserves_guest_carts(): void
    {
        $this->createLegacyCartSchema();

        $userId = $this->createUser('Cart Owner');
        $otherUserId = $this->createUser('Other Cart Owner');
        $vendorId = $this->createVendor($this->createUser('Cart Vendor'), 'CART-VENDOR');
        $productA = $this->createProduct($vendorId, 'product-a');
        $productB = $this->createProduct($vendorId, 'product-b');
        $productC = $this->createProduct($vendorId, 'product-c');

        $canonicalCartId = $this->createCart($userId, 'canonical-session');
        $duplicateCartId = $this->createCart($userId, 'duplicate-session');
        $emptyCartId = $this->createCart($userId, 'empty-session');
        $otherCartId = $this->createCart($otherUserId, 'other-session');
        $guestCartId = $this->createCart(null, 'guest-session-one');
        $secondGuestCartId = $this->createCart(null, 'guest-session-two');

        $canonicalItemId = $this->createCartItem($canonicalCartId, $productA, 2);
        $this->createCartItem($duplicateCartId, $productA, 3);
        $movedItemId = $this->createCartItem($duplicateCartId, $productB, 4);
        $this->createCartItem($otherCartId, $productC, 1);
        $this->createCartItem($guestCartId, $productA, 7);
        $this->createCartItem($secondGuestCartId, $productC, 2);

        $this->assertFalse(Schema::hasIndex('carts', 'carts_user_id_unique', 'unique'));

        $this->migration('2026_11_24_000002_add_unique_user_id_to_carts_table.php')->up();

        $this->assertDatabaseHas('carts', [
            'id' => $canonicalCartId,
            'user_id' => $userId,
            'session_id' => null,
        ]);
        $this->assertDatabaseMissing('carts', ['id' => $duplicateCartId]);
        $this->assertDatabaseMissing('carts', ['id' => $emptyCartId]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $canonicalItemId,
            'cart_id' => $canonicalCartId,
            'product_id' => $productA,
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $movedItemId,
            'cart_id' => $canonicalCartId,
            'product_id' => $productB,
            'quantity' => 4,
        ]);
        $this->assertDatabaseHas('carts', [
            'id' => $otherCartId,
            'user_id' => $otherUserId,
            'session_id' => 'other-session',
        ]);
        $this->assertDatabaseHas('carts', [
            'id' => $guestCartId,
            'user_id' => null,
            'session_id' => 'guest-session-one',
        ]);
        $this->assertDatabaseHas('carts', [
            'id' => $secondGuestCartId,
            'user_id' => null,
            'session_id' => 'guest-session-two',
        ]);
        $this->assertDatabaseCount('carts', 4);
        $this->assertDatabaseCount('cart_items', 5);
        $this->assertTrue(Schema::hasIndex('carts', 'carts_user_id_unique', 'unique'));
        $this->assertTrue(Schema::hasIndex('carts', ['user_id'], 'unique'));

        // SQLite/MySQL unique indexes continue to allow multiple nullable guest carts.
        $this->createCart(null, 'guest-session-three');
        $this->assertDatabaseCount('carts', 5);

        $this->expectException(QueryException::class);
        DB::table('carts')->insert([
            'user_id' => $userId,
            'session_id' => 'must-not-be-created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_vendor_upgrade_preserves_dependencies_and_archives_each_merged_identity(): void
    {
        $this->createLegacyVendorWorkflowSchema();

        $ownerId = $this->createUser('Duplicate Vendor Owner');
        $singletonOwnerId = $this->createUser('Single Vendor Owner');
        $customerId = $this->createUser('Vendor Customer');
        $olderId = $this->createVendor($ownerId, 'OLDER', [
            'verification_status' => 'approved',
            'status' => 'active',
            'approved_at' => '2026-01-01 00:00:00',
            'submitted_at' => '2025-12-01 00:00:00',
            'created_at' => '2025-12-01 00:00:00',
        ]);
        $canonicalId = $this->createVendor($ownerId, 'NEWER', [
            'verification_status' => 'approved',
            'status' => 'active',
            'approved_at' => '2026-02-01 00:00:00',
            'submitted_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00',
        ]);
        $draftId = $this->createVendor($ownerId, 'NEWEST-DRAFT', [
            'verification_status' => 'draft',
            'status' => 'inactive',
            'created_at' => '2026-03-01 00:00:00',
        ]);
        $singletonId = $this->createVendor($singletonOwnerId, 'SINGLE', [
            'verification_status' => 'pending',
            'status' => 'inactive',
        ]);

        $vendorIds = [$olderId, $canonicalId, $draftId, $singletonId];
        $products = [];
        $documents = [];
        $bankAccounts = [];
        $histories = [];
        $memberships = [];
        $commissions = [];
        $settlements = [];
        $subscriptions = [];

        foreach ($vendorIds as $index => $vendorId) {
            $products[$vendorId] = $this->createProduct($vendorId, 'product-'.$index);
            DB::table('inventories')->insert([
                'product_id' => $products[$vendorId],
                'quantity' => 10 + $index,
                'low_stock_threshold' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $documents[$vendorId] = $this->createDocument($vendorId, 'registration', $index);
            $bankAccounts[$vendorId] = $this->createBankAccount($vendorId, sprintf('%04d', 1000 + $index), true);
            $histories[$vendorId] = DB::table('vendor_status_histories')->insertGetId([
                'vendor_id' => $vendorId,
                'previous_verification_status' => 'draft',
                'new_verification_status' => $vendorId === $singletonId ? 'pending' : 'approved',
                'previous_account_status' => 'inactive',
                'new_account_status' => $vendorId === $singletonId ? 'inactive' : 'active',
                'reason' => 'Original history '.$index,
                'created_at' => now(),
            ]);
            $memberships[$vendorId] = $this->createMembership(
                $vendorId,
                $this->createUser('Vendor Staff '.$index),
                'staff',
                'active',
                '2026-01-01 00:00:00',
            );
            $commissions[$vendorId] = DB::table('commissions')->insertGetId([
                'vendor_id' => $vendorId,
                'amount' => 10 + $index,
                'rate' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $settlements[$vendorId] = DB::table('settlements')->insertGetId([
                'vendor_id' => $vendorId,
                'amount' => 20 + $index,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $subscriptions[$vendorId] = DB::table('subscriptions')->insertGetId([
                'vendor_id' => $vendorId,
                'plan' => 'legacy-plan',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customerId,
            'order_number' => 'LEGACY-VENDOR-ORDER',
            'total' => 25,
            'status' => 'processing',
            'payment_status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $orderItemId = DB::table('order_items')->insertGetId([
            'order_id' => $orderId,
            'product_id' => $products[$draftId],
            'name' => 'Legacy Draft Product',
            'unit_price' => 25,
            'quantity' => 1,
            'line_total' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $movementId = DB::table('inventory_movements')->insertGetId([
            'product_id' => $products[$draftId],
            'order_id' => $orderId,
            'type' => 'sale',
            'quantity' => 1,
            'created_at' => now(),
        ]);
        $cartId = $this->createCart($customerId, 'legacy-customer-session');
        $cartItemId = $this->createCartItem($cartId, $products[$draftId], 3);
        $reviewId = DB::table('reviews')->insertGetId([
            'user_id' => $customerId,
            'product_id' => $products[$draftId],
            'rating' => 5,
            'comment' => 'Keep this review',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration('2026_11_24_000003_extend_vendor_workflows.php')->up();

        $this->assertDatabaseHas('vendors', ['id' => $canonicalId, 'owner_user_id' => $ownerId]);
        $this->assertDatabaseMissing('vendors', ['id' => $olderId]);
        $this->assertDatabaseMissing('vendors', ['id' => $draftId]);
        $this->assertDatabaseHas('vendors', ['id' => $singletonId, 'owner_user_id' => $singletonOwnerId]);
        $this->assertDatabaseCount('vendors', 2);

        $dependentIds = [
            'vendor_documents' => $documents,
            'vendor_bank_accounts' => $bankAccounts,
            'vendor_users' => $memberships,
            'commissions' => $commissions,
            'settlements' => $settlements,
            'subscriptions' => $subscriptions,
        ];

        foreach ($dependentIds as $table => $records) {
            foreach ($records as $vendorId => $recordId) {
                $this->assertDatabaseHas($table, [
                    'id' => $recordId,
                    'vendor_id' => $vendorId === $singletonId ? $singletonId : $canonicalId,
                ]);
            }
        }

        foreach ($products as $vendorId => $productId) {
            $this->assertDatabaseHas('products', [
                'id' => $productId,
                'vendor_id' => $vendorId === $singletonId ? $singletonId : $canonicalId,
            ]);
        }

        foreach ($histories as $vendorId => $historyId) {
            $this->assertDatabaseHas('vendor_status_histories', [
                'id' => $historyId,
                'vendor_id' => $vendorId === $singletonId ? $singletonId : $canonicalId,
                'reason' => 'Original history '.array_search($vendorId, $vendorIds, true),
            ]);
        }

        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $bankAccounts[$canonicalId], 'is_default' => true]);
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $bankAccounts[$olderId], 'is_default' => false]);
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $bankAccounts[$draftId], 'is_default' => false]);
        $this->assertSame(1, DB::table('vendor_bank_accounts')->where('vendor_id', $canonicalId)->where('is_default', true)->count());
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $bankAccounts[$singletonId], 'is_default' => true]);

        $this->assertDatabaseHas('inventories', ['product_id' => $products[$draftId], 'quantity' => 12]);
        $this->assertDatabaseHas('order_items', ['id' => $orderItemId, 'product_id' => $products[$draftId]]);
        $this->assertDatabaseHas('inventory_movements', ['id' => $movementId, 'product_id' => $products[$draftId]]);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItemId, 'product_id' => $products[$draftId], 'quantity' => 3]);
        $this->assertDatabaseHas('reviews', ['id' => $reviewId, 'product_id' => $products[$draftId]]);

        $snapshots = DB::table('vendor_status_histories')
            ->where('reason', 'like', 'Legacy duplicate owner merge:%')
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $snapshots);
        $this->assertDatabaseCount('vendor_status_histories', 6);

        foreach ([$olderId => 'OLDER', $draftId => 'NEWEST-DRAFT'] as $mergedId => $code) {
            $snapshot = $snapshots->first(fn (object $row): bool => str_contains($row->reason, "vendor #{$mergedId} (VND-{$code};"));
            $this->assertNotNull($snapshot);
            $this->assertSame($canonicalId, (int) $snapshot->vendor_id);
            $this->assertSame('approved', $snapshot->new_verification_status);
            $this->assertSame('active', $snapshot->new_account_status);
            $this->assertNull($snapshot->changed_by);
            $this->assertStringContainsString("merged into vendor #{$canonicalId} (VND-NEWER;", $snapshot->reason);
            $this->assertStringContainsString('Reason: enforce one vendor per owner.', $snapshot->reason);
        }

        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
        $this->assertTrue(Schema::hasIndex('vendors', 'vendors_owner_user_id_unique', 'unique'));
        $this->assertTrue(Schema::hasIndex('vendors', ['owner_user_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('vendor_users', ['invitation_token_hash'], 'unique'));
        $this->assertTrue(Schema::hasColumn('inventory_movements', 'user_id'));
        $this->assertTrue(Schema::hasColumn('inventory_movements', 'note'));

        $this->expectException(QueryException::class);
        DB::table('vendors')->insert([
            'owner_user_id' => $ownerId,
            'vendor_code' => 'DUPLICATE-AFTER-UPGRADE',
            'business_name' => 'Must Be Rejected',
            'verification_status' => 'draft',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_vendor_upgrade_keeps_stronger_staff_and_one_legacy_default_account(): void
    {
        $this->createLegacyVendorWorkflowSchema();

        $ownerId = $this->createUser('Staff Collision Owner');
        $canonicalId = $this->createVendor($ownerId, 'STAFF-CANONICAL', [
            'verification_status' => 'approved',
            'status' => 'active',
            'approved_at' => '2026-02-01 00:00:00',
        ]);
        $duplicateId = $this->createVendor($ownerId, 'STAFF-DUPLICATE');

        $activeCandidateId = $this->createUser('Active Candidate');
        $canonicalStrongId = $this->createUser('Canonical Strong Staff');
        $duplicateStrongId = $this->createUser('Duplicate Strong Staff');
        $uniqueStaffId = $this->createUser('Duplicate Only Staff');

        $weakCanonicalMembershipId = $this->createMembership($canonicalId, $activeCandidateId, 'staff', 'invited');
        $strongDuplicateMembershipId = $this->createMembership($duplicateId, $activeCandidateId, 'manager', 'active', '2026-02-02 00:00:00');
        $strongCanonicalMembershipId = $this->createMembership($canonicalId, $canonicalStrongId, 'manager', 'active', '2026-01-01 00:00:00');
        $this->createMembership($duplicateId, $canonicalStrongId, 'staff', 'active', '2026-03-01 00:00:00');
        $weakCanonicalRoleId = $this->createMembership($canonicalId, $duplicateStrongId, 'staff', 'active', '2026-01-01 00:00:00');
        $this->createMembership($duplicateId, $duplicateStrongId, 'manager', 'active', '2026-01-01 00:00:00');
        $uniqueMembershipId = $this->createMembership($duplicateId, $uniqueStaffId, 'staff', 'active', '2026-01-01 00:00:00');

        $firstDefaultId = $this->createBankAccount($duplicateId, '0001', true);
        $secondDefaultId = $this->createBankAccount($duplicateId, '0002', true);
        $canonicalAccountId = $this->createBankAccount($canonicalId, '0003', false);

        $this->migration('2026_11_24_000003_extend_vendor_workflows.php')->up();

        $this->assertDatabaseHas('vendor_users', [
            'id' => $weakCanonicalMembershipId,
            'vendor_id' => $canonicalId,
            'user_id' => $activeCandidateId,
            'staff_role' => 'manager',
            'status' => 'active',
            'joined_at' => '2026-02-02 00:00:00',
        ]);
        $this->assertDatabaseMissing('vendor_users', ['id' => $strongDuplicateMembershipId]);
        $this->assertDatabaseHas('vendor_users', [
            'id' => $strongCanonicalMembershipId,
            'vendor_id' => $canonicalId,
            'user_id' => $canonicalStrongId,
            'staff_role' => 'manager',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('vendor_users', [
            'id' => $weakCanonicalRoleId,
            'vendor_id' => $canonicalId,
            'user_id' => $duplicateStrongId,
            'staff_role' => 'manager',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('vendor_users', [
            'id' => $uniqueMembershipId,
            'vendor_id' => $canonicalId,
            'user_id' => $uniqueStaffId,
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('vendor_users', 4);
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $firstDefaultId, 'vendor_id' => $canonicalId, 'is_default' => true]);
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $secondDefaultId, 'vendor_id' => $canonicalId, 'is_default' => false]);
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $canonicalAccountId, 'vendor_id' => $canonicalId, 'is_default' => false]);
        $this->assertSame(1, DB::table('vendor_bank_accounts')->where('vendor_id', $canonicalId)->where('is_default', true)->count());

        $snapshot = DB::table('vendor_status_histories')->where('reason', 'like', 'Legacy duplicate owner merge:%')->firstOrFail();
        $this->assertStringContainsString("user #{$activeCandidateId} kept membership #{$weakCanonicalMembershipId} (active), removed membership #{$strongDuplicateMembershipId} (active)", $snapshot->reason);
        $this->assertTrue(Schema::hasIndex('vendor_users', ['vendor_id', 'user_id'], 'unique'));
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }

    public function test_vendor_upgrade_uses_newest_id_as_fallback_without_inventing_a_default_account(): void
    {
        $this->createLegacyVendorWorkflowSchema();

        $ownerId = $this->createUser('Fallback Vendor Owner');
        $singletonOwnerId = $this->createUser('Untouched Vendor Owner');
        $fallbackIds = [];

        foreach (['draft', 'pending', 'rejected'] as $status) {
            $fallbackIds[] = $this->createVendor($ownerId, 'FALLBACK-'.strtoupper($status), [
                'verification_status' => $status,
                'status' => 'inactive',
                'submitted_at' => '2026-01-01 00:00:00',
                'created_at' => '2026-01-01 00:00:00',
            ]);
        }

        $canonicalId = end($fallbackIds);
        $productId = $this->createProduct($fallbackIds[0], 'fallback-product');
        $singletonId = $this->createVendor($singletonOwnerId, 'UNTOUCHED', [
            'verification_status' => 'approved',
            'status' => 'active',
            'approved_at' => '2026-04-01 00:00:00',
        ]);
        $singletonAccountId = $this->createBankAccount($singletonId, '9999', true);

        $this->migration('2026_11_24_000003_extend_vendor_workflows.php')->up();

        $this->assertDatabaseHas('vendors', ['id' => $canonicalId, 'owner_user_id' => $ownerId]);
        $this->assertDatabaseHas('products', ['id' => $productId, 'vendor_id' => $canonicalId]);
        $this->assertDatabaseHas('vendors', ['id' => $singletonId, 'owner_user_id' => $singletonOwnerId]);
        $this->assertDatabaseHas('vendor_bank_accounts', ['id' => $singletonAccountId, 'is_default' => true]);
        $this->assertDatabaseCount('vendors', 2);
        $this->assertSame(0, DB::table('vendor_bank_accounts')->where('vendor_id', $canonicalId)->count());
        $this->assertSame(2, DB::table('vendor_status_histories')->where('vendor_id', $canonicalId)->count());
        $this->assertTrue(Schema::hasIndex('vendors', ['owner_user_id'], 'unique'));
    }

    public function test_vendor_consolidation_rolls_back_all_data_when_a_dependent_update_fails(): void
    {
        $this->createLegacyVendorWorkflowSchema();

        $ownerId = $this->createUser('Rollback Vendor Owner');
        $canonicalId = $this->createVendor($ownerId, 'ROLLBACK-CANONICAL', [
            'verification_status' => 'approved',
            'status' => 'active',
            'approved_at' => '2026-02-01 00:00:00',
        ]);
        $duplicateId = $this->createVendor($ownerId, 'ROLLBACK-DUPLICATE');
        $canonicalProductId = $this->createProduct($canonicalId, 'rollback-canonical-product');
        $duplicateProductId = $this->createProduct($duplicateId, 'rollback-duplicate-product');
        $documentId = $this->createDocument($duplicateId, 'registration', 1);
        $membershipId = $this->createMembership($duplicateId, $this->createUser('Rollback Staff'), 'staff', 'active');
        $historyId = DB::table('vendor_status_histories')->insertGetId([
            'vendor_id' => $duplicateId,
            'reason' => 'Original rollback history',
            'created_at' => now(),
        ]);

        DB::statement("CREATE TRIGGER fail_vendor_document_merge BEFORE UPDATE OF vendor_id ON vendor_documents
            BEGIN SELECT RAISE(ABORT, 'Forced vendor document merge failure'); END");

        try {
            $this->migration('2026_11_24_000003_extend_vendor_workflows.php')->up();
            $this->fail('The forced dependent-row failure should abort the migration.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('Forced vendor document merge failure', $exception->getMessage());
        }

        $this->assertDatabaseCount('vendors', 2);
        $this->assertDatabaseHas('vendors', ['id' => $canonicalId, 'owner_user_id' => $ownerId]);
        $this->assertDatabaseHas('vendors', ['id' => $duplicateId, 'owner_user_id' => $ownerId]);
        $this->assertDatabaseHas('products', ['id' => $canonicalProductId, 'vendor_id' => $canonicalId]);
        $this->assertDatabaseHas('products', ['id' => $duplicateProductId, 'vendor_id' => $duplicateId]);
        $this->assertDatabaseHas('vendor_documents', ['id' => $documentId, 'vendor_id' => $duplicateId]);
        $this->assertDatabaseHas('vendor_users', ['id' => $membershipId, 'vendor_id' => $duplicateId]);
        $this->assertDatabaseHas('vendor_status_histories', ['id' => $historyId, 'vendor_id' => $duplicateId]);
        $this->assertDatabaseCount('vendor_status_histories', 1);
        $this->assertFalse(Schema::hasColumn('vendor_users', 'invited_email'));
        $this->assertFalse(Schema::hasIndex('vendors', 'vendors_owner_user_id_unique', 'unique'));
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }

    public function test_vendor_scope_upgrade_backfills_only_single_vendor_fulfillment(): void
    {
        $this->createLegacyVendorWorkflowSchema();
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('gateway', 64)->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('transaction_id', 128)->nullable();
            $table->timestamps();
        });
        Schema::create('shippings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('carrier', 128)->nullable();
            $table->string('tracking_number', 128)->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();
        });

        $customerId = $this->createUser('Fulfillment Customer');
        $vendorA = $this->createVendor($this->createUser('Fulfillment Vendor A'), 'FULFILL-A');
        $vendorB = $this->createVendor($this->createUser('Fulfillment Vendor B'), 'FULFILL-B');
        $productA = $this->createProduct($vendorA, 'fulfillment-product-a');
        $productB = $this->createProduct($vendorB, 'fulfillment-product-b');
        $singleOrderId = DB::table('orders')->insertGetId([
            'user_id' => $customerId, 'order_number' => 'LEGACY-SINGLE-FULFILLMENT', 'total' => 25,
            'status' => 'processing', 'payment_status' => 'unpaid', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $mixedOrderId = DB::table('orders')->insertGetId([
            'user_id' => $customerId, 'order_number' => 'LEGACY-MIXED-FULFILLMENT', 'total' => 50,
            'status' => 'processing', 'payment_status' => 'unpaid', 'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ([[$singleOrderId, $productA], [$mixedOrderId, $productA], [$mixedOrderId, $productB]] as [$orderId, $productId]) {
            DB::table('order_items')->insert([
                'order_id' => $orderId, 'product_id' => $productId, 'name' => 'Legacy fulfillment item',
                'unit_price' => 25, 'quantity' => 1, 'line_total' => 25, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ([$singleOrderId, $mixedOrderId] as $orderId) {
            DB::table('payments')->insert([
                'order_id' => $orderId, 'amount' => $orderId === $singleOrderId ? 25 : 50,
                'gateway' => 'cod', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('shippings')->insert([
                'order_id' => $orderId, 'carrier' => 'Legacy Courier', 'tracking_number' => 'LEGACY-'.$orderId,
                'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $migration = $this->migration('2026_11_24_000005_add_vendor_scope_to_fulfillment_tables.php');
        $migration->up();

        $this->assertDatabaseHas('order_items', ['order_id' => $singleOrderId, 'vendor_id' => $vendorA]);
        $this->assertDatabaseHas('order_items', ['order_id' => $mixedOrderId, 'product_id' => $productA, 'vendor_id' => $vendorA]);
        $this->assertDatabaseHas('order_items', ['order_id' => $mixedOrderId, 'product_id' => $productB, 'vendor_id' => $vendorB]);
        $this->assertDatabaseHas('payments', ['order_id' => $singleOrderId, 'vendor_id' => $vendorA]);
        $this->assertDatabaseHas('shippings', ['order_id' => $singleOrderId, 'vendor_id' => $vendorA]);
        $this->assertDatabaseHas('payments', ['order_id' => $mixedOrderId, 'vendor_id' => null]);
        $this->assertDatabaseHas('shippings', ['order_id' => $mixedOrderId, 'vendor_id' => null]);
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));

        $migration->down();

        $this->assertFalse(Schema::hasColumn('order_items', 'vendor_id'));
        $this->assertFalse(Schema::hasColumn('payments', 'vendor_id'));
        $this->assertFalse(Schema::hasColumn('shippings', 'vendor_id'));
    }

    private function migration(string $filename): Migration
    {
        return require database_path('migrations/'.$filename);
    }

    private function createLegacyCartSchema(): void
    {
        $this->createLegacyUsersAndVendors();
        $this->createLegacyProducts();
        $this->createLegacyCartTables();
    }

    private function createLegacyVendorWorkflowSchema(): void
    {
        $this->createLegacyUsersAndVendors();
        $this->createLegacyProducts();
        $this->createLegacyCartTables();

        Schema::create('vendor_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('staff_role', 64)->default('staff');
            $table->string('status', 32)->default('active');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('vendor_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('original_filename', 255);
            $table->string('storage_disk', 64);
            $table->string('storage_path', 500);
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('file_size');
            $table->string('verification_status', 32)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'document_type']);
        });

        Schema::create('vendor_bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('account_holder_name', 150);
            $table->string('bank_name', 150);
            $table->string('branch_name', 150)->nullable();
            $table->text('account_number_encrypted');
            $table->string('account_number_last_four', 4);
            $table->string('routing_number', 64)->nullable();
            $table->string('account_type', 32)->nullable();
            $table->string('verification_status', 32)->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['vendor_id', 'is_default']);
        });

        Schema::create('vendor_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('previous_verification_status', 32)->nullable();
            $table->string('new_verification_status', 32)->nullable();
            $table->string('previous_account_status', 32)->nullable();
            $table->string('new_account_status', 32)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('inventories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->timestamps();
            $table->unique('product_id');
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_number', 64)->unique();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status', 32)->default('pending');
            $table->string('payment_status', 32)->default('unpaid');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name', 255);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            $table->unique(['order_id', 'product_id']);
        });

        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('rate', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('settlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 32)->default('pending');
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('plan', 64)->nullable();
            $table->string('status', 32)->default('inactive');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type', 32);
            $table->unsignedInteger('quantity');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    private function createLegacyUsersAndVendors(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('vendor_code', 32)->unique();
            $table->string('business_name', 255);
            $table->string('business_type', 100)->nullable();
            $table->string('business_email', 255)->nullable();
            $table->string('business_phone', 32)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->text('business_address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('upazila', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->text('business_description')->nullable();
            $table->string('verification_status', 32)->default('draft');
            $table->string('status', 32)->default('inactive');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index('owner_user_id');
            $table->index(['verification_status', 'status']);
        });
    }

    private function createLegacyProducts(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 260)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('status', 32)->default('pending');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
            $table->index(['category_id', 'is_active']);
        });
    }

    private function createLegacyCartTables(): void
    {
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 128)->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'product_id']);
        });
    }

    private function createUser(string $name): int
    {
        return (int) DB::table('users')->insertGetId([
            'name' => $name,
            'email' => str($name)->slug()->append('-'.uniqid())->value().'@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createVendor(int $ownerId, string $code, array $attributes = []): int
    {
        return (int) DB::table('vendors')->insertGetId(array_merge([
            'owner_user_id' => $ownerId,
            'vendor_code' => 'VND-'.$code,
            'business_name' => 'Legacy Shop '.$code,
            'verification_status' => 'draft',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    private function createProduct(int $vendorId, string $slug): int
    {
        return (int) DB::table('products')->insertGetId([
            'vendor_id' => $vendorId,
            'name' => 'Legacy '.$slug,
            'slug' => $slug.'-'.uniqid(),
            'price' => 25,
            'status' => 'approved',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCart(?int $userId, ?string $sessionId): int
    {
        return (int) DB::table('carts')->insertGetId([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCartItem(int $cartId, int $productId, int $quantity): int
    {
        return (int) DB::table('cart_items')->insertGetId([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDocument(int $vendorId, string $type, int $suffix): int
    {
        return (int) DB::table('vendor_documents')->insertGetId([
            'vendor_id' => $vendorId,
            'document_type' => $type,
            'original_filename' => 'legacy-'.$suffix.'.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'vendor-documents/legacy-'.$suffix.'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'verification_status' => 'verified',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createBankAccount(int $vendorId, string $lastFour, bool $isDefault): int
    {
        return (int) DB::table('vendor_bank_accounts')->insertGetId([
            'vendor_id' => $vendorId,
            'account_holder_name' => 'Legacy Account '.$lastFour,
            'bank_name' => 'Legacy Bank',
            'account_number_encrypted' => 'encrypted-'.$lastFour,
            'account_number_last_four' => $lastFour,
            'verification_status' => 'verified',
            'is_default' => $isDefault,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMembership(
        int $vendorId,
        int $userId,
        string $role,
        string $status,
        ?string $joinedAt = null,
    ): int {
        return (int) DB::table('vendor_users')->insertGetId([
            'vendor_id' => $vendorId,
            'user_id' => $userId,
            'staff_role' => $role,
            'status' => $status,
            'joined_at' => $joinedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
