<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\SimpleAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;

class SimpleAdapter extends AbstractSimpleAdapter implements AdapterInterface
{
    public function minify(string $htmlSource): string
    {
        $lut = [
            '/(\n|^)(\x20+|\t)/'      => "\n",
            '/(\n|^)\/\/(.*?)(\n|$)/' => "\n",
            '/\n/'                    => ' ',
            '/\<\!--.*?-->/'          => '',
            '/(\x20+|\t)/'            => ' ',   // Delete multi space (Without \n)
            '/\>\s+\</'               => '> <', // Replace white spaces between tags with one space
            '/(\"|\')\s+\>/'          => '$1>', // Strip whitespaces between quotation ("') and end tags
            '/=\s+(\"|\')/'           => '=$1', // Strip whitespaces between = "'
        ];

        $htmlMinified = preg_replace(array_keys($lut), array_values($lut), $htmlSource);
        assert(is_string($htmlMinified));

        return $this->postProcess($htmlMinified);
    }
}
