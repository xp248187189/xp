<?php 
//时间轴
class TimeAxisController extends __Controller{
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
            $return['count'] = $this->model('TimeAxis')->where($where)->getCount();
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('TimeAxis')->where($where)->order(array('year'=>'desc','month'=>'desc','day'=>'desc','hour'=>'desc','minute'=>'desc'))->limit(array($page->limit[0],$_POST['limit']))->select();
            foreach ($return['data'] as $key => $value) {
                $return['data'][$key]['content'] = html_entity_decode($value['content'],ENT_QUOTES);
            }
            exit(json_encode($return));
        }
        $this->display();
    }
    /**
     * 添加分类
     */
    public function add(){
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
        $_POST['year'] = date('Y');
        $_POST['month'] = date('m');
        $_POST['day'] = date('d');
        $_POST['hour'] = date('H');
        $_POST['minute'] = date('i');
        $_POST['time'] = time();
        if($this->model('TimeAxis')->add($_POST)){
            $res['state'] = true;
            $res['echo'] = '添加成功';
        }else{
            $res['echo'] = '添加失败';
        }
        exit(json_encode($res));
    }
    /**
     * 修改分类
     */
    public function edit(){
        $id = intval($_GET['id']);
        $TimeAxisInfo =  $this->model('TimeAxis')->where(array('id'=>$id))->selectOne();
        $this->assign('TimeAxisInfo',$TimeAxisInfo);
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
        if($this->model('TimeAxis')->where(array('id'=>$id))->update($_POST)){
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
        if($this->model('TimeAxis')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}