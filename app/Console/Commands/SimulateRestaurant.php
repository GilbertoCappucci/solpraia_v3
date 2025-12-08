<?php

namespace App\Console\Commands;

use App\Models\Table;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\MenuService;
use App\Enums\OrderStatusEnum;
use App\Enums\CheckStatusEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateRestaurant extends Command
{
    protected $signature = 'simulate:restaurant 
                            {--mode=auto : Modo de simulação (auto, interactive, stress)}
                            {--tables=5 : Número de mesas ativas}
                            {--duration=10 : Duração em minutos}
                            {--speed=1 : Velocidade (1=normal, 2=2x, 10=10x)}
                            {--no-clear : Não limpa dados antes de iniciar}
                            {--config : Exibir configurações atuais}';

    protected $description = 'Simula operação completa do restaurante com clientes, pedidos e fluxo de trabalho';

    protected $running = true;
    protected $stats = [
        'customers' => 0,
        'orders' => 0,
        'completed' => 0,
        'revenue' => 0,
        'canceled' => 0,
        'startTime' => null,
    ];
    
    protected OrderService $orderService;
    protected MenuService $menuService;
    protected $activeTables = [];
    protected $products = [];
    protected $config = [];

    public function __construct(OrderService $orderService, MenuService $menuService)
    {
        parent::__construct();
        $this->orderService = $orderService;
        $this->menuService = $menuService;
    }

    public function handle()
    {
        // Carrega configurações
        $this->config = config('simulator');
        
        // Se --config, apenas exibe configurações
        if ($this->option('config')) {
            $this->displayConfig();
            return 0;
        }
        
        $this->displayWelcome();
        
        if (!$this->confirmStart()) {
            $this->info('Simulação cancelada.');
            return 0;
        }
        
        $this->prepare();
        $this->runSimulation();
        $this->displayFinalStats();
        
        return 0;
    }
    
    protected function displayConfig()
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════╗');
        $this->line('║           ⚙️  CONFIGURAÇÕES DO SIMULADOR ⚙️             ║');
        $this->line('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $this->info('📁 Arquivo: config/simulator.php');
        $this->newLine();
        
        $this->table(
            ['Configuração', 'Valor'],
            [
                ['Tempo de Preparo (min-max)', $this->config['timing']['preparation_time']['min'] . 's - ' . $this->config['timing']['preparation_time']['max'] . 's'],
                ['Tempo de Entrega (min-max)', $this->config['timing']['delivery_time']['min'] . 's - ' . $this->config['timing']['delivery_time']['max'] . 's'],
                ['Permanência do Cliente', $this->config['timing']['customer_stay']['min'] . 'min - ' . $this->config['timing']['customer_stay']['max'] . 'min'],
                ['', ''],
                ['Chance Novo Cliente', $this->config['probabilities']['new_customer'] . '%'],
                ['Chance Mais Pedidos', $this->config['probabilities']['add_order'] . '%'],
                ['Chance Avançar Status', $this->config['probabilities']['advance_order'] . '%'],
                ['Chance Pagamento', $this->config['probabilities']['checkout'] . '%'],
                ['', ''],
                ['Pedidos Inicial (min-max)', $this->config['orders']['initial_order']['min'] . ' - ' . $this->config['orders']['initial_order']['max']],
                ['Pedidos Adicionais (min-max)', $this->config['orders']['additional_order']['min'] . ' - ' . $this->config['orders']['additional_order']['max']],
                ['Máximo Pedidos/Mesa', $this->config['orders']['max_orders_per_table']],
            ]
        );
        
        $this->newLine();
        $this->info('💡 Para alterar, edite o arquivo: config/simulator.php');
    }

    protected function displayWelcome()
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════╗');
        $this->line('║         🍽️  SIMULADOR DE RESTAURANTE 🍽️                ║');
        $this->line('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $mode = match($this->option('mode')) {
            'interactive' => '🎮 Interativo',
            'stress' => '🔥 Teste de Stress',
            default => '🤖 Automático'
        };
        
        $this->info("Modo: {$mode}");
        $this->info("Mesas: {$this->option('tables')}");
        $this->info("Duração: {$this->option('duration')} minutos");
        $this->info("Velocidade: {$this->option('speed')}x");
        $this->newLine();
    }

    protected function confirmStart()
    {
        if (!$this->option('no-clear')) {
            $this->warn('⚠️  Esta simulação vai limpar todos os checks e pedidos existentes!');
            return $this->confirm('Deseja continuar?', true);
        }
        return true;
    }

    protected function prepare()
    {
        $this->info('🔧 Preparando simulação...');
        
        if (!$this->option('no-clear') && $this->config['advanced']['auto_cleanup']) {
            $this->cleanupData();
        }
        
        $this->products = Product::with('category')->get();
        $this->stats['startTime'] = now();
        
        $this->info('✅ Pronto para iniciar!');
        $this->newLine();
        sleep(1);
    }

    protected function cleanupData()
    {
        DB::table('order_status_history')->delete();
        DB::table('orders')->delete();
        DB::table('checks')->delete();
        Table::query()->update(['status' => 'free']);
        
        $this->line('   Dados limpos...');
    }

    protected function runSimulation()
    {
        $endTime = now()->addMinutes((int) $this->option('duration'));
        $iteration = 0;
        
        $this->info('🚀 Iniciando simulação...');
        $this->newLine();
        
        while (now()->lt($endTime) && $this->running) {
            $iteration++;
            
            // Simula ações aleatórias
            $this->simulateRandomAction();
            
            // Atualiza status dos pedidos existentes
            $this->updateOrdersProgress();
            
            // Exibe estatísticas a cada 5 iterações
            if ($iteration % 5 === 0) {
                $this->displayLiveStats();
            }
            
            // Sleep baseado na velocidade
            $sleepTime = $this->config['advanced']['iteration_sleep'] / $this->option('speed');
            usleep((int) $sleepTime);
            
            // Verifica se deve parar (Ctrl+C)
            if ($this->shouldStop()) {
                break;
            }
        }
    }

    protected function simulateRandomAction()
    {
        $action = rand(1, 100);
        
        $newCustomerChance = $this->config['probabilities']['new_customer'];
        $addOrderChance = $newCustomerChance + $this->config['probabilities']['add_order'];
        $advanceOrderChance = $addOrderChance + $this->config['probabilities']['advance_order'];
        $checkoutChance = $advanceOrderChance + $this->config['probabilities']['checkout'];
        
        if ($action <= $newCustomerChance && count($this->activeTables) < $this->option('tables')) {
            // Novo cliente chega
            $this->simulateNewCustomer();
        } elseif ($action <= $addOrderChance && count($this->activeTables) > 0) {
            // Cliente faz mais pedidos
            $this->simulateAddOrder();
        } elseif ($action <= $advanceOrderChance && count($this->activeTables) > 0) {
            // Avançar status de pedido
            $this->simulateAdvanceOrder();
        } elseif ($action <= $checkoutChance && count($this->activeTables) > 0) {
            // Cliente paga e sai
            $this->simulateCheckout();
        }
    }

    protected function simulateNewCustomer()
    {
        $freeTables = Table::where('status', 'free')->get();
        
        if ($freeTables->isEmpty()) {
            return;
        }
        
        $table = $freeTables->random();
        $table->update(['status' => 'occupied']);
        
        // Cria check manualmente
        $check = \App\Models\Check::create([
            'table_id' => $table->id,
            'total' => 0,
            'status' => CheckStatusEnum::OPEN->value,
            'opened_at' => now(),
        ]);
        
        // Cria primeiro pedido
        $initialOrders = rand(
            $this->config['orders']['initial_order']['min'],
            $this->config['orders']['initial_order']['max']
        );
        $this->createRandomOrders($table->id, $check, $initialOrders);
        
        $this->activeTables[$table->id] = [
            'table' => $table,
            'check' => $check,
            'arrived_at' => now(),
        ];
        
        $this->stats['customers']++;
        
        $this->line("🚶 <fg=green>Cliente chegou</> - Mesa {$table->number}");
    }

    protected function simulateAddOrder()
    {
        if (empty($this->activeTables)) {
            return;
        }
        
        $tableData = collect($this->activeTables)->random();
        $table = $tableData['table'];
        $check = $this->orderService->findOrCreateCheck($table->id);
        
        if ($check && $check->status === CheckStatusEnum::OPEN->value) {
            $additionalOrders = rand(
                $this->config['orders']['additional_order']['min'],
                $this->config['orders']['additional_order']['max']
            );
            $this->createRandomOrders($table->id, $check, $additionalOrders);
            $this->line("📝 <fg=cyan>Mais pedidos</> - Mesa {$table->number}");
        }
    }

    protected function simulateAdvanceOrder()
    {
        if (empty($this->activeTables)) {
            return;
        }
        
        $tableData = collect($this->activeTables)->random();
        $check = $this->orderService->findOrCreateCheck($tableData['table']->id);
        
        if (!$check) {
            return;
        }
        
        $orders = $check->orders()
            ->whereIn('orders.id', function($query) {
                $query->select('order_id')
                    ->from('order_status_history')
                    ->whereIn('to_status', [
                        OrderStatusEnum::PENDING->value,
                        OrderStatusEnum::IN_PRODUCTION->value,
                        OrderStatusEnum::IN_TRANSIT->value
                    ])
                    ->whereRaw('changed_at = (SELECT MAX(changed_at) FROM order_status_history WHERE order_id = orders.id)');
            })
            ->get();
        
        if ($orders->isEmpty()) {
            return;
        }
        
        $order = $orders->random();
        $currentStatus = $order->status;
        
        $nextStatus = match($currentStatus) {
            OrderStatusEnum::PENDING->value => OrderStatusEnum::IN_PRODUCTION->value,
            OrderStatusEnum::IN_PRODUCTION->value => OrderStatusEnum::IN_TRANSIT->value,
            OrderStatusEnum::IN_TRANSIT->value => OrderStatusEnum::COMPLETED->value,
            default => null
        };
        
        if ($nextStatus) {
            // Simula o tempo de preparo ou entrega
            if ($currentStatus === OrderStatusEnum::IN_PRODUCTION->value) {
                $this->simulateDelay('preparation_time');
            } elseif ($currentStatus === OrderStatusEnum::IN_TRANSIT->value) {
                $this->simulateDelay('delivery_time');
            }
            
            $this->orderService->updateOrderStatus($order->id, $nextStatus);
            
            if ($nextStatus === OrderStatusEnum::COMPLETED->value) {
                $this->stats['completed']++;
                $this->stats['revenue'] += $order->product->price * $order->quantity;
            }
            
            $statusLabel = match($nextStatus) {
                OrderStatusEnum::IN_PRODUCTION->value => '👨‍🍳 Preparando',
                OrderStatusEnum::IN_TRANSIT->value => '🚶 Saiu',
                OrderStatusEnum::COMPLETED->value => '✅ Entregue',
                default => ''
            };
            
            $this->line("{$statusLabel} - {$order->product->name} (Mesa {$tableData['table']->number})");
        }
    }

    protected function simulateCheckout()
    {
        if (empty($this->activeTables)) {
            return;
        }
        
        $tableData = collect($this->activeTables)->random();
        $table = $tableData['table'];
        $check = $this->orderService->findOrCreateCheck($table->id);
        
        if (!$check) {
            return;
        }
        
        // Verifica se todos pedidos foram entregues
        $pendingCount = $check->orders()
            ->whereIn('orders.id', function($query) {
                $query->select('order_id')
                    ->from('order_status_history')
                    ->whereIn('to_status', [
                        OrderStatusEnum::PENDING->value,
                        OrderStatusEnum::IN_PRODUCTION->value,
                        OrderStatusEnum::IN_TRANSIT->value
                    ])
                    ->whereRaw('changed_at = (SELECT MAX(changed_at) FROM order_status_history WHERE order_id = orders.id)');
            })
            ->count();
        
        if ($pendingCount > 0) {
            return; // Ainda há pedidos não entregues
        }
        
        // Processo de pagamento: Open -> Closed -> Paid
        if ($check->status === CheckStatusEnum::OPEN->value) {
            $this->orderService->updateStatuses($table, $check, 'occupied', CheckStatusEnum::CLOSED->value);
        } elseif ($check->status === CheckStatusEnum::CLOSED->value) {
            $this->orderService->updateStatuses($table, $check, 'free', CheckStatusEnum::PAID->value);
            
            $duration = $tableData['arrived_at']->diffInMinutes(now());
            $this->line("💰 <fg=yellow>Cliente pagou</> - Mesa {$table->number} (R$ " . number_format($check->total, 2, ',', '.') . ") - {$duration}min");
            
            unset($this->activeTables[$table->id]);
        }
    }

    protected function updateOrdersProgress()
    {
        // Automaticamente avança alguns pedidos
        $autoAdvanceChance = $this->config['probabilities']['auto_advance'];
        if (rand(1, 100) <= $autoAdvanceChance) {
            $this->simulateAdvanceOrder();
        }
    }

    protected function createRandomOrders($tableId, $check, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            /** @var \App\Models\Product $product */
            $product = $this->products->random();
            $quantity = rand(
                $this->config['orders']['quantity_per_product']['min'],
                $this->config['orders']['quantity_per_product']['max']
            );
            
            $order = \App\Models\Order::create([
                'user_id' => 1,
                'check_id' => $check->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            
            // Cria histórico de status inicial
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatusEnum::PENDING->value,
                'changed_by' => 1,
                'changed_at' => now(),
                'created_at' => now(),
            ]);
            
            // Atualiza total do check
            $check->total += ((float) $product->price) * $quantity;
            $check->save();
            
            $this->stats['orders']++;
        }
    }

    protected function displayLiveStats()
    {
        $elapsed = $this->stats['startTime'] ? $this->stats['startTime']->diffInSeconds(now()) : 0;
        $remaining = ((int) $this->option('duration') * 60) - $elapsed;
        
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("📊 <fg=bright-white>ESTATÍSTICAS</>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("👥 Clientes atendidos: <fg=cyan>{$this->stats['customers']}</>");
        $this->line("📝 Pedidos criados: <fg=cyan>{$this->stats['orders']}</>");
        $this->line("✅ Pedidos completados: <fg=green>{$this->stats['completed']}</>");
        $this->line("💰 Faturamento: <fg=yellow>R$ " . number_format($this->stats['revenue'], 2, ',', '.') . "</>");
        $this->line("🪑 Mesas ocupadas: <fg=magenta>" . count($this->activeTables) . "/" . $this->option('tables') . "</>");
        $this->line("⏱️  Tempo restante: <fg=bright-white>" . gmdate('i:s', $remaining) . "</>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
    }

    protected function displayFinalStats()
    {
        $elapsed = $this->stats['startTime'] ? $this->stats['startTime']->diffInMinutes(now()) : 0;
        
        $this->newLine(2);
        $this->line('╔══════════════════════════════════════════════════════════╗');
        $this->line('║              📊 RELATÓRIO FINAL 📊                       ║');
        $this->line('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Duração', "{$elapsed} minutos"],
                ['Clientes atendidos', $this->stats['customers']],
                ['Pedidos criados', $this->stats['orders']],
                ['Pedidos completados', $this->stats['completed']],
                ['Taxa de conclusão', $this->stats['orders'] > 0 ? round(($this->stats['completed'] / $this->stats['orders']) * 100, 1) . '%' : '0%'],
                ['Faturamento total', 'R$ ' . number_format($this->stats['revenue'], 2, ',', '.')],
                ['Ticket médio', $this->stats['customers'] > 0 ? 'R$ ' . number_format($this->stats['revenue'] / $this->stats['customers'], 2, ',', '.') : 'R$ 0,00'],
                ['Pedidos/minuto', $elapsed > 0 ? round($this->stats['orders'] / $elapsed, 2) : 0],
            ]
        );
        
        $this->newLine();
        $this->info('✨ Simulação concluída com sucesso!');
    }

    protected function shouldStop()
    {
        // Aqui você pode implementar lógica para detectar Ctrl+C
        // Por enquanto, retorna false
        return false;
    }
    
    /**
     * Simula delay de preparo ou entrega baseado nas configurações
     */
    protected function simulateDelay(string $type): void
    {
        $config = $this->config['timing'][$type];
        $delaySeconds = rand($config['min'], $config['max']);
        
        // Divide pelo speed para acelerar/desacelerar
        $actualDelay = ($delaySeconds * 1000000) / $this->option('speed');
        
        if ($this->config['advanced']['debug_mode']) {
            $this->line("  ⏱️  Delay de {$type}: {$delaySeconds}s (real: " . round($actualDelay/1000000, 2) . "s)");
        }
        
        usleep((int) $actualDelay);
    }
}
