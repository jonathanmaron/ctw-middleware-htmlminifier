<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AbstractAdapter as ParentAbstractAdapter;

class AbstractTidyAdapter extends ParentAbstractAdapter
{
    protected function postProcess(string $htmlMinified): string
    {
        $htmlMinified = $this->trim($htmlMinified);
        $htmlMinified = $this->doctype($htmlMinified);

        return $htmlMinified;
    }

    /**
     * Tidy removes the doctype when parsing HTML5 (bug?).
     * This causes the browser to switch to quirks mode, which is undesirable.
     * This method re-adds the doctype in the case of HTML5.
     *
     * @param string $html
     *
     * @return string
     */
    private function doctype(string $html): string
    {
        $config = $this->getConfig();

        if (!isset($config['doctype'])) {
            return $html;
        }

        if ('html5' !== $config['doctype']) {
            return $html;
        }

        if (str_starts_with($html, $prefix = '<!DOCTYPE html>')) {
            return $html;
        }

        return $prefix . PHP_EOL . $html;
    }
}
