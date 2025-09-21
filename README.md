# dependency-tree
Lib for generate a dependency tree.

[![PHP Versions](https://img.shields.io/badge/php-5.6%20to%208.3-777bb3.svg?logo=php&logoColor=white&labelColor=555555)](https://github.com/bulton-fr/dependency-tree/actions)
[![Unit Tests](https://gh-badges.bulton.fr/badge?repository=bulton-fr/dependency-tree&branch=master&workflow=CI&job=test)](https://github.com/bulton-fr/dependency-tree/actions/workflows/ci.yml)
[![codecov](https://codecov.io/github/bulton-fr/dependency-tree/graph/badge.svg)](https://codecov.io/github/bulton-fr/dependency-tree)
[![PHPStan](https://gh-badges.bulton.fr/badge?repository=bulton-fr/dependency-tree&branch=master&workflow=CI&job=phpstan)](https://github.com/bulton-fr/dependency-tree/actions/workflows/ci.yml)
[![Code Style](https://gh-badges.bulton.fr/badge?repository=bulton-fr/dependency-tree&branch=master&workflow=CI&job=phpcs)](https://github.com/bulton-fr/dependency-tree/actions/workflows/ci.yml)
[![PHPMD](https://gh-badges.bulton.fr/badge?repository=bulton-fr/dependency-tree&branch=master&workflow=CI&job=phpmd)](https://github.com/bulton-fr/dependency-tree/actions/workflows/ci.yml)

[![Latest Stable Version](https://poser.pugx.org/bulton-fr/dependency-tree/v/stable.svg)](https://packagist.org/packages/bulton-fr/dependency-tree) 
[![Latest Unstable Version](https://poser.pugx.org/bulton-fr/dependency-tree/v/unstable.svg)](https://packagist.org/packages/bulton-fr/dependency-tree) 
[![License](https://poser.pugx.org/bulton-fr/dependency-tree/license.svg)](https://packagist.org/packages/bulton-fr/dependency-tree)

The Principle is to have a tree who contains lines. For each lines, there is a tree who contains lines too.

The first lines is for the package which loaded at the same times. But, for the same line, its package may have dependencies between them. So each lines contain a tree where packages are sorted for have their dependencies of the same line loaded before them.

## Install with composer

Download composer
```
$ curl -s https://getcomposer.org/installer | php
```

Add call-curl repository to you composer.json
```json
{
    "require": {
        "bulton-fr/dependency-tree": "@stable"
    }
}
```

Execute the command
```
$ php composer.phar install
```

## Use in your code
### Basic usage
```php
$tree = new \bultonFr\DependencyTree\DependencyTree;
$tree->addDependency('package1')
     ->addDependency('package2');

$generatedTree = $tree->generateTree();
```
The `generatedTree` contains :
```php
array(1) {
  [0]=> array(1) {
    [0]=> array(2) {
      [0]=> string(8) "package1"
      [1]=> string(8) "package2"
    }
  }
```

### Advanced usage
```php
$tree = new \bultonFr\DependencyTree\DependencyTree;
$tree->addDependency('package1')
     ->addDependency('package2', 1)
     ->addDependency('package3', 1, ['package2'])
     ->addDependency('package4', 0, ['package2'])
     ->addDependency('package5', 1, ['package4'])
     ->addDependency('package6', 1, ['package3', 'package5'])
     ->addDependency('package7', 3)
     ->addDependency('package8', 3);

$generatedTree = $tree->generateTree();
```

The `generatedTree` contains :
```php
array(3) {
  [0]=> array(1) {
    [0]=> array(1) {
      [0]=> string(8) "package1"
    }
  }
  [1]=> array(4) {
    [0]=> array(1) {
      [0]=> string(8) "package2"
    }
    [1]=> array(1) {
      [0]=> string(8) "package4"
    }
    [2]=> array(2) {
      [0]=> string(8) "package3"
      [1]=> string(8) "package5"
    }
    [3]=> array(1) {
      [0]=> string(8) "package6"
    }
  }
  [3]=> array(1) {
    [0]=> array(2) {
      [0]=> string(8) "package7"
      [1]=> string(8) "package8"
    }
  }
}
```

In a graphic representation : 

![dependency-tree graphic reprensentation](https://projects.bulton.fr/dependency-tree/github-readme-graphic.png)

At left we see the first tree with the list of package at their time loader.
At right, we see the tree for the second line, with packages sorted to be loaded in the correct order.

Note : 
The package "package4" is declared to be loaded in the first line. But its dependencies are declared to be loaded in the second line. So the "package4" has be moved to be loaded in the same line as its dependencies
