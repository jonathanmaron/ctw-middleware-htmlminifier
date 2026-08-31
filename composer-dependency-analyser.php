<?php
declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$configuration = new Configuration();

/*
 * WyriHaximusAdapter is one of three adapters and the only one with a
 * dependency of its own. voku/html-min and wyrihaximus/html-compress stay in
 * require-dev, so a consumer picking SimpleAdapter or TidyAdapter does not
 * install a compressor it will never call; the adapter guards for their
 * absence rather than assuming them.
 *
 * That is a genuinely optional dependency, which the analyser reports as a dev
 * dependency used in production code. Promoting either package to require
 * would silence it and make every consumer pay for an adapter most of them do
 * not select.
 *
 * The exclusion is scoped to the one adapter that reaches them, so the same
 * import appearing anywhere else in src/ is still reported.
 */
$configuration->ignoreErrorsOnPackagesAndPaths(
    [
        'voku/html-min',
        'wyrihaximus/html-compress',
    ],
    [
        sprintf('%s/src/Adapter/WyriHaximusAdapter', __DIR__),
    ],
    [ErrorType::DEV_DEPENDENCY_IN_PROD]
);

return $configuration;
