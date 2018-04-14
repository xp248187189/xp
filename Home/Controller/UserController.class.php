<?php 
class UserController extends Controller{
    protected function _init(){   
        
    }
    /**
     * 用户登录
     */
    public function qqLogin(){
        $code = $_REQUEST["code"];
        //申请QQ互联后得到的APP_ID 和 APP_KEY
        $app_id = 101449564;
        $app_key = 'f9d96c8edcea2f32f2f7c27875e43b5c';
        //回调接口，接受QQ服务器返回的信息的脚本
        $callback = 'http://www.yxiupei.cn/qqLogin';
        //实例化qq登陆类，传入上面三个参数
        $qq = new QqLogin($app_id,$app_key,$callback);
        if(empty($code)){
            //存储来源页地址
            $_SESSION['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
            $url=$qq->login_url();
            header("location:$url"); 
        }else{
            $token=$qq->access_token($code);//获取access-toking
            $openid=$qq->get_openid($token); //获取openid
            if(!empty($openid)){
                $r = $this->model('User')->where(array('connectid'=>$openid))->selectOne();
                if($r){
                    if ($r['state']==0) {
                        //禁止登录
                        $exit = '<script src="'.__COMMON__.'/layui/layui.all.js"></script>';
                        $exit.= '<script src="'.__COMMON__.'/layui/layuiGlobal.js"></script>';
                        $exit.= '<script type="text/javascript">';
                        $exit.= 'layer.msg("对不起，您已被限制登录！", function(){location.href="'.$_SESSION['HTTP_REFERER'].'"});';
                        $exit.= '</script>';
                        exit($exit);
                    }
                    //用户已经存在，更新用户信息(避免用户修改qq昵称或者头像时，本网站不同步)
                    $user = $qq->get_user_info($openid,$token);
                    $arr['account'] = $user['nickname'];
                    $arr['sex'] = $user['gender'];
                    $arr['head'] = $user['figureurl_qq_2'];
                    $this->model('User')->where(array('connectid'=>$openid))->update($arr);
                    setcookie('user_openid',encrypt($openid,getConfig('key')),time()+3600*24*7,'/');
                    setcookie('user_head',encrypt($user['figureurl_qq_2'],getConfig('key')),time()+3600*24*7,'/');
                    $loginInfo = array(
                            'ip' => getIP(),
                            'time' => time(),
                            'account' => $user['nickname'],
                            'browser' => $_SERVER['HTTP_USER_AGENT']
                        );
                    $this->Model('UserLogin')->add($loginInfo);
                    if($_SESSION['HTTP_REFERER']!=''){
                        header("location:".$_SESSION['HTTP_REFERER']);
                    }else{
                        header("location:".__HTTPTYPE__.'://'.__HOST__); 
                    }
                    exit;
                }else{
                    //用户不存在，添加用户信息
                    $user = $qq->get_user_info($openid,$token);
                    $arr['addTime'] = time();
                    $arr['account'] = $user['nickname'];
                    $arr['sex'] = $user['gender'];
                    $arr['head'] = $user['figureurl_qq_2'];
                    $arr['connectid'] = $openid;
                    $arr['state'] = 1;
                    $this->model('User')->add($arr);
                    setcookie('user_openid',encrypt($openid,getConfig('key')),time()+3600*24*7,'/');
                    setcookie('user_head',encrypt($user['figureurl_qq_2'],getConfig('key')),time()+3600*24*7,'/');
                    $loginInfo = array(
                            'ip' => getIP(),
                            'time' => time(),
                            'account' => $user['nickname'],
                            'browser' => $_SERVER['HTTP_USER_AGENT']
                        );
                    $this->Model('UserLogin')->add($loginInfo);
                    if($_SESSION['HTTP_REFERER']!=''){
                        header("location:".$_SESSION['HTTP_REFERER']);
                    }else{
                        header("location:".__HTTPTYPE__.'://'.__HOST__); 
                    }
                    exit;
                } 
            }
        }
    }
    public function userLogOut(){
        setcookie('user_openid','',time()-1,'/');
        setcookie('user_head','',time()-1,'/');
        header("location:".$_SERVER['HTTP_REFERER']);
    }
        
}