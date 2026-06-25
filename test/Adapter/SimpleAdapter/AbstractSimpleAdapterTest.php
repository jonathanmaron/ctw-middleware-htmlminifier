<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\AbstractSimpleAdapter;
use PHPUnit\Framework\TestCase;

final class AbstractSimpleAdapterTest extends TestCase
{
    private AbstractSimpleAdapter $simpleAdapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleAdapter = new class() extends AbstractSimpleAdapter {
            public function minify(string $htmlSource): string
            {
                return $htmlSource;
            }

            public function callPostProcess(string $htmlMinified): string
            {
                return $this->postProcess($htmlMinified);
            }
        };
    }

    /**
     * Test that postProcess removes the surrounding whitespace when given a string padded with leading and trailing whitespace.
     */
    public function testPostProcessTrimsWhitespace(): void
    {
        $input = "  \t\n  <html>Content</html>  \n\t  ";
        $expected = '<html>Content</html>';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns an empty string when given an empty string.
     */
    public function testPostProcessHandlesEmptyString(): void
    {
        $input = '';
        $expected = '';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when given a string that has no surrounding whitespace.
     */
    public function testPostProcessHandlesStringWithNoWhitespace(): void
    {
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns an empty string when given a string composed entirely of whitespace.
     */
    public function testPostProcessHandlesStringWithOnlyWhitespace(): void
    {
        $input = "  \t\n\r  ";
        $expected = '';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess removes only the surrounding whitespace when given a string that also contains internal whitespace.
     */
    public function testPostProcessPreservesInternalWhitespace(): void
    {
        $input = '  <div>  Content  with  spaces  </div>  ';
        $expected = '<div>  Content  with  spaces  </div>';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess removes the leading whitespace when given a string prefixed with newlines and tabs.
     */
    public function testPostProcessRemovesLeadingNewlinesAndTabs(): void
    {
        $input = "\n\n\t\t<html>Content</html>";
        $expected = '<html>Content</html>';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess removes the trailing whitespace when given a string suffixed with newlines and tabs.
     */
    public function testPostProcessRemovesTrailingNewlinesAndTabs(): void
    {
        $input = "<html>Content</html>\n\n\t\t";
        $expected = '<html>Content</html>';

        $actual = $this->simpleAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }
}
