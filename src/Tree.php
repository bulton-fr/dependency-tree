<?php

namespace bultonFr\DependencyTree;

use \Exception;

class Tree
{
    /**
     * @var array $dependenciesInfos : List of dependency with there infos;
     */
    protected $dependenciesInfos = [];
    
    /**
     * @var array $listDepends : List the depends of each dependancy
     */
    protected $listDepends = [];
    
    /**
     * @var array $dependenciesPositions : List of position in the tree for
     *                                      each dependancy
     */
    protected $dependenciesPositions = [];
    
    /**
     * @var array $tree : The generate tree
     */
    protected $tree = [];
    
    /**
     * @var \stdClass $genOrderSecurityLoop : Informations about dependency
     *  during the generateOrder.
     *  It's a security against infinite loop.
     */
    protected $genOrderSecurityLoop;

    /**
     * Add a dependency to system
     * 
     * @param string $name         : Dependency's name
     * @param int    $order        : Order to load dependency
     * @param array  $dependencies : All dependencies for this dependency
     * 
     * @throws Exception : If dependency already declared
     * 
     * @return Tree : Current instance
     */
    public function addDependency($name, $order = 0, $dependencies = [])
    {
        //Check if dependency is already declared.
        if(isset($this->dependenciesInfos[$name])) {
            throw new Exception('Dependency '.$name.' already declared.');
        }
        
        if(!is_array($dependencies)) {
            throw new Exception('Dependencies must be passed in a array.');
        }

        $dependencyInfos        = new \stdClass;
        $dependencyInfos->order = $order;
        $dependencyInfos->dependencies = $dependencies;

        $this->dependenciesInfos[$name] = $dependencyInfos;
        
        //Create the key for list of depends if she doesn't exist
        if(!isset($this->listDepends[$name])) {
            $this->listDepends[$name] = [];
        }
        
        //Generate the list of depends
        if($dependencies !== []) {
            foreach($dependencies as $dependencyName) {
                if(!isset($this->listDepends[$dependencyName])) {
                    $this->listDepends[$dependencyName] = [];
                }
                
                $this->listDepends[$dependencyName][] = $name;
            }
        }
        
        return $this;
    }

    /**
     * Generate the tree
     * 
     * @return array
     */
    public function generateTree()
    {
        //Read all depencies declared and positioned each dependency on
        //the tree with they order value
        foreach($this->dependenciesInfos as $name => $dependency) {
            $order = $dependency->order;
            
            //If the line for this order not exist
            if(!isset($this->tree[$order])) {
                $this->tree[$order] = [];
            }
            
            //Add to the tree and save the current position
            $this->tree[$order][]               = $name;
            $this->dependenciesPositions[$name] = [$order];
        }
        
        //Read the tree and check depends of each package.
        //Move some package in the tree if need for depends.
        foreach($this->tree as $dependencies) {
            foreach($dependencies as $dependencyName) {
                $this->checkDepend($dependencyName);
            }
        }
        
        //Sort by key. Some keys should be unorganized
        ksort($this->tree);
        
        return $this->tree;
    }

    /**
     * Check if all depends is correctly spoted in the tree
     * 
     * @param string $dependencyName : The name of the dependency for which
     *                                  check depends
     * 
     * @return void
     */
    protected function checkDepend($dependencyName)
    {
        $listDepends     = $this->listDepends[$dependencyName];
        $dependencyInfos = $this->dependenciesInfos[$dependencyName];
        $order           = $dependencyInfos->order;

        //No depends :)
        if($listDepends === []) {
            return;
        }

        //Read all depends and check if they correctly spoted.
        //If not, call the method to move the depend read.
        foreach($listDepends as $dependencyName) {
            $dependencyPos   = $this->dependenciesPositions[$dependencyName];
            $dependencyOrder = $dependencyPos[0];

            if($dependencyOrder < $order) {
                $this->moveDepend($dependencyName, $order);
            }
        }
    }

    /**
     * Move a depend to a new position in the tree
     * 
     * @param string $dependencyName : The dependency name
     * @param int    $newOrder       : The new position in the tree
     * 
     * @return void
     */
    protected function moveDepend($dependencyName, $newOrder)
    {
        //Get old position in the tree for this dependency
        $dependencyInfos = $this->dependenciesInfos[$dependencyName];
        $oldOrder        = $dependencyInfos->order;

        //If the new position not already exist in the tree
        if(!isset($this->tree[$newOrder])) {
            $this->tree[$newOrder] = [];
        }

        //Add dependency to this new position
        $this->tree[$newOrder][] = $dependencyName;

        //Search the key corresponding to the old position in the array tree
        $oldKey = array_search($dependencyName, $this->tree[$oldOrder]);
        //Remove dependency from this old position
        unset($this->tree[$oldOrder][$oldKey]);

        //Call checkDepend for check all depends of this dependency
        $this->checkDepend($dependencyName);
    }
    
    /**
     * Generate the order position from the depends of each dependencies
     * 
     * First we generate a reversed tree from depends
     * Reverse the tree for have a tree in the correct depends order
     * And update the order value of each dependency with the tree of depends
     * 
     * @return void
     */
    public function generateOrderFromDependencies()
    {
        $this->initGenOrderSecurityLoop(0);
        $this->tree = [[]]; //generate a empty tree
        
        //Read all depends of each dependencies
        foreach($this->listDepends as $dependencyName => $depends) {
        
            //If the dependency in the depend's list is declared on this tree.
            if (!isset($this->dependenciesInfos[$dependencyName])) {
                continue;
            }
            
            //If the package have depends, we continue
            if($depends !== []) {
                continue;
            }
            
            //Add the dependency to the first line of tree
            $this->tree[0][$dependencyName] = $dependencyName;
            
            //And generate the order for this depends
            $this->genOrderSecurityLoopAddDepend($dependencyName, 0);
            $this->generateOrderForADependency($dependencyName, 0);
        }
        
        //Here, $this->tree have a reversed array of depends
        //So we reverse the array to have the array of depends
        //in the correct order
        $this->tree = array_reverse($this->tree);
        
        //Some line could be empty because the dependency is in another tree
        //So we define the order manually.
        $treeOrder = 0;
        
        //Read the tree for update the order of each dependency
        foreach($this->tree as $dependencies) {
            if ($dependencies === []) {
                continue;
            }
            
            foreach($dependencies as $dependencyName) {
                $dependencyInfos = &$this->dependenciesInfos[$dependencyName];
                
                //If the order has not be already updated
                if($dependencyInfos->order > -1) {
                    continue;
                }
                
                $dependencyInfos->order = $treeOrder;
            }
            
            $treeOrder++;
        }
        
        //Reinit the tree for use the main system of tree generator.
        $this->tree = null;
    }
    
    /**
     * Define the order of all the dependencies of dependency
     * 
     * @param string $dependencyName : The name of dependency for which we
     *                                  read the dependencies
     * @param int    $currentOrder   : The current order of the dependency
     * 
     * @return void
     */
    protected function generateOrderForADependency($dependencyName, $currentOrder)
    {
        $depends = $this->dependenciesInfos[$dependencyName]->dependencies;
        if($depends === []) {
            return;
        }
        
        $order = $currentOrder+1;
        if(!isset($this->tree[$order])) {
            $this->tree[$order] = [];
        }
        
        foreach($depends as $dependName) {
            //If the dependency of the dependency is in a other tree
            if (!isset($this->dependenciesInfos[$dependName])) {
                continue;
            }
            
            //Infinite dependency loop security
            $this->checkReinitGenOrderSecurityLoop($order);
            $this->genOrderCheckInfiniteLoop($dependName);
            $this->tree[$order][$dependName] = $dependName;
            
            $this->genOrderSecurityLoopAddDepend($dependName, $order);
            $this->generateOrderForADependency(
                $dependName,
                $order
            );
        }
    }
    
    /**
     * Add a dependency into list used by security infinite depend loop.
     * 
     * @param string $dependencyName Dependency name
     * @param int    $order          The current order of the dependency
     * 
     * @return void
     */
    protected function genOrderSecurityLoopAddDepend($dependencyName, $order)
    {
        $this->checkReinitGenOrderSecurityLoop($order);
        
        $this->genOrderSecurityLoop->order  = $order;
        $this->genOrderSecurityLoop->list[] = (object) [
            'dependName' => $dependencyName,
            'dependList' => []
        ];
    }
    
    /**
     * Check if we reinitialize the list used against dependency infinite loop
     * 
     * @param int $order The current order of the dependency
     * 
     * @return void
     */
    protected function checkReinitGenOrderSecurityLoop($order)
    {
        if ($order <= $this->genOrderSecurityLoop->order) {
            $this->initGenOrderSecurityLoop($order);
        }
    }
    
    /**
     * (re)Initialize the list used against dependency infinite loop
     * 
     * @param int $order The current order of the dependency
     * 
     * @return void
     */
    protected function initGenOrderSecurityLoop($order)
    {
        $this->genOrderSecurityLoop = (object) [
            'order' => $order,
            'list'  => []
        ];
    }
    
    /**
     * Check if we allow to moved a dependency in the tree to protect
     * against infine loop.
     * It's for the case where a dependency is moved to be loaded before an
     * another, but this another dependency depend on the first package
     * who asked to be moved.
     * So the system try to moved packages at the infine. We protect this.
     * 
     * @see Issue #4 on the github repo.
     * 
     * @param string $checkDependName : The name of dependency
     * 
     * @return void
     * 
     * @throws Exception The infinite loop security.
     */
    protected function genOrderCheckInfiniteLoop($checkDependName)
    {
        $runException = false;
        foreach ($this->genOrderSecurityLoop->list as &$checkInfos) {
            if ($checkInfos->dependList === []) {
                $checkInfos->dependList[] = $checkDependName;
                continue;
            }
            
            if (
                $checkInfos->dependList !== []
                && $checkInfos->dependName !== $checkDependName
            ) {
                $checkInfos->dependList[] = $checkDependName;
                continue;
            }
            
            $runException = true;
            break;
        }
        
        if ($runException === false) {
            unset($checkInfos); //Kill ref
            return;
        }
        
        //Package is already moved for the original dependency : Loop error
        $loopInfos = '';
        foreach ($checkInfos->dependList as $packageName) {
            if ($loopInfos !== '') {
                $loopInfos .= ', ';
            }
            
            $loopInfos .= $packageName;
        }
        
        throw new Exception(
            'Infinite depends loop find for package '.$checkInfos->dependName
            .' - Loop info : '.$loopInfos
        );
    }
}
