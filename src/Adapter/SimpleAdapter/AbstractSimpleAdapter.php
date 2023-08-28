<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AbstractAdapter as ParentAbstractAdapter;

class AbstractSimpleAdapter extends ParentAbstractAdapter
{
    protected function postProcess(string $htmlMinified): string
    {
        return $this->trim($htmlMinified);
    }
}
