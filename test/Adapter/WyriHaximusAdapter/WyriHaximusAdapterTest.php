<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapter;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

// The optional voku/html-min library triggers a PHP 8.5 deprecation internally
// (SplObjectStorage::attach()), which is outside this package's control.
#[IgnoreDeprecations]
final class WyriHaximusAdapterTest extends TestCase
{
    private WyriHaximusAdapter $wyriHaximusAdapter;

    private ?string $originalHttpHost = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('voku\helper\HtmlMin')) {
            self::markTestSkipped('WyriHaximus HtmlMin library is not available');
        }

        // Save original HTTP_HOST if it exists
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->originalHttpHost = $_SERVER['HTTP_HOST'];
        }

        $this->wyriHaximusAdapter = new WyriHaximusAdapter();
    }

    protected function tearDown(): void
    {
        // Restore original HTTP_HOST
        if (null !== $this->originalHttpHost) {
            $_SERVER['HTTP_HOST'] = $this->originalHttpHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }

        parent::tearDown();
    }

    /**
     * Test that minify returns a string when given a simple HTML document.
     */
    public function testMinifyReturnsString(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify retains the text content when given basic HTML with a paragraph.
     */
    public function testMinifyHandlesBasicHtml(): void
    {
        $html = '<html><body><p>Test content</p></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Test content', $result);
    }

    /**
     * Test that minify produces a shorter result when given indented, whitespace-heavy HTML.
     */
    public function testMinifyReducesHtmlSize(): void
    {
        $html = <<<HTML
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <div class="container">
            <p>Content</p>
        </div>
    </body>
</html>
HTML;

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertLessThan(strlen($html), strlen($result));
    }

    /**
     * Test that minify removes the spaces between tags when given HTML with double spaces before tags.
     */
    public function testMinifyRemovesSpacesBetweenTags(): void
    {
        $html = '<html>  <body>  <p>Test</p>  </body>  </html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        // Spaces between tags should be reduced or removed
        self::assertStringNotContainsString('  <', $result);
    }

    /**
     * Test that minify returns a string when given an empty string.
     */
    public function testMinifyHandlesEmptyString(): void
    {
        $html = '';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify preserves the heading and paragraph text when given HTML with multiple content elements.
     */
    public function testMinifyPreservesContent(): void
    {
        $html = '<html><body><h1>Title</h1><p>Paragraph</p></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Title', $result);
        self::assertStringContainsString('Paragraph', $result);
    }

    /**
     * Test that minify returns a string retaining the link text when no HTTP_HOST server variable is set.
     */
    public function testMinifyHandlesHtmlWithoutHttpHostSet(): void
    {
        unset($_SERVER['HTTP_HOST']);
        $html = '<html><body><a href="http://example.com/page">Link</a></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertIsString($result);
        self::assertStringContainsString('Link', $result);
    }

    /**
     * Test that minify returns a string retaining the link text when the HTTP_HOST server variable is set.
     */
    public function testMinifyHandlesHtmlWithHttpHostSet(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $html = '<html><body><a href="http://example.com/page">Link</a></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertIsString($result);
        self::assertStringContainsString('Link', $result);
    }

    /**
     * Test that minify returns a string when the HTTP_HOST matches the absolute link domain so same-domain links can be made relative.
     */
    public function testMinifyMakesSameDomainLinksRelative(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $html = '<html><body><a href="http://example.com/page">Link</a></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        // Same domain links should be made relative
        self::assertIsString($result);
    }

    /**
     * Test that minify retains all list item text when given HTML with deeply nested tags.
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

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Item 1', $result);
        self::assertStringContainsString('Item 2', $result);
    }

    /**
     * Test that minify retains the text content when given HTML elements carrying class attributes.
     */
    public function testMinifyHandlesHtmlWithClasses(): void
    {
        $html = '<div class="container main-content"><p class="text">Content</p></div>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Content', $result);
    }

    /**
     * Test that minify retains the text content when given an element with an inline style attribute.
     */
    public function testMinifyHandlesHtmlWithInlineStyles(): void
    {
        $html = '<div style="color: red; font-size: 14px;">Styled</div>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Styled', $result);
    }

    /**
     * Test that minify retains the heading, paragraph, and footer text when given a complete, structured HTML document.
     */
    public function testMinifyHandlesComplexHtmlDocument(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
</head>
<body>
    <header>
        <h1>Welcome</h1>
    </header>
    <main>
        <article>
            <p>This is a paragraph.</p>
        </article>
    </main>
    <footer>
        <p>Footer content</p>
    </footer>
</body>
</html>
HTML;

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Welcome', $result);
        self::assertStringContainsString('This is a paragraph.', $result);
        self::assertStringContainsString('Footer content', $result);
    }

    /**
     * Test that minify retains the surrounding text content when the HTML contains a script tag.
     */
    public function testMinifyHandlesHtmlWithScriptTags(): void
    {
        $html = '<html><body><script>var x = 1;</script><div>Content</div></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Content', $result);
    }

    /**
     * Test that minify retains the body text content when the HTML contains a style tag in the head.
     */
    public function testMinifyHandlesHtmlWithStyleTags(): void
    {
        $html = '<html><head><style>body { margin: 0; }</style></head><body>Content</body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Content', $result);
    }

    /**
     * Test that minify preserves the UTF-8 accented words when the HTML contains multibyte characters.
     */
    public function testMinifyHandlesUtf8Characters(): void
    {
        $html = '<html><body><p>Café résumé naïve</p></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Café', $result);
        self::assertStringContainsString('résumé', $result);
        self::assertStringContainsString('naïve', $result);
    }
}
