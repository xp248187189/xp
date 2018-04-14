<?php 
class ArticleController extends Controller{
    protected function _init(){   
        
    }
    /**
     * 显示
     */
    public function articleList(){
        //关键字
        $keyWordsInfo = $this->model('About')->where(array('id'=>3))->selectOne();
        //描述
        $descriptionInfo = $this->model('About')->where(array('id'=>4))->selectOne();
        //关于博客
        $blogInfo = $this->model('About')->where(array('id'=>2))->selectOne();
        //分类
        $categoryList = $this->model('Category')->where(array('state'=>1))->select();
        //根据分类id查询分类
        if (intval($_GET['category'])){
            $categoryName = $this->model('Category')->where(array('id'=>intval($_GET['category'])))->selectOne();
            $titleName = $categoryName['name'];
        }else if(trim($_GET['keyWord']) !== ''){
            $titleName = trim($_GET['keyWord']);
        }else{
            $titleName = '文章专栏';
        }
        //作者推荐
        $isRecommendList = $this->model('Article')->filed(array('title','id'))->where(array('state'=>1,'isRecommend'=>1))->order(array('sort','addTime'=>'desc'))->limit(array(0,8))->select();
        //随便看看
        $suijiList = $this->model('Article')->query('SELECT  title,id FROM `xp_article` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `xp_article`)-(SELECT MIN(id) FROM `xp_article`))+(SELECT MIN(id) FROM `xp_article`)) AS ccid) AS t2 WHERE t1.id >= t2.ccid ORDER BY t1.id LIMIT 8;');
        $this->assign('blogInfo',$blogInfo);
        $this->assign('categoryList',$categoryList);
        $this->assign('isRecommendList',$isRecommendList);
        $this->assign('suijiList',$suijiList);
        $this->assign('keyWordsInfo',$keyWordsInfo);
        $this->assign('descriptionInfo',$descriptionInfo);
        $this->assign('titleName',$titleName);
        $this->display();
    }
    /**
     * ajax获取流数据
     */
    public function getData(){
        $where = 'xp_article.state =1';
        if (!empty($_GET['category'])) {
            $where .= ' and xp_category.id = '.$_GET['category'];
        }
        if (!empty($_GET['keyWord'])) {
            $where .= ' and xp_article.title like "%'.$_GET['keyWord'].'%"';
        }
        //流加载文章
        $count = count($this->model('Article')->query('select xp_article.id,xp_category.name as categoryName from xp_article left join xp_category on xp_article.category_id=xp_category.id where '.$where));
        $page = new Page($count,10);
        if ($count==0) {
            $list = $this->model('Article')->query('SELECT * FROM `xp_article` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `xp_article`)-(SELECT MIN(id) FROM `xp_article`))+(SELECT MIN(id) FROM `xp_article`)) AS ccid) AS t2 WHERE t1.id >= t2.ccid ORDER BY t1.id LIMIT 8;');
            foreach ($list as $key => $value) {
                $list[$key]['categoryName'] = $this->model('Category')->filed(array('name'))->where(array('id'=>$value['category_id']))->selectOne()['name'];
            }
        }else{
            $list = $this->model('Article')->query('select xp_article.id,xp_article.title,xp_article.img,xp_article.author,xp_article.outline,xp_article.addTime,xp_article.showNum,xp_article.category_id,xp_category.name as categoryName from xp_article left join xp_category on xp_article.category_id=xp_category.id where '.$where.' order by xp_article.sort asc,xp_article.addTime desc limit '.$page->limit[0].','.$page->limit[1]);
        }
        foreach ($list as $key => $value) {
            $list[$key]['commentCount'] = $this->model('ArticleComment')->where(array('article_id'=>$value['id']))->getCount();
        }
        exit(json_encode(array('data'=>$list,'pageCount'=>$page->pageCount)));
    }
    /**
     * 详情
     */
    public function detail(){
        $id = intval($_GET['id']);
        $info = $this->model('Article')->where(array('id'=>$id))->selectOne();
        if (empty($info)) {
            showError(true);
            exit();
        }
        $this->model('Article')->query('update `xp_article` set `showNum` = showNum+1 where `id` = "'.$id.'"');
        //关于博客
        $blogInfo = $this->model('About')->where(array('id'=>2))->selectOne();
        //关键字
        $keyWordsInfo = $this->model('About')->where(array('id'=>3))->selectOne();
        //描述
        $descriptionInfo = $this->model('About')->where(array('id'=>4))->selectOne();
        //分类
        $categoryList = $this->model('Category')->where(array('state'=>1))->select();
        //作者推荐
        $xiangshiList = $this->model('Article')->filed(array('title','id'))->where(array('state'=>1,'category_id'=>$info['category_id']))->order(array('sort','addTime'=>'desc'))->limit(array(0,8))->select();
        //随便看看
        $suijiList = $this->model('Article')->query('SELECT  title,id FROM `xp_article` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `xp_article`)-(SELECT MIN(id) FROM `xp_article`))+(SELECT MIN(id) FROM `xp_article`)) AS ccid) AS t2 WHERE t1.id >= t2.ccid ORDER BY t1.id LIMIT 8;');
        $this->assign('blogInfo',$blogInfo);
        $this->assign('categoryList',$categoryList);
        $this->assign('xiangshiList',$xiangshiList);
        $this->assign('suijiList',$suijiList);
        $this->assign('info',$info);
        $this->assign('keyWordsInfo',$keyWordsInfo);
        $this->assign('descriptionInfo',$descriptionInfo);
        $this->display();
    }
    //提交评论
    public function articleComment(){
        $user_openid = decrypt($_COOKIE['user_openid'],getConfig('key'));
        $userInfo = $this->model('User')->where(array('connectid'=>$user_openid))->selectOne();
        $arr = array(
                'article_id' => $_POST['articleId'],
                'user_id' => $userInfo['id'],
                'time' => time(),
                'connect' => $_POST['editorContent'],
            );
        if($this->model('ArticleComment')->add($arr)){
            exit(json_encode(array('state'=>true,'echo'=>'评论成功')));
        }else{
            exit(json_encode(array('state'=>false,'echo'=>'评论失败')));
        }
    }
    //获取评论
    public function getArticleComment(){
        $sql = 'select 
                    u.account as userAccount,u.head as userHead,a.title as articleTitle,c.* 
                    from xp_article_comment as c 
                    left join xp_user as u on u.id = c.user_id 
                    left join xp_article as a on a.id = c.article_id 
                    ';
        $count = count($this->model('ArticleComment')->query($sql));
        $page = new Page($count,10);
        $articleComment = $this->model('ArticleComment')->query($sql.' where c.article_id = '.intval($_GET['articleId']).' order by c.time desc limit '.$page->limit[0].','.$page->limit[1]);
        foreach ($articleComment as $key => $value) {
            $articleComment[$key]['connect'] = html_entity_decode($value['connect'],ENT_QUOTES);
        }
        exit(json_encode(array('data'=>$articleComment,'pageCount'=>$page->pageCount)));
    }
}