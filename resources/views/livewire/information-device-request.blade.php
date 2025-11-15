<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Informações do Dispositivo e Requisição</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informações Básicas -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3 text-blue-600">Informações Básicas</h2>
            <div class="space-y-2">
                <div><strong>IP:</strong> {{ $deviceRequest['ip'] }}</div>
                <div><strong>Método:</strong> {{ $deviceRequest['method'] }}</div>
                <div><strong>URL:</strong> <span class="break-all">{{ $deviceRequest['url'] }}</span></div>
                <div><strong>Host:</strong> {{ $deviceRequest['host'] }}</div>
                <div><strong>Esquema:</strong> {{ $deviceRequest['scheme'] }}</div>
                <div><strong>Porta:</strong> {{ $deviceRequest['port'] }}</div>
            </div>
        </div>

        <!-- User Agent -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3 text-green-600">Navegador</h2>
            <div class="space-y-2">
                <div><strong>User Agent:</strong></div>
                <div class="bg-gray-100 p-2 rounded text-sm break-all">{{ $deviceRequest['user_agent'] }}</div>
            </div>
        </div>

        <!-- Headers -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3 text-purple-600">Headers</h2>
            <div class="space-y-2">
                <div><strong>Accept:</strong> <span class="text-sm">{{ $deviceRequest['headers']['accept'] ?? 'N/A' }}</span></div>
                <div><strong>Accept Language:</strong> {{ $deviceRequest['headers']['accept_language'] ?? 'N/A' }}</div>
                <div><strong>Accept Encoding:</strong> {{ $deviceRequest['headers']['accept_encoding'] ?? 'N/A' }}</div>
                <div><strong>Referer:</strong> {{ $deviceRequest['headers']['referer'] ?? 'N/A' }}</div>
                <div><strong>Origin:</strong> {{ $deviceRequest['headers']['origin'] ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Informações do Servidor -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3 text-red-600">Servidor</h2>
            <div class="space-y-2">
                <div><strong>Server Name:</strong> {{ $deviceRequest['server']['server_name'] ?? 'N/A' }}</div>
                <div><strong>Server Software:</strong> {{ $deviceRequest['server']['server_software'] ?? 'N/A' }}</div>
                <div><strong>Request Time:</strong> {{ $deviceRequest['server']['request_time'] ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Debug completo (opcional) -->
    <div class="mt-6 bg-gray-50 rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-3">Debug Completo (JSON)</h2>
        <pre class="bg-gray-800 text-green-400 p-4 rounded text-xs overflow-x-auto">{{ json_encode($deviceRequest, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
