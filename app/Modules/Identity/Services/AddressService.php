<?php

namespace App\Modules\Identity\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AddressService
{
    private const FIELDS = [
        'type', 'full_name', 'phone', 'address_line',
        'division', 'district', 'upazila', 'postal_code',
    ];

    /**
     * @return Collection<int, Address>
     */
    public function listFor(User $user): Collection
    {
        return $user->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * The first address of a user always becomes the default.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data, bool $makeDefault = false): Address
    {
        return DB::transaction(function () use ($user, $data, $makeDefault): Address {
            $this->lockOwner($user);

            $makeDefault = $makeDefault || ! $user->addresses()->exists();

            if ($makeDefault) {
                $this->clearDefault($user);
            }

            $address = $user->addresses()->make(Arr::only($data, self::FIELDS));
            $address->is_default = $makeDefault;
            $address->save();

            return $address;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, Address $address, array $data, bool $makeDefault = false): Address
    {
        $this->assertOwnedBy($user, $address);

        return DB::transaction(function () use ($user, $address, $data, $makeDefault): Address {
            $this->lockOwner($user);

            $address = $user->addresses()->whereKey($address->getKey())->lockForUpdate()->firstOrFail();
            $address->fill(Arr::only($data, self::FIELDS));

            if ($makeDefault && ! $address->is_default) {
                $this->clearDefault($user);
                $address->is_default = true;
            }

            $address->save();

            return $address;
        });
    }

    public function setDefault(User $user, Address $address): Address
    {
        $this->assertOwnedBy($user, $address);

        return DB::transaction(function () use ($user, $address): Address {
            $this->lockOwner($user);

            $address = $user->addresses()->whereKey($address->getKey())->lockForUpdate()->firstOrFail();

            if (! $address->is_default) {
                $this->clearDefault($user);
                $address->is_default = true;
                $address->save();
            }

            return $address;
        });
    }

    /**
     * Deleting the default address promotes the newest remaining address.
     */
    public function delete(User $user, Address $address): void
    {
        $this->assertOwnedBy($user, $address);

        DB::transaction(function () use ($user, $address): void {
            $this->lockOwner($user);

            $address = $user->addresses()->whereKey($address->getKey())->lockForUpdate()->firstOrFail();
            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $next = $user->addresses()->orderByDesc('id')->first();

                if ($next !== null) {
                    $next->is_default = true;
                    $next->save();
                }
            }
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function assertOwnedBy(User $user, Address $address): void
    {
        if ((int) $address->user_id !== (int) $user->getKey()) {
            throw new AuthorizationException('This address does not belong to the user.');
        }
    }

    /**
     * Serialize concurrent address changes of one user.
     */
    private function lockOwner(User $user): void
    {
        User::query()->whereKey($user->getKey())->lockForUpdate()->first();
    }

    private function clearDefault(User $user): void
    {
        $user->addresses()->where('is_default', true)->update(['is_default' => false]);
    }
}
