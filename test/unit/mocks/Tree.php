<?php

namespace bultonFr\DependencyTree\test\unit\mocks;

require_once(__DIR__.'/../../../vendor/autoload.php');

/**
 * Mock de la class à tester
 */
class Tree extends \bultonFr\DependencyTree\Tree
{
    /**
     * Accesseur get
     */
    public function __get($name)
    {
        return $this->$name;
    }
    
    /**
     * Accesseur set
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
