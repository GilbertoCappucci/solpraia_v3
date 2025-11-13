<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\BeachUmbrella;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BeachUmbrella>
 */
class BeachUmbrellaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = $this->getRamdomUserId();

        return [
            'user_id' => $userId,
            'name' => $this->faker->optional()->word(),
            'number' => $this->getNextUmbrellaNumber($userId),
            'active' => $this->faker->boolean(80),
        ];
    }

    public function getRamdomUserId(): int
    {
        return User::where(['role' => RoleEnum::ADMIN->value])
            ->inRandomOrder()
            ->first()->id;
    }

    public function getNextUmbrellaNumber(int $userId): int
    {
        $lastUmbrella = BeachUmbrella::where('user_id', $userId)
            ->orderBy('number', 'desc')
            ->first();
        return $lastUmbrella ? $lastUmbrella->number + 1 : 1;
    }   
}
