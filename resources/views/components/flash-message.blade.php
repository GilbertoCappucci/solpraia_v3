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
