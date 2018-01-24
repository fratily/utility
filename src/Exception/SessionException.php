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
namespace Fratily\Utility\Exception;

/**
 * 
 */
class SessionException extends \Exception{

    public static function disabled(){
        return new static("disabled");
    }
    
    public static function started(){
        return new static("started");
    }
    
    public static function notStarted(){
        return new static("not started");
    }
    
    public static function canNotRegenerateID(){
        return new static("can not regenerate id");
    }
}