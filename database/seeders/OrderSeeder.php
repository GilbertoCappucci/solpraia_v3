<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Models\Check;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * REFATORADO: Agora usa diretamente os Models de forma simples para seeders.
     * Business logic complexa está nos Services (MenuService, OrderService).
     * 
     * Este seeder:
     * - Cria checks manualmente (seeders não precisam da lógica completa de Services)
     * - Cria orders manualmente
     * - IMPORTANTE: Cria registros em order_status_history para tracking de tempo
     * - Calcula totals manualmente (aceitável em seeders)
     */
    public function run(): void
    {
        // Usuário para todos os pedidos
        $user = User::find(2);

        // ==================================================================
        // Cenário 1: Mesa 1 - Pedido em PRODUÇÃO
        // ==================================================================
        $table1 = Table::find(1);
        
        // Cria check manualmente
        $check1 = Check::create([
            'table_id' => $table1->id,
            'total' => 0,
            'status' => 'open',
            'opened_at' => now()->subMinutes(10),
        ]);
        
        $table1->update(['status' => 'occupied']);
        
        // Cria pedido
        $product1 = Product::find(1);
        $order1 = Order::create([
            'user_id' => $user->id,
            'check_id' => $check1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        
        // Cria histórico de status (PENDING) - 10 minutos atrás
        $order1->statusHistory()->create([
            'from_status' => null,
            'to_status' => OrderStatusEnum::PENDING->value,
            'changed_at' => now()->subMinutes(10),
        ]);
        
        // Muda para EM PRODUÇÃO - 5 minutos atrás (apenas histórico)
        $order1->statusHistory()->create([
            'from_status' => OrderStatusEnum::PENDING->value,
            'to_status' => OrderStatusEnum::IN_PRODUCTION->value,
            'changed_at' => now()->subMinutes(5),
        ]);
        
        // Atualiza total do check
        $check1->update(['total' => $product1->price * 2]);

        // ==================================================================
        // Cenário 2: Mesa 2 - Pedido em TRÂNSITO
        // ==================================================================
        $table2 = Table::find(2);
        
        $check2 = Check::create([
            'table_id' => $table2->id,
            'total' => 0,
            'status' => 'open',
            'opened_at' => now()->subMinutes(8),
        ]);
        
        $table2->update(['status' => 'occupied']);
        
        $product2 = Product::find(2);
        $order2 = Order::create([
            'user_id' => $user->id,
            'check_id' => $check2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);
        
        // Histórico completo: PENDING -> PRODUCTION -> TRANSIT
        $order2->statusHistory()->create([
            'from_status' => null,
            'to_status' => OrderStatusEnum::PENDING->value,
            'changed_at' => now()->subMinutes(8),
        ]);
        
        $order2->statusHistory()->create([
            'from_status' => OrderStatusEnum::PENDING->value,
            'to_status' => OrderStatusEnum::IN_PRODUCTION->value,
            'changed_at' => now()->subMinutes(3),
        ]);
        
        $order2->statusHistory()->create([
            'from_status' => OrderStatusEnum::IN_PRODUCTION->value,
            'to_status' => OrderStatusEnum::IN_TRANSIT->value,
            'changed_at' => now()->subMinutes(1),
        ]);
        
        $check2->update(['total' => $product2->price]);

        // ==================================================================
        // Cenário 3: Adicionar mais um pedido PENDENTE na Mesa 1
        // ==================================================================
        $product3 = Product::find(3);
        $order3 = Order::create([
            'user_id' => $user->id,
            'check_id' => $check1->id,
            'product_id' => $product3->id,
            'quantity' => 3,
        ]);
        
        $order3->statusHistory()->create([
            'from_status' => null,
            'to_status' => OrderStatusEnum::PENDING->value,
            'changed_at' => now(),
        ]);
        
        // Atualiza total do check1
        $check1->update(['total' => $check1->total + ($product3->price * 3)]);
    }
}
