<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\AbstractTidyAdapter;
use PHPUnit\Framework\TestCase;

final class AbstractTidyAdapterTest extends TestCase
{
    private AbstractTidyAdapter $tidyAdapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tidyAdapter = new class() extends AbstractTidyAdapter {
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
     * Test that postProcess trims surrounding whitespace when the input is padded with leading and trailing spaces, tabs, and newlines.
     */
    public function testPostProcessTrimsWhitespace(): void
    {
        $input = "  \t\n  <html>Content</html>  \n\t  ";
        $expected = '<html>Content</html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns an empty string when the input is an empty string.
     */
    public function testPostProcessHandlesEmptyString(): void
    {
        $input = '';
        $expected = '';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when no config has been set.
     */
    public function testPostProcessDoesNotAddDoctypeWhenConfigMissing(): void
    {
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the config has no doctype key.
     */
    public function testPostProcessDoesNotAddDoctypeWhenDoctypeConfigNotSet(): void
    {
        $this->tidyAdapter->setConfig([
            'other' => 'value',
        ]);
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the doctype config is a non-html5 value.
     */
    public function testPostProcessDoesNotAddDoctypeWhenNotHtml5(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html4',
        ]);
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess prepends the HTML5 doctype when the doctype config is html5 and the input has no doctype.
     */
    public function testPostProcessAddsDoctypeWhenHtml5AndMissing(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html5',
        ]);
        $input = '<html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertStringStartsWith('<!DOCTYPE html>', $actual);
        self::assertStringContainsString('<html><body>Test</body></html>', $actual);
    }

    /**
     * Test that postProcess leaves the input unchanged when the doctype config is html5 and the input already starts with a doctype.
     */
    public function testPostProcessDoesNotDuplicateDoctypeWhenAlreadyPresent(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html5',
        ]);
        $input = '<!DOCTYPE html><html><body>Test</body></html>';
        $expected = '<!DOCTYPE html><html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess separates the prepended HTML5 doctype from the HTML with a newline when the doctype config is html5.
     */
    public function testPostProcessAddsDoctypeWithNewlineSeparator(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html5',
        ]);
        $input = '<html><body>Test</body></html>';
        $expected = "<!DOCTYPE html>\n<html><body>Test</body></html>";

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess trims surrounding whitespace before prepending the HTML5 doctype when the doctype config is html5 and the input is padded.
     */
    public function testPostProcessTrimsBeforeAddingDoctype(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html5',
        ]);
        $input = "  \n\t<html><body>Test</body></html>  \n\t";
        $expected = "<!DOCTYPE html>\n<html><body>Test</body></html>";

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess prepends the HTML5 doctype when the doctype config exactly matches the lowercase html5 value.
     */
    public function testPostProcessHandlesHtml5DoctypeWithExactCase(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html5',
        ]);
        $input = '<html>Content</html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertStringStartsWith('<!DOCTYPE html>', $actual);
    }

    /**
     * Test that postProcess returns the input unchanged when the doctype config is the uppercase HTML5 value rather than lowercase html5.
     */
    public function testPostProcessDoesNotAddDoctypeForHtml5Uppercase(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'HTML5',
        ]);
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        // Only 'html5' lowercase triggers doctype addition
        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess returns only the HTML5 doctype followed by a newline when the doctype config is html5 and the input is whitespace-only.
     */
    public function testPostProcessHandlesWhitespaceOnlyStringWithHtml5Config(): void
    {
        $this->tidyAdapter->setConfig([
            'doctype' => 'html5',
        ]);
        $input = "  \t\n\r  ";
        // After trimming, it becomes empty, and doctype is added to empty string
        $expected = "<!DOCTYPE html>\n";

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }
}
