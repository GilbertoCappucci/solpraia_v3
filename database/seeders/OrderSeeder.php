<?php

namespace Database\Seeders;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        //Adicionar pedido em check 1 na mesa 1
        $deviceUser = User::find(2)->devices()->first();
        $check = Check::find(1);
        $product = Product::find(1);
        $quantity = 2;

        if(!$check) {
            $table = Table::find(1);
            $check = $this->openCheck($table);
        }

        $this->addOrder(
            $deviceUser,
            $check,
            $product,
            $quantity
        );

        //Adicionar pedido em check 2 na mesa 2
        $deviceUser = User::find(2)->devices()->first();
        $check = Check::find(2);
        $product = Product::find(2);
        $quantity = 1;

        if(!$check) {
            $table = Table::find(2);
            $check = $this->openCheck($table);
        }

        $this->addOrder(
            $deviceUser,
            $check,
            $product,
            $quantity
        );

        //Pedido 1 esta em producao
        $order = Order::find(1);
        $this->inProductionOrder($order);

        //Adicionar pedido em check 1 na mesa 1
        $deviceUser = User::find(2)->devices()->first();
        $check = Check::find(1);
        $product = Product::find(3);
        $quantity = 3;

        $this->addOrder(
            $deviceUser,
            $check,
            $product,
            $quantity
        );

        //Pedido 2 esta em transito
        $order = Order::find(2);
        $this->inTransitOrder($order);

        /*
            for ($i = 0; $i < 10; $i++) {

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
                $val = rand(0, 9);

                switch ($val) {
                    case 0:
                        $this->cancelOrder($order, $check);
                        break;
                    case 1:
                    case 2:
                    case 3:
                        $this->completeOrder($order);
                        break;
                    case 4:
                    case 5:
                    case 6:
                        $this->inTransitOrder($order);
                        break;
                    case 7:
                    case 8:
                        $this->inProductionOrder($order);
                        break;  
                    default:
                        $this->pendingOrder($order);
                        break;
                }

                // Randomly decide to close the check
                if (rand(0, 4) === 0) {
                    $this->closeCheck($check);
                }
            }
        */
        
    }

    private function inProductionOrder($order)
    {
        $order->update([
            'status' => OrderStatusEnum::IN_PRODUCTION->value,
        ]);
    }

    private function inTransitOrder($order)
    {
        $order->update([
            'status' => OrderStatusEnum::IN_TRANSIT->value,
        ]);
    }

    private function pendingOrder($order)
    {
        $order->update([
            'status' => OrderStatusEnum::PENDING->value,
        ]);
    }   

    private function hasTableCheck($table): ?Check
    {
        return Check::where('table_id', $table->id)
                    ->where('status', CheckStatusEnum::OPEN->value)
                    ->first();
    }

    private function openCheck($table)
    {
        $table->update([
            'status' => 'occupied',
        ]);

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
        $table = $check->table;

        $table->update([
            'status' => 'free',
        ]);

        $check->update([
            'status' => CheckStatusEnum::CLOSED->value,
            'closed_at' => now(),
        ]);
    }

    private function addOrder($deviceUser, $check, $product, $quantity)
    {
        //Criar pedido
        $order = Order::factory()->create([
            'user_id' => $deviceUser->id,
            'check_id' => $check->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'status' => 'pending',
        ]);

        //Update Check total
        $this->updateCheckTotal($check, $product->price * $quantity);

        return $order;
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
