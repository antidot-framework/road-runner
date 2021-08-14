<?php

declare(strict_types=1);

namespace Antidot\RoadRunner\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\RoadRunner\Container\ApplicationFactory;

final class ConfigProvider
{
    /**
     * @return array<string, array<string, string>>
     */
    public function __invoke(): array
    {
        return [
            'factories' => [
                Application::class => ApplicationFactory::class,
            ]
        ];
    }
}
