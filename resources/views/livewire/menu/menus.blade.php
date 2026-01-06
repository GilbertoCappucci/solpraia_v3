<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="bg-green-500 text-white px-4 py-3 text-center">
        {{ session('success') }}
    </div>
    @endif
    @if (session()->has('error'))
    <div class="bg-red-500 text-white px-4 py-3 text-center">
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    @livewire('menu.menu-header', [
        'selectedTable' => $selectedTable,
        'currentCheck' => $currentCheck,
        'title' => $title,
    ])

    {{-- Menu Content --}}
    @if($activeMenuId)
        {{-- Search Bar --}}
        @livewire('menu.menu-search', [
            'activeMenuId' => $activeMenuId,
        ])

        {{-- Category Filters --}}
        @livewire('menu.menu-category-filter', [
            'activeMenuId' => $activeMenuId,
            'parentCategories' => $parentCategories,
            'childCategories' => $childCategories,
        ])

        {{-- Product List --}}
        <div class="p-4 pb-32 bg-gray-50">
            @if($this->products->count() > 0)
            <div class="space-y-3">
                @foreach($this->products as $product)
                    <livewire:menu.menu-product-card 
                        :product="$product"
                        :userId="$userId"
                        :cart="$cart"
                        :key="'product-'.$product['id']"
                    />
                @endforeach
            </div>
            @else
            <div class="text-center py-16 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-base font-medium">Nenhum produto encontrado</p>
            </div>
            @endif
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
            <div class="bg-orange-100 p-4 rounded-full mb-4">
                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Cardápio não configurado</h3>
            <p class="text-gray-600 mb-6">É necessário selecionar um Menu Ativo nas configurações globais para visualizar os produtos.</p>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('settings.global') }}" class="bg-orange-600 text-white px-6 py-2.5 rounded-lg font-bold shadow-lg hover:bg-orange-700 transition">
                Configurar agora
            </a>
            @endif
        </div>
    @endif

    {{-- Shopping Cart --}}
    @livewire('menu.menu-cart', [
        'cart' => $cart,
    ])
</div>
