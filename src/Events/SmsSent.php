<?php

namespace FurkanMeclis\PayTRLink\Events;

use FurkanMeclis\PayTRLink\Data\PayTRResponseData;
use FurkanMeclis\PayTRLink\Data\SendSmsData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SmsSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly SendSmsData $sendSmsData,
        public readonly PayTRResponseData $response
    ) {}
}
