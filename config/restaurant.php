<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações de Tempo do Restaurante
    |--------------------------------------------------------------------------
    |
    | Tempos limite em minutos para alertas de atraso nos pedidos.
    | No futuro, essas configurações serão armazenadas por admin no banco de dados.
    |
    */

    'time_limits' => [
        'pending' => 1,        // Tempo limite para pedidos aguardando (minutos)
        'in_production' => 2,  // Tempo limite para pedidos em preparo (minutos)
        'in_transit' => 5,      // Tempo limite para pedidos em trânsito (minutos)
        'closed' => 1,         // Tempo limite para checks fechados aguardando pagamento (minutos)
        'releasing' => 5,      // Tempo limite para mesas em processo de liberação (minutos)
    ],

    'table_filter_default_options' => [
        'mode' => 'AND', // Opções: 'AND' ou 'OR'
        'table' => [], // Status das mesas
        'check' => [], // Status dos checks
        'order' => [], // Status dos pedidos
        'departament' => [] // Departamentos
    ],
];
