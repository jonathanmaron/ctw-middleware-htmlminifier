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
        $this->adapter = self::createStub(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($this->adapter);
    }

    /**
     * Test that getAdapter returns the same adapter instance when an adapter has been set.
     */
    public function testGetAdapterReturnsSetAdapter(): void
    {
        $adapter = self::createStub(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($adapter);

        $result = $this->htmlMinifierMiddleware->getAdapter();

        self::assertSame($adapter, $result);
    }

    /**
     * Test that setAdapter returns the middleware instance to allow method chaining when an adapter is set.
     */
    public function testSetAdapterReturnsSelfForMethodChaining(): void
    {
        $adapter = self::createStub(AdapterInterface::class);

        $result = $this->htmlMinifierMiddleware->setAdapter($adapter);

        self::assertSame($this->htmlMinifierMiddleware, $result);
    }

    /**
     * Test that setAdapter stores the adapter so getAdapter returns it when an adapter is set.
     */
    public function testSetAdapterSetsAdapterCorrectly(): void
    {
        $adapter = self::createStub(AdapterInterface::class);

        $this->htmlMinifierMiddleware->setAdapter($adapter);

        self::assertSame($adapter, $this->htmlMinifierMiddleware->getAdapter());
    }

    /**
     * Test that process returns the response unchanged when the content type is not HTML.
     */
    public function testProcessReturnsResponseUnchangedWhenNotHtml(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['application/json']);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process returns the response unchanged when the response has no content type header.
     */
    public function testProcessReturnsResponseUnchangedWhenNoContentTypeHeader(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn([]);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process minifies the body and returns a response when the content type is text/html.
     */
    public function testProcessMinifiesHtmlWhenContentTypeIsTextHtml(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

        $htmlSource = '<html><body>  Test  </body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process minifies the body and returns a response when the content type is application/xhtml.
     */
    public function testProcessMinifiesHtmlWhenContentTypeIsApplicationXhtml(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['application/xhtml']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process returns the response unchanged when the HTML response body is empty.
     */
    public function testProcessReturnsResponseUnchangedWhenHtmlBodyIsEmpty(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn('');

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process calls the adapter minify method once with the original HTML when the content type is text/html.
     */
    public function testProcessCallsAdapterMinifyMethod(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);
        $adapter = $this->createMock(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($adapter);

        $htmlSource = '<html><body>Original</body></html>';
        $htmlMinified = '<html><body>Minified</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $response->method('withBody')
            ->willReturn($response);

        $adapter->expects(self::once())
            ->method('minify')
            ->with($htmlSource)
            ->willReturn($htmlMinified);

        $this->htmlMinifierMiddleware->process($request, $handler);
    }

    /**
     * Test that process writes a new stream body onto the response when the HTML content is minified.
     */
    public function testProcessAddsStatisticsSuffixToMinifiedHtml(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

        $htmlSource = '<html><body>Original Content Here</body></html>';
        $htmlMinified = '<html><body>Minified</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);

        $response->expects(self::once())
            ->method('withBody')
            ->with(self::callback(fn($newBody) => $newBody instanceof StreamInterface))
            ->willReturn($response);

        $this->htmlMinifierMiddleware->process($request, $handler);
    }

    /**
     * Test that process minifies the body and returns a response when the content type includes a charset.
     */
    public function testProcessHandlesHtmlWithCharsetInContentType(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html; charset=utf-8']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process minifies the body and returns a response when the content type header has multiple values.
     */
    public function testProcessHandlesMultipleContentTypeHeaders(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html', 'charset=utf-8']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process never calls the adapter and returns the response unchanged when the content type is text/plain.
     */
    public function testProcessDoesNotMinifyWhenContentTypeIsTextPlain(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $adapter = $this->createMock(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($adapter);

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/plain']);

        $adapter->expects(self::never())->method('minify');

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process never calls the adapter and returns the response unchanged when the content type is application/json.
     */
    public function testProcessDoesNotMinifyWhenContentTypeIsApplicationJson(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $adapter = $this->createMock(AdapterInterface::class);
        $this->htmlMinifierMiddleware->setAdapter($adapter);

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['application/json']);

        $adapter->expects(self::never())->method('minify');

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertSame($response, $result);
    }

    /**
     * Test that process minifies the body and returns a response when given a complex multi-line HTML document.
     */
    public function testProcessHandlesComplexHtmlDocument(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);

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
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html; charset=utf-8']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($response);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * Test that process returns the new response produced by withBody when the minified content is written to a new stream.
     */
    public function testProcessCreatesNewStreamWithMinifiedContent(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $response = self::createStub(ResponseInterface::class);
        $handler = self::createStub(RequestHandlerInterface::class);
        $body = self::createStub(StreamInterface::class);
        $newResponse = self::createStub(ResponseInterface::class);

        $htmlSource = '<html><body>Test</body></html>';
        $htmlMinified = '<html><body>Test</body></html>';

        $handler->method('handle')
            ->willReturn($response);
        $response->method('getHeader')
            ->willReturn(['text/html']);
        $response->method('getBody')
            ->willReturn($body);
        $body->method('getContents')
            ->willReturn($htmlSource);
        $this->adapter->method('minify')
            ->willReturn($htmlMinified);
        $response->method('withBody')
            ->willReturn($newResponse);

        $result = $this->htmlMinifierMiddleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }
}
