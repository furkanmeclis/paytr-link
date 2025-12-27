<?php

namespace FurkanMeclis\PayTRLink\Events;

use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Data\PayTRResponseData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LinkCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly CreateLinkData $createLinkData,
        public readonly PayTRResponseData $response
    ) {}
}
