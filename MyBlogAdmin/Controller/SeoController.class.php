<?php 
//关键字与描述
class SeoController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 修改分类
     */
    public function show(){
        $keywords_info =  $this->model('About')->where(array('id'=>3))->selectOne();
        $description_info =  $this->model('About')->where(array('id'=>4))->selectOne();
        $this->assign('keywords_info',$keywords_info);
        $this->assign('description_info',$description_info);
        $this->display();
    }
    /**
     * ajax执行修改
     */
    public function ajaxEdit(){
        $res = array(
                'state' => false,
                'echo'  => '',
                'blogName'  => '',
            );
        if($this->model('About')->where(array('id'=>3))->update(array('label'=>$_POST['label_1'])) && $this->model('About')->where(array('id'=>4))->update(array('label'=>$_POST['label_2']))){
            $res['state'] = true;
            $res['echo'] = '修改成功';
            $res['blogName'] = $_POST['name'];
        }else{
            $res['echo'] = '修改失败';
        }
        exit(json_encode($res));
    }
}