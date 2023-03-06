<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AbstractAdapter as ParentAbstractAdapter;

class AbstractWyriHaximusAdapter extends ParentAbstractAdapter
{
    protected function postProcess(string $htmlMinified): string
    {
        return $htmlMinified;
    }
}
