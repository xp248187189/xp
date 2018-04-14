<?php 
/**
 * 控制器基类
 */
class Controller{
    protected $_modules;
    protected $_controller;
    protected $_action;
    protected $_view;
 
    // 构造函数，初始化属性，并实例化对应模型
    public function __construct(){
        //实例化视图类，用于分配变量和渲染视图
        $this->_view = new View(__MODULE__, __CONTROLLER__, __ACTION__);
        //把当前类赋值费view类的controller
        //这样的话，视图类(view)的controller属性就是当前控制器了
        //就可以在视图中使用控制器的方法了，如 $this->controller->aaa()
        $this->_view->controller = $this;
        //执行 _init 方法
        $this->_init();
    }
    //这里不做任何操作，只是用于控制器初始化信息
    protected function _init(){
    }
    // 分配变量
    protected function assign($name, $value){
        $this->_view->assign($name, $value);
    }
    // 渲染视图
    protected function display($viewName=''){
        $this->_view->display($viewName);
    }
    //实例化模型
    public function model($model){
        static $_model = array();
        $model = $model.'Model';
        if (isset($_model[$model])) {
            $_model[$model]->close();
            return $_model[$model];
        }else{
            $_model[$model] = new $model;
            $_model[$model]->close();
            return $_model[$model];
        }
    }
}