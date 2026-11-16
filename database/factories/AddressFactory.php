<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use App\Modules\Identity\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => AddressType::Home,
            'full_name' => fake()->name(),
            'phone' => '+8801'.fake()->numerify('7########'),
            'address_line' => fake()->streetAddress(),
            'division' => 'Khulna',
            'district' => 'Jashore',
            'upazila' => 'Sadar',
            'postal_code' => '7400',
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => ['is_default' => true]);
    }
}
