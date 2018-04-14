<?php 
//文章评论
class ArticleCommentController extends __Controller{
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
                $where .= ' and (a.title like "%'.$_POST['keyWord'].'%" or u.account like "%'.$_POST['keyWord'].'%")';
            }
            $sql = 'select 
                        u.account as userAccount,a.title as articleTitle,c.* 
                        from xp_article_comment as c 
                        left join xp_user as u on u.id = c.user_id 
                        left join xp_article as a on a.id = c.article_id 
                        ';
            $return['count'] = count($this->model('ArticleComment')->query($sql.' where '.$where));
            $_GET['page'] = $_POST['page'];//因为page类的当前页数是get方式获取
            $page = new Page($return['count'],$_POST['limit']);
            $return['data'] = $this->model('ArticleComment')->query($sql.' where '.$where.' order by c.time desc limit '.$page->limit[0].','.$_POST['limit']);
            foreach ($return['data'] as $key => $value) {
                $return['data'][$key]['connect'] = html_entity_decode($value['connect'],ENT_QUOTES);
            }
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
        if($this->model('ArticleComment')->where(array('id'=>array($ids,'in')))->delete()){
            $res['state'] = true;
            $res['echo'] = '删除成功';
        }else{
            $res['echo'] = '删除失败';
        }
        exit(json_encode($res));
    }
}