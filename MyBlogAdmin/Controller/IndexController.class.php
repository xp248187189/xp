<?php 
class IndexController extends __Controller{
    protected function _init(){   
    }
    public function index(){
        $blogInfo =  $this->model('About')->filed(array('name'))->where(array('id'=>2))->selectOne();
        //判断是否是超级管理员
        if($_SESSION['adminInfo']['id']==1){
            //查询权限信息 一级
            $authList = $this->model('Auth')->where(array('level'=>0))->select();
            foreach ($authList as $key => $value) {
                //查询权限信息 二级
                $s_authList = $this->model('Auth')->where(array('pid'=>$value['id'],'level'=>1))->select();
                if (empty($s_authList)) {
                    //没有二级权限，那么一级权限也不显示
                    unset($authList[$key]);
                }else{
                    $authList[$key]['s_authList'] = $s_authList;
                }
            }
        }else{
            //查询角色信息
            $roleInfo = $this->model('Role')->where(array('id'=>$_SESSION['adminInfo']['role_id']))->selectOne();
            if ($roleInfo['auth_ids']=='') {
                $authList = array();
            }else{
                //查询权限信息 一级
                $authList = $this->model('Auth')->where(array('id'=>array($roleInfo['auth_ids'],'in'),'level'=>0))->select();
                foreach ($authList as $key => $value) {
                    //查询权限信息 二级
                    $s_authList = $this->model('Auth')->where(array('pid'=>$value['id'],'id'=>array($roleInfo['auth_ids'],'in'),'level'=>1))->select();
                    if (empty($s_authList)) {
                        //没有二级权限，那么一级权限也不显示
                        unset($authList[$key]);
                    }else{
                        $authList[$key]['s_authList'] = $s_authList;
                    }
                }
            }
        }
        $this->assign('authList',$authList);
        $this->assign('blogInfo',$blogInfo);
        $this->display();        
    }
    public function welcome(){
        $lastLoginInfo = $this->Model('AdminLogin')->order(array('time'=>'desc'))->limit(array(1,1))->selectOne();
        if(getIpLookup($lastLoginInfo['ip'])){
            $lastLoginInfo['province'] = getIpLookup($lastLoginInfo['ip'])['province'];
            $lastLoginInfo['city'] = getIpLookup($lastLoginInfo['ip'])['city'];
        }else{
            $lastLoginInfo['province'] = '未知';
            $lastLoginInfo['city'] = '';
        }
        $articleCount = $this->Model('Article')->getCount();//文章数
        $userCount = $this->Model('User')->getCount();//用户数
        //进入注册
        $where = 'addTime >= '.strtotime(date('Y-m-d')).' and addTime <= '.strtotime("+1 day");
        $today_add = $this->Model('User')->where($where)->getCount();
        //今日登录
        $where = 'time >= '.strtotime(date('Y-m-d')).' and time <= '.strtotime("+1 day");
        $today_login = $this->Model('UserLogin')->where($where)->getCount();
        $this->assign('lastLoginInfo',$lastLoginInfo);
        $this->assign('articleCount',$articleCount);
        $this->assign('userCount',$userCount);
        $this->assign('today_add',$today_add);
        $this->assign('today_login',$today_login);
        $this->display();
    }
    /**
     * 修改个人资料
     */
    public function editMe(){
        $this->display();
    }
    /**
     * ajax执行修改
     */
    public function ajaxEdit(){
        $res = array(
                'state' => false,
                'echo'  => '',
                'name'  => '',
            );
        $id = intval($_POST['id']);
        $info = $this->model('Admin')->where(array('id'=>array($id,'!=')))->select();
        if(isset($_POST['account'])){
            if(deep_in_array($_POST['account'],$info)){
                $res['echo'] = '此登录账号已被使用';
                exit(json_encode($res));
            }
        }
        if (empty($_POST['password'])) {
            unset($_POST['password']);
        }else{
            $_POST['password'] = md5($_POST['password']);
        }
        unset($_POST['password2']);
        if($this->model('Admin')->where(array('id'=>$id))->update($_POST)){
            $res['state'] = true;
            $res['echo'] = '修改成功';
            $res['name'] = html_entity_decode($_POST['name'],ENT_QUOTES);
        }else{
            $res['echo'] = '修改失败';
        }
        exit(json_encode($res));
    }
    /**
     * 换肤
     */
    public function ajaxChangeSkin(){
        $id = intval($_POST['id']);
        if($this->model('Admin')->where(array('id'=>$id))->update($_POST)){
            exit(json_encode(array('state'=>true,'echo'=>'修改成功')));
        }else{
            exit(json_encode(array('state'=>true,'echo'=>'修改失败')));
        }
    }
}