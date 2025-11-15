<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;

class InformationDeviceRequest extends Component
{
    
    public $deviceRequest;

    public function render()
    {
        $request = request();
        
        $this->deviceRequest = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'host' => $request->getHost(),
            'scheme' => $request->getScheme(),
            'port' => $request->getPort(),
            'headers' => [
                'accept' => $request->header('Accept'),
                'accept_language' => $request->header('Accept-Language'),
                'accept_encoding' => $request->header('Accept-Encoding'),
                'referer' => $request->header('Referer'),
                'origin' => $request->header('Origin'),
            ],
            'server' => [
                'server_name' => $request->server('SERVER_NAME'),
                'server_software' => $request->server('SERVER_SOFTWARE'),
                'request_time' => date('Y-m-d H:i:s', $request->server('REQUEST_TIME')),
            ]
        ];

        return view('livewire.information-device-request');
    }
}
