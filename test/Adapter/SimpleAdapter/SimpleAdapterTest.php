<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter\SimpleAdapter;
use PHPUnit\Framework\TestCase;

final class SimpleAdapterTest extends TestCase
{
    private SimpleAdapter $simpleAdapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleAdapter = new SimpleAdapter();
    }

    /**
     * Test that minify returns string
     */
    public function testMinifyReturnsString(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->simpleAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify handles empty string
     */
    public function testMinifyHandlesEmptyString(): void
    {
        $html = '';
        $expected = '';

        $actual = $this->simpleAdapter->minify($html);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that minify removes HTML comments
     */
    public function testMinifyRemovesHtmlComments(): void
    {
        $html = '<html><!-- This is a comment --><body>Content</body></html>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString('<!-- This is a comment -->', $actual);
        self::assertStringContainsString('Content', $actual);
    }

    /**
     * Test that minify removes multiple HTML comments
     */
    public function testMinifyRemovesMultipleHtmlComments(): void
    {
        $html = '<!-- Comment 1 --><div><!-- Comment 2 -->Text<!-- Comment 3 --></div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString('<!-- Comment 1 -->', $actual);
        self::assertStringNotContainsString('<!-- Comment 2 -->', $actual);
        self::assertStringNotContainsString('<!-- Comment 3 -->', $actual);
        self::assertStringContainsString('Text', $actual);
    }

    /**
     * Test that minify removes leading spaces and tabs
     */
    public function testMinifyRemovesLeadingSpacesAndTabs(): void
    {
        $html = "    <div>Content</div>\n\t<p>Text</p>";

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString("\n    ", $actual);
        self::assertStringNotContainsString("\n\t", $actual);
    }

    /**
     * Test that minify replaces newlines with spaces
     */
    public function testMinifyReplacesNewlinesWithSpaces(): void
    {
        $html = "<div>Line1\nLine2\nLine3</div>";

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString("\n", $actual);
        self::assertStringContainsString('Line1', $actual);
        self::assertStringContainsString('Line2', $actual);
        self::assertStringContainsString('Line3', $actual);
    }

    /**
     * Test that minify removes multiple spaces between tags
     */
    public function testMinifyRemovesMultipleSpacesBetweenTags(): void
    {
        $html = '<div>    </div>     <p>Text</p>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString('     ', $actual);
    }

    /**
     * Test that minify strips whitespace between quote and end tag
     */
    public function testMinifyStripsWhitespaceBetweenQuoteAndEndTag(): void
    {
        $html = '<div class="test"  ><p id="para"   >Content</p></div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString('"  >', $actual);
        self::assertStringNotContainsString('"   >', $actual);
        self::assertStringContainsString('">', $actual);
    }

    /**
     * Test that minify strips whitespace between equals and quote
     */
    public function testMinifyStripsWhitespaceBetweenEqualsAndQuote(): void
    {
        $html = '<div class=  "test"><p id =  "para">Content</p></div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString('=  "', $actual);
        self::assertStringNotContainsString(' =  "', $actual);
    }

    /**
     * Test that minify handles complex HTML document
     */
    public function testMinifyHandlesComplexHtmlDocument(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <!-- This is a comment -->
        <title>Test Page</title>
        <meta charset="utf-8">
    </head>
    <body>
        <div class="container"  >
            <h1>Hello World</h1>
            <p id="para"   >
                This is a test paragraph.
            </p>
        </div>
    </body>
</html>
HTML;

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString('<!-- This is a comment -->', $actual);
        self::assertStringNotContainsString("\n", $actual);
        self::assertStringContainsString('Hello World', $actual);
        self::assertStringContainsString('This is a test paragraph.', $actual);
    }

    /**
     * Test that minify preserves content inside tags
     */
    public function testMinifyPreservesContentInsideTags(): void
    {
        $html = '<div>Important Content</div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringContainsString('Important Content', $actual);
    }

    /**
     * Test that minify handles HTML with inline styles
     */
    public function testMinifyHandlesHtmlWithInlineStyles(): void
    {
        $html = '<div style="color: red; font-size: 14px;">Styled Text</div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringContainsString('color: red', $actual);
        self::assertStringContainsString('Styled Text', $actual);
    }

    /**
     * Test that minify handles HTML with single quotes
     */
    public function testMinifyHandlesHtmlWithSingleQuotes(): void
    {
        $html = "<div class='test'  ><p id='para'   >Content</p></div>";

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringNotContainsString("'  >", $actual);
        self::assertStringNotContainsString("'   >", $actual);
        self::assertStringContainsString("'>", $actual);
    }

    /**
     * Test that minify handles HTML with script tags
     */
    public function testMinifyHandlesHtmlWithScriptTags(): void
    {
        $html = '<script>var x = 1;</script><div>Content</div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringContainsString('var x = 1;', $actual);
        self::assertStringContainsString('Content', $actual);
    }

    /**
     * Test that minify handles HTML with style tags
     */
    public function testMinifyHandlesHtmlWithStyleTags(): void
    {
        $html = '<style>body { margin: 0; }</style><div>Content</div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringContainsString('body { margin: 0; }', $actual);
        self::assertStringContainsString('Content', $actual);
    }

    /**
     * Test that minify trims final output
     */
    public function testMinifyTrimsFinalOutput(): void
    {
        $html = '   <div>Content</div>   ';

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringStartsNotWith(' ', $actual);
        self::assertStringEndsNotWith(' ', $actual);
    }

    /**
     * Test that minify reduces whitespace between tags to single space
     */
    public function testMinifyReducesWhitespaceBetweenTagsToSingleSpace(): void
    {
        $html = '<div>Text1</div>      <div>Text2</div>';

        $actual = $this->simpleAdapter->minify($html);

        self::assertMatchesRegularExpression('/<\/div> <div>/', $actual);
    }

    /**
     * Test that minify handles nested tags correctly
     */
    public function testMinifyHandlesNestedTagsCorrectly(): void
    {
        $html = <<<HTML
<div>
    <ul>
        <li>Item 1</li>
        <li>Item 2</li>
        <li>Item 3</li>
    </ul>
</div>
HTML;

        $actual = $this->simpleAdapter->minify($html);

        self::assertStringContainsString('Item 1', $actual);
        self::assertStringContainsString('Item 2', $actual);
        self::assertStringContainsString('Item 3', $actual);
        self::assertStringNotContainsString("\n", $actual);
    }
}
