<?php 
//菜单
class AuthController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 菜单列表
     */
    public function showList(){
        if (isset($_GET['action']) && $_GET['action']=='getData') {
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array(),'current'=>array('id'=>0,'name'=>'根目录','level'=>0,'id_list'=>0),'queueStr'=>'');
            //查询权限信息
            if (empty($_GET['id'])) {
                $whereArray = array('level'=>0);
            }else{
                $whereArray = array('pid'=>$_GET['id']);
                //查询队列
                $current = $this->model('Auth')->where(array('id'=>$_GET['id']))->selectOne();
                $return['current'] = $current;
                $return['current']['level'] = $return['current']['level']+1;
                $queue = $this->model('Auth')->order(array('field(id,'.$current['id_list'].')'))->where(array('id'=>array($current['id_list'],'in')))->select();
                $queueStr = '';
                foreach ($queue as $key => $value) {
                    $queueStr .= ' <i class="fa fa-fw fa-arrow-right"></i> <a href="javascript:;" onclick="nextLevel('.$value['id'].')">'.$value['name'].'</a>';
                    $return['queueStr'] = $queueStr;
                }
            }
            if (!empty($_POST['name'])) {
                $whereArray['name'] = array('%'.$_POST['name'].'%','like');
            }
            $return['count'] = $this->model('Auth')->where($whereArray)->getCount();
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('Auth')->order(array('sort'))->where($whereArray)->limit(array($page->limit[0],$_POST['limit']))->select();
            
            exit(json_encode($return));
        }
        $this->display();
    }
    /**
     * 菜单添加页面
     */
    public function add(){
        //获取图标class
        $iconClassFile = file_get_contents(APP_PATH.'/Common/font-awesome/css/font-awesome.css');
        preg_match_all('/\.(.*?):before {/',$iconClassFile,$iconInfo);
        $iconClass = $iconInfo[1];
        $this->assign('iconClass',$iconClass);
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
        if (isset($_POST['controller'])) {
            $_POST['controller'] = ucfirst($_POST['controller']);
        }
        $insertTd = $this->model('Auth')->add($_POST);
        if ($_POST['id_list']==0) {
            $r = $this->model('Auth')->where(array('id'=>$insertTd))->update(array('id_list'=>$insertTd));
        }else{
            $r = $this->model('Auth')->where(array('id'=>$insertTd))->update(array('id_list'=>$_POST['id_list'].','.$insertTd));
        }
        if($r){
            $res['state'] = true;
            $res['echo'] = '添加成功';
        }else{
            $res['echo'] = '添加失败';
        }
        exit(json_encode($res));
    }
    /**
     * 菜单编辑页面
     */
    public function edit(){
        $id = intval($_GET['id']);
        $authInfo =  $this->model('Auth')->where(array('id'=>$id))->selectOne();
        //获取图标class
        $iconClassFile = file_get_contents(APP_PATH.'/Common/font-awesome/css/font-awesome.css');
        preg_match_all('/\.(.*?):before {/',$iconClassFile,$iconInfo);
        $iconClass = $iconInfo[1];
        $this->assign('iconClass',$iconClass);
        $this->assign('authInfo',$authInfo);
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
        if (isset($_POST['controller'])) {
            $_POST['controller'] = ucfirst($_POST['controller']);
        }   
        if($this->model('Auth')->where(array('id'=>$id))->update($_POST)){
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
        $this->delAllSon($ids);
        $res['state'] = true;
        $res['echo'] = '删除成功';
        exit(json_encode($res));
    }
    /**
     * 递归删除所有子级
     */
    private function delAllSon($ids){
        $ids = trim($ids,',');
        //删除本身
        $this->model('Auth')->where(array('id'=>array($ids,'in')))->delete();
        //把本身ids转换为数组
        $idsArr = explode(',',$ids);
        //循环数组
        foreach ($idsArr as $key => $value) {
            //查出子级
            $r = $this->model('Auth')->where(array('pid'=>array($value,'in')))->select();
            if($r){
                //拼接子级id
                $son_ids = '';
                foreach ($r as $k => $v) {
                    $son_ids .= $v['id'].',';
                }
                $this->delAllSon($son_ids);
            }
            
        }
    }
}