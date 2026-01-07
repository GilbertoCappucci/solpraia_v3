<div>
    <h2 class="text-2xl font-bold mb-4">Pagamento do Pedido #{{ $orderId }}</h2>
    <p>Aqui você pode processar o pagamento do pedido.</p>
    <!-- Adicione mais detalhes e funcionalidades de pagamento conforme necessário -->
    <button
        wire:click="processPayment"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
    >
        Processar Pagamento
    </button>
</div>
