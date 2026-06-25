<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapter;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TidyCleanRepairOverride.php';

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
     * Test that minify returns a string when given well-formed HTML.
     */
    public function testMinifyReturnsStringWhenGivenWellFormedHtml(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify preserves the text content when given basic HTML.
     */
    public function testMinifyPreservesTextContentWhenGivenBasicHtml(): void
    {
        $html = '<html><body><p>Test content</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Test content', $result);
    }

    /**
     * Test that minify preserves the content when cleaning and repairing malformed HTML.
     */
    public function testMinifyPreservesContentWhenRepairingMalformedHtml(): void
    {
        $html = '<div><p>Unclosed paragraph<div>Nested incorrectly</div>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
        self::assertStringContainsString('Unclosed paragraph', $result);
        self::assertStringContainsString('Nested incorrectly', $result);
    }

    /**
     * Test that minify returns a string when a custom tidy configuration is applied.
     */
    public function testMinifyReturnsStringWhenCustomConfigIsApplied(): void
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
     * Test that minify trims leading whitespace when post-processing the output.
     */
    public function testMinifyTrimsLeadingWhitespaceWhenPostProcessing(): void
    {
        $html = '<html><body>Content</body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringStartsNotWith(' ', $result);
        self::assertStringStartsNotWith("\n", $result);
    }

    /**
     * Test that minify returns a string when given an empty input string.
     */
    public function testMinifyReturnsStringWhenGivenEmptyString(): void
    {
        $html = '';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify preserves heading and paragraph content when given structured HTML.
     */
    public function testMinifyPreservesContentWhenGivenStructuredHtml(): void
    {
        $html = '<html><body><h1>Title</h1><p>Paragraph text</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Title', $result);
        self::assertStringContainsString('Paragraph text', $result);
    }

    /**
     * Test that minify preserves multibyte characters when given UTF-8 encoded HTML.
     */
    public function testMinifyPreservesMultibyteCharactersWhenGivenUtf8Html(): void
    {
        $html = '<html><body><p>Café résumé naïve</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertStringContainsString('Café', $result);
        self::assertStringContainsString('résumé', $result);
        self::assertStringContainsString('naïve', $result);
    }

    /**
     * Test that minify preserves the decoded text when given HTML entity references.
     */
    public function testMinifyPreservesTextWhenGivenHtmlEntities(): void
    {
        $html = '<html><body><p>&lt;div&gt; &amp; &quot;test&quot;</p></body></html>';

        $result = $this->tidyAdapter->minify($html);

        self::assertIsString($result);
        self::assertStringContainsString('test', $result);
    }

    /**
     * Test that minify preserves list items when given deeply nested tags.
     */
    public function testMinifyPreservesListItemsWhenGivenNestedTags(): void
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
     * Test that minify prepends the doctype when the html5 doctype config is set.
     */
    public function testMinifyPrependsDoctypeWhenHtml5DoctypeConfigIsSet(): void
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
     * Test that minify returns a string when no doctype config is provided.
     */
    public function testMinifyReturnsStringWhenNoDoctypeConfigIsProvided(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->tidyAdapter->minify($html);

        // Without an explicit doctype config tidy may or may not add one;
        // the contract only guarantees a string is returned.
        self::assertIsString($result);
    }

    /**
     * Test that minify returns the unmodified source when tidy_clean_repair reports failure.
     *
     * The {@see \tidy_clean_repair()} call is intercepted by the namespaced shim in
     * TidyCleanRepairOverride.php so the otherwise unreachable early-return guard
     * (`return $htmlSource;`) can be exercised deterministically.
     */
    public function testMinifyReturnsUnmodifiedSourceWhenCleanRepairFails(): void
    {
        $GLOBALS['__ctw_force_tidy_clean_repair_fail'] = true;

        try {
            $html = '<html><body>Test</body></html>';

            $result = $this->tidyAdapter->minify($html);

            self::assertSame($html, $result);
        } finally {
            $GLOBALS['__ctw_force_tidy_clean_repair_fail'] = false;
        }
    }
}
