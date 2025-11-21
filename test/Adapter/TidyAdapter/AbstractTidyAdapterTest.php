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
     * Test that postProcess trims whitespace
     */
    public function testPostProcessTrimsWhitespace(): void
    {
        $input = "  \t\n  <html>Content</html>  \n\t  ";
        $expected = '<html>Content</html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess handles empty string
     */
    public function testPostProcessHandlesEmptyString(): void
    {
        $input = '';
        $expected = '';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess does not add doctype when config missing
     */
    public function testPostProcessDoesNotAddDoctypeWhenConfigMissing(): void
    {
        $input = '<html><body>Test</body></html>';
        $expected = '<html><body>Test</body></html>';

        $actual = $this->tidyAdapter->callPostProcess($input);

        self::assertSame($expected, $actual);
    }

    /**
     * Test that postProcess does not add doctype when doctype config not set
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
     * Test that postProcess does not add doctype when not html5
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
     * Test that postProcess adds doctype when html5 and missing
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
     * Test that postProcess does not duplicate doctype when already present
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
     * Test that postProcess adds doctype with newline separator
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
     * Test that postProcess trims before adding doctype
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
     * Test that postProcess handles html5 doctype with different cases
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
     * Test that postProcess does not add doctype for HTML5 uppercase
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
     * Test that postProcess handles whitespace-only string with html5 config
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
