<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

use tidy;
use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;

class TidyAdapter extends AbstractTidyAdapter implements AdapterInterface
{
    public function minify(string $htmlSource): string
    {
        $tidy = tidy_parse_string($htmlSource, $this->getConfig(), 'utf8');
        assert($tidy instanceof tidy);

        //dd($tidy->errorBuffer);

        if (!tidy_clean_repair($tidy)) {
            return $htmlSource;
        }

        return $this->postProcess($tidy->html()->value);
    }
}
