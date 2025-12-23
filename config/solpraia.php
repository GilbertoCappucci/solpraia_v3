<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações de Tempo do Restaurante
    |--------------------------------------------------------------------------
    */

    'table_filter' => [
        'mode' => 'AND', // Opções: 'AND' ou 'OR'
        'table' => [], // Status das mesas
        'check' => [], // Status dos checks
        'order' => [], // Status dos pedidos
        'departament' => [] // Departamentos
    ],

    'polling_interval' => 10000000,
];
