<?php 
//后台公用类，验证登录和权限信息
class __Controller extends Controller{
    public function __construct(){
        parent::__construct();
        //判断是否登录
        if(empty($_COOKIE['admin_id'])){
            header('location:'.u('/Login/login'));
            exit();
        }
        //已登录，每次执行操作都进行查询登录人信息并赋值给$_SESSION['adminInfo']
        $adminInfo = $this->model('Admin')->where(array('id'=>decrypt($_COOKIE['admin_id'],getConfig('key'))))->selectOne();
        if(empty($adminInfo)){
            header('location:'.u('/Login/login'));
            exit();
        }
        $_SESSION['adminInfo'] = $adminInfo;
        //如果不是超级管理员，就需要进行权限判断
        if($_SESSION['adminInfo']['id'] != 1){
            //查询角色信息，根据角色查询对应的权限
            $roleInfo = $this->model('Role')->where(array('id'=>$_SESSION['adminInfo']['role_id']))->selectOne();
            if ($roleInfo['auth_ids']=='') {
                $roleInfo['auth_ids'] = 0;
            }
            $authList = $this->model('Auth')->where(array('id'=>array($roleInfo['auth_ids'],'in')))->select();
            //Index控制器不设置权限
            if(__CONTROLLER__ != 'Index'){
                //如果没有控制器的权限，或者没有方法的权限，就提示错误信息
                if(!deep_in_array(__CONTROLLER__,$authList) || !deep_in_array(__ACTION__,$authList)){
                    if(isAjax()){
                        exit(json_encode(array('state'=>false,'echo'=>'(●′ω`●) 对不起... 您无权进行此操作！！！')));
                    }else{
                        $echo  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link rel="icon" href="'.__COMMON__.'/img/favicon.ico" type="image/x-icon" /><link rel="shortcut icon" href="'.__COMMON__.'/img/favicon.ico" type="image/x-icon" /><title>这个.. 页面出错了！！！</title></head><body>';
                        $echo .= '<div style="text-align:center;overflow:hidden;height:120px;margin: auto;position: absolute;top: 0; left: 0; bottom: 0; right: 0; ">';
                        $echo .= '<h1>(●′ω`●) 对不起... 您无权进行此操作！！！</h1>';
                        $echo .= '</div></body></html>';
                        exit($echo);
                    }
                }
            }
        }
    }
}