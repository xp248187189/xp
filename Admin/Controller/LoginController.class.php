<?php 
class LoginController extends Controller{
    protected function _init(){
        $this->adminModel = $this->model('Admin');//实例化模型
    }
    /**
     * 获取验证码
     */
    public function getVerify(){
        $verifyClass = new Verify();
        $verifyClass->show();
    }
    /**
     * 检查验证码
     * @param  string $code 验证码
     * @return boolean      
     */
    public function checkVerify($code){
        $verifyClass = new Verify();
        return $verifyClass->check($code);
    }
    /**
     * 登录页面
     */
	public function login(){
        $this->display();
    }
    /**
     * ajax执行登录
     */
    public function doLogin(){
        $res = array(
                'state' => false,
                'echo'  => ''
            );
        if (!$this->checkVerify($_POST['verify'])) {
            $res['echo'] = '验证码错误';
            exit(json_encode($res));
        }
        $adminInfo = $this->adminModel->where(array('account'=>trim($_POST['account'])))->selectOne();
        if (empty($adminInfo)) {
            $res['echo'] = '用户名或密码错误';
            exit(json_encode($res));
        }
        if ($adminInfo['state']==0) {
            $res['echo'] = '此账号已被禁用';
            exit(json_encode($res));
        }
        if($adminInfo['password'] != md5(trim($_POST['password']))){
            $res['echo'] = '用户名或密码错误';
            exit(json_encode($res));
        }
        if(isset($_POST['online']) && $_POST['online']==1){
            setcookie('admin_id',encrypt($adminInfo['id'],getConfig('key')),time()+3600*24*7,'/');
            //setcookie('admin_account',encrypt($adminInfo['account'],getConfig('key')),time()+3600*24*7,'/');
        }else{
            setcookie('admin_id',encrypt($adminInfo['id'],getConfig('key')),0,'/');
            //setcookie('admin_account',encrypt($adminInfo['account'],getConfig('key')),0,'/');
        }
        $loginInfo = array(
                'ip' => getIP(),
                'time' => time(),
                'account' => $adminInfo['account'],
                'browser' => $_SERVER['HTTP_USER_AGENT']
            );
        $this->Model('AdminLogin')->add($loginInfo);
        $res['state'] = true;
        $res['echo'] = '登录成功';
        exit(json_encode($res));
    }
    /**
     * 退出登录
     */
    public function doLogOut(){
        unset($_SESSION['adminInfo']);
        setcookie('admin_id','',time()-1,'/');
        //setcookie('admin_account','',time()-1,'/');
        exit(json_encode(array('state'=>true,'echo'=>'退出成功')));
    }
}