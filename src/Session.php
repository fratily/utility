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
class Session implements \Countable, \IteratorAggregate{
    
    const DISABLED  = 0;
    const NONE      = 1;
    const ACTIVE    = 2;

    const DEFAULT_CONF  = [
        "save"      => [
            "path"      => null,
            "handler"   => null,
            "serialize" => null
        ],
        "cookie"    => [
            "name"      => null,
            "lifetime"  => null,
            "path"      => null,
            "domain"    => null,
            "secure"    => null,
            "httponly"  => null
        ],
        "sid"   => [
            "length"    => null,
            "bpc"       => null,
        ]
    ];
    
    /**
     * @var mixed[]|null
     */
    private $finishedData   = [];
    
    /**
     * @var mixed[]
     */
    private $config;

    /**
     * Constructor
     * 
     * @param   mixed[] $options    [optional]
     */
    public function __construct(array $options = []){
        if(self::status() === self::DISABLED){
            throw Exception\SessionException::disabled();
        }else if(self::status() === self::NONE){
            $this->config   = self::DEFAULT_CONF;

            foreach($options as $key => $option){
                $this->config   = Hash::set($this->config, $key, $option);
            }
            
            $this->initSave(
                $this->getConf("save.path"),
                $this->getConf("save.handler"),
                $this->getConf("save.serialize")
            );
            
            $this->initCookie(
                $this->getConf("cokkie.name"),
                $this->getConf("cookie.kifetime"),
                $this->getConf("cookie.path"),
                $this->getConf("cookie.domain"),
                $this->getConf("cookie.secure"),
                $this->getConf("cookie.httponly")
            );
            
            $this->initSid(
                $this->getConf("sid.length"),
                $this->getConf("sid.bpc")
            );
        }
    }
    
    private function getConf(string $key){
        return Hash::get($this->config, $key);
    }
    
    private function initSave($path, $handler, $serialize){
        if(is_string($path)){
            ini_set("session.save_path", $path);
        }
        
        if(is_array($handler)){
            call_user_func_array("session_set_save_handler", $handler);
        }else if($handler instanceof \SessionHandlerInterface){
            session_set_save_handler($handler);
        }
        
        if(is_string($serialize)){
            ini_set("session.serialize_handler", $serialize);
        }
    }
    
    private function initCookie($name, $lifetime, $path, $domain, $secure, $httponly){
        if(is_string($name) && $name !== ""){
            session_name($name);
        }
        
        $lifetime   = (is_int($lifetime) ? $lifetime : null) ?? ini_get("session.cookie_lifetime");
        
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    }
    
    private function initSid($length, $bpc){
        if(is_int($length)){
            ini_set("session.sid_length", $length);
        }
        
        if(is_int($bpc)){
            ini_set("session.sid_bit_per_character", $bpc);
        }
    }
   
    
    /**
     * Magic getter
     * 
     * @param   string  $key
     * 
     * @return  mixed
     */
    public function __get($key){
        return $this->get($key, null);
    }

    /**
     * Magic isset
     *
     * @param   string  $key
     *
     * @return  bool
     */
    public function __isset($key){
        return $this->has($key);
    }

    /**
     * Magic setter
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return  void
     */
    public function __set($key, $value){
        $this->set($key, $value);
    }

    /**
     * Magic unsetter
     *
     * @param   string  $key
     *
     * @return  void
     */
    public function __unset($key){
        $this->remove($key);
    }

    /**
     * セッションの実行状態を返す
     *
     * @return  int
     *      <dl>
     *          <dt><b>Session::DISABLED</b></dt>
     *              <dd>セッション機能が無効</dd>
     *          <dt><b>Session::NONE</b></dt>
     *              <dd>セッションが開始されていない</dd>
     *          <dt><b>Session::ACTIVE</b></dt>
     *              <dd>セッションが開始されている</dd>
     *      </dl>
     */
    public function status(){
        switch(session_status()){
            case PHP_SESSION_NONE:
                return self::NONE;
                
            case PHP_SESSION_ACTIVE:
                return self::ACTIVE;
                
            case PHP_SESSION_DISABLED:
            default:
                return self::DISABLED;
        }
    }
    
    /**
     * セッションを開始する
     *
     * @return  void
     * 
     * @throws  Exception\SessionException
     */
    public function start(){
        if($this->status() === self::ACTIVE){
            throw Exception\SessionException::started();
        }
        
        session_start();
    }

    /**
     * セッションデータの変更を保存してセッションを終了する
     *
     * @return  void
     * 
     * @throws  Exception\SessionException
     */
    public function close(){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }

        session_commit();
        
        $this->finishedData = $_SESSION;
    }

    /**
     * セッションデータの変更を破棄してセッションを終了する
     *
     * @return  void
     * 
     * @throws  Exception\SessionException
     */
    public function abort(){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }

        session_abort();
        
        $this->finishedData = $_SESSION;
    }
    
    /**
     * セッションを完全に破棄する
     *
     * @return  $this
     *
     * @throws  Exception\SessionException
     */
    public function destroy(){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }
        
        $_SESSION   = [];

        if(ini_get("session.use_cookies")){
            $params = session_get_cookie_params();

            setcookie(session_name(), "", time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        
        $this->finishedData = [];
    }

    /**
     * 現在のセッションIDを新しく生成した値に変更する
     *
     * @param   bool    $destroy
     *      関連付けられた古いセッションデータを削除するかどうか
     *
     * @return  $this
     *
     * @throws  Exception\SessionException
     */
    public function regenerateID(bool $destroy = false){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }else if(!session_regenerate_id($destroy)){
            throw Exception\SessionException::canNotRegenerateID();
        }

        return $this;
    }

    

    /**
     * セッションデータをセッション開始時の値に戻す
     *
     * @return  $this
     *
     * @throws  Exception\SessionException
     */
    public function reset(){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }

        session_reset();

        return $this;
    }

    /**
     * セッションデータを空にする
     *
     * @return  $this
     *
     * @throws  Exception\SessionException
     */
    public function clear(){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }

        session_unset();

        return $this;
    }
    
    /**
     * セッションデータの値を取得する
     *
     * @param   string  $key
     * @param   mixed   $default
     *
     * @return  mixed
     */
    public function get(string $key, $default = null){
        $data   = $this->status() === self::ACTIVE
            ? $_SESSION : $this->finishedData;
        
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * セッションデータが存在するかどうか
     *
     * @param   string  $key
     *
     * @return  bool
     */
    public function has(string $key){
        $data   = $this->status() === self::ACTIVE
            ? $_SESSION : $this->finishedData;
        
        return array_key_exists($key, $data);
    }

    /**
     * セッションデータに値をセットする
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return  $this
     *
     * @throws  Exception\SessionException
     */
    public function set(string $key, $value){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }

        $_SESSION[$key] = $value;
    }

    /**
     * セッションデータから値を削除する
     *
     * @param   string  $key
     *
     * @return  $this
     *
     * @throws  Exception\SessionException
     */
    public function remove(string $key){
        if($this->status() !== self::ACTIVE){
            throw Exception\SessionException::notStarted();
        }

        if(array_key_exists($key, $_SESSION)){
            unset($_SESSION[$key]);
        }

        return $this;
    }

    /**
     * Countable
     */
    public function count(){
        $data   = $this->status() === self::ACTIVE
            ? $_SESSION : $this->finishedData;
        
        return count($data);
    }
    
    /**
     * IteratorAggregate
     */
    public function getIterator(){
        return $this->status() === self::ACTIVE
            ? $_SESSION : $this->finishedData;
    }
}