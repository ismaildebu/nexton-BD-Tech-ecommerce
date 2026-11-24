<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'email_verified_at' => now(),
            'phone_verified_at' => null,
            'password' => static::$password ??= Hash::make('password'),
            'status' => UserStatus::Active,
            'last_login_at' => null,
            'is_protected' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * A phone-only account (no email).
     */
    public function phoneOnly(?string $phone = null): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
            'email_verified_at' => null,
            'phone' => $phone ?? '+8801'.fake()->unique()->numerify('7########'),
            'phone_verified_at' => now(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => ['status' => UserStatus::Suspended]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => UserStatus::Inactive]);
    }

    public function protectedAccount(): static
    {
        return $this->state(fn (array $attributes) => ['is_protected' => true]);
    }
}
