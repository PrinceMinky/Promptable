<?php

declare(strict_types=1);

namespace Princeminky\Promptable\Facades;

use Illuminate\Support\Facades\Facade;

class Promptable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'promptable';
    }
}
