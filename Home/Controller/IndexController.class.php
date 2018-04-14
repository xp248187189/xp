<?php 
class IndexController extends Controller{
    protected function _init(){   
        
    }
    /**
     * 首页
     */
    public function index(){
        //推荐文章
        $isRecommendList = $this->model('Article')->where(array('state'=>1,'isRecommend'=>1))->order(array('sort','addTime'=>'desc'))->limit(array(0,8))->filed(array('title','id'))->select();
        //最新文章
        $newestList = $this->model('Article')->where(array('state'=>1))->order(array('addTime'=>'desc','sort'))->limit(array(0,8))->filed(array('title','id'))->select();
        //一路走来
        $timeAxisList = $this->model('TimeAxis')->where(array('state'=>1,'isHome'=>1))->order(array('year'=>'desc','month'=>'desc','day'=>'desc','hour'=>'desc','minute'=>'desc'))->limit(array(0,8))->select();
        //友情链接
        $linkList = $this->model('Link')->where(array('state'=>1))->order(array('sort'))->select();
        //关于博客
        $blogInfo = $this->model('About')->where(array('id'=>2))->filed(array('name','introduce','label'))->selectOne();
        //关于博主
        $bloggerInfo = $this->model('About')->where(array('id'=>1))->filed(array('name','introduce','label','img'))->selectOne();
        //关键字
        $keyWordsInfo = $this->model('About')->where(array('id'=>3))->selectOne();
        //描述
        $descriptionInfo = $this->model('About')->where(array('id'=>4))->selectOne();
        //网站公告
        $noticeList = $this->model('Notice')->where(array('state'=>1))->order(array('sort'))->select();
        $this->assign('isRecommendList',$isRecommendList);
        $this->assign('newestList',$newestList);
        $this->assign('timeAxisList',$timeAxisList);
        $this->assign('linkList',$linkList);
        $this->assign('blogInfo',$blogInfo);
        $this->assign('bloggerInfo',$bloggerInfo);
        $this->assign('noticeList',$noticeList);
        $this->assign('keyWordsInfo',$keyWordsInfo);
        $this->assign('descriptionInfo',$descriptionInfo);
        $this->display();
    }
    /**
     * ajax获取数据
     */
    public function getDataForIndex(){
        //首页流加载文章
        $isHomeCount = count($this->model('Article')->query('select xp_article.id,xp_category.name as categoryName from xp_article left join xp_category on xp_article.category_id=xp_category.id where xp_article.state = 1 and xp_article.isHome = 1'));
        $page = new Page($isHomeCount,10);
        if ($isHomeCount==0) {
            $isHomeList = $this->model('Article')->query('SELECT * FROM `xp_article` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `xp_article`)-(SELECT MIN(id) FROM `xp_article`))+(SELECT MIN(id) FROM `xp_article`)) AS ccid) AS t2 WHERE t1.id >= t2.ccid ORDER BY t1.id LIMIT 8;');
            foreach ($isHomeList as $key => $value) {
                $isHomeList[$key]['categoryName'] = $this->model('Category')->filed(array('name'))->where(array('id'=>$value['category_id']))->selectOne()['name'];
            }
        }else{
            $isHomeList = $this->model('Article')->query('select xp_article.id,xp_article.title,xp_article.img,xp_article.author,xp_article.outline,xp_article.addTime,xp_article.showNum,xp_article.category_id,xp_category.name as categoryName from xp_article left join xp_category on xp_article.category_id=xp_category.id where xp_article.state =1 and xp_article.isHome = 1 order by xp_article.sort asc,xp_article.addTime desc limit '.$page->limit[0].','.$page->limit[1]);
        }
        foreach ($isHomeList as $key => $value) {
            $isHomeList[$key]['commentCount'] = $this->model('ArticleComment')->where(array('article_id'=>$value['id']))->getCount();
        }
        exit(json_encode(array('data'=>$isHomeList,'pageCount'=>$page->pageCount)));
    }
}