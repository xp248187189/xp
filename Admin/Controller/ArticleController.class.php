<?php 
//文章
class ArticleController extends __Controller{
    protected function _init(){   
        
    }
    /**
     * 文章列表
     */
    public function showList(){
        if (isset($_GET['action']) && $_GET['action']=='getData') {
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereStr = '1 = 1';
            if (!empty($_POST['keyWord'])) {
                $whereStr .= ' and xp_article.title like "%'.$_POST['keyWord'].'%"';
            }
            if (!empty($_POST['category_id'])) {
                $whereStr .= ' and xp_article.category_id = "'.$_POST['category_id'].'"';
            }
            $return['count'] = count($this->model('Admin')->query('select xp_article.id,xp_category.name as categoryName from xp_article left join xp_category on xp_article.category_id=xp_category.id where '.$whereStr));
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('Admin')->query('select xp_article.id,xp_article.title,xp_article.isHome,xp_article.isRecommend,xp_article.sort,xp_article.state,xp_article.img,xp_article.author,xp_article.addTime,xp_article.showNum,xp_category.name as categoryName from xp_article left join xp_category on xp_article.category_id=xp_category.id where '.$whereStr.' order by sort asc,addTime desc limit '.$page->limit[0].','.$_POST['limit']);
            foreach ($return['data'] as $key => $value) {
                $return['data'][$key]['addTime'] = date('Y-m-d H:i:s',$value['addTime']);
            }
            exit(json_encode($return));
        }
        $categoryArr = $this->model('Category')->where(array('state'=>1))->order(array('sort'))->select();
        $this->assign('categoryArr',$categoryArr);
        $this->display();
    }
    /**
     * 添加文章
     */
    public function add(){
        $categoryArr = $this->model('Category')->where(array('state'=>1))->order(array('sort'))->select();
        $this->assign('categoryArr',$categoryArr);
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
        if (!empty($_FILES['img']['tmp_name'])) {
            $upload = new Upload('article');
            $_POST['img'] = $upload->upload()['newFile'][1];
        }
        $_POST['addTime'] = time();
        $_POST['author'] = $_SESSION['adminInfo']['name'];
        $_POST['showNum'] = 0;
        if($this->model('Article')->add($_POST)){
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
        $articleInfo = $this->model('Article')->where(array('id'=>$id))->selectOne();
        $categoryArr = $this->model('Category')->where(array('state'=>1))->order(array('sort'))->select();
        $this->assign('categoryArr',$categoryArr);
        $this->assign('articleInfo',$articleInfo);
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
            $upload = new Upload('article');
            $_POST['img'] = $upload->upload()['newFile'][1];
            $img = $this->model('Article')->where(array('id'=>$id))->filed(array('img'))->selectOne();
            if(is_file(APP_PATH.$img['img'])){
                unlink(APP_PATH.$img['img']);
            }
        }
        if($this->model('Article')->where(array('id'=>$id))->update($_POST)){
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
        $imgArr = $this->model('Article')->where(array('id'=>array($ids,'in')))->filed(array('img'))->select();
        foreach ($imgArr as $key => $value) {
            if(is_file(APP_PATH.$value['img'])){
                unlink(APP_PATH.$value['img']);
            }
        }
        if($this->model('Article')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}