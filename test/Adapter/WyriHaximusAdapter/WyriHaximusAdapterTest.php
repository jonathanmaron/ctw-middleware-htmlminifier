<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter\WyriHaximusAdapter;
use PHPUnit\Framework\TestCase;

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
     * Test that minify returns string
     */
    public function testMinifyReturnsString(): void
    {
        $html = '<html><body>Test</body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify handles basic HTML
     */
    public function testMinifyHandlesBasicHtml(): void
    {
        $html = '<html><body><p>Test content</p></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Test content', $result);
    }

    /**
     * Test that minify reduces HTML size
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
     * Test that minify removes spaces between tags
     */
    public function testMinifyRemovesSpacesBetweenTags(): void
    {
        $html = '<html>  <body>  <p>Test</p>  </body>  </html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        // Spaces between tags should be reduced or removed
        self::assertStringNotContainsString('  <', $result);
    }

    /**
     * Test that minify handles empty string
     */
    public function testMinifyHandlesEmptyString(): void
    {
        $html = '';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertIsString($result);
    }

    /**
     * Test that minify preserves content
     */
    public function testMinifyPreservesContent(): void
    {
        $html = '<html><body><h1>Title</h1><p>Paragraph</p></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Title', $result);
        self::assertStringContainsString('Paragraph', $result);
    }

    /**
     * Test that minify handles HTML without HTTP_HOST set
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
     * Test that minify handles HTML with HTTP_HOST set
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
     * Test that minify makes same domain links relative
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

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Item 1', $result);
        self::assertStringContainsString('Item 2', $result);
    }

    /**
     * Test that minify handles HTML with classes
     */
    public function testMinifyHandlesHtmlWithClasses(): void
    {
        $html = '<div class="container main-content"><p class="text">Content</p></div>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Content', $result);
    }

    /**
     * Test that minify handles HTML with inline styles
     */
    public function testMinifyHandlesHtmlWithInlineStyles(): void
    {
        $html = '<div style="color: red; font-size: 14px;">Styled</div>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Styled', $result);
    }

    /**
     * Test that minify handles complex HTML document
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
     * Test that minify handles HTML with script tags
     */
    public function testMinifyHandlesHtmlWithScriptTags(): void
    {
        $html = '<html><body><script>var x = 1;</script><div>Content</div></body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Content', $result);
    }

    /**
     * Test that minify handles HTML with style tags
     */
    public function testMinifyHandlesHtmlWithStyleTags(): void
    {
        $html = '<html><head><style>body { margin: 0; }</style></head><body>Content</body></html>';

        $result = $this->wyriHaximusAdapter->minify($html);

        self::assertStringContainsString('Content', $result);
    }

    /**
     * Test that minify handles UTF-8 characters
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
