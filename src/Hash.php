<?php
/**
 * FratilyPHP Utility
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Utility;

/**
 * 
 */
class Hash{
    
    private static function getMatchedNodes(array $root, string $key){
        $nodes  = [$root];

        foreach(explode(".", $key) as $key){
            if(empty($nodes)){
                break;
            }
            
            $newNodes   = [];
            
            foreach($nodes as $node){
                if(is_array($node)){
                    if($key === "*"){
                        foreach($node as $child){
                            $newNodes[] = $child;
                        }
                    }else if(array_key_exists($key, $node)){
                        $newNodes[] = $node[$key];
                    }
                }
            }

            $nodes  = $newNodes;
        }
        
        return $nodes;
    }
    
    public static function get(array $root, string $key){
        $nodes  = self::getMatchedNodes($root, $key);

        if(empty($nodes)){
            return null;
        }else if(count($nodes) === 1){
            return array_pop($nodes);
        }

        return $nodes;
    }

    public static function has(array $root, string $key){
        $nodes  = self::getMatchedNodes($root, $key);
        
        return !empty($nodes);
    }

    public static function set(array $root, string $key, $val){
        $origin = $root;
        $node   = &$root;

        foreach(explode(".", $key) as $key){
            if(!array_key_exists($key, $node)){
                return $origin;
            }
            
            $node   = &$node[$key];
        }
        
        $node   = $val;
        
        return $root;
    }
}