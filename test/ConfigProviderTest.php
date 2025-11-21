<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapter;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapterFactory;
use Ctw\Middleware\HtmlMinifierMiddleware\ConfigProvider;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddlewareFactory;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configProvider = new ConfigProvider();
    }

    /**
     * Test that invoke returns array with dependencies key
     */
    public function testInvokeReturnsArrayWithDependenciesKey(): void
    {
        $result = ($this->configProvider)();

        self::assertIsArray($result);
        self::assertArrayHasKey('dependencies', $result);
    }

    /**
     * Test that invoke returns correct structure
     */
    public function testInvokeReturnsCorrectStructure(): void
    {
        $expected = [
            'dependencies' => $this->configProvider->getDependencies(),
        ];

        $actual = ($this->configProvider)();

        self::assertSame($expected, $actual);
    }

    /**
     * Test that getDependencies returns array with factories key
     */
    public function testGetDependenciesReturnsArrayWithFactoriesKey(): void
    {
        $result = $this->configProvider->getDependencies();

        self::assertIsArray($result);
        self::assertArrayHasKey('factories', $result);
    }

    /**
     * Test that getDependencies returns all required factories
     */
    public function testGetDependenciesReturnsAllRequiredFactories(): void
    {
        $result = $this->configProvider->getDependencies();

        self::assertArrayHasKey('factories', $result);
        $factories = $result['factories'];

        self::assertArrayHasKey(HtmlMinifierMiddleware::class, $factories);
        self::assertArrayHasKey(SimpleAdapter::class, $factories);
        self::assertArrayHasKey(WyriHaximusAdapter::class, $factories);
        self::assertArrayHasKey(TidyAdapter::class, $factories);
    }

    /**
     * Test that getDependencies maps middleware to correct factory
     */
    public function testGetDependenciesMapsMiddlewareToCorrectFactory(): void
    {
        $result = $this->configProvider->getDependencies();
        $factories = $result['factories'];

        self::assertSame(HtmlMinifierMiddlewareFactory::class, $factories[HtmlMinifierMiddleware::class]);
    }

    /**
     * Test that getDependencies maps SimpleAdapter to correct factory
     */
    public function testGetDependenciesMapsSimpleAdapterToCorrectFactory(): void
    {
        $result = $this->configProvider->getDependencies();
        $factories = $result['factories'];

        self::assertSame(SimpleAdapterFactory::class, $factories[SimpleAdapter::class]);
    }

    /**
     * Test that getDependencies maps WyriHaximusAdapter to correct factory
     */
    public function testGetDependenciesMapsWyriHaximusAdapterToCorrectFactory(): void
    {
        $result = $this->configProvider->getDependencies();
        $factories = $result['factories'];

        self::assertSame(WyriHaximusAdapterFactory::class, $factories[WyriHaximusAdapter::class]);
    }

    /**
     * Test that getDependencies maps TidyAdapter to correct factory
     */
    public function testGetDependenciesMapsTidyAdapterToCorrectFactory(): void
    {
        $result = $this->configProvider->getDependencies();
        $factories = $result['factories'];

        self::assertSame(TidyAdapterFactory::class, $factories[TidyAdapter::class]);
    }

    /**
     * Test that factories array has exactly four entries
     */
    public function testGetDependenciesFactoriesHasExactlyFourEntries(): void
    {
        $result = $this->configProvider->getDependencies();
        $factories = $result['factories'];

        self::assertCount(4, $factories);
    }
}
