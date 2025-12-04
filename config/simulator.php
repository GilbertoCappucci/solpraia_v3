<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configurações de Tempo
    |--------------------------------------------------------------------------
    |
    | Controla os tempos de cada etapa do processo de pedidos
    |
    */

    'timing' => [
        // Tempo médio de preparo na cozinha (em segundos)
        'preparation_time' => [
            'min' => 120,  // Mínimo: 2 minutos
            'max' => 600, // Máximo: 5 minutos
        ],

        // Tempo médio de entrega do garçom (em segundos)
        'delivery_time' => [
            'min' => 60,  // Mínimo: 60 segundos
            'max' => 300,  // Máximo: 5 minutos
        ],

        // Tempo médio que um cliente fica na mesa (em minutos)
        'customer_stay' => [
            'min' => 15,  // Mínimo: 15 minutos
            'max' => 45,  // Máximo: 45 minutos
        ],

        // Intervalo entre pedidos adicionais (em segundos)
        'order_interval' => [
            'min' => 120, // Mínimo: 2 minutos
            'max' => 300, // Máximo: 5 minutos
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Probabilidades de Ações
    |--------------------------------------------------------------------------
    |
    | Chance de cada ação acontecer a cada iteração (0-100)
    |
    */

    'probabilities' => [
        'new_customer' => 25,      // 25% chance de novo cliente chegar
        'add_order' => 10,         // 10% chance de fazer mais pedidos
        'advance_order' => 5,     // 5% chance de avançar status de pedido
        'checkout' => 10,          // 10% chance de cliente pagar e sair
        'auto_advance' => 50,      // 50% chance de avançar pedidos automaticamente
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Pedidos
    |--------------------------------------------------------------------------
    |
    | Controla quantidade e comportamento dos pedidos
    |
    */

    'orders' => [
        // Quantidade de pedidos no primeiro pedido
        'initial_order' => [
            'min' => 1,
            'max' => 3,
        ],

        // Quantidade de pedidos adicionais
        'additional_order' => [
            'min' => 1,
            'max' => 2,
        ],

        // Quantidade por produto
        'quantity_per_product' => [
            'min' => 1,
            'max' => 2,
        ],

        // Máximo de pedidos por mesa durante toda a estadia
        'max_orders_per_table' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Mesas
    |--------------------------------------------------------------------------
    |
    | Controla comportamento das mesas
    |
    */

    'tables' => [
        // Máximo de mesas que podem estar ocupadas simultaneamente (null = sem limite)
        'max_occupied' => null,

        // Prioridade de status (maior = mais chance de ser escolhido)
        'status_priority' => [
            'free' => 100,
            'occupied' => 50,
            'reserved' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Exibição
    |--------------------------------------------------------------------------
    |
    | Controla como as informações são exibidas no console
    |
    */

    'display' => [
        // Intervalo entre atualizações de estatísticas (em iterações)
        'stats_interval' => 5,

        // Mostrar detalhes de cada ação
        'show_actions' => true,

        // Mostrar barra de progresso
        'show_progress' => false,

        // Cores para diferentes status
        'colors' => [
            'customer_arrived' => 'green',
            'order_created' => 'cyan',
            'order_preparing' => 'blue',
            'order_transit' => 'magenta',
            'order_delivered' => 'green',
            'checkout' => 'yellow',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modo de Simulação
    |--------------------------------------------------------------------------
    |
    | Diferentes modos de operação
    |
    */

    'modes' => [
        'auto' => [
            'description' => 'Simulação automática com ações aleatórias',
            'enabled' => true,
        ],
        'interactive' => [
            'description' => 'Usuário controla cada ação manualmente',
            'enabled' => false,
        ],
        'stress' => [
            'description' => 'Teste de carga com máxima atividade',
            'enabled' => false,
            'multiplier' => 3, // Multiplica probabilidades por 3
        ],
        'rush_hour' => [
            'description' => 'Simula horário de pico',
            'enabled' => false,
            'new_customer_boost' => 60, // Aumenta chance de novos clientes para 60%
            'concurrent_orders_boost' => 2, // Clientes fazem 2x mais pedidos
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorias de Produtos Populares
    |--------------------------------------------------------------------------
    |
    | Pesos para escolha de produtos (maior = mais chance)
    |
    */

    'product_weights' => [
        'bebidas' => 40,     // 40% dos pedidos
        'lanches' => 35,     // 35% dos pedidos
        'pratos' => 25,      // 25% dos pedidos
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações Avançadas
    |--------------------------------------------------------------------------
    |
    | Opções avançadas para fine-tuning
    |
    */

    'advanced' => [
        // Sleep entre iterações (microsegundos) - será dividido pela velocidade
        'iteration_sleep' => 500000, // 0.5 segundos

        // Seed aleatório (null = aleatório, número = reproduzível)
        'random_seed' => null,

        // Ativar logs detalhados para debug
        'debug_mode' => false,

        // Salvar estatísticas em arquivo JSON ao final
        'save_stats' => false,
        'stats_file' => storage_path('logs/simulator_stats.json'),

        // Limpar dados antes de iniciar
        'auto_cleanup' => true,

        // ID do usuário para os pedidos
        'user_id' => 1,

        // Percentual de pedidos que podem ser cancelados (apenas status PENDING)
        'cancel_order_chance' => 5, // 5% de chance
    ],

    /*
    |--------------------------------------------------------------------------
    | Horário de Pico (Rush Hour)
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para simular horários de pico
    |
    */

    'rush_hour' => [
        // Horários considerados de pico (array de [início, fim])
        'periods' => [
            ['12:00', '14:00'], // Almoço
            ['19:00', '21:00'], // Jantar
        ],

        // Multiplicador de atividade durante pico
        'activity_multiplier' => 2.5,

        // Durante pico, aumentar ocupação mínima
        'min_occupied_tables' => 0.7, // 70% das mesas
    ],

    /*
    |--------------------------------------------------------------------------
    | Eventos Especiais
    |--------------------------------------------------------------------------
    |
    | Eventos aleatórios que podem acontecer durante a simulação
    |
    */

    'events' => [
        'enabled' => false,

        'types' => [
            'vip_customer' => [
                'probability' => 5,      // 5% de chance
                'order_multiplier' => 3, // Faz 3x mais pedidos
                'tip_bonus' => 0.2,     // 20% de gorjeta extra
            ],
            'kitchen_delay' => [
                'probability' => 3,      // 3% de chance
                'delay_multiplier' => 2, // Dobra tempo de preparo
                'duration' => 5,         // Dura 5 iterações
            ],
            'happy_hour' => [
                'probability' => 2,      // 2% de chance
                'discount' => 0.3,       // 30% de desconto
                'customer_boost' => 50,  // +50% de novos clientes
            ],
        ],
    ],

];
