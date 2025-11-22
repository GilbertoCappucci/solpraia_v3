<?php

namespace Database\Seeders;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        for ($i = 0; $i < 50; $i++) {

            $user = User::where('role', RoleEnum::ADMIN->value)->inRandomOrder()->first();
            $deviceUser = $user->devices()->inRandomOrder()->first();
            $tables = $user->tables()->where('active', true)->get();

            if ($tables->isEmpty()) {
                continue; // Skip if no active tables
            }

            $table = $tables->random();

            $check = $this->hasTableCheck($table);

            if (!$check) {
                $check = $this->openCheck($table);
            }

            $product = $user->products()->inRandomOrder()->first();
            $quantity = rand(1, 5);

            $order = $this->addOrder($deviceUser, $check, $product, $quantity);
            $this->updateCheckTotal($check, $product->price * $quantity);

            // Randomly decide to cancel or complete the order
            if (rand(0, 1)) {
                $this->cancelOrder($order, $check);
            } else {
                $this->completeOrder($order);
            }

            // Randomly decide to close the check
            if (rand(0, 4) === 0) {
                $this->closeCheck($check);
            }
        }
        */
    }

    private function hasTableCheck($table): ?Check
    {
        return Check::where('table_id', $table->id)
                    ->where('status', CheckStatusEnum::OPEN->value)
                    ->first();
    }

    private function openCheck($table)
    {
        return Check::factory()->create([
            'table_id' => $table->id,
            'total' => 0,
            'status' => CheckStatusEnum::OPEN->value,
            'opened_at' => now(),
            'closed_at' => null,
        ]);
    }

    private function closeCheck($check)
    {
        $check->update([
            'status' => CheckStatusEnum::CLOSED->value,
            'closed_at' => now(),
        ]);
    }

    private function addOrder($deviceUser, $check, $product, $quantity)
    {
        return Order::factory()->create([
            'user_id' => $deviceUser->id,
            'check_id' => $check->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'status' => 'pending',
        ]);
    }

    private function updateCheckTotal($check, $amount)
    {
        $check->update([
            'total' => $check->total + $amount,
        ]);
    }

    private function cancelOrder($order, $check)
    {
        $order->update([
            'status' => OrderStatusEnum::CANCELED->value,
        ]);

        $check->update([
            'total' => $check->total - ($order->product->price * $order->quantity),
        ]);
    }

    private function completeOrder($order)
    {
        $order->update([
            'status' => OrderStatusEnum::COMPLETED->value,
        ]);
    }


}
