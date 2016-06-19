<?php

namespace bultonFr\DependencyTree;

use \Exception;

class Tree
{
    /**
     * @var array $dependenciesInfos : List of dependency with there infos;
     */
    protected $dependenciesInfos;
    
    /**
     * @var array $listDepends : List the depends of each dependancy
     */
    protected $listDepends;
    
    /**
     * @var array $dependenciesPositions : List of position in the tree for
     *                                      each dependancy
     */
    protected $dependenciesPositions;
    
    /**
     * @var array : The generate tree
     */
    protected $tree;

    /**
     * Add a dependency to system
     * 
     * @param string $name         : Dependency's name
     * @param int    $order        : Order to load dependency
     * @param array  $dependencies : All dependencies for this dependency
     * 
     * @throws Exception : If dependency already declared
     * 
     * @return void
     */
    public function addDependency($name, $order = 0, $dependencies = [])
    {
        //Check if dependency is already declared.
        if(isset($this->dependenciesInfos[$name])) {
            throw new Exception('Dependency '.$name.' already declared.');
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
                $this->listDepends[$dependencyName][] = $name;
            }
        }
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
        $this->tree = [[]]; //generate a empty tree
        
        //Read all depends of each dependencies
        foreach($this->listDepends as $dependencyName => $depends) {
            
            //If the package have depends, we continue
            if($depends !== []) {
                continue;
            }
            
            //Add the dependency to the first line of tree
            $this->tree[0][$dependencyName] = $dependencyName;
            
            //And generate the order for this depends
            $this->generateOrderForADependency($dependencyName, 0);
        }
        
        //Here, $this->tree have a reversed array of depends
        //So we reverse the array to have the array of depends
        //in the correct order
        $this->tree = array_reverse($this->tree);
        
        //Read the tree for update the order of each dependency
        foreach($this->tree as $order => $dependencies) {
            foreach($dependencies as $dependencyName) {
                $dependencyInfos = $this->dependenciesInfos[$dependencyName];
                
                //If the order has not be already updated
                if($dependencyInfos->order > -1) {
                    continue;
                }
                
                $dependencyInfos->order = $order;
            }
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
            $this->tree[$order][$dependName] = $dependName;
            
            $this->generateOrderForADependency(
                $dependName,
                $order
            );
        }
    }
}
