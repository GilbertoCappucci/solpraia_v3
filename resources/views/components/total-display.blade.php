@props(['total'])

<div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-4 border-2 border-orange-200">
    <div class="flex items-center justify-between">
        <span class="mr-2 text-gray-600 font-semibold">TOTAL</span>
        <span class="text-3xl font-bold text-orange-600">R$ {{ number_format($total, 2, ',', '.') }}</span>
    </div>
</div>
