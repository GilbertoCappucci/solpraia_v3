<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>

    {{-- Botões de Status --}}
    <div class="flex flex-wrap gap-2">
        @foreach($this->getStatuses() as $status)
        @php
        $isCurrent = $newTableStatus === $status['value'];
        $isDisabled = $hasActiveCheck && !$isCurrent;
        // Exceção: Se o status for 'close', e houver check, bloqueia
        if ($status['value'] === 'close' && $hasActiveCheck) {
        $isDisabled = true;
        }
        @endphp
        <button
            wire:click="selectStatus('{{ $status['value'] }}')"
            @if($isDisabled) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ $isDisabled ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($isCurrent ? $status['activeColor'] . ' text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            {{ $status['label'] }}
        </button>
        @endforeach
    </div>

    @if($hasActiveCheck)
    <p class="mt-2 text-xs text-orange-600 font-medium">
        <i class="fas fa-info-circle mr-1"></i>
        Status da mesa bloqueado enquanto houver um check ativo.
    </p>
    @endif
</div>
