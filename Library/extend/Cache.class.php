<?php
/**
* 缓存类
*/
class Cache{
    private $cache;
    public function __construct(){
        $this->connect();
    }
    /**
     * 判断是什么缓存
     */
    private function check(){
        if(getConfig('cache_type') == 'memcache'){
            //memcache缓存
            return 1;
        }else{
            //文件缓存
            return 2;
        }
    }
	
	/**
     * 连接缓存
     */
    private function connect(){
        //判断是否开启缓存
        if (getConfig('cache_isDataCache') || getConfig('cache_isViewCache')) {
            if($this->check()===1){
                //memcache缓存
                $this->cache = new Memcache;
                $this->cache->connect(getConfig('cache_host'),getConfig('cache_port'));
            }else{
                $this->cache = 'file';
                //文件缓存
                $this->cache_path = APP_PATH.'/Runtime/cache/'.__MODULE__;
                //先判断是否存在目录，不存在就创建
                if (!is_dir($this->cache_path)) {
                    //创建目录
                    mkdir($this->cache_path,0777,true);
                }
            }
        }
    }
    /**
     * 设置缓存
     * @param string $name  缓存名称
     * @param string $value 缓存值
     * @return Boolean
     */
    public function setCache($name,$value){
        if($this->check()===1){
            return $this->cache->set($name,$value,MEMCACHE_COMPRESSED,getConfig('cache_time'));
        }else{
            //文件缓存
            //fopen()打开或者创建文件，w+，读/写。打开并清空文件的内容；如果文件不存在，则创建新文件。
            $file = fopen($this->cache_path.'/'.$name.'.php','w+');
            fwrite($file, serialize($value));
            fclose($file);
        }
    }
    /**
     * 获取缓存
     * @param  string $name 缓存名称
     * @return string
     */
    public function getCache($name){
        if($this->check()===1){
            return $this->cache->get($name);
        }else{
            //文件缓存
            if (is_file($this->cache_path.'/'.$name.'.php')) {
                $file = fopen($this->cache_path.'/'.$name.'.php','r');
                $data = fread($file,filesize($this->cache_path.'/'.$name.'.php'));
                fclose($file);
                return unserialize($data);
            }else{
                //文件不存在，直接返回空
                return '';
            } 
        }
    }
    /**
     * 删除缓存
     * @param  string $name 缓存名称
     * @return Boolean       
     */
    public function deleteCache($name){
        if($this->check()===1){
            return $this->cache->delete($name);
        }else{
            //文件缓存
            $file = $this->cache_path.'/'.$name.'.php';
            @unlink($file);
            return true;
        }
    }
    /**
     * 清空所有缓存，不是真的删除缓存的内容，只是使所有变量的缓存过期，使内存中的内容被重写
     * @return Boolean
     */
    public function flushCache(){
        if($this->check()===1){
            return $this->cache->flush();
        }else{
            //文件缓存,删除所有文件
            delDirAndFile($this->cache_path);
        }
    }
    /**
     * 删除过期缓存(文件)
     */
    public static function delOverdue($path){
        if (!file_exists($path)) {
            return false;
        }
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != ".."){
                    if(is_dir("$path/$item")){
                        self::delOverdue("$path/$item");
                    }else{
                        //文件存在，判断是否过期
                        $time = filemtime($path.'/'.$item);
                        if ($time+getConfig('cache_time') < time()) {
                            //过期。删除文件，并返回空
                            @unlink($path.'/'.$item);
                        }
                    }
                }
            }
            closedir($handle);
        }else {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
	
    /**
     * 判断缓存是否存在
     * @param  [type] $name 缓存名称
     * @return Boolean
     */
    public function check_key_exists($name){
        if($this->check()===1){
            $data = $this->cache->get($name);
            return $data!==false;
        }else{
            //文件缓存
            $file = $this->cache_path.'/'.$name.'.php';
            if (is_file($file)) {
                return true;
            }else{
                return false;
            }
        }
    }
}