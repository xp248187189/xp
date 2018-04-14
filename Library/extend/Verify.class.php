<?php
//验证码类
class Verify {
    private $charset = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';//随机因子
    private $code;//验证码
    private $codelen;//验证码长度
    private $width;//宽度
    private $height;//高度
    private $img;//图形资源句柄
    private $font;//指定的字体
    private $fontsize;//指定字体大小
    private $crypt;//加密密匙
    
    /**
     * 构造方法
     * @param integer $codelen  验证码长度
     * @param integer $width    宽度
     * @param integer $height   高度
     * @param integer $fontsize 指定字体大小
     * @param string  $font     指定的字体
     */
    public function __construct($codelen=4,$width=150,$height=50,$fontsize=25,$font='') {
        $this->crypt = getConfig('key');
    	$this->codelen = $codelen;
    	$this->width = $width;
    	$this->height = $height;
    	$this->fontsize = $fontsize;
    	$this->font = $font;
		if(empty($this->font)){
			//未指定字体，使用随机字体
            $dir = dir(APP_PATH."/Library/extend/font/");
            $ttfs = array();		
            while (false !== ($file = $dir->read())) {
                if($file[0] != '.' && substr($file, -4) == '.ttf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            //array_rand(),返回随机键名
            $this->font = APP_PATH."/Library/extend/font/".$ttfs[array_rand($ttfs)];//注意字体路径要写对，否则显示不了图片
        }
    }
    //生成随机码
    private function createCode() {
        //strlen($str),获取字符串长度，这里要减1，因为是从零开始
        $_len = strlen($this->charset)-1;
        for ($i = 0; $i < $this->codelen; $i++) {
            //mt_rand($min,$max) 使用 Mersenne Twister 算法返回随机整数。
            $this->code .= $this->charset[mt_rand(0,$_len)];
        }
    }
    //生成背景
    private function createBg() {
        //创建一块画布
        $this->img = imagecreatetruecolor($this->width, $this->height);
        //imagecolorallocate()为一幅图像分配颜色
        $color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
        //画一矩形并填充 imagefilledrectangle(资源,x1,y1,x2,y2,颜色)，x1,y1为左上角那个点，x2,y2为右下角那个点
        imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
    }
    //生成文字
    private function createFont() {
        $_x = $this->width / $this->codelen;
        for ($i = 0; $i < $this->codelen; $i++) {
            //imagecolorallocate()为一幅图像分配颜色
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            //imagettftext(),用 $this->font 字体向图像写入文本
            //array imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
            imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$color,$this->font,$this->code[$i]);
        }
    }
    //生成线条、雪花
    private function createLine() {
        //线条
        for ($i=0;$i<6;$i++) {
            //imagecolorallocate()为一幅图像分配颜色
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            //imageline() 画一条线段
            //bool imageline ( resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color )
            //imageline() 用 color 颜色在图像 image 中从坐标 x1，y1 到 x2，y2（图像左上角为 0, 0）画一条线段。
            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }
        //雪花
        for ($i=0;$i<100;$i++) {
            //imagecolorallocate()为一幅图像分配颜色
            $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            //imagestring() 水平地画一行字符串
            //bool imagestring ( resource $image , int $font , int $x , int $y , string $s , int $col )
            //imagestring() 用 col 颜色将字符串 s 画到 image 所代表的图像的 x，y 坐标处（这是字符串左上角坐标，整幅图像的左上角为 0，0）。如果 font 是 1，2，3，4 或 5，则使用内置字体。
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }
    //保存session
    private function saveSession(){
        $_SESSION['verify'] = md5(strtolower($this->code).$this->crypt);
    }
    //输出
    private function outPut() {
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);		
        header('Pragma: no-cache');
        header("content-type: image/png");
        imagepng($this->img);
        imagedestroy($this->img);
    }
    //对外生成
    public function show() {
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->saveSession();
        $this->outPut();
    }
    //验证
    public function check($code) {
        if (empty($code)) {
            return false;
        }
        if (md5(strtolower($code).$this->crypt)==$_SESSION['verify']) {
            return true;
        }
        return false;
    }
}