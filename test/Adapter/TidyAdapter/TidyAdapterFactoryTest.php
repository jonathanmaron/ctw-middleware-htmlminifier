<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class TidyAdapterFactoryTest extends TestCase
{
    private TidyAdapterFactory $tidyAdapterFactory;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('tidy')) {
            self::markTestSkipped('Tidy extension is not available');
        }

        $this->tidyAdapterFactory = new TidyAdapterFactory();
    }

    /**
     * Test that invoke returns TidyAdapter instance
     */
    public function testInvokeReturnsTidyAdapterInstance(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke creates adapter with config when provided
     */
    public function testInvokeCreatesAdapterWithConfigWhenProvided(): void
    {
        $adapterConfig = [
            'indent' => false,
            'wrap' => 0,
        ];
        $config = [
            HtmlMinifierMiddleware::class => [
                TidyAdapter::class => $adapterConfig,
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoke handles nested config correctly
     */
    public function testInvokeHandlesNestedConfigCorrectly(): void
    {
        $adapterConfig = [
            'doctype' => 'html5',
            'clean' => true,
        ];
        $config = [
            HtmlMinifierMiddleware::class => [
                TidyAdapter::class => $adapterConfig,
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoke handles empty adapter config array
     */
    public function testInvokeHandlesEmptyAdapterConfigArray(): void
    {
        $config = [
            HtmlMinifierMiddleware::class => [
                TidyAdapter::class => [],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn($config);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }
}
