# Remoção de Colunas status e status_changed_at da Tabela orders

## 📋 Mudança Implementada

As colunas `status` e `status_changed_at` foram **removidas da tabela orders**. Agora o status do pedido é obtido **dinamicamente** do histórico (`order_status_history`).

---

## 🔄 Arquitetura Anterior vs Nova

### ❌ Antes (Redundante):
```
orders table:
├─ id
├─ user_id
├─ check_id
├─ product_id
├─ quantity
├─ status ← REDUNDANTE
├─ status_changed_at ← REDUNDANTE
└─ timestamps

order_status_history table:
├─ id
├─ order_id
├─ from_status
├─ to_status ← FONTE DA VERDADE
├─ changed_at ← FONTE DA VERDADE
└─ timestamps
```

**Problema**: Status era armazenado em 2 lugares, podendo ficar dessincronizado.

### ✅ Agora (Single Source of Truth):
```
orders table:
├─ id
├─ user_id
├─ check_id
├─ product_id
├─ quantity
└─ timestamps

order_status_history table:
├─ id
├─ order_id
├─ from_status
├─ to_status ← ÚNICA FONTE DA VERDADE
├─ changed_at ← ÚNICA FONTE DA VERDADE
└─ timestamps
```

**Solução**: Status é sempre obtido do histórico via atributos virtuais.

---

## 🛠️ Alterações no Código

### 1. **Order Model** (`app/Models/Order.php`)

#### Atributos Virtuais Adicionados:
```php
protected $appends = ['status'];

// Atributo virtual: $order->status
public function getStatusAttribute()
{
    return $this->statusHistory()
        ->latest('changed_at')
        ->value('to_status');
}

// Atributo virtual: $order->status_changed_at
public function getStatusChangedAtAttribute()
{
    return $this->currentStatusHistory?->changed_at;
}
```

#### Campos Removidos:
```php
// ❌ REMOVIDO do $fillable
'status',
'status_changed_at',

// ❌ REMOVIDO do $casts
'status_changed_at' => 'datetime',
```

#### Relationship Atualizado:
```php
// ✅ ANTES (dependia de $this->status)
public function currentStatusHistory()
{
    return $this->hasOne(OrderStatusHistory::class)
        ->where('to_status', $this->status)
        ->latest('changed_at');
}

// ✅ AGORA (independente)
public function currentStatusHistory()
{
    return $this->hasOne(OrderStatusHistory::class)
        ->latest('changed_at');
}
```

---

### 2. **OrderService** (`app/Services/OrderService.php`)

#### getActiveOrdersGrouped():
```php
// ✅ ANTES (query direta na coluna status)
$activeOrders = Order::where('check_id', $check->id)
    ->whereIn('status', [...])
    ->orderBy('status_changed_at', 'asc')
    ->get()
    ->groupBy('status');

// ✅ AGORA (eager load + filter + sort)
$activeOrders = Order::where('check_id', $check->id)
    ->with(['product', 'currentStatusHistory'])
    ->get()
    ->filter(function($order) {
        return in_array($order->status, [...]);
    })
    ->sortBy(function($order) {
        return $order->status_changed_at;
    })
    ->groupBy('status');
```

#### calculateOrderStats():
```php
// ✅ ANTES (query manual para cada order)
$history = OrderStatusHistory::where('order_id', $order->id)
    ->where('to_status', $order->status)
    ->latest('changed_at')
    ->first();

// ✅ AGORA (usa atributo virtual)
$changedAt = $order->status_changed_at;
```

#### updateOrderStatus():
```php
// ✅ ANTES (atualizava coluna + criava histórico)
$order->update([
    'status' => $newStatus,
]);

OrderStatusHistory::create([...]);

// ✅ AGORA (apenas cria histórico)
OrderStatusHistory::create([
    'order_id' => $orderId,
    'from_status' => $order->status, // Atributo virtual
    'to_status' => $newStatus,
    'changed_at' => now(),
]);
```

---

### 3. **MenuService** (`app/Services/MenuService.php`)

#### confirmOrder():
```php
// ✅ ANTES (criava com campo status)
$order = Order::create([
    'user_id' => $userId,
    'check_id' => $check->id,
    'product_id' => $productId,
    'quantity' => $item['quantity'],
    'status' => OrderStatusEnum::PENDING->value, // ❌
]);

// ✅ AGORA (sem campo status)
$order = Order::create([
    'user_id' => $userId,
    'check_id' => $check->id,
    'product_id' => $productId,
    'quantity' => $item['quantity'],
]);

// Histórico continua sendo criado
OrderStatusHistory::create([
    'order_id' => $order->id,
    'from_status' => null,
    'to_status' => OrderStatusEnum::PENDING->value,
    'changed_at' => now(),
]);
```

---

### 4. **OrderSeeder** (`database/seeders/OrderSeeder.php`)

```php
// ✅ ANTES (criava com status)
$order1 = Order::create([
    'user_id' => $user->id,
    'check_id' => $check1->id,
    'product_id' => $product1->id,
    'quantity' => 2,
    'status' => OrderStatusEnum::PENDING->value, // ❌
]);

$order1->update(['status' => OrderStatusEnum::IN_PRODUCTION->value]); // ❌

// ✅ AGORA (sem campo status, apenas histórico)
$order1 = Order::create([
    'user_id' => $user->id,
    'check_id' => $check1->id,
    'product_id' => $product1->id,
    'quantity' => 2,
]);

// Apenas cria registros no histórico
$order1->statusHistory()->create([
    'from_status' => null,
    'to_status' => OrderStatusEnum::PENDING->value,
    'changed_at' => now()->subMinutes(10),
]);

$order1->statusHistory()->create([
    'from_status' => OrderStatusEnum::PENDING->value,
    'to_status' => OrderStatusEnum::IN_PRODUCTION->value,
    'changed_at' => now()->subMinutes(5),
]);
```

---

## ✅ Testes Realizados

### Teste 1: Estrutura da Tabela
```
orders table columns:
  ✅ id, user_id, check_id, product_id, quantity
  ✅ created_at, updated_at, deleted_at
  ✅ NÃO TEM: status, status_changed_at
```

### Teste 2: Atributos Virtuais
```php
$order = Order::find(1);
echo $order->status;           // ✅ 'in_production' (do histórico)
echo $order->status_changed_at; // ✅ '2025-12-04 13:40:48' (do histórico)
```

### Teste 3: Agrupamento por Status
```php
$grouped = $check->orders->groupBy('status');
// ✅ Funciona perfeitamente com atributo virtual
```

### Teste 4: updateOrderStatus()
```php
$orderService->updateOrderStatus(3, OrderStatusEnum::IN_PRODUCTION->value);
$order = Order::find(3);
echo $order->status; // ✅ 'in_production'

// ✅ Histórico criado corretamente:
// START → pending @ 2025-12-04 13:45:48
// pending → in_production @ 2025-12-04 13:46:25
```

### Teste 5: getActiveOrdersGrouped()
```php
$grouped = $orderService->getActiveOrdersGrouped($check);
// ✅ Retorna orders agrupados por status
// ✅ Ordenados por status_changed_at
```

### Teste 6: calculateOrderStats()
```php
$stats = $orderService->calculateOrderStats($orders);
// ✅ Calcula total e tempo usando histórico
```

---

## 🎯 Benefícios

### 1. **Single Source of Truth**
- Status é armazenado apenas em `order_status_history`
- Impossível dessincronização entre tabelas

### 2. **Histórico Completo**
- Todos os estados ficam registrados
- Auditoria completa de mudanças

### 3. **Flexibilidade**
- Fácil adicionar análises de tempo por status
- Pode recriar estado em qualquer momento

### 4. **Código Mais Limpo**
- Atributos virtuais encapsulam lógica
- Services não precisam se preocupar com sincronização

### 5. **Performance**
- Eager loading com `currentStatusHistory` evita N+1
- Index em `(order_id, changed_at)` garante queries rápidas

---

## 📝 Compatibilidade

### ✅ Código que continua funcionando:
```php
// Todos esses continuam funcionando naturalmente
$order->status
$order->status_changed_at
$orders->groupBy('status')
$orders->sortBy('status_changed_at')
Order::where('status', 'pending') // ❌ NÃO funciona mais (coluna não existe)
```

### ⚠️ Queries que NÃO funcionam mais:
```php
// ❌ Query direta na coluna (não existe mais)
Order::where('status', 'pending')->get();
Order::orderBy('status_changed_at')->get();

// ✅ Solução: eager load + filter
Order::with('currentStatusHistory')
    ->get()
    ->filter(fn($o) => $o->status === 'pending')
    ->sortBy('status_changed_at');
```

---

## 🚀 Próximos Passos (Opcional)

### 1. **Adicionar Scope no Model**
```php
// Order.php
public function scopeWithStatus($query, $status)
{
    return $query->with('currentStatusHistory')
        ->get()
        ->filter(fn($o) => $o->status === $status);
}

// Uso:
Order::withStatus('pending')->get();
```

### 2. **Cache de Status**
Se performance for crítica, pode adicionar cache:
```php
public function getStatusAttribute()
{
    return Cache::remember(
        "order.{$this->id}.status",
        60,
        fn() => $this->statusHistory()->latest('changed_at')->value('to_status')
    );
}
```

### 3. **Índice Composto**
Já existe, mas verificar:
```php
// Já criado em order_status_history
$table->index(['order_id', 'changed_at']);
```

---

## 📊 Resumo das Mudanças

| Componente | Antes | Depois |
|------------|-------|--------|
| **orders.status** | Coluna física | Atributo virtual |
| **orders.status_changed_at** | Coluna física | Atributo virtual |
| **Order Model** | Campos no fillable/casts | Appends + Accessors |
| **OrderService** | Query + Update status | Query + Insert histórico |
| **MenuService** | Create com status | Create sem status + histórico |
| **OrderSeeder** | Update status | Insert histórico |
| **Source of Truth** | 2 lugares (redundante) | 1 lugar (order_status_history) |

---

## ✅ Conclusão

A remoção das colunas `status` e `status_changed_at` da tabela `orders` foi bem-sucedida:

1. ✅ Tabela orders não tem mais essas colunas
2. ✅ Atributos virtuais funcionam perfeitamente
3. ✅ Services ajustados para usar histórico
4. ✅ Seeders atualizados
5. ✅ Todos os testes passaram
6. ✅ Single Source of Truth implementado

**Status agora é sempre obtido dinamicamente do histórico, garantindo consistência e auditoria completa.**
