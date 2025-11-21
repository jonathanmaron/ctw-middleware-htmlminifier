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
     * Test that postProcess returns input unchanged
     */
    public function testPostProcessReturnsInputUnchanged(): void
    {
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess handles empty string
     */
    public function testPostProcessHandlesEmptyString(): void
    {
        $input = '';
        $expected = '';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess preserves whitespace
     */
    public function testPostProcessPreservesWhitespace(): void
    {
        $input = "  \n\t  <html>  Content  </html>  \n\t  ";
        $expected = "  \n\t  <html>  Content  </html>  \n\t  ";

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess preserves leading whitespace
     */
    public function testPostProcessPreservesLeadingWhitespace(): void
    {
        $input = '    <div>Content</div>';
        $expected = '    <div>Content</div>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess preserves trailing whitespace
     */
    public function testPostProcessPreservesTrailingWhitespace(): void
    {
        $input = '<div>Content</div>    ';
        $expected = '<div>Content</div>    ';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess handles complex HTML
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
     * Test that postProcess handles HTML with newlines
     */
    public function testPostProcessHandlesHtmlWithNewlines(): void
    {
        $input = "<div>\n<p>Line 1</p>\n<p>Line 2</p>\n</div>";
        $expected = "<div>\n<p>Line 1</p>\n<p>Line 2</p>\n</div>";

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess handles HTML with tabs
     */
    public function testPostProcessHandlesHtmlWithTabs(): void
    {
        $input = "<div>\t<p>Tabbed</p>\t</div>";
        $expected = "<div>\t<p>Tabbed</p>\t</div>";

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess handles special characters
     */
    public function testPostProcessHandlesSpecialCharacters(): void
    {
        $input = '<div>&lt;p&gt;Special &amp; chars&lt;/p&gt;</div>';
        $expected = '<div>&lt;p&gt;Special &amp; chars&lt;/p&gt;</div>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess handles UTF-8 characters
     */
    public function testPostProcessHandlesUtf8Characters(): void
    {
        $input = '<div>Café résumé naïve</div>';
        $expected = '<div>Café résumé naïve</div>';

        $actual = $this->wyriHaximusAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }
}
