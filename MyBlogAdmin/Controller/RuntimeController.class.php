<?php
//日志文件
class RuntimeController extends __Controller{
    protected function _init(){

    }
    /**
     * 查看
     */
    public function show(){
        $keyWord = trim($_GET['keyWord']);
        if(empty($_GET['path'])){
            //打开Runtime文件夹
            $path = scandir(APP_PATH.'/Runtime');
            $showArr = array();
            $num = 0;
            foreach ($path as $key => $value){
                //这里Runtime是根目录，所以.和..
                if($value !== '.' && $value !== '..'){
                    if (is_dir(APP_PATH.'/Runtime/'.$value)){
                        $showArr[$num]['icon'] = '<i class="fa fa-5x fa-fw fa-folder"></i>';
                    }else{
                        $showArr[$num]['icon'] = '<i class="fa fa-5x fa-fw fa-file-text-o"></i>';
                    }
                    if (strlen($value)>17){
                        $showArr[$num]['name'] = substr($value,0,17).'...';
                    }else{
                        $showArr[$num]['name'] = $value;
                    }
                    $showArr[$num]['trueName'] = $value;
                    $showArr[$num]['path'] = $value;
                    $num++;
                }
            }
            $newShowArr = array();
            if ($keyWord == ''){
                $newShowArr = $showArr;
            }else{
                foreach ($showArr as $key => $value){
                    if (stripos($value['trueName'],$keyWord) !== false){
                        $newShowArr[] = $value;
                    }
                }
            }
            $this->assign('isfile','0');
            $this->assign('showArr',$newShowArr);
        }else{
            $getPath = trim($_GET['path']);
            $newGetPath = str_replace('---','/',$getPath);
            if(is_dir(APP_PATH.'/Runtime/'.$newGetPath)){
                $path = scandir(APP_PATH.'/Runtime/'.$newGetPath);
                $showArr = array();
                $num = 0;
                foreach ($path as $key => $value){
                    //这里Runtime是根目录，所以.和..
                    if($value !== '.' && $value !== '..'){
                        if (is_dir(APP_PATH.'/Runtime/'.$newGetPath.'/'.$value)){
                            $showArr[$num]['icon'] = '<i class="fa fa-5x fa-fw fa-folder"></i>';
                        }else{
                            $showArr[$num]['icon'] = '<i class="fa fa-5x fa-fw fa-file-text-o"></i>';
                        }
                        if (strlen($value)>17){
                            $showArr[$num]['name'] = substr($value,0,17).'...';
                        }else{
                            $showArr[$num]['name'] = $value;
                        }
                        $showArr[$num]['trueName'] = $value;
                        $showArr[$num]['path'] = $getPath.'---'.$value;
                        $num++;
                    }
                }
                $newShowArr = array();
                if ($keyWord == ''){
                    $newShowArr = $showArr;
                }else{
                    foreach ($showArr as $key => $value){
                        if (stripos($value['trueName'],$keyWord) !== false){
                            $newShowArr[] = $value;
                        }
                    }
                }
                $this->assign('isfile','0');
                $this->assign('showArr',$newShowArr);
            }else{
                if(is_file(APP_PATH.'/Runtime/'.$newGetPath)){
                    $str = file_get_contents(APP_PATH.'/Runtime/'.$newGetPath);//将整个文件内容读入到一个字符串中
                    $str = str_replace("\r\n","<br/>",$str);
                    $this->assign('asdstr',array_iconv($str));
                    $this->assign('isfile','1');
                }
            }
            $arr = explode('/', $newGetPath);
            //定义新数组
            $newArr = array();
            //定义中间数组
            $tem = array();
            foreach($arr as $key => $value){
                //把$value 一个一个装进中间数组
                $tem[] = $value;
                //把中间数组转换成字符串,并放进新数组
                $str = implode ('---', $tem);
                $newArr[$key]['str'] = $str;
                $newArr[$key]['name'] = $value;
            }
            // p($newArr);
            $this->assign('newArr',$newArr);
            $this->assign('newGetPath',$newGetPath);
        }
        $this->display();
    }

    /**
     * ajax执行删除
     */
    public function ajaxDel(){
        $getPath = $_POST['path'];
        if($getPath){
            $newGetPath = str_replace('---','/',$getPath);
            $getPathArr = explode(',',$newGetPath);
            foreach ($getPathArr as $key => $value) {
                delDirAndFile(APP_PATH.'/Runtime/'.$value,true);
            }
        }
        exit(json_encode(array('echo'=>'删除成功','state'=>true)));
    }
}