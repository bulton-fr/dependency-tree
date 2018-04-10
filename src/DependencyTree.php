<?php

namespace bultonFr\DependencyTree;

use \Exception;

class DependencyTree
{
    /**
     * @var array $dependencies : List of dependency with there infos;
     */
    protected $dependencies = [];
    
    /**
     * Add a dependency to system
     * 
     * @param string $name         : Dependency's name
     * @param int    $order        : Order to load dependency
     * @param array  $dependencies : All dependencies for this dependency
     * 
     * @throws Exception : If dependency already declared
     * 
     * @return DependencyTree : Current instance
     */
    public function addDependency($name, $order = 0, $dependencies = [])
    {
        //Check if dependency is already declared.
        if (isset($this->dependencies[$name])) {
            throw new Exception('Dependency '.$name.' already declared.');
        }
        
        if (!is_array($dependencies)) {
            throw new Exception('Dependencies must be passed in a array.');
        }

        $dependencyInfos               = new \stdClass;
        $dependencyInfos->order        = (int) $order;
        $dependencyInfos->dependencies = $dependencies;

        $this->dependencies[$name] = $dependencyInfos;
        
        return $this;
    }

    /**
     * Generate the dependency tree
     * 
     * @return array
     */
    public function generateTree()
    {
        $firstTree = $this->generateOrderTree();
        $finalTree = $this->generateDependenciesTree($firstTree);
        
        return $finalTree;
    }
    
    /**
     * Generate the first tree. It's a order tree
     * 
     * @return array : the order tree
     */
    protected function generateOrderTree()
    {
        $tree = new Tree;
        
        //add all dependency to the Tree
        foreach ($this->dependencies as $dependencyName => $dependencyInfos) {
            $tree->addDependency(
                $dependencyName,
                $dependencyInfos->order,
                $dependencyInfos->dependencies
            );
        }
        
        return $tree->generateTree();
    }
    
    /**
     * Generate the second tree. It's a dependency tree
     * 
     * @param array $orderTree : The first tree
     * 
     * @return array : The final tree
     */
    protected function generateDependenciesTree($orderTree)
    {
        //Read the orderTree and generate a tree
        //for each line of the first tree
        
        foreach ($orderTree as $order => $dependencies) {
            $tree = new Tree;
            
            foreach ($dependencies as $dependencyName) {
                $dependencyInfos = $this->dependencies[$dependencyName];
                
                $tree->addDependency(
                    $dependencyName,
                    -1, //Order negative because it's generate after
                    $dependencyInfos->dependencies
                );
            }
            
            //Generate the order of dependencies from the depends
            $tree->generateOrderFromDependencies();
            
            //Generate the tree for this line of the final tree
            $orderTree[$order] = $tree->generateTree();
        }
        
        return $orderTree;
    }
}
