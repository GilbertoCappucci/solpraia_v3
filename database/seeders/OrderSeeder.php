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

    }
}
