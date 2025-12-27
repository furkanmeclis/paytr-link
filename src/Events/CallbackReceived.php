<?php

namespace FurkanMeclis\PayTRLink\Events;

use FurkanMeclis\PayTRLink\Data\CallbackData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallbackReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly CallbackData $callbackData,
        public readonly bool $isValid
    ) {}
}
