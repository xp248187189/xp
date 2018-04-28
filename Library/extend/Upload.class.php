<?php
//文件上传类
class Upload {
    private $uploadMaxSize;//最大上传文件大小
    private $uploadPath;//文件保存路径
    private $uploadType;//上传文件的类型
    private $uploadFile = array();//上传的文件（可以是多个）
    private $skipError;//是否跳过错误,默认false，不跳过
    private $return = array('status'=>false,'echo'=>'');
    /**
     * 构造函数
     * @param string  $folder        保存的文件夹名字，固定是upload下面
     * @param array   $uploadType    上传文件的类型
     * @param boolean $skipError     是否跳过错误,默认false，不跳过
     * @param integer $uploadMaxSize 最大上传文件大小
     */
    public function __construct($folder='',array $uploadType=array(),$skipError=false,$uploadMaxSize=2048000){
        $this->skipError = $skipError;
        $this->uploadMaxSize = $uploadMaxSize;
        $this->uploadType = $uploadType;
        if (empty($folder)) {
            $this->uploadPath = APP_PATH.'/Upload/'.date('Y-m-d').'/';
        }else{
            $this->uploadPath = APP_PATH.'/Upload/'.$folder.'/'.date('Y-m-d').'/';
        }
        if (empty($this->uploadType)) {
            $this->uploadType = array('chm', 'pdf', 'zip', 'rar', 'tar', 'gz', 'gif', 'jpg', 'jpeg', 'png', 'doc', 'xls');
        }
    }
    /**
     * 错误输出
     */
    private function getError($number,$fileName=''){
        switch ($number) {
            case '1':
                $this->return['echo'] = $fileName.' 文件超过了php.ini中upload_max_filesize选项限制的值';
                break;
            case '2':
                $this->return['echo'] = $fileName.' 文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case '3':
                $this->return['echo'] = $fileName.' 文件只有部分被上传';
                break;
            case '4':
                $this->return['echo'] = '没有文件被上传';
                break;
            case '5':
                $this->return['echo'] = $fileName.' 的文件大小为0';
                break;
            case '-1':
                $this->return['echo'] = $fileName.' 是未允许类型';
                break;
            case '-2':
                $this->return['echo'] = $fileName.' 文件过大,上传的文件不能超过'.$this->uploadMaxSize.'个字节';
                break;
            case '-3':
                $this->return['echo'] = '建立存放上传文件目录失败，请重新指定上传目录';
                break;
            case '-4':
                $this->return['echo'] = $fileName.' 上传失败';
                break;
            default:
                $this->return['echo'] = '未知错误';
                break;
        }
    }
    /**
     * 把 $_FILES 数组装进一个新数组（为了让 input的name 是 普通形式和数组形式 混用 也能正常的上传）
     * 并检查错误
     */
    private function intoUploadFile(){
        foreach ($_FILES as $key => $value) {
            if (is_array($value['name'])) {
                foreach ($value['name'] as $k => $v) {
                    //后缀
                    $aryStr = explode(".", $v);
                    $suffix = strtolower($aryStr[count($aryStr)-1]);
                    //检查文件类型
                    $t_return = $this->checkFileType($suffix);
                    if($t_return !== true){
                        //判断是否跳过错误
                        if($this->skipError){
                            continue;
                        }else{
                            $this->getError($t_return,$v);
                            break 2;
                        }
                    }
                    //检查文件大小
                    $s_Return = $this->checkFileSize($value['size'][$k]);
                    if($s_Return !== true){
                        //判断是否跳过错误
                        if($this->skipError){
                            continue;
                        }else{
                            $this->getError($s_Return,$v);
                            break 2;
                        }
                    }
                    //检查文件错误
                    $e_Return = $this->checkFileError($value['error'][$k]);
                    if($e_Return !== true){
                        //判断是否跳过错误
                        if($this->skipError){
                            continue;
                        }else{
                            $this->getError($e_Return,$v);
                            break 2;
                        }
                    }
                    $tmp_file['field'] = $key;
                    $tmp_file['name'] = $v;
                    $tmp_file['type'] = $value['type'][$k];
                    $tmp_file['tmp_name'] = $value['tmp_name'][$k];
                    $tmp_file['error'] = $value['error'][$k];
                    $tmp_file['size'] = $value['size'][$k];
                    $tmp_file['suffix'] = $suffix;
                    
                    //装进数组
                    $this->uploadFile[] = $tmp_file;
                }
            }else{
                //后缀
                $aryStr = explode(".", $value['name']);
                $suffix = strtolower($aryStr[count($aryStr)-1]);
                //检查文件类型
                $t_return = $this->checkFileType($suffix);
                if($t_return !== true){
                    //判断是否跳过错误
                    if($this->skipError){
                        continue;
                    }else{
                        $this->getError($t_return,$value['name']);
                        break;
                    }
                }
                //检查文件大小
                $s_Return = $this->checkFileSize($value['size']);
                if($s_Return !== true){
                    //判断是否跳过错误
                    if($this->skipError){
                        continue;
                    }else{
                        $this->getError($s_Return,$value['name']);
                        break;
                    }
                }
                //检查文件错误
                $e_Return = $this->checkFileError($value['error']);
                if($e_Return !== true){
                    //判断是否跳过错误
                    if($this->skipError){
                        continue;
                    }else{
                        $this->getError($e_Return,$value['name']);
                        break;
                    }
                }
                $tmp_file['field'] = $key;
                $tmp_file['name'] = $value['name'];
                $tmp_file['type'] = $value['type'];
                $tmp_file['tmp_name'] = $value['tmp_name'];
                $tmp_file['error'] = $value['error'];
                $tmp_file['size'] = $value['size'];
                $tmp_file['suffix'] = $suffix;
                //装进数组
                $this->uploadFile[] = $tmp_file;
            }
        }
    }
    /**
     * 检查文件类型
     */
    private function checkFileType($suffix){
        if (empty($suffix)) {
            return 4;//后缀为空，表示没有文件上传
        }
        if (!in_array(strtolower($suffix), $this->uploadType)) {
             return -1;
        }
        return true;
    }
    /**
     * 检查文件大小
     */
    private function checkFileSize($fileSize){
        if ($fileSize > $this->uploadMaxSize) {
            return -2;
        }
        return true;
    }
    /**
     * 检查文件错误
     */
    private function checkFileError($fileErroe){
        if ($fileErroe===0) {
            return true;
        }
        return $fileErroe;
    }
    /**
     * 检查目录
     */
    private function checkDir(){
        //先判断路径是否存在，不存在则创建
        if (!is_dir($this->uploadPath)) {
            //创建目录
            if(!mkdir($this->uploadPath,0777,true)){
                return -3;
            }else{
                return true;
            }
        }
        return true;
    }
    /**
     * 实现上传
     */
    public function upload(){
        //检查目录
        $d_Return = $this->checkDir();
        if($d_Return !==true){
            $this->getError($d_Return);
            return $this->return;
        }
        //把$_FILES放进新数组并检查错误
        $this->intoUploadFile();
        if (!empty($this->return['echo'])) {
            return $this->return;
        }
        //判断有无文件
        if(empty($this->uploadFile)){
            $this->return['echo'] = '无文件上传';
        }
        //开始上传
        foreach ($this->uploadFile as $key => $value) {
            $newFile = $this->uploadPath.date('YmdHis').rand(100,999).'.'.$value['suffix'];
            if (move_uploaded_file($value['tmp_name'],$newFile)) {
                $this->return['status'] = true;
                $this->return['echo'] = '上传成功';
                $this->return['newFile'][] = array($value['name'],substr($newFile,strlen(APP_PATH)),$value['field']);//把APP_PATH去掉
            }else{
                if(!$this->skipError){
                    $this->return['status'] = false;
                    $this->getError(-4,$value['name']);
                    break;
                }
            }
        }
        if (!empty($this->return['newFile']) && count($this->return['newFile'])==1) {
            $this->return['newFile'] = $this->return['newFile'][0];
        }
        return $this->return;
    }
}