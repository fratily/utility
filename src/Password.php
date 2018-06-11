<?php
/**
 * FratilyPHP Utility
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Utility;

/**
 * 
 */
class Password{
        
    public static function getInfo(string $hash){
        return password_get_info($hash);
    }
    
    public static function hash(string $passwd, int $algo, array $options = []){
        return password_hash($passwd, $algo, $options);
    }
    
    public static function verify(string $passwd, string $hash){
        return password_verify($passwd, $hash);
    }
    
    public static function needsRehash(string $hash, int $algo, array $options = []){
        return password_needs_rehash($hash, $algo, $options);
    }
}