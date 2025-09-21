# PHP Dependency Tree Library

PHP library for generating dependency trees with conflict resolution and cycle detection. The library helps organize packages into ordered loading sequences while respecting interdependencies.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

- Bootstrap and test the repository:
  - `composer install --prefer-dist --no-interaction` -- takes ~1.5 seconds. Install dependencies.
  - `php ./vendor/bin/atoum -ncc -c .atoum.php` -- takes ~0.3 seconds. Run tests without code coverage.
  - `php test/user/essai.php` -- takes ~0.05 seconds. Run example script to verify functionality.

- Never run tests with code coverage in CI environment - use the `-ncc` (no-code-coverage) flag to avoid Xdebug configuration issues.

- Always run all validation steps after making changes - the library has complex dependency resolution logic that can break easily.

## Validation

- ALWAYS run through the complete validation scenarios after making changes:
  - Basic usage: Create simple dependency tree with 2 packages
  - Advanced usage: Create complex tree with multiple levels and cross-dependencies 
  - Error handling: Test duplicate dependency detection
  - Infinite loop detection: Test circular dependency detection
- Use the example script `test/user/essai.php` to verify functionality - it should output a properly formatted dependency tree array.
- Always run `php ./vendor/bin/atoum -ncc -c .atoum.php` before committing - tests must pass.
- The library works correctly if the example outputs a multi-dimensional array with packages grouped by dependency levels.

## Common Tasks

### Repository root structure
```
.
├── .atoum.php          # Test configuration
├── .gitignore
├── .travis.yml         # Legacy CI config
├── LICENSE
├── README.md
├── composer.json       # Dependencies: PHP >=5.6, atoum ^4.0
├── src/
│   ├── DependencyTree.php  # Main API class
│   └── Tree.php           # Internal tree implementation
└── test/
    ├── unit/          # Unit tests (atoum framework)
    └── user/          # Example scripts
```

### Running tests
```bash
# Install dependencies (required first time)
composer install --prefer-dist --no-interaction

# Run unit tests (NEVER cancel - completes in <1 second)
php ./vendor/bin/atoum -ncc -c .atoum.php

# Run example to verify functionality
php test/user/essai.php
```

### Core functionality validation
```php
// Basic usage - should create simple tree
$tree = new \bultonFr\DependencyTree\DependencyTree;
$tree->addDependency('package1')->addDependency('package2');
$result = $tree->generateTree();

// Advanced usage - should handle complex dependencies
$tree = new \bultonFr\DependencyTree\DependencyTree;
$tree->addDependency('package1')
     ->addDependency('package2', 1)
     ->addDependency('package3', 1, ['package2'])
     ->addDependency('package4', 0, ['package2'])
     ->addDependency('package5', 1, ['package4'])
     ->addDependency('package6', 1, ['package3', 'package5'])
     ->addDependency('package7', 3)
     ->addDependency('package8', 3);
$result = $tree->generateTree(); // Should output 3-level tree
```

### Expected behavior
- Library generates multi-dimensional arrays where:
  - First level: dependency loading order (0, 1, 2, etc.)
  - Second level: groups of packages that can load simultaneously
  - Third level: individual packages within each group
- Throws exceptions for duplicate dependencies and circular references
- Automatically moves packages to appropriate dependency levels
- Example output format: `[0 => [[package1]], 1 => [[package2], [package4], [package3, package5], [package6]], 3 => [[package7, package8]]]`

## Dependencies and Compatibility

- PHP: Requires PHP >=5.6 (tested with PHP 8.3)
- atoum: Test framework updated to ^4.0 for PHP 8.x compatibility
- atoum/visibility-extension: ^2.0 for testing private methods
- composer: Required for dependency management

Note: The project was originally designed for PHP 5.6-7.2 but dependencies have been updated for modern PHP compatibility.

## Troubleshooting

- If `composer install` fails with GitHub authentication errors, use `--prefer-dist --no-interaction` flags
- If tests fail with code coverage errors, always use `-ncc` flag with atoum
- If you see "makeVisible" errors, ensure atoum/visibility-extension is properly installed
- The library has no external runtime dependencies - only testing dependencies