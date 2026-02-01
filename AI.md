# AI Guidelines (TL;DR)

## TL;DR
 - Do not suppress errors with `@`; it either validates or throws exceptions.
 - Memory safety: Use `MemoryInspector` before loading large CSV files.
 - Messages and i18n: always via `Language` and `locales/` files.
 - Maintain decoupling: work against interfaces (`ICsv`, `ISource`, `IDriver`).
 - Any source validation must implement ISource; never use unvalidated sources in the core.
 - Maintain deprecated Facades/Classes (at least one minor version). Document the replacement; do not break public APIs without notice.
 - Avoid reading everything into RAM; process in chunks if possible.
 - Unit tests are required for new features.

 For details and examples: see [README.md](README.md)

## Context
PHP library for efficient management of large CSV files. Designed for projects in Laravel, Symfony, or native PHP, with a simple and customizable interface.

## Main rules
1) Architecture: Separate Drivers/Sources; do not mix I/O with models.
2) New code: do not use @; existing code: migration plan to phase out @ with explicit error/exception handling
3) Backwards compatibility: respecting deprecated facades/classes until their removal.
4) CsvCache and cache-policies: Respect consistency of the lock with a flock, `.tmp` and hash used to identify them.

## Coding style
PSR-12, strict typing, `readonly` when applies (PHP 8.2+), do not delete public code without prior deprecation of a version.

## Testing
Add unit/feature tests in `tests/`. Use `resources/example-test-files/`.

## AI-assisted contribution
In AI-generated PRs: summary of changes, risks, performance impact, and test coverage.