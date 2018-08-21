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
}
