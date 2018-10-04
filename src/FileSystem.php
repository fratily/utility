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
class FileSystem{

    public static function getFiles(string $dir, bool $recursive = false){
        if(!is_dir($dir)){
            throw new \InvalidArgumentException();
        }

        $dir    = realpath($dir);
        $result = [];

        foreach(scandir($dir) as $file){
            if("." === $file || ".." === $file){
                continue;
            }

            $path   = $dir . DIRECTORY_SEPARATOR . $file;

            if(is_dir($path) && $recursive){
                $result = array_merge($result, static::getFiles(true));
            }elseif(is_file($path)){
                $result[]   = $path;
            }
        }

        return $result;
    }
}