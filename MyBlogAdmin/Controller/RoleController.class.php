<?php 
//角色
class RoleController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 角色列表
     */
    public function showList(){
        if (isset($_GET['action']) && $_GET['action']=='getData') {
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereArray = array();
            if (!empty($_POST['name'])) {
                $whereArray['name'] = array('%'.$_POST['name'].'%','like');
            }
            $return['count'] = $this->model('Role')->where($whereArray)->getCount();
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('Role')->order(array('sort'))->where($whereArray)->limit(array($page->limit[0],$_POST['limit']))->select();
            exit(json_encode($return));
        }
        $this->display();
    }
    /**
     * 添加角色
     */
    public function add(){
        //查询所有菜单
        $one_authList = $this->model('Auth')->where(array('level'=>0))->select();
        $two_authList = $this->model('Auth')->where(array('level'=>1))->select();
        $three_authList = $this->model('Auth')->where(array('level'=>2))->select();
        $this->assign('one_authList',$one_authList);
        $this->assign('two_authList',$two_authList);
        $this->assign('three_authList',$three_authList);
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
        if (isset($_POST['auth_ids'])) {
            $_POST['auth_ids'] = implode(',',$_POST['auth_ids']);
        }else{
        	$_POST['auth_ids'] = '';
        }
        if($this->model('Role')->add($_POST)){
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
        $roleInfo =  $this->model('Role')->where(array('id'=>$id))->selectOne();
        //查询所有菜单
        $one_authList = $this->model('Auth')->where(array('level'=>0))->select();
        $two_authList = $this->model('Auth')->where(array('level'=>1))->select();
        $three_authList = $this->model('Auth')->where(array('level'=>2))->select();
        $this->assign('one_authList',$one_authList);
        $this->assign('two_authList',$two_authList);
        $this->assign('three_authList',$three_authList);
        $this->assign('roleInfo',$roleInfo);
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
        if (isset($_POST['auth_ids'])) {
            $_POST['auth_ids'] = implode(',',$_POST['auth_ids']);
        }else{
            $_POST['auth_ids'] = '';
        }
        if($this->model('Role')->where(array('id'=>$id))->update($_POST)){
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
        if($this->model('Role')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}