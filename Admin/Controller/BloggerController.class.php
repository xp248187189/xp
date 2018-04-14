<?php 
//博主信息
class BloggerController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 修改分类
     */
    public function show(){
        $info =  $this->model('About')->where(array('id'=>1))->selectOne();
        $this->assign('info',$info);
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
        if (!empty($_FILES['img']['tmp_name'])) {
            $upload = new Upload('about');
            $_POST['img'] = $upload->upload()['newFile'][1];
            $img = $this->model('About')->where(array('id'=>1))->filed(array('img'))->selectOne();
            if(is_file(APP_PATH.$img['img'])){
                unlink(APP_PATH.$img['img']);
            }
        }
        if($this->model('About')->where(array('id'=>$id))->update($_POST)){
            $res['state'] = true;
            $res['echo'] = '修改成功';
        }else{
            $res['echo'] = '修改失败';
        }
        exit(json_encode($res));
    }
}