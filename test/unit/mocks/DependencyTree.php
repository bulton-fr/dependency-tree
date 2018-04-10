<?php

namespace bultonFr\DependencyTree\test\unit\mocks;

require_once(__DIR__.'/../../../vendor/autoload.php');

/**
 * Mock de la class à tester
 */
class DependencyTree extends \bultonFr\DependencyTree\DependencyTree
{
    /**
     * Accesseur get
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public function generateOrderTree()
    {
        return parent::generateOrderTree();
    }

    public function generateDependenciesTree($orderTree)
    {
        return parent::generateDependenciesTree($orderTree);
    }
}
