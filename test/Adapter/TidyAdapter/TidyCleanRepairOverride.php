<?php

declare(strict_types=1);

/**
 * Test-only override of the internal {@see \tidy_clean_repair()} function.
 *
 * The production class {@see \Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter\TidyAdapter}
 * calls the unqualified function `tidy_clean_repair()` from within its own namespace. PHP resolves
 * an unqualified internal function call to the current namespace first, falling back to the global
 * function only when no namespaced function exists. By declaring a namespaced shim here we can
 * deterministically force the rarely reachable failure branch (`return $htmlSource;`) without
 * touching `src/` or relying on libtidy ever returning false for real input.
 *
 * The shim is transparent by default: it delegates to the genuine global function unless the
 * `$GLOBALS['__ctw_force_tidy_clean_repair_fail']` flag is explicitly set to true.
 */

namespace Ctw\Middleware\HtmlMinifierMiddleware\Adapter\TidyAdapter;

if (!\function_exists(__NAMESPACE__ . '\\tidy_clean_repair')) {
    /**
     * Namespaced shim that can force {@see \tidy_clean_repair()} to report failure.
     *
     * @param \tidy $tidy The parsed tidy document to clean and repair.
     */
    function tidy_clean_repair(\tidy $tidy): bool
    {
        if (true === ($GLOBALS['__ctw_force_tidy_clean_repair_fail'] ?? false)) {
            return false;
        }

        return \tidy_clean_repair($tidy);
    }
}
