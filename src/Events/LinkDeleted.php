<?php

namespace FurkanMeclis\PayTRLink\Events;

use FurkanMeclis\PayTRLink\Data\PayTRResponseData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LinkDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $linkId,
        public readonly PayTRResponseData $response
    ) {}
}
