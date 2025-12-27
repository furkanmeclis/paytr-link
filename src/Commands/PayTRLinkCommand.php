<?php

namespace FurkanMeclis\PayTRLink\Commands;

use Illuminate\Console\Command;

class PayTRLinkCommand extends Command
{
    public $signature = 'paytr-link';

    public $description = 'PayTR Link command';

    public function handle(): int
    {
        $this->comment('PayTR Link package is installed and ready to use!');

        return self::SUCCESS;
    }
}
