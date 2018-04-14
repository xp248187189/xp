<?php 
//角色
class AdminController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 管理员列表
     */
    public function showList(){
        if (isset($_GET['action']) && $_GET['action']=='getData') {
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereStr = 'xp_admin.id != 1';
            if (!empty($_POST['keyWord'])) {
                $whereStr .= ' and (xp_admin.account like "%'.$_POST['keyWord'].'%" or xp_admin.name like "%'.$_POST['keyWord'].'%" or xp_admin.phone like "%'.$_POST['keyWord'].'%" or xp_admin.email like "%'.$_POST['keyWord'].'%")';
            }
            if (!empty($_POST['sex'])) {
                $whereStr .= ' and xp_admin.sex = "'.$_POST['sex'].'"';
            }
            if (!empty($_POST['role_id'])) {
                $whereStr .= ' and xp_admin.role_id = "'.$_POST['role_id'].'"';
            }
            $return['count'] = count($this->model('Admin')->query('select xp_admin.id,xp_admin.account,xp_admin.name,xp_admin.state,xp_admin.phone,xp_admin.email,xp_role.name as roleName from xp_admin left join xp_role on xp_admin.role_id=xp_role.id where '.$whereStr));
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('Admin')->query('select xp_admin.id,xp_admin.account,xp_admin.name,xp_admin.state,xp_admin.phone,xp_admin.email,xp_admin.sex,xp_role.name as roleName from xp_admin left join xp_role on xp_admin.role_id=xp_role.id where '.$whereStr.' limit '.$page->limit[0].','.$_POST['limit']);
            exit(json_encode($return));
        }
        //查询角色列表
        $roleList = $this->model('Role')->order(array('sort'))->select();
        $this->assign('roleList',$roleList);
        $this->display();
    }
    /**
     * 添加角色
     */
    public function add(){
        //查询角色列表
        $roleList = $this->model('Role')->order(array('sort'))->select();
        $this->assign('roleList',$roleList);
        $this->display();
    }
    /**
     * ajax执行添加
     */
    public function ajaxAdd(){
        $res = array(
                'state' => false,
                'echo'  => ''
            );
        $info = $this->model('Admin')->select();
        if(deep_in_array($_POST['account'],$info)){
            $res['echo'] = '此登录账号已被使用';
            exit(json_encode($res));
        }
        $_POST['password'] = md5($_POST['password']);
        unset($_POST['password2']);
        if($this->model('Admin')->add($_POST)){
            $res['state'] = true;
            $res['echo'] = '添加成功';
        }else{
            $res['echo'] = '添加失败';
        }
        exit(json_encode($res));
    }
    /**
     * 修改角色
     */
    public function edit(){
        $id = intval($_GET['id']);
        $adminInfo =  $this->model('Admin')->where(array('id'=>$id))->selectOne();
        //查询角色列表
        $roleList = $this->model('Role')->order(array('sort'))->select();
        $this->assign('roleList',$roleList);
        $this->assign('adminInfo',$adminInfo);
        $this->display();
    }
    /**
     * ajax执行修改
     */
    public function ajaxEdit(){
        $res = array(
                'state' => false,
                'echo'  => ''
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
        }else{
            $res['echo'] = '修改失败';
        }
        exit(json_encode($res));
    }
    /**
     * ajax执行删除
     */
    public function ajaxDel(){
        $res = array(
                'state' => false,
                'echo'  => ''
            );
        $ids = $_GET['id'];
        $ids = trim($ids,',');
        if($this->model('Admin')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}