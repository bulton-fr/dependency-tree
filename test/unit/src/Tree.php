<?php

namespace bultonFr\DependencyTree\test\unit;

use \atoum;

require_once(__DIR__.'/../../../vendor/autoload.php');

class Tree extends atoum
{
    /**
     * @var $mock : Instance du mock pour la class Tree
     */
    protected $mock;

    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->mock = new \bultonFr\DependencyTree\test\unit\mocks\Tree;
    }

    public function testAddDependency()
    {
        $mock = $this->mock;
        
        $dependencyInfos               = new \stdClass;
        $dependencyInfos->order        = 0;
        $dependencyInfos->dependencies = [];
        
        $this->assert('add dependency : default parameters')
            ->object($this->mock->addDependency('test'))
                ->isEqualTo($this->mock)
            ->array($this->mock->dependenciesInfos)
                ->hasKey('test')
                ->size
                    ->isEqualTo(1)
            ->object($this->mock->dependenciesInfos['test'])
                ->isEqualTo($dependencyInfos)
            ->array($this->mock->listDepends)
                ->hasKey('test')
                ->size
                    ->isEqualTo(1)
            ->array($this->mock->listDepends['test'])
                ->isEqualTo([]);
        
        $this->assert('add dependency : already exist')
            ->exception(function() use ($mock) {
                $mock->addDependency('test');
            })
            ->hasMessage('Dependency test already declared.');
        
        $dependencyInfos->order = 2;
        $this->assert('add dependency : order declared')
            ->given($this->mock->addDependency('test2', 2))
            ->object($this->mock->dependenciesInfos['test2'])
                ->isEqualTo($dependencyInfos)
            ->array($this->mock->listDepends)
                ->hasKey('test2')
                ->size
                    ->isEqualTo(2)
            ->array($this->mock->listDepends['test2'])
                ->isEqualTo([]);
        
        $dependencyInfos->dependencies = ['test2'];
        $this->assert('add dependency : order and depends declared')
            ->given($this->mock->addDependency('test3', 2, ['test2']))
            ->object($this->mock->dependenciesInfos['test3'])
                ->isEqualTo($dependencyInfos)
            ->array($this->mock->listDepends)
                ->hasKey('test3')
                ->size
                    ->isEqualTo(3)
            ->array($this->mock->listDepends['test3'])
                ->isEqualTo([])
            ->array($this->mock->listDepends['test2'])
                ->isEqualTo(['test3']);
        
        $this->assert('add dependency : dependencies not in an array')
            ->exception(function() use ($mock) {
                $mock->addDependency('test4', 2, 'test3');
            })
            ->hasMessage('Dependencies must be passed in a array.');
    }

    public function testGenerateTree()
    {
        $this->assert('test generateOrderTree without dependency.')
            ->array($this->mock->generateTree())
                ->isEqualTo([]);
        
        $this->mock->addDependency('package1');
        $this->mock->addDependency('package2', 1);
        $this->mock->addDependency('package3', 1, ['package2']);
        $this->mock->addDependency('package7', 3);
        
        $expected = [
            0 => ['package1'],
            1 => ['package2', 'package3'],
            3 => ['package7']
        ];
        
        $this->assert('test generateOrderTree')
            ->array($this->mock->generateTree())
                ->isEqualTo($expected);
    }
    
    /*
    public function testCheckDepend()
    {
        $this->mock->addDependency('package1');
        $this->mock->addDependency('package2', 1);
        
        $this->mock->tree = [
            0 => ['package1'],
            1 => ['package2']
        ];
        
        $this->assert('test checkDepend : Without depends')
            ->given($this->mock->checkDepend('package2'))
            ->array($this->mock->tree)
                ->isEqualTo([
                    0 => ['package1'],
                    1 => ['package2']
                ]);
        
        $this->mock->addDependency('package3', 1, ['package2']);
        $this->mock->tree = [
            0 => ['package1'],
            1 => ['package2', 'package3']
        ];
        
        $this->assert('test checkDepend : With depends')
            ->given($this->mock->checkDepend('package3'))
            ->array($this->mock->tree)
                ->isEqualTo([
                    0 => ['package1'],
                    1 => ['package2', 'package3']
                ]);
        
        $this->mock->tree = [
            0 => ['package1', 'package3'],
            1 => ['package2']
        ];
        
        //@TODO : Create the "dependenciesPositions" array
        
        $this->assert('test checkDepend : With depends unordered')
            ->given($this->mock->checkDepend('package2'))
            ->array($this->mock->tree)
                ->isEqualTo([
                    0 => ['package1'],
                    1 => ['package2', 'package3']
                ]);
    }

    public function testMoveDepend()
    {
        
    }
    */
    
    public function testGenerateOrderFromDependencies()
    {
        $this->mock->addDependency('package2', -1);
        $this->mock->addDependency('package3', -1, ['package2']);
        $this->mock->addDependency('package4', -1, ['package2']);
        $this->mock->addDependency('package5', -1, ['package4']);
        $this->mock->addDependency('package6', -1, ['package3', 'package5']);
        
        $this->assert('test generate order from dependencies')
            ->given($this->mock->generateOrderFromDependencies())
            ->integer($this->mock->dependenciesInfos['package2']->order)
                ->isEqualTo(0)
            ->integer($this->mock->dependenciesInfos['package3']->order)
                ->isEqualTo(2) //Possiblity to it's 1. No change...
            ->integer($this->mock->dependenciesInfos['package4']->order)
                ->isEqualTo(1)
            ->integer($this->mock->dependenciesInfos['package5']->order)
                ->isEqualTo(2)
            ->integer($this->mock->dependenciesInfos['package6']->order)
                ->isEqualTo(3)
        ;
    }
    
    /*
    public function testGenerateOrderForADependency()
    {
        
    }
    */
}
