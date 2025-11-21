<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddlewareFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class HtmlMinifierMiddlewareFactoryTest extends TestCase
{
    private HtmlMinifierMiddlewareFactory $htmlMinifierMiddlewareFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->htmlMinifierMiddlewareFactory = new HtmlMinifierMiddlewareFactory();
    }

    /**
     * Test that invoke returns HtmlMinifierMiddleware instance
     */
    public function testInvokeReturnsHtmlMinifierMiddlewareInstance(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                [
                    'config', [
                        HtmlMinifierMiddleware::class => [
                            SimpleAdapter::class => [],
                        ],
                    ]],
                [SimpleAdapter::class, $adapter],
            ]);

        $result = ($this->htmlMinifierMiddlewareFactory)($container);

        self::assertInstanceOf(HtmlMinifierMiddleware::class, $result);
    }

    /**
     * Test that invoke throws exception when config has no elements
     */
    public function testInvokeThrowsExceptionWhenConfigHasNoElements(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn([
                HtmlMinifierMiddleware::class => [],
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke throws exception when config has multiple elements
     */
    public function testInvokeThrowsExceptionWhenConfigHasMultipleElements(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn([
                HtmlMinifierMiddleware::class => [
                    SimpleAdapter::class => [],
                    'AnotherAdapter' => [],
                ],
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke creates middleware without config when container has no config
     */
    public function testInvokeCreatesMiddlewareWhenContainerHasNoConfig(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(false);
        $container->method('get')
            ->with(0)
            ->willReturn($adapter);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke retrieves adapter from container
     */
    public function testInvokeRetrievesAdapterFromContainer(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                [
                    'config', [
                        HtmlMinifierMiddleware::class => [
                            SimpleAdapter::class => [],
                        ],
                    ]],
                [SimpleAdapter::class, $adapter],
            ]);

        $result = ($this->htmlMinifierMiddlewareFactory)($container);

        self::assertSame($adapter, $result->getAdapter());
    }

    /**
     * Test that invoke sets adapter on middleware
     */
    public function testInvokeSetsAdapterOnMiddleware(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapterClass = 'CustomAdapter';

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                [
                    'config', [
                        HtmlMinifierMiddleware::class => [
                            $adapterClass => [
                                'option' => 'value',
                            ],
                        ],
                    ]],
                [$adapterClass, $adapter],
            ]);

        $result = ($this->htmlMinifierMiddlewareFactory)($container);

        self::assertInstanceOf(HtmlMinifierMiddleware::class, $result);
        self::assertSame($adapter, $result->getAdapter());
    }

    /**
     * Test that invoke handles config with exactly one adapter
     */
    public function testInvokeHandlesConfigWithExactlyOneAdapter(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $config = [
            HtmlMinifierMiddleware::class => [
                SimpleAdapter::class => [
                    'option1' => 'value1',
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([['config', $config], [SimpleAdapter::class, $adapter]]);

        $result = ($this->htmlMinifierMiddlewareFactory)($container);

        self::assertInstanceOf(HtmlMinifierMiddleware::class, $result);
    }

    /**
     * Test that invoke extracts first key as adapter class name
     */
    public function testInvokeExtractsFirstKeyAsAdapterClassName(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapterClassName = 'MyCustomAdapter';
        $config = [
            HtmlMinifierMiddleware::class => [
                $adapterClassName => [],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->willReturnCallback(function ($key) use ($config, $adapter, $adapterClassName) {
                if ('config' === $key) {
                    return $config;
                }
                if ($key === $adapterClassName) {
                    return $adapter;
                }
                return null;
            });

        $result = ($this->htmlMinifierMiddlewareFactory)($container);

        self::assertInstanceOf(HtmlMinifierMiddleware::class, $result);
        self::assertSame($adapter, $result->getAdapter());
    }

    /**
     * Test that invoke throws exception when middleware config is missing
     */
    public function testInvokeThrowsExceptionWhenMiddlewareConfigIsMissing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn([
                'SomeOtherMiddleware' => [
                    'adapter' => [],
                ],
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke handles empty global config
     */
    public function testInvokeHandlesEmptyGlobalConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->with('config')
            ->willReturn(true);
        $container->method('get')
            ->with('config')
            ->willReturn([]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }
}
