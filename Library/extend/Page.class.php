<?php
//分页类
class Page{
	private $totle;//总记录数
	private $listRows;//每页显示的条数
	private $nowPage;//当前页数
	public $pageCount;//总页数
	private $pageNum;//显示多少个页码
	private $url;//当前url地址
	private $goto_url;//跳转url地址
	private $firstPage;//首页
	private $endPage;//末页
	private $prePage;//上一页
	private $nextPage;//下一页
	private $start;//页码开头
	private $end;//页码结束
	public $limit;//返回sql语句的limit
	public $serialNumber;//返回列表序号，如第一页123.第二页456
	/**
	 * 构造方法
	 * @param [type]  $totle    总记录数
	 * @param integer $listRows 每页显示的条数
	 * @param integer $pageNum  页码个数
	 */
	public function __construct($totle,$listRows=10,$pageNum=5){
		//总记录数
		$this->totle = $totle;
		//每页显示的条数
		$this->listRows = $listRows;
		//页码个数
		$this->pageNum = $pageNum;
		//总页数，ceil()向上取整
		$this->pageCount = ceil($this->totle/$this->listRows);
		//当前页数
		$this->nowPage = empty($_GET['page'])?1:intval($_GET['page']);
		$this->nowPage = $this->nowPage<=0?1:$this->nowPage;
		$this->nowPage = $this->nowPage>=$this->pageCount?$this->pageCount:$this->nowPage;
		//末页
		$this->endPage = $this->pageCount;
		//上一页
		$this->prePage = ($this->nowPage<=1)?1:($this->nowPage-1);
		//下一页
		$this->nextPage = ($this->nowPage>=$this->pageCount)?$this->pageCount:($this->nowPage+1);
		//取得页码开头和结束，floor()向下取整
		$this->start = $this->nowPage-floor($this->pageNum/2);
		$this->end = $this->nowPage+floor($this->pageNum/2);
		if ($this->start < 1) {
			$this->start = 1;
            $this->end = $this->pageNum;
        }
        if ($this->end > $this->pageCount) {
            $this->start = $this->start - ($this->end - $this->pageCount);
            $this->end = $this->pageCount;
        }
        if ($this->start < 1) {
			$this->start = 1;
        }
        //当前url地址
        $this->url = $_SERVER['REQUEST_URI'];
        //跳转地址
        $this->goto_url = $this->goto_url();
        //返回sql语句的limit
        $this->limit = $this->getLimit();
        //返回列表序号，如第一页123.第二页456
        $this->serialNumber = $this->getSerialNumber();
	}
	//地址替换
    private function page_replace($page) {
		if (getConfig('urlType')==1) {
			//pathinfo
			if (stripos($this->url,'page') !== false) {//有page参数
				//把url地址中的 /page/原页码 替换成 /page/现页码
				return preg_replace('/page\/[\d]*/','page/'.intval($page),$this->url);
			}else{//无page参数
				return $this->url.'/page/'.intval($page);
			}
		}else if(getConfig('urlType')==2){
			//get
			//把地址中的page=数字 替换成 page=当前页
	        if (stripos($this->url,'page') !== false) {
				return preg_replace('/page=\d*/','page='.intval($page),$this->url);
			}else{
				return $this->url.'&page='.intval($page);
			}
		}
    }
    //跳转地址,自定义跳转用
    private function goto_url(){
		if (getConfig('urlType')==1) {
			//pathinfo
			if (stripos($this->url,'page') !== false) {//有page参数
				//把url地址中的 /page/页码 替换成空
				$trmpurl = preg_replace('/\/page\/[\d]*/','',$this->url);
				//在url末尾加成 /page/
				return $trmpurl.'/page/';
			}else{//无page参数
				return $this->url.'/page/';
			}
		}else if(getConfig('urlType')==2){
			//把地址中的page=数字 替换成 page=当前页
	        if (stripos($this->url,'page') !== false) {
	        	//把url地址中的 &page=页码 或者 page=页码 替换成空
	        	// &? 是为了避免page参数是第一个参数，也就是在?后面
	        	$trmpurl = preg_replace('/\&?page=\d*/','',$this->url);
	        	return $trmpurl.'&page=';
			}else{
				return $this->url.'&page=';
			}
		}
    }
    //列表序号，如第一页123.第二页456,注意：这个序号只是每个页面的第一个的序号
    private function getSerialNumber(){
    	return $this->listRows * ($this->nowPage - 1)+1;
    }
    //获取limit
    private function getLimit(){
    	$s = ($this->nowPage-1)*$this->listRows<0?0:($this->nowPage-1)*$this->listRows;
    	$limit = array($s,$this->listRows);
    	return $limit;
    }
    //显示分页条
	public function show(){
		//选中的a标签style
		$sel_a_style = 'style="display:block;float:left;margin-right:10px;padding:2px 12px;height:24px;border:1px #cccccc solid;background:#87C0F1;text-decoration:none;color:#808080;font-size:12px;line-height:24px;"';
		//a标签style
        $a_style = 'style="display:block;float:left;margin-right:10px;padding:2px 12px;height:24px;border:1px #cccccc solid;background:#fff;text-decoration:none;color:#808080;font-size:12px;line-height:24px;"';
        //9标签style
        $p_style = 'style="margin:0;float:left;padding:4px 0px;font-size:12px;height:24px;line-height:24px;color:#666;border:1px #ccc solid;background:none;margin-right:0px;border-style:none;"';
        //input标签style
        $input_style = 'style="display:block;float:left;margin-right:10px;padding:2px 12px;height:24px;border:1px #cccccc solid;background:#fff;text-decoration:none;color:#808080;font-size:12px;line-height:24px;width:32px;text-align:center"';
        //input标签js
        $input_js = 'onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,\'\')}else{this.value=this.value.replace(/\D/g,\'\')}"onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,\'0\')}else{this.value=this.value.replace(/\D/g,\'\')}"';
        $a_style_goPage = 'style="display:block;float:left;margin-right:10px;"';
		//首页
		$firstPageStr = '<a '.$a_style.' href="'.$this->page_replace(1).'">首页</a>';
		//末页
		$endPageStr = '<a '.$a_style.' href="'.$this->page_replace($this->endPage).'">末页</a>';
		//上一页
		$prePageStr = '<a '.$a_style.' href="'.$this->page_replace($this->prePage).'">上一页</a>';
		//下一页
		$nextPageStr = '<a '.$a_style.' href="'.$this->page_replace($this->nextPage).'">下一页</a>';
		//跳转到指定页，input框
		$goPage_input = '<input '.$input_style.' '.$input_js.' type="text" id="go_page_input" maxlength="5" value="'.$this->nowPage.'" />';
		//获取input的值
		$input_val = '';
		//跳转到指定页，按钮
		$goPage_button = '<a href="javascript:void(0);" onclick="window.location.href=\''.$this->goto_url.'\'+document.getElementById(\'go_page_input\').value;" '.$a_style.'>GO</a>';
		//页码
		$pageStr = '';
		for ($i = $this->start; $i <= $this->end; $i++) {
            if ($i == $this->nowPage) {
                $pageStr .= '<a '.$sel_a_style.' href="javascript:void(0);">'.$i.'</a>';
            } else {
                $pageStr .= '<a '.$a_style.' href="'.$this->page_replace($i).'">'.$i.'</a>';
            }
        }
        return '<div style="height:40px;padding:20px 0px;">'.$firstPageStr.$prePageStr.$pageStr.$nextPageStr.$endPageStr.$goPage_input.$goPage_button.'<p '.$p_style.'>共<b>'.$this->pageCount.'</b>页<b>'.$this->totle.'</b>条数据</p></div>';
	}
}