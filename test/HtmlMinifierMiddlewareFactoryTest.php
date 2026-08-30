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
     * Test that invoke returns an HtmlMinifierMiddleware instance when the container provides valid config and an adapter.
     */
    public function testInvokeReturnsHtmlMinifierMiddlewareInstance(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
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
     * Test that invoke throws an exception when the middleware config is an empty array.
     */
    public function testInvokeThrowsExceptionWhenConfigHasNoElements(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn([
                HtmlMinifierMiddleware::class => [],
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke throws an exception when the middleware config contains more than one element.
     */
    public function testInvokeThrowsExceptionWhenConfigHasMultipleElements(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn([
                HtmlMinifierMiddleware::class => [
                    SimpleAdapter::class => [],
                    'AnotherAdapter' => [],
                ],
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke throws an exception when the container reports no config and returns a non-array value.
     */
    public function testInvokeCreatesMiddlewareWhenContainerHasNoConfig(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);
        $container->method('get')
            ->willReturn($adapter);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke retrieves the configured adapter from the container and sets it on the middleware when the config is valid.
     */
    public function testInvokeRetrievesAdapterFromContainer(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
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
     * Test that invoke sets the resolved adapter on the returned middleware when the config references a custom adapter class.
     */
    public function testInvokeSetsAdapterOnMiddleware(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $adapterClass = 'CustomAdapter';

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
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
     * Test that invoke returns an HtmlMinifierMiddleware instance when the config contains exactly one adapter with options.
     */
    public function testInvokeHandlesConfigWithExactlyOneAdapter(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $config = [
            HtmlMinifierMiddleware::class => [
                SimpleAdapter::class => [
                    'option1' => 'value1',
                ],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([['config', $config], [SimpleAdapter::class, $adapter]]);

        $result = ($this->htmlMinifierMiddlewareFactory)($container);

        self::assertInstanceOf(HtmlMinifierMiddleware::class, $result);
    }

    /**
     * Test that invoke uses the first config key as the adapter class name and sets the resolved adapter when the config is valid.
     */
    public function testInvokeExtractsFirstKeyAsAdapterClassName(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $adapterClassName = 'MyCustomAdapter';
        $config = [
            HtmlMinifierMiddleware::class => [
                $adapterClassName => [],
            ],
        ];

        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
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
     * Test that invoke throws an exception when the config does not contain the middleware key.
     */
    public function testInvokeThrowsExceptionWhenMiddlewareConfigIsMissing(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn([
                'SomeOtherMiddleware' => [
                    'adapter' => [],
                ],
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }

    /**
     * Test that invoke throws an exception when the global config is an empty array.
     */
    public function testInvokeHandlesEmptyGlobalConfig(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn([]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(
            'The config key for "Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware" must be an array with one element'
        );

        ($this->htmlMinifierMiddlewareFactory)($container);
    }
}
