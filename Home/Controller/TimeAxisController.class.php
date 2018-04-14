<?php 
class TimeAxisController extends Controller{
    protected function _init(){   
        
    }
    /**
     * 首页
     */
    public function timeAxis(){
        //时间轴
        $year = $this->model('TimeAxis')->where(array('state'=>1))->order(array('year'=>'desc'))->filed(array('year'))->group(array('year'))->select();
        foreach ($year as $k => $v) {
            $year[$k]['zi'] = $this->model('TimeAxis')->where(array('state'=>1,'year'=>$v['year']))->order(array('month'=>'desc'))->group(array('month'))->filed(array('month'))->select();
            foreach ($year[$k]['zi'] as $kk => $vv) {
                $year[$k]['zi'][$kk]['zi'] = $this->model('TimeAxis')->where(array('state'=>1,'year'=>$v['year'],'month'=>$vv['month']))->order(array('day'=>'desc'))->select();
            }
        }
        //关于博客
        $blogInfo = $this->model('About')->where(array('id'=>2))->filed(array('name','introduce','label'))->selectOne();
        //关键字
        $keyWordsInfo = $this->model('About')->where(array('id'=>3))->selectOne();
        //描述
        $descriptionInfo = $this->model('About')->where(array('id'=>4))->selectOne();
        $this->assign('timeAxisList',$year);
        $this->assign('blogInfo',$blogInfo);
        $this->assign('keyWordsInfo',$keyWordsInfo);
        $this->assign('descriptionInfo',$descriptionInfo);
        $this->display();
    }
}