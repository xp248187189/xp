<?php 
//笔记
class NoteController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 列表
     */
    public function showList(){
        if (isset($_GET['action']) && $_GET['action']=='getData') {
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereArray = array();
            if (!empty($_POST['name'])) {
                $whereArray['title'] = array('%'.$_POST['name'].'%','like');
            }
            $return['count'] = $this->model('Note')->where($whereArray)->getCount();
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('Note')->where($whereArray)->limit(array($page->limit[0],$_POST['limit']))->select();
            exit(json_encode($return));
        }
        $this->display();
    }
    /**
     * 添加
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
        if($this->model('Note')->add($_POST)){
            $res['state'] = true;
            $res['echo'] = '添加成功';
        }else{
            $res['echo'] = '添加失败';
        }
        exit(json_encode($res));
    }
    /**
     * 修改
     */
    public function edit(){
        $id = intval($_GET['id']);
        $NoteInfo =  $this->model('Note')->where(array('id'=>$id))->selectOne();
        $this->assign('NoteInfo',$NoteInfo);
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
        if($this->model('Note')->where(array('id'=>$id))->update($_POST)){
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
        if($this->model('Note')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}