# Junie Adapter (TL;DR)
First read the [AI.md](../AI.md).

## TL;DR
- Focus on each new development:
  - Add to the development focus only on classes that extend or implement.
  - Add to the development focus classes if they use the public functions of the class involved.
  - Just 100 lines of code per class, 300 lines if it is a test.
  - If you need to exceed the limits, ask for permission to edit and explain the reason in detail.
- Always executing PHP commands:
  - Do not use native installations or configurations on the machine.
  - Use Docker or similar containerization tools. You can find them at: [Docker Tools](../docker/docker-compose.yml)
- Test coverage:
  - Prioritize unit tests `../tests/Unit/`.
  - Add Feature tests on `../tests/Feature/`.
  - Minimum coverage per line and branch: 80–90% if 100% is not realistic.
  - Static analysis levels (PHPStan level 8+) and a linter (PHP-CS-Fixer/PHPCS) as gates.
- Documentation:
  - Prioritize documentation blocks at the beginning of each class and each function. 
- Benchmarking tools:
  - Ask for permission and justify their use in detail.
  - Use when the development focus is on Core classes `../core/`.
  - Always report the results.

## Automatic Checks
- Lint + PHPStan + PHPUnit with minimum coverage.
- Code style check.
- Code review.