<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class SimpleAdapterFactoryTest extends TestCase
{
    private SimpleAdapterFactory $simpleAdapterFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleAdapterFactory = new SimpleAdapterFactory();
    }

    /**
     * Test that invoke returns a SimpleAdapter instance when the container reports no config entry.
     */
    public function testInvokeReturnsSimpleAdapterInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
    }

    /**
     * Test that invoke creates an adapter with an empty config when the container has no config entry.
     */
    public function testInvokeCreatesAdapterWithoutConfigWhenContainerHasNoConfig(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates an adapter with an empty config when the container returns an empty config array.
     */
    public function testInvokeCreatesAdapterWithEmptyConfigWhenConfigIsEmpty(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn([]);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates an adapter populated with the adapter config when the container provides it under the middleware and adapter keys.
     */
    public function testInvokeCreatesAdapterWithConfigWhenProvided(): void
    {
        $adapterConfig = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $config = [
            HtmlMinifierMiddleware::class => [
                SimpleAdapter::class => $adapterConfig,
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoke selects only the adapter's config when the container provides config containing multiple adapter entries.
     */
    public function testInvokeHandlesNestedConfigCorrectly(): void
    {
        $adapterConfig = [
            'minify' => true,
            'compress' => false,
        ];
        $config = [
            HtmlMinifierMiddleware::class => [
                SimpleAdapter::class => $adapterConfig,
                'OtherAdapter' => [
                    'other' => 'config',
                ],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoke creates an adapter with an empty config when the config lacks the middleware key.
     */
    public function testInvokeHandlesMissingMiddlewareConfigKey(): void
    {
        $config = [
            'SomeOtherKey' => [
                'value' => 'data',
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates an adapter with an empty config when the middleware config lacks the adapter key.
     */
    public function testInvokeHandlesMissingAdapterConfigKey(): void
    {
        $config = [
            HtmlMinifierMiddleware::class => [
                'OtherAdapter' => [
                    'other' => 'config',
                ],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates an adapter with an empty config when the adapter config key maps to an empty array.
     */
    public function testInvokeHandlesEmptyAdapterConfigArray(): void
    {
        $config = [
            HtmlMinifierMiddleware::class => [
                SimpleAdapter::class => [],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->simpleAdapterFactory)($container);

        self::assertInstanceOf(SimpleAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }
}
