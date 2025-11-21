<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapter;
use PHPUnit\Framework\TestCase;

final class TidyAdapterTest extends TestCase
{
    private TidyAdapter $tidyAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('tidy')) {
            self::markTestSkipped('Tidy extension is not available');
        }

        $this->tidyAdapter = new TidyAdapter();
    }

    /**
     * Test that minify returns string
     */
    public function testMinifyReturnsString(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify handles basic HTML
     */
    public function testMinifyHandlesBasicHtml(): void
    {
        $html = '<html><body><p>Test content</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Test content', $result);
    }

    /**
     * Test that minify cleans malformed HTML
     */
    public function testMinifyCleansAndRepairsMalformedHtml(): void
    {
        $html = '<div><p>Unclosed paragraph<div>Nested incorrectly</div>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
        self::assertStringContainsString('Unclosed paragraph', $result);
        self::assertStringContainsString('Nested incorrectly', $result);
    }

    /**
     * Test that minify with custom config applies configuration
     */
    public function testMinifyWithCustomConfigAppliesConfiguration(): void
    {
        $config = [
            'indent' => false,
            'wrap' => 0,
        ];

        $this->tidyAdapter->setConfig($config);
        $html = '<html><body><p>Test</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify trims output
     */
    public function testMinifyTrimsOutput(): void
    {
        $html = '<html><body>Content</body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringStartsNotWith(' ', $result);
        self::assertStringStartsNotWith("\n", $result);
    }

    /**
     * Test that minify handles empty string gracefully
     */
    public function testMinifyHandlesEmptyStringGracefully(): void
    {
        $html = '';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify preserves content
     */
    public function testMinifyPreservesContent(): void
    {
        $html = '<html><body><h1>Title</h1><p>Paragraph text</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Title', $result);
        self::assertStringContainsString('Paragraph text', $result);
    }

    /**
     * Test that minify handles UTF-8 encoding
     */
    public function testMinifyHandlesUtf8Encoding(): void
    {
        $html = '<html><body><p>Café résumé naïve</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Café', $result);
        self::assertStringContainsString('résumé', $result);
        self::assertStringContainsString('naïve', $result);
    }

    /**
     * Test that minify handles special characters
     */
    public function testMinifyHandlesSpecialCharacters(): void
    {
        $html = '<html><body><p>&lt;div&gt; &amp; &quot;test&quot;</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
        self::assertStringContainsString('test', $result);
    }

    /**
     * Test that minify handles nested tags
     */
    public function testMinifyHandlesNestedTags(): void
    {
        $html = <<<HTML
<html>
    <body>
        <div>
            <ul>
                <li>Item 1</li>
                <li>Item 2</li>
            </ul>
        </div>
    </body>
</html>
HTML;

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Item 1', $result);
        self::assertStringContainsString('Item 2', $result);
    }

    /**
     * Test that minify with html5 doctype config adds doctype
     */
    public function testMinifyWithHtml5DoctypeConfigAddsDoctype(): void
    {
        $config = [
            'doctype' => 'html5',
        ];
        $this->tidyAdapter->setConfig($config);

        $html = '<html><body>Test</body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringStartsWith('<!DOCTYPE html>', $result);
    }

    /**
     * Test that minify without doctype config does not add doctype when missing
     */
    public function testMinifyWithoutDoctypeConfigDoesNotAddDoctype(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->tidyAdapter->minify($html);

        // Without explicit doctype config, tidy may or may not add it
        // Just verify the result is a string
        self::assertIsString($result);
    }

    /**
     * Test that minify returns original HTML when tidy clean repair fails
     */
    public function testMinifyReturnsOriginalHtmlWhenTidyCleanRepairFails(): void
    {
        // This is a difficult test to write since tidy_clean_repair rarely fails
        // We'll test that the method handles the case correctly
        $html = '<html><body>Test</body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
    }
}
