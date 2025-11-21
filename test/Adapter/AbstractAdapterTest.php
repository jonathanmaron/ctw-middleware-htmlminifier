<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AbstractAdapter;
use PHPUnit\Framework\TestCase;

final class AbstractAdapterTest extends TestCase
{
    private AbstractAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new class() extends AbstractAdapter {
            public function minify(string $htmlSource): string
            {
                return $htmlSource;
            }

            public function callTrim(string $htmlMinified): string
            {
                return $this->trim($htmlMinified);
            }
        };
    }

    /**
     * Test that getConfig returns empty array by default
     */
    public function testGetConfigReturnsEmptyArrayByDefault(): void
    {
        $expected = [];
        $actual = $this->adapter->getConfig();

        self::assertSame($expected, $actual);
    }

    /**
     * Test that setConfig sets configuration correctly
     */
    public function testSetConfigSetsConfigurationCorrectly(): void
    {
        $config = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];

        $result = $this->adapter->setConfig($config);

        self::assertSame($config, $this->adapter->getConfig());
        self::assertInstanceOf(AbstractAdapter::class, $result);
    }

    /**
     * Test that setConfig returns self for method chaining
     */
    public function testSetConfigReturnsSelfForMethodChaining(): void
    {
        $config = [
            'test' => 'value',
        ];

        $result = $this->adapter->setConfig($config);

        self::assertSame($this->adapter, $result);
    }

    /**
     * Test that setConfig overwrites previous configuration
     */
    public function testSetConfigOverwritesPreviousConfiguration(): void
    {
        $firstConfig = [
            'first' => 'config',
        ];
        $secondConfig = [
            'second' => 'config',
        ];

        $this->adapter->setConfig($firstConfig);
        $this->adapter->setConfig($secondConfig);

        self::assertSame($secondConfig, $this->adapter->getConfig());
    }

    /**
     * Test that setConfig handles empty array
     */
    public function testSetConfigHandlesEmptyArray(): void
    {
        $this->adapter->setConfig([
            'initial' => 'value',
        ]);
        $this->adapter->setConfig([]);

        self::assertSame([], $this->adapter->getConfig());
    }

    /**
     * Test that trim removes whitespace from both sides
     */
    public function testTrimRemovesWhitespaceFromBothSides(): void
    {
        $input = "  \t\n  test content  \n\t  ";
        $expected = 'test content';

        $actual = $this->adapter->callTrim($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that trim handles string with no whitespace
     */
    public function testTrimHandlesStringWithNoWhitespace(): void
    {
        $input = 'test';
        $expected = 'test';

        $actual = $this->adapter->callTrim($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that trim handles empty string
     */
    public function testTrimHandlesEmptyString(): void
    {
        $input = '';
        $expected = '';

        $actual = $this->adapter->callTrim($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that trim handles string with only whitespace
     */
    public function testTrimHandlesStringWithOnlyWhitespace(): void
    {
        $input = "  \t\n\r  ";
        $expected = '';

        $actual = $this->adapter->callTrim($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that trim handles string with internal whitespace
     */
    public function testTrimHandlesStringWithInternalWhitespace(): void
    {
        $input = '  test  with  spaces  ';
        $expected = 'test  with  spaces';

        $actual = $this->adapter->callTrim($input);

        self::assertSame($expected, $actual);
    }
}
