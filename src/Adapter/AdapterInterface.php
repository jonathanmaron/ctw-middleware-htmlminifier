<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter;

interface AdapterInterface
{
    public function setConfig(array $config): AbstractAdapter;

    public function minify(string $htmlSource): string;
}
