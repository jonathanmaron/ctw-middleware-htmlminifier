<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapterFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                HtmlMinifierMiddleware::class => HtmlMinifierMiddlewareFactory::class,
                SimpleAdapter::class          => SimpleAdapterFactory::class,
                WyriHaximusAdapter::class     => WyriHaximusAdapterFactory::class,
                TidyAdapter::class            => TidyAdapterFactory::class,
            ],
        ];
    }
}
