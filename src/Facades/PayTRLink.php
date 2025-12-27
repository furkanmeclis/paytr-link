<?php

namespace FurkanMeclis\PayTRLink\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FurkanMeclis\PayTRLink\PayTRLinkService
 */
class PayTRLink extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FurkanMeclis\PayTRLink\PayTRLinkService::class;
    }
}
