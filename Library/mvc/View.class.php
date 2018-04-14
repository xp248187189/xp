<?php
/**
 * 视图基类
 */
class View{
    protected $_modules;
    protected $_controller;
    protected $_action;
    protected $_str;//模板变量
    public function __construct($modules, $controller, $action){
        $this->_modules = $modules;
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_str = array(
                        '__PUBLIC__'=>__PUBLIC__,
                        '__COMMON__'=>__COMMON__,
                        '__ROOT__'  =>__ROOT__
                    );
    }
 
    // 分配变量
    public function assign($name, $value){
        $this->$name = $value;
    }
 
    // 渲染显示
    public function display($viewName){
        if (!empty($viewName)) {
            $viewPath = APP_PATH.'/'.$this->_modules.'/View/'.$this->_controller.'/'.$viewName;
        }else{
            $viewPath = APP_PATH.'/'.$this->_modules.'/View/'.$this->_controller.'/'.$this->_action.'.html';
        }
        if (getConfig('cache_isViewCache')) {
            $notCacheModule = getConfig('cache_notCacheModule');
            $notCacheModuleArray = explode(',',$notCacheModule);
            if (!in_array(__MODULE__, $notCacheModuleArray)) {
                $cache = new Cache;
                //判断key是否存在
                $name = md5($_SERVER['REQUEST_URI']);
                if($cache->check_key_exists($name)){
                    exit($cache->getCache($name));
                }else{
                    $res = $this->strReplace($viewPath);
                    $cache->setCache($name,$res);
                    exit($cache->getCache($name));
                }
            }
        }
        exit($this->strReplace($viewPath));
    }
    /**
     * 模板变量替换
     * @param  [type] $viewPath 模板路径
     * @return string
     */
    private function strReplace($viewPath){
        //开启缓冲区,在缓冲区引入模板，就不会显示了，用缓冲区来替换是为了不用fopen()等文件函数
        ob_start();
        include $viewPath;
        //ob_get_clean()，得到当前缓冲区的内容并删除当前输出缓冲区
        //这里就是把缓冲区的内容(html模板)赋值给$content,并删除了缓冲区的内容(也就是html模板)
        $content = ob_get_clean();
        if(is_array($this->_str)){
            //array_keys(),返回包含数组中所有键名的一个新数组
            //array_values(),返回数组的所有值（非键名）
            //这里就是把$content中的 $this->_str中的key值，替换成value值
            $content=str_replace(array_keys($this->_str),array_values($this->_str),$content);
        }
        //替换完成，再输出html
        return $content;
    }
}