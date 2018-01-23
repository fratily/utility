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
class Variable{
    
    const T_INTEGER     = "integer";
    const T_FLOAT       = "double";
    const T_STRING      = "string";
    const T_BOOLEAN     = "boolean";
    const T_ARRAY       = "array";
    const T_OBJECT      = "object";
    const T_RESOURCE    = "resource";
    const T_NULL        = "null";
    const T_UNKNOWN     = "unknown";
    
    /**
     * 変数の型を取得する
     * 
     * @param   mixed   $var
     * 
     * @return  mixed
     */
    public static function getType($var){
        if(is_int($var)){
            return self::T_INTEGER;
        }else if(is_string($var)){
            return self::T_STRING;
        }else if(is_float($var)){
            return self::T_FLOAT;
        }else if(is_bool($var)){
            return self::T_BOOLEAN;
        }else if(is_array($var)){
            return self::T_ARRAY;
        }else if(is_object($var)){
            return self::T_OBJECT;
        }else if(is_resource($var)){
            return self::T_RESOURCE;
        }else if($var === null){
            return self::T_NULL;
        }
        
        return self::T_UNKNOWN;
    }
    
    /**
     * 簡易的な変数ダンプ
     * 
     * @param   mixed   $var
     *      ダンプする値
     * @param   bool    $echo
     *      出力するか
     * @param   bool    $cl
     *      出力する場合、最後の改行を挿入するか
     * 
     * @return  string
     */
    public static function dumpSimple($var, bool $echo = false, bool $cl = false){
        $type   = self::getType($var);
        $dump   = null;
        
        switch($type){
            case self::T_INTEGER:
            case self::T_FLOAT:
            case self::T_STRING:
            case self::T_BOOLEAN:
                $dump   = var_export($var, true);
                break;
            
            case self::T_ARRAY:
                $dump   = count($var);
                break;
            
            case self::T_OBJECT:
                $dump   = get_class($var);
                break;
            
            case self::T_RESOURCE:
                $dump   = get_resource_type($var);
                break;
        }
        
        $return = $type . ($dump !== null ? "({$dump})" : "");
        
        if($echo){
            echo $return . $cl ? PHP_EOL : "";
            return;
        }
        
        return $return;
    }
}