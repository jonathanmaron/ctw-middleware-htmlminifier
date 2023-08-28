<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HtmlMinifierMiddleware extends AbstractHtmlMinifierMiddleware
{
    protected AdapterInterface $adapter;

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function setAdapter(AdapterInterface $adapter): HtmlMinifierMiddleware
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$this->containsHtml($response)) {
            return $response;
        }

        $htmlSource = $response->getBody()->getContents();

        if ($htmlSource === '') {
            return $response;
        }

        $htmlMinified = $this->getAdapter()->minify($htmlSource);

        [$in, $out, $diff] = $this->getSuffixStatistics($htmlSource, $htmlMinified);

        $htmlMinified .= PHP_EOL . sprintf(self::HTML_SUFFIX, $in, $out, $diff);

        $body = Factory::getStreamFactory()->createStream($htmlMinified);

        return $response->withBody($body);
    }
}
