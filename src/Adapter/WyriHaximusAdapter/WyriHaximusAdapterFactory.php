<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class WyriHaximusAdapterFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): WyriHaximusAdapter
    {
        $config = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            assert(is_array($config));
            $config = $config[HtmlMinifierMiddleware::class][WyriHaximusAdapter::class] ?? [];
        }

        $adapter = new WyriHaximusAdapter();

        if ((is_countable($config) ? count($config) : 0) > 0) {
            $adapter->setConfig($config);
        }

        return $adapter;
    }
}
