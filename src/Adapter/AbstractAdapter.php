<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter;

abstract class AbstractAdapter
{
    protected array $config = [];

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    protected function trim(string $htmlMinified): string
    {
        return trim($htmlMinified);
    }
}
