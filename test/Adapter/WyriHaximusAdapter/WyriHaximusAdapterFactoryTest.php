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
     * Test that invoke returns WyriHaximusAdapter instance
     */
    public function testInvokeReturnsWyriHaximusAdapterInstance(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
    }

    /**
     * Test that invoke creates adapter without config when container has no config
     */
    public function testInvokeCreatesAdapterWithoutConfigWhenContainerHasNoConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(false);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates adapter with empty config when config is empty
     */
    public function testInvokeCreatesAdapterWithEmptyConfigWhenConfigIsEmpty(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn([]);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates adapter with config when provided
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

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoke handles nested config correctly
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

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoke handles missing middleware config key
     */
    public function testInvokeHandlesMissingMiddlewareConfigKey(): void
    {
        $config = [
            'SomeOtherKey' => [
                'value' => 'data',
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke handles missing adapter config key
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

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke handles empty adapter config array
     */
    public function testInvokeHandlesEmptyAdapterConfigArray(): void
    {
        $config = [
            HtmlMinifierMiddleware::class => [
                WyriHaximusAdapter::class => [],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->wyriHaximusAdapterFactory)($container);

        self::assertInstanceOf(WyriHaximusAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }
}
