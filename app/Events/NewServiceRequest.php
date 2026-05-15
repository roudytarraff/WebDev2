<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewServiceRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ServiceRequest $serviceRequest) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('office.' . $this->serviceRequest->office_id)];
    }

    public function broadcastAs(): string
    {
        return 'request.new';
    }

    public function broadcastWith(): array
    {
        $this->serviceRequest->loadMissing(['citizen', 'service']);

        return [
            'id'             => $this->serviceRequest->id,
            'request_number' => $this->serviceRequest->request_number,
            'citizen_name'   => $this->serviceRequest->citizen->full_name ?? '',
            'service_name'   => $this->serviceRequest->service->name ?? '',
            'status'         => $this->serviceRequest->status,
            'url'            => route('office.requests.show', $this->serviceRequest->id),
        ];
    }
}
