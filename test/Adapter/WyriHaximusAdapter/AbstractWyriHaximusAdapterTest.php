<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\AbstractWyriHaximusAdapter;
use PHPUnit\Framework\TestCase;

final class AbstractWyriHaximusAdapterTest extends TestCase
{
    private AbstractWyriHaximusAdapter $wyriHaximusAdapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wyriHaximusAdapter = new class() extends AbstractWyriHaximusAdapter {
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
     * Test that postProcess returns the input unchanged when given a simple HTML document.
     */
    public function testPostProcessReturnsInputUnchanged(): void
    {
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns an empty string when the input is an empty string.
     */
    public function testPostProcessHandlesEmptyString(): void
    {
        $input = '';
        $expected = '';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess preserves surrounding whitespace when the input is padded with spaces, tabs, and newlines.
     */
    public function testPostProcessPreservesWhitespace(): void
    {
        $input = "  \n\t  <html>  Content  </html>  \n\t  ";
        $expected = "  \n\t  <html>  Content  </html>  \n\t  ";

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess preserves leading whitespace when the input begins with spaces.
     */
    public function testPostProcessPreservesLeadingWhitespace(): void
    {
        $input = '    <div>Content</div>';
        $expected = '    <div>Content</div>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess preserves trailing whitespace when the input ends with spaces.
     */
    public function testPostProcessPreservesTrailingWhitespace(): void
    {
        $input = '<div>Content</div>    ';
        $expected = '<div>Content</div>    ';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when given a multi-line, indented complex HTML document.
     */
    public function testPostProcessHandlesComplexHtml(): void
    {
        $input = <<<HTML
<!DOCTYPE html>
<html>
<head><title>Test</title></head>
<body>
    <div class="container">
        <p>Content</p>
    </div>
</body>
</html>
HTML;

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($input, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the HTML contains embedded newlines.
     */
    public function testPostProcessHandlesHtmlWithNewlines(): void
    {
        $input = "<div>\n<p>Line 1</p>\n<p>Line 2</p>\n</div>";
        $expected = "<div>\n<p>Line 1</p>\n<p>Line 2</p>\n</div>";

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the HTML contains embedded tabs.
     */
    public function testPostProcessHandlesHtmlWithTabs(): void
    {
        $input = "<div>\t<p>Tabbed</p>\t</div>";
        $expected = "<div>\t<p>Tabbed</p>\t</div>";

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the HTML contains escaped special character entities.
     */
    public function testPostProcessHandlesSpecialCharacters(): void
    {
        $input = '<div>&lt;p&gt;Special &amp; chars&lt;/p&gt;</div>';
        $expected = '<div>&lt;p&gt;Special &amp; chars&lt;/p&gt;</div>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the HTML contains UTF-8 accented characters.
     */
    public function testPostProcessHandlesUtf8Characters(): void
    {
        $input = '<div>Café résumé naïve</div>';
        $expected = '<div>Café résumé naïve</div>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }
}
