<?php

namespace App\Events;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentUploaded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public RequestDocument $document
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('office.' . $this->serviceRequest->office_id)];
    }

    public function broadcastAs(): string
    {
        return 'document.uploaded';
    }

    public function broadcastWith(): array
    {
        return [
            'request_number' => $this->serviceRequest->request_number,
            'file_name'      => $this->document->file_name,
            'uploaded_by'    => $this->document->uploadedBy->full_name ?? '',
            'url'            => route('office.requests.show', $this->serviceRequest->id),
        ];
    }
}
