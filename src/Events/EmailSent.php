<?php

namespace FurkanMeclis\PayTRLink\Events;

use FurkanMeclis\PayTRLink\Data\PayTRResponseData;
use FurkanMeclis\PayTRLink\Data\SendEmailData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly SendEmailData $sendEmailData,
        public readonly PayTRResponseData $response
    ) {}
}
