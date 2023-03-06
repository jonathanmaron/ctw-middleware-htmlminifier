<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware;

use Exception;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class HtmlMinifierMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): HtmlMinifierMiddleware
    {
        $config = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            assert(is_array($config));
            $config = $config[HtmlMinifierMiddleware::class] ?? [];
        }

        if (1 !== count($config)) {
            $format  = 'The config key for "%s" must be an array with one element';
            $message = sprintf($format, HtmlMinifierMiddleware::class);
            throw new Exception($message);
        }

        $className = array_key_first($config);
        assert(is_string($className));

        $adapter = $container->get($className);
        assert($adapter instanceof AdapterInterface);

        $middleware = new HtmlMinifierMiddleware();

        $middleware->setAdapter($adapter);

        return $middleware;
    }
}
