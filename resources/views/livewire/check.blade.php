<div>

    <x-flash-message />

    {{-- Botões de navegação e impressão - Escondidos na impressão --}}
    <div class="print:hidden bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
        {{-- Lado Esquerdo --}}
        <div class="flex items-center gap-2">
            <button
                wire:click="goBack"
                class="p-1.5 hover:bg-white/20 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">{{ $table->number }}</span>
                <span class="text-sm opacity-90">{{ $table->name }}</span>
            </div>
        </div>

        {{-- Lado Direito --}}
        <div class="flex items-center gap-2">
            @if($check->status === 'Open')
            <button
                wire:click="goToOrders"
                class="flex items-center gap-1 px-3 py-1.5 border-2 border-white/30 bg-white/10 text-white hover:bg-white/20 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Pedidos
            </button>
            @endif

            <button
                onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
            </button>

            <button
                wire:click="openStatusModal"
                class="flex items-center gap-1 px-3 py-1.5 border-2 border-white/30 bg-white/10 text-white hover:bg-white/20 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Status
            </button>


        </div>
    </div>

    {{-- Layout tipo recibo para impressão --}}
    @php
    // Se o check estiver fechado, mostra apenas entregues; caso contrário, mostra todos exceto pending e canceled
    if ($check->status === 'Closed' || $check->status === 'Paid') {
    $checkOrders = $check->orders->where('status', 'completed')->sortBy('created_at');
    } else {
    $checkOrders = $check->orders->whereNotIn('status', ['pending', 'canceled'])->sortBy('created_at');
    }
    $checkTotal = $checkOrders->sum(fn($order) => $order->price);
    @endphp

    <div class="max-w-sm mx-auto bg-white p-6 print:p-4 print:max-w-none">
        {{-- Cabeçalho do recibo --}}
        <div class="text-center border-b-2 border-dashed border-gray-400 pb-4 mb-4">
            <h1 class="text-2xl font-bold text-gray-900 uppercase">Recibo</h1>
            <p class="text-lg font-semibold text-gray-700">#{{ $check->id }}</p>
        </div>

        {{-- Informações do local --}}
        <div class="mb-4 space-y-1">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Local:</span>
                <span class="text-gray-900 font-bold text-lg">{{ $table->number }} - {{ $table->name }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Abertura:</span>
                <span class="text-gray-900">{{ \Carbon\Carbon::parse($check->created_at)->format('d/m/y - H:i') }}</span>
            </div>
            @if($check->closed_at)
            <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Fechamento:</span>
                <span class="text-gray-900">{{ \Carbon\Carbon::parse($check->updated_at)->format('d/m/y - H:i') }}</span>
            </div>
            @endif
        </div>

        {{-- Linha separadora --}}
        <div class="border-t-2 border-dashed border-gray-400 my-4"></div>

        {{-- Lista de produtos --}}
        @if($checkOrders->count() > 0)
        <div class="mb-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="text-left py-2 font-bold text-gray-700">Item</th>
                        <th class="text-right py-2 font-bold text-gray-700">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkOrders as $order)
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-900">{{ $order->product->name }}</td>
                        <td class="py-2 text-right text-gray-900 font-medium">R$ {{ number_format($order->price, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Linha separadora antes do total --}}
        <div class="border-t-2 border-gray-800 my-4"></div>

        {{-- Total --}}
        <div class="flex justify-between items-center mb-4">
            <span class="text-xl font-bold text-gray-900">TOTAL</span>
            <span class="text-2xl font-bold text-gray-900">R$ {{ number_format($checkTotal, 2, ',', '.') }}</span>
        </div>

        {{-- Quantidade de itens --}}
        <div class="text-center text-gray-600 text-sm">
            {{ $checkOrders->count() }} {{ $checkOrders->count() === 1 ? 'item' : 'itens' }}
        </div>
        @else
        <div class="text-center text-gray-500 py-8">
            Nenhum item na comanda
        </div>
        @endif

        {{-- QR Code PIX --}}
        @if($pix_enabled && isset($pixPayload) && $pixPayload)
        <div class="flex flex-col items-center justify-center mt-6 mb-4">
            <p class="text-sm font-bold text-gray-900 mb-2">Pagamento via PIX</p>

            {{-- Carrega QRious apenas se necessário --}}
            <script>
                if (!window.QRious) {
                    var script = document.createElement('script');
                    script.src = "https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js";
                    document.head.appendChild(script);
                }
            </script>

            <div wire:key="pix-qr-{{ $pixKey }}-{{ md5($pixPayload) }}"
                x-data="{ 
                        initQR() {
                            if (window.QRious) {
                                new QRious({
                                    element: this.$refs.qrcode,
                                    value: '{{ $pixPayload }}',
                                    size: 150
                                });
                            } else {
                                setTimeout(() => this.initQR(), 100);
                            }
                        }
                    }"
                x-init="initQR()">
                <canvas x-ref="qrcode"></canvas>
            </div>

            <p class="text-[10px] text-center mt-1 text-gray-500">
                Aponte a câmera do seu celular
            </p>
        </div>
        @endif

        {{-- Linha separadora final --}}
        <div class="border-t-2 border-dashed border-gray-400 mt-6 pt-4">
            <p class="text-center text-xs text-gray-500">Obrigado pela preferência!</p>
        </div>
    </div>

    {{-- Resumo dos Pedidos por Status (apenas quando check estiver aberto e na tela, escondido na impressão) --}}
    @if($check->status === 'Open')
    <div class="print:hidden bg-gray-50 p-4 space-y-3 mt-6">
        {{-- Pedidos Aguardando --}}
        @if($groupedOrders['pending']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-900">AGUARDANDO</h3>
                </div>
                <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['pending']->count() }} {{ $groupedOrders['pending']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
            </div>
            <div class="space-y-2">
                @foreach($groupedOrders['pending'] as $order)
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">R$ {{ number_format($order->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pedidos Em Preparo --}}
        @if($groupedOrders['inProduction']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-900">EM PREPARO</h3>
                </div>
                <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['inProduction']->count() }} {{ $groupedOrders['inProduction']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
            </div>
            <div class="space-y-2">
                @foreach($groupedOrders['inProduction'] as $order)
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">R$ {{ number_format($order->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pedidos Em Trânsito --}}
        @if($groupedOrders['inTransit']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-900">EM TRÂNSITO</h3>
                </div>
                <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['inTransit']->count() }} {{ $groupedOrders['inTransit']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
            </div>
            <div class="space-y-2">
                @foreach($groupedOrders['inTransit'] as $order)
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">R$ {{ number_format($order->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pedidos Entregues --}}
        @if($groupedOrders['delivered']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-900">ENTREGUE</h3>
                </div>
                <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['delivered']->count() }} {{ $groupedOrders['delivered']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
            </div>
            <div class="space-y-2">
                @foreach($groupedOrders['delivered'] as $order)
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">R$ {{ number_format($order->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pedidos Cancelados --}}
        @if($groupedOrders['canceled']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4 opacity-60">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-900">CANCELADO</h3>
                </div>
                <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['canceled']->count() }} {{ $groupedOrders['canceled']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
            </div>
            <div class="space-y-2">
                @foreach($groupedOrders['canceled'] as $order)
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 line-through">{{ $order->product->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 line-through">R$ {{ number_format($order->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Modal Alterar Status do Check --}}
    @if($showStatusModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeStatusModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Alterar Status do Check</h3>
                <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <x-check-status-selector
                    :check="$check"
                    :newCheckStatus="$newCheckStatus"
                    :pendingCount="$groupedOrders['pending']->count()"
                    :inProductionCount="$groupedOrders['inProduction']->count()"
                    :inTransitCount="$groupedOrders['inTransit']->count()"
                    :checkStatusAllowed="$checkStatusAllowed" />

                <div class="flex gap-3">
                    <button
                        wire:click="closeStatusModal"
                        class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                        Cancelar
                    </button>
                    <button
                        wire:click="updateCheckStatus"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold transition shadow-lg">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Estilos específicos para impressão --}}
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            /* Oculta tudo que não deve ser impresso */
            .print\:hidden {
                display: none !important;
            }

            /* Ajusta o layout para impressora térmica */
            .max-w-sm {
                max-width: 100% !important;
                width: 80mm;
                /* Largura típica de impressora térmica */
            }

            /* Remove cores de fundo para economizar tinta */
            * {
                background: white !important;
                color: black !important;
            }

            /* Ajusta tamanhos de fonte para impressão */
            body {
                font-size: 12pt;
                line-height: 1.3;
            }

            /* Remove sombras e efeitos */
            * {
                box-shadow: none !important;
                text-shadow: none !important;
            }

            /* Garante quebras de página adequadas */
            table {
                page-break-inside: avoid;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</div>