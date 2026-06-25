# PHP 8.5.7 Migration — `ctw/ctw-middleware-htmlminifier`

- **Branch:** `php85` (cut from `master`)
- **Runtime:** PHP 8.3.31 → **8.5.7**
- **PHPUnit:** 12 → **13.2.1**
- **Status:** ✅ done

PSR-15 middleware that formats and beautifies the HTML in the response body.
Requires `ext-tidy` (present in this environment). Depends on
`ctw/ctw-middleware`; the PHP 8.5 fixes arrive transitively through that base
package's `dev-php85` branch.

## Audit checklist

### Composer resolution (root blocker)

- [x] **(fatal) `composer update -W`** — under PHP 8.5 the update aborted:
  `ctw/ctw-middleware ^4.0` → `laminas/laminas-diactoros ^2.11`, and Diactoros
  2.x caps PHP at `~8.0 || ~8.1 || ~8.2 || ~8.3`, so it refused PHP 8.5.7. The
  cap was transitive — this package has no direct `laminas-diactoros` require.
  - **Fix:** require `ctw/ctw-middleware: dev-php85`, which bumps Diactoros to
    `^3` (3.8.0), `middlewares/utils` to `^4` (4.0.2) and
    `laminas/laminas-servicemanager` to 4.5.1. Resolution is green under 8.5.7.

### Vendor runtime deprecations (`middlewares/utils`)

These "implicitly nullable parameter" deprecations were emitted by
`middlewares/utils` 3.x under PHP 8.5 and surfaced through this package's
factory tests. No first-party `src/` change is required — they are cleared by
the dependency bump.

- [x] **(deprecation) `vendor/middlewares/utils/src/Factory.php:88`** —
  `Factory::createUploadedFile()` parameter `$size` implicitly nullable.
  - **Fix:** resolved by `middlewares/utils ^4` (explicit `?int $size`), pulled
    in via `ctw/ctw-middleware: dev-php85`.
- [x] **(deprecation) `vendor/middlewares/utils/src/Factory.php:90`** —
  `Factory::createUploadedFile()` parameter `$filename` implicitly nullable.
  - **Fix:** resolved by `middlewares/utils ^4` (explicit `?string $filename`).
- [x] **(deprecation) `vendor/middlewares/utils/src/Factory.php:91`** —
  `Factory::createUploadedFile()` parameter `$mediaType` implicitly nullable.
  - **Fix:** resolved by `middlewares/utils ^4` (explicit `?string $mediaType`).

### Test suite — PHPUnit 13 mock modernization

PHPUnit 13 emits a "test double without configured expectations" notice for
`createMock()` doubles used purely as stubs. Stub-style doubles were migrated to
`createStub()`; `createMock()` was kept only where the test asserts call counts
with `->expects()`.

- [x] **(notice) `test/Adapter/SimpleAdapter/SimpleAdapterFactoryTest.php`** —
  container doubles created with `createMock()` configured only with `method()`.
  - **Fix:** migrated to `createStub()`; dropped now-redundant `->with('config')`
    on stubs whose return value is fixed.
- [x] **(notice) `test/Adapter/TidyAdapter/TidyAdapterFactoryTest.php`** —
  same stub-style container doubles.
  - **Fix:** migrated to `createStub()`; dropped `->with()` where return is fixed.
- [x] **(notice) `test/Adapter/WyriHaximusAdapter/WyriHaximusAdapterFactoryTest.php`** —
  same stub-style container doubles.
  - **Fix:** migrated to `createStub()`; dropped `->with()` where return is fixed.
- [x] **(notice) `test/HtmlMinifierMiddlewareFactoryTest.php`** — container and
  PSR doubles used as stubs.
  - **Fix:** migrated to `createStub()`.
- [x] **(notice) `test/HtmlMinifierMiddlewareTest.php`** — request/response/handler
  /stream/adapter doubles, mixed stub and expectation usage.
  - **Fix:** migrated stub doubles to `createStub()`; kept `createMock()` for the
    four doubles that assert behavior with `->expects(self::once())` /
    `->expects(self::never())`.
- [x] **(tooling) PHPStan `staticMethod.dynamicCall` ×92** — in PHPUnit 13
  `TestCase::createStub()` is a `static` method, so the migrated
  `$this->createStub(...)` calls were flagged as dynamic calls to a static
  method across the five test files above.
  - **Fix:** call it statically — `self::createStub(...)` — matching the
    existing `self::assert*` / `self::once()` convention. PHPStan clean.

### PHPUnit configuration

- [x] **(tooling) `phpunit.xml.dist`** — schema pinned to the 12.2 XSD.
  - **Fix:** bumped `xsi:noNamespaceSchemaLocation` to
    `https://schema.phpunit.de/13.2/phpunit.xsd`.

## composer.json & CI

- [x] **`require.php` `^8.3` → `^8.5`** — package now targets PHP 8.5 only.
- [x] **`ctw/ctw-middleware` `^4.0` → `dev-php85`** — pulls the Diactoros 3 /
  middlewares-utils 4 / servicemanager 4 stack (the resolution fix above).
- [x] **`ctw/ctw-qa` `^5.0` → `dev-php85`** — QA toolchain aligned for PHP 8.5.
- [x] **`phpunit/phpunit` `^12.0` → `^13.0`** — installs 13.2.1; drove the
  `createStub` modernization.
- [x] **`ext-tidy` require retained** — unchanged; the tidy adapter still needs it.
- [x] **`.github/workflows/tests.yml` → PHP 8.5 only** — matrix reduced from the
  commented `[ '8.3', '8.4', '8.5' ]` / active `[ '8.3' ]` to `[ '8.5' ]`.

## Final audit (PHP 8.5.7)

- [x] **`php -v`** — PHP **8.5.7** (cli).
- [x] **`composer update -W`** — clean; no security advisories. Installed:
  `laminas/laminas-diactoros 3.8.0`, `middlewares/utils 4.0.2`,
  `laminas/laminas-servicemanager 4.5.1`, `phpunit/phpunit 13.2.1`,
  `ctw/ctw-middleware dev-php85`, `ctw/ctw-qa dev-php85`.
- [x] **PHPUnit** — **145 tests, 200 assertions, 16 skipped**; 0 deprecations,
  0 warnings, 0 notices, 0 errors. The 16 skips are intentional: the optional
  `WyriHaximus HtmlMin` adapter library is not installed in this environment
  (the `ext-tidy` adapter tests run and pass).
- [x] **PHPStan** — no issues found.
- [x] **Rector (dry-run)** — no changes proposed.

> **Before merge:** re-tag `ctw/ctw-middleware` (and `ctw/ctw-qa`) to a stable
> release and replace the `dev-php85` pins.
