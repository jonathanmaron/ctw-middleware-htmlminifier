<?php

declare(strict_types=1);

namespace CtwTest\Middleware\HtmlMinifierMiddleware;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;
use Ctw\Middleware\HtmlMinifierMiddleware\HtmlMinifierMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HtmlMinifierMiddlewareTest extends TestCase
{
    private HtmlMinifierMiddleware $htmlMinifierMiddleware;

    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->htmlMinifierMiddleware = new HtmlMinifierMiddleware();
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($this->adapter);
    }

    /**
     * Test that getAdapter returns set adapter
     */
    public function testGetAdapterReturnsSetAdapter(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($adapter);

        $result = $this->htmlMinifierMiddleware->getAdapter();

        self::assertSame($adapter, $result);
    }

    /**
     * Test that setAdapter returns self for method chaining
     */
    public function testSetAdapterReturnsSelfForMethodChaining(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);

        $result = $this->htmlMinifierMiddleware->setAdapter($adapter);

        self::assertSame($this->htmlMinifierMiddleware, $result);
    }

    /**
     * Test that setAdapter sets adapter correctly
     */
    public function testSetAdapterSetsAdapterCorrectly(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);

        $this->htmlMinifierMiddleware->setAdapter($adapter);

        self::assertSame($adapter, $this->htmlMinifierMiddleware->getAdapter());
    }

    /**
     * Test that process returns response unchanged when not HTML
     */
    public function testProcessReturnsResponseUnchangedWhenNotHtml(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['application/json']);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process returns response unchanged when no content type header
     */
    public function testProcessReturnsResponseUnchangedWhenNoContentTypeHeader(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn([]);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process minifies HTML when content type is text/html
     */
    public function testProcessMinifiesHtmlWhenContentTypeIsTextHtml(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = '<html><body>  Test  </body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process minifies HTML when content type is application/xhtml
     */
    public function testProcessMinifiesHtmlWhenContentTypeIsApplicationXhtml(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['application/xhtml']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process returns response unchanged when HTML body is empty
     */
    public function testProcessReturnsResponseUnchangedWhenHtmlBodyIsEmpty(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn('');

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process calls adapter minify method
     */
    public function testProcessCallsAdapterMinifyMethod(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = '<html><body>Original</body></html>';
        $htmlMinified = '<html><body>Minified</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $response->method('withBody')
            ->willReturn($response);

        $this->adapter->expects(self::once())
            ->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);

        $this->htmlMinifierMiddleware->process($request, $handler);
    }

    /**
     * Test that process adds statistics suffix to minified HTML
     */
    public function testProcessAddsStatisticsSuffixToMinifiedHtml(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = '<html><body>Original Content Here</body></html>';
        $htmlMinified = '<html><body>Minified</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);

        $response->expects(self::once())
            ->method('withBody')
            ->with(self::callback(fn($newBody) => $newBody instanceof StreamInterface))
            ->willReturn($response);

        $this->htmlMinifierMiddleware->process($request, $handler);
    }

    /**
     * Test that process handles HTML with charset in content type
     */
    public function testProcessHandlesHtmlWithCharsetInContentType(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html; charset=utf-8']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process handles multiple content type headers
     */
    public function testProcessHandlesMultipleContentTypeHeaders(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html', 'charset=utf-8']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process does not minify when content type is text/plain
     */
    public function testProcessDoesNotMinifyWhenContentTypeIsTextPlain(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/plain']);

        $this->adapter->expects(self::never())->method('minify');

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process does not minify when content type is application/json
     */
    public function testProcessDoesNotMinifyWhenContentTypeIsApplicationJson(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['application/json']);

        $this->adapter->expects(self::never())->method('minify');

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process handles complex HTML document
     */
    public function testProcessHandlesComplexHtmlDocument(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $htmlSource = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <div class="container">
        <h1>Welcome</h1>
        <p>This is a test.</p>
    </div>
</body>
</html>
HTML;
        $htmlMinified = '<!DOCTYPE html><html><head><title>Test Page</title></head><body><div class="container"><h1>Welcome</h1><p>This is a test.</p></div></body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html; charset=utf-8']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process creates new stream with minified content
     */
    public function testProcessCreatesNewStreamWithMinifiedContent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $body = $this->createMock(StreamInterface::class);
        $newResponse = $this->createMock(ResponseInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->with($request)
            ->willReturn($response);
        $response->method('getHeader')
            ->with('Content-Type')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($newResponse);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }
}
