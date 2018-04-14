<?php 
class AboutController extends Controller{
    protected function _init(){   
        
    }
    /**
     * 首页
     */
    public function about(){
        //友情链接
        $linkList = $this->model('Link')->where(array('state'=>1))->order(array('sort'))->select();
        //关于博客
        $blogInfo = $this->model('About')->where(array('id'=>2))->selectOne();
        //关于博主
        $bloggerInfo = $this->model('About')->where(array('id'=>1))->selectOne();
        //关键字
        $keyWordsInfo = $this->model('About')->where(array('id'=>3))->selectOne();
        //描述
        $descriptionInfo = $this->model('About')->where(array('id'=>4))->selectOne();
        $this->assign('linkList',$linkList);
        $this->assign('blogInfo',$blogInfo);
        $this->assign('bloggerInfo',$bloggerInfo);
        $this->assign('keyWordsInfo',$keyWordsInfo);
        $this->assign('descriptionInfo',$descriptionInfo);
        $this->display();
    }
    //获取留言
    public function getUserComment(){
        $sql = 'select 
                    u.account as userAccount,u.head as userHead,c.* 
                    from xp_user_comment as c 
                    left join xp_user as u on u.id = c.user_id 
                    ';
        $count = count($this->model('UserComment')->query($sql));
        $page = new Page($count,10);
        $userComment = $this->model('UserComment')->query($sql.' order by c.time desc limit '.$page->limit[0].','.$page->limit[1]);
        foreach ($userComment as $key => $value) {
            $userComment[$key]['connect'] = html_entity_decode($value['connect'],ENT_QUOTES);
        }
        exit(json_encode(array('data'=>$userComment,'pageCount'=>$page->pageCount)));
    }
    //提交留言
    public function userComment(){
        $user_openid = decrypt($_COOKIE['user_openid'],getConfig('key'));
        $userInfo = $this->model('User')->where(array('connectid'=>$user_openid))->selectOne();
        $arr = array(
                'user_id' => $userInfo['id'],
                'time' => time(),
                'connect' => $_POST['editorContent'],
            );
        if($this->model('UserComment')->add($arr)){
            exit(json_encode(array('state'=>true,'echo'=>'评论成功')));
        }else{
            exit(json_encode(array('state'=>false,'echo'=>'评论失败')));
        }
    }
}