<?php

require_once(__DIR__.'/../../vendor/autoload.php');

$dependencies = new bultonFr\DependencyTree\DependencyTree;
$dependencies->addDependency('package1');
$dependencies->addDependency('package2', 1);
$dependencies->addDependency('package3', 1, ['package2']);
$dependencies->addDependency('package4', 0, ['package2']);
$dependencies->addDependency('package5', 1, ['package4']);
$dependencies->addDependency('package6', 1, ['package3', 'package5']);
$dependencies->addDependency('package7', 3);
$dependencies->addDependency('package8', 3);

$tree = $dependencies->generateTree();

var_dump($tree);

/***** OBJECTIF *****
 * [0] => ['package1']
 * [1] => [
 *      [0] => ['package2']
 *      [1] => ['package4'] //Possibility to have "package3" here
 *      [2] => ['package3', 'package5']
 *      [3] => ['package6']
 * [3] => ['package7', 'package8']
 */
