<?php
declare(strict_types=1);

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\WyriHaximusAdapter;

use Ctw\Middleware\HtmlMinifierMiddleware\Adapter\AdapterInterface;
use voku\helper\HtmlMin;
use WyriHaximus\HtmlCompress\Factory;

class WyriHaximusAdapter extends AbstractWyriHaximusAdapter implements AdapterInterface
{
    public function minify(string $htmlSource): string
    {
        /** @phpstan-ignore class.notFound */
        $htmlMin = new HtmlMin();

        $httpHost = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $httpHost = $_SERVER['HTTP_HOST'];
        }

        // optimize html via "HtmlDomParser()"
        // $htmlMin->doOptimizeViaHtmlDomParser();

        // remove default HTML comments (depends on "doOptimizeViaHtmlDomParser(true)")
        // $htmlMin->doRemoveComments();

        // sum-up extra whitespace from the Dom (depends on "doOptimizeViaHtmlDomParser(true)")
        // $htmlMin->doSumUpWhitespace();

        // remove whitespace around tags (depends on "doOptimizeViaHtmlDomParser(true)")
        // $htmlMin->doRemoveWhitespaceAroundTags();

        // optimize html attributes (depends on "doOptimizeViaHtmlDomParser(true)")
        // $htmlMin->doOptimizeAttributes();

        // remove optional "http:"-prefix from attributes (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveHttpPrefixFromAttributes();

        // remove optional "https:"-prefix from attributes (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveHttpsPrefixFromAttributes();

        // keep "http:"- and "https:"-prefix for all external links
        // $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes();

        // make some links relative, by removing the domain from attributes
        $htmlMin->doMakeSameDomainsLinksRelative([$httpHost]);

        // remove defaults (depends on "doOptimizeAttributes(true)" | disabled by default)
        // $htmlMin->doRemoveDefaultAttributes();

        // remove deprecated anchor-jump (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveDeprecatedAnchorName();

        // remove deprecated charset-attribute - the browser will use the charset from the HTTP-Header, anyway
        // (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveDeprecatedScriptCharsetAttribute();

        // remove deprecated script-mime-types (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveDeprecatedTypeFromScriptTag();

        // remove "type=text/css" for css links (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveDeprecatedTypeFromStylesheetLink();

        // remove "type=text/css" from all links and styles
        // $htmlMin->doRemoveDeprecatedTypeFromStyleAndLinkTag();

        // remove "media="all" from all links and styles
        // $htmlMin->doRemoveDefaultMediaTypeFromStyleAndLinkTag();

        // remove type="submit" from button tags
        // $htmlMin->doRemoveDefaultTypeFromButton();

        // remove some empty attributes (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveEmptyAttributes();

        // remove 'value=""' from empty <input> (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doRemoveValueFromEmptyInput();

        // sort css-class-names, for better gzip results (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doSortCssClassNames();

        // sort html-attributes, for better gzip results (depends on "doOptimizeAttributes(true)")
        // $htmlMin->doSortHtmlAttributes();

        // remove more (aggressive) spaces in the dom (disabled by default)
        $htmlMin->doRemoveSpacesBetweenTags();

        // remove quotes e.g. class="lall" => class=lall
        $htmlMin->doRemoveOmittedQuotes(false);

        // remove omitted html tags e.g. <p>lall</p> => <p>lall
        // $htmlMin->doRemoveOmittedHtmlTags();

        /** @phpstan-ignore class.notFound, method.nonObject */
        $htmlModified = Factory::constructSmallest()->withHtmlMin($htmlMin)->compress($htmlSource);
        assert(is_string($htmlModified));

        return $this->postProcess($htmlModified);
    }
}
