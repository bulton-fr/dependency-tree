<?php

namespace bultonFr\DependencyTree\test\unit;

use \atoum;

require_once(__DIR__.'/../../../vendor/autoload.php');

class DependencyTree extends atoum
{
    /**
     * @var $mock : Instance du mock pour la class DependencyTree
     */
    protected $mock;

    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->mockGenerator
            ->makeVisible('generateOrderTree')
            ->makeVisible('generateDependenciesTree')
            ->generate('bultonFr\DependencyTree\test\unit\mocks\DependencyTree')
        ;
        
        $this->mock = new \mock\bultonFr\DependencyTree\test\unit\mocks\DependencyTree;
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
            ->array($this->mock->dependencies)
                ->hasKey('test')
                ->size
                    ->isEqualTo(1)
            ->object($this->mock->dependencies['test'])
                ->isEqualTo($dependencyInfos);
        
        $this->assert('add dependency : already exist')
            ->exception(function() use ($mock) {
                $mock->addDependency('test');
            })
            ->hasMessage('Dependency test already declared.');
        
        $dependencyInfos->order = 2;
        $this->assert('add dependency : order declared')
            ->given($this->mock->addDependency('test2', 2))
            ->object($this->mock->dependencies['test2'])
                ->isEqualTo($dependencyInfos);
        
        $dependencyInfos->dependencies = ['test2'];
        $this->assert('add dependency : order and depends declared')
            ->given($this->mock->addDependency('test3', 2, ['test2']))
            ->object($this->mock->dependencies['test3'])
                ->isEqualTo($dependencyInfos);
        
        $this->assert('add dependency : dependencies not in an array')
            ->exception(function() use ($mock) {
                $mock->addDependency('test4', 2, 'test3');
            })
            ->hasMessage('Dependencies must be passed in a array.');
    }
    
    public function testGenerateTree()
    {
        $this->mock->addDependency('package1');
        $this->mock->addDependency('package2', 1);
        $this->mock->addDependency('package3', 1, ['package2']);
        $this->mock->addDependency('package4', 0, ['package2']);
        $this->mock->addDependency('package5', 1, ['package4']);
        $this->mock->addDependency('package6', 1, ['package3', 'package5']);
        $this->mock->addDependency('package7', 3);
        $this->mock->addDependency('package8', 3);
        
        /**
         * Objectif: 
         * [0] => ['package1']
         * [1] => [
         *      [0] => ['package2']
         *      [1] => ['package4'] //Possibility to have "package3" here
         *      [2] => ['package3', 'package5']
         *      [3] => ['package6']
         * [3] => ['package7', 'package8']
         *
         * IRL PHP : 
         * array(3) {
         *   [0]=> array(1) {
         *     [0]=> array(1) {
         *       [0]=> string(8) "package1"
         *     }
         *   }
         *   [1]=> array(4) {
         *     [0]=> array(1) {
         *       [0]=> string(8) "package2"
         *     }
         *     [1]=> array(1) {
         *       [0]=> string(8) "package4"
         *     }
         *     [2]=> array(2) {
         *       [0]=> string(8) "package3"
         *       [1]=> string(8) "package5"
         *     }
         *     [3]=> array(1) {
         *       [0]=> string(8) "package6"
         *     }
         *   }
         *   [3]=> array(1) {
         *     [0]=> array(2) {
         *       [0]=> string(8) "package7"
         *       [1]=> string(8) "package8"
         *     }
         *   }
         * }
         */
        
        $expectedTree = [
            0 => [['package1']],
            1 => [
                ['package2'],
                ['package4'],
                ['package3', 'package5'],
                ['package6']
            ],
            3 => [['package7', 'package8']]
        ];
        
        $this->assert('test generateTree')
            ->array($this->mock->generateTree())
                ->isEqualTo($expectedTree);
    }
    
    public function testGenerateOrderTree()
    {
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
            ->array($this->mock->generateOrderTree())
                ->isEqualTo($expected);
    }
    
    public function testGenerateDependenciesTree()
    {
        $this->mock->addDependency('package1');
        $this->mock->addDependency('package2', 1);
        $this->mock->addDependency('package3', 1, ['package2']);
        $this->mock->addDependency('package7', 3);
        
        $orderTree = [
            0 => ['package1'],
            1 => ['package2', 'package3'],
            3 => ['package7']
        ];
        
        $expected = [
            0 => [['package1']],
            1 => [
                ['package2'],
                ['package3']
            ],
            3 => [['package7']]
        ];
        
        $this->assert('test generateDependenciesTree')
            ->array($this->mock->generateDependenciesTree($orderTree))
                ->isEqualTo($expected);
    }
    
    public function testIssue3()
    {
        $this->mock->addDependency('package1');
        $this->mock->addDependency('package2', 1, ['package1']);
        $this->mock->addDependency('package3', 1, ['package2']);
        $this->mock->addDependency('package7', 3);
        
        $orderTree = [
            0 => ['package1'],
            1 => ['package2', 'package3'],
            3 => ['package7']
        ];
        
        $expected = [
            0 => [['package1']],
            1 => [
                ['package2'],
                ['package3']
            ],
            3 => [['package7']]
        ];
        
        $this->assert('test fix issue #3')
            ->array($this->mock->generateDependenciesTree($orderTree))
                ->isEqualTo($expected);
    }
    
    public function testIssue4()
    {
        $this->mock->addDependency('package1');
        $this->mock->addDependency('package7', 3);
        $this->mock->addDependency('package8', 3);
        
        $this->mock->addDependency('package2', 1);
        $this->mock->addDependency('package3', 1, ['package2']);
        $this->mock->addDependency('package4', 0, ['package5']);
        $this->mock->addDependency('package5', 1, ['package2']);
        $this->mock->addDependency('package9', 1, ['package4']);
        $this->mock->addDependency('package6', 1, ['package3', 'package5']);
        
        $this->assert('test fix issue #4 : Case where tree is good')
            ->array($this->mock->generateTree())
                ->size
                    ->isGreaterThan(0);
        
        
        $mock = new \bultonFr\DependencyTree\test\unit\mocks\DependencyTree;
        
        $mock->addDependency('package1');
        $mock->addDependency('package7', 3);
        $mock->addDependency('package8', 3);
        
        $mock->addDependency('package2', 1);
        $mock->addDependency('package3', 1, ['package2']);
        $mock->addDependency('package4', 0, ['package5']);
        $mock->addDependency('package5', 1, ['package9']);
        $mock->addDependency('package9', 1, ['package4']);
        $mock->addDependency('package6', 1, ['package3', 'package5']);
        
        $this->assert('test fix issue #4 : Case where dependency is an infinite loop')
            ->exception(function() use ($mock) {
                $mock->generateTree();
            })
                ->hasMessage('Infinite depends loop find for package package5 - Loop info : package9, package4');
    }
}
