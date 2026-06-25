<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class WyriHaximusAdapterFactoryTest extends TestCase
{
    private WyriHaximusAdapterFactory $wyriHaximusAdapterFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wyriHaximusAdapterFactory = new WyriHaximusAdapterFactory();
    }

    /**
     * Test that invoking the factory returns a WyriHaximusAdapter instance when the container reports no config.
     */
    public function testInvokeReturnsWyriHaximusAdapterInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the container has no config entry.
     */
    public function testInvokeCreatesAdapterWithoutConfigWhenContainerHasNoConfig(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the container returns an empty config array.
     */
    public function testInvokeCreatesAdapterWithEmptyConfigWhenConfigIsEmpty(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn([]);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter carrying the adapter config when the container provides matching middleware and adapter config keys.
     */
    public function testInvokeCreatesAdapterWithConfigWhenProvided(): void
    {
        $adapterConfig = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $config = [
            HtmlMinifierMiddleware::class => [
                WyriHaximusAdapter::class => $adapterConfig,
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoking the factory extracts only the adapter config when the middleware config also contains other adapter entries.
     */
    public function testInvokeHandlesNestedConfigCorrectly(): void
    {
        $adapterConfig = [
            'compress' => true,
            'optimize' => false,
        ];
        $config = [
            HtmlMinifierMiddleware::class => [
                WyriHaximusAdapter::class => $adapterConfig,
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

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the config lacks the middleware config key.
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

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the middleware config lacks the adapter config key.
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

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the adapter config key maps to an empty array.
     */
    public function testInvokeHandlesEmptyAdapterConfigArray(): void
    {
        $config = [
            HtmlMinifierMiddleware::class => [
                WyriHaximusAdapter::class => [],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }
}
