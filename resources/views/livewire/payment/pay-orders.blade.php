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

            <button
                wire:click="goToOrders"
                class="flex items-center gap-1 px-3 py-1.5 border-2 border-white/30 bg-white/10 text-white hover:bg-white/20 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Pedidos
            </button>


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

    <div class="max-w-sm mx-auto bg-white p-6 print:p-4 print:max-w-none">
        {{-- Cabeçalho do recibo --}}
        <div class="text-center border-b-2 border-dashed border-gray-400 pb-4 mb-4">
            <h1 class="text-2xl font-bold text-gray-900 uppercase">Recibo</h1>
            <p class="text-lg font-semibold text-gray-700">#</p>
        </div>

        {{-- Informações do local --}}
        <div class="mb-4 space-y-1">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Local:</span>
                <span class="text-gray-900 font-bold text-lg">{{ $table->number }} - {{ $table->name }}</span>
            </div>
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
                        <th class="text-center py-2 font-bold text-gray-700">Qut</th>
                        <th class="text-right py-2 font-bold text-gray-700">Preço</th>
                        <th class="text-right py-2 font-bold text-gray-700">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkOrders as $productName => $orders)
                        @foreach($orders as $order)
                        <tr>
                            <td class="py-2 text-gray-900">{{ $productName }}</td>
                            <td class="py-2 text-center text-gray-900">{{ $order->currentStatusHistory->quantity }}</td>
                            <td class="py-2 text-right text-gray-900">R$ {{ number_format($order->first()->price, 2, ',', '.') }}</td>
                            <td class="py-2 text-right text-gray-900 font-medium">R$ {{ number_format($order->currentStatusHistory->quantity * $order->currentStatusHistory->price, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        
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
        @else
        <div class="text-center text-gray-500 mt-6 mb-4">
            Pagamento via PIX indisponível.
        </div>

        @endif

        {{-- Linha separadora final --}}
        <div class="border-t-2 border-dashed border-gray-400 mt-6 pt-4">
            <p class="text-center text-xs text-gray-500">Obrigado pela preferência!</p>
        </div>
    </div>

    {{-- Estilos para impressão --}}
    <link rel="stylesheet" href="{{ asset('css/print.css') }}" media="print">

</div>
