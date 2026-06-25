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
     * Test that invoking the factory returns a TidyAdapter instance when the container reports no config.
     */
    public function testInvokeReturnsTidyAdapterInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the container has no config entry.
     */
    public function testInvokeCreatesAdapterWithoutConfigWhenContainerHasNoConfig(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter carrying the adapter config when the container provides matching middleware and adapter config keys.
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

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame($adapterConfig, $result->getConfig());
    }

    /**
     * Test that invoking the factory extracts only the adapter config when the middleware config also contains other adapter entries.
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

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
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

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }

    /**
     * Test that invoking the factory creates an adapter with an empty config when the adapter config key maps to an empty array.
     */
    public function testInvokeHandlesEmptyAdapterConfigArray(): void
    {
        $config = [
            HtmlMinifierMiddleware::class => [
                TidyAdapter::class => [],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($config);

        $result = ($this->tidyAdapterFactory)($container);

        self::assertInstanceOf(TidyAdapter::class, $result);
        self::assertSame([], $result->getConfig());
    }
}
