<?php 
//后台登录
class AdminLoginController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 列表
     */
    public function showList(){
        if (isset($_GET['action']) && $_GET['action']=='getData') {
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $where = '1 = 1';
            if (!empty($_POST['startTime'])) {
                $where .= ' and time >= '.strtotime($_POST['startTime']);
            }
            if (!empty($_POST['endTime'])) {
                $where .= ' and time <= '.(strtotime($_POST['endTime'])+86400);
            }
            if (!empty($_POST['keyWord'])) {
                $where .= ' and (ip like "%'.$_POST['keyWord'].'%" or account like "%'.$_POST['keyWord'].'%")';
            }
            $return['count'] = $this->model('AdminLogin')->where($where)->getCount();
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('AdminLogin')->order(array('time'=>'desc'))->where($where)->limit(array($page->limit[0],$_POST['limit']))->select();
            exit(json_encode($return));
        }
        $this->display();
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
        if($this->model('AdminLogin')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}