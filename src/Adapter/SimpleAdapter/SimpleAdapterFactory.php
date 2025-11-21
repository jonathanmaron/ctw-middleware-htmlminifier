<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use Psr\Container\ContainerInterface;

class SimpleAdapterFactory
{
    public function __invoke(ContainerInterface $container): SimpleAdapter
    {
        $config = [];
        if ($container->has('config')) {
            $globalConfig = $container->get('config');
            assert(is_array($globalConfig));
            $middlewareConfig = $globalConfig[HtmlMinifierMiddleware::class] ?? [];
            assert(is_array($middlewareConfig));
            $adapterConfig = $middlewareConfig[SimpleAdapter::class] ?? [];
            assert(is_array($adapterConfig));
            $config = $adapterConfig;
        }

        $adapter = new SimpleAdapter();

        if ((is_countable($config) ? count($config) : 0) > 0) {
            $adapter->setConfig($config);
        }

        return $adapter;
    }
}
