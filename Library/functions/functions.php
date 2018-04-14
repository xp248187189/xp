<?php 
/**
 * print_r打印数组
 * @param  array   $array    数组
 */
function p($array=array()){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}
/**
 * var_dump打印数组
 * @param  array   $array    数组
 */
function dump($array=array()){
	echo '<pre>';
	var_dump($array);
	echo '</pre>';
}
/**
 * 获取配置信息
 * @param  mixed $configName 配置名字
 * @return mixed             配置信息
 */
function getConfig($configName){
    if (empty($configName)) {
        return null;
    }
    $config = include APP_PATH.'/Library/config/config.php';
    if (is_array($configName)) {
        $return = array();
        foreach ($configName as $key => $value) {
            if(!isset($config[$value])){
                $return[$value] = null;
            }else{
                $return[$value] = $config[$value];
            }
        }
        return $return;
    }
    return isset($config[$configName])?$config[$configName]:null;
}
/**
 * 错误页面（系统自动调用，手动调用的话需要传如参数 true）
 * @param  boolean $mustDisplay 是否强制显示
 * @return string               html页面
 */
function showError($mustDisplay=false){
    //把请求方式设置为常量
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
    defined('__HTTPTYPE__') or define('__HTTPTYPE__',$http_type);
    //把网站首页设置为常量
    if(substr($_SERVER['DOCUMENT_ROOT'],-strlen(APP_FOLDER),strlen(APP_FOLDER))==APP_FOLDER || substr($_SERVER['DOCUMENT_ROOT'],-strlen(APP_FOLDER)-1,strlen(APP_FOLDER))==APP_FOLDER){
        //这里-strlen(APP_FOLDER)，-strlen(APP_FOLDER)-1是为了Apache设置目录时，最后带/和不带/都能识别
        defined('__HOST__') or define('__HOST__',$_SERVER['HTTP_HOST']);
    }else{
        defined('__HOST__') or define('__HOST__',$_SERVER['HTTP_HOST'].'/'.APP_FOLDER);
    }
	$html =  '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link rel="icon" href="'.__COMMON__.'/img/favicon.ico" type="image/x-icon" /><link rel="shortcut icon" href="'.__COMMON__.'/img/favicon.ico" type="image/x-icon" /><title>这个.. 页面出错了！！！</title><style type="text/css">body{ margin:0; padding:0; background:#efefef; font-family:Georgia, Times, Verdana, Geneva, Arial, Helvetica, sans-serif; }div#mother{ margin:0 auto; width:943px; height:572px; position:relative; }div#errorBox{ width:943px; height:572px; margin:auto; }div#errorText{ color:#39351e; padding:150px 0 0 400px }div#errorText p{ width:310px; font-size:14px; line-height:26px; }h1{ font-size:40px; margin-bottom:35px; }a{text-decoration:none;color:#FFB5B5}</style></head><body><div id="mother"><div id="errorBox"><div id="errorText"><h1>Sorry..页面出错了！</h1><p>似乎你所寻找的网页已移动或丢失了。<p>或者也许你只是键入错误了一些东西。</p>请不要担心，这没事。如果该资源对你很重要，请与<a target="_blank" href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=CTs9MTgxPjgxMEl4eCdqZmQ" style="text-decoration:none;">管理员</a>联系。</p><p>火星不太安全，我可以免费送你回地球</p><h1><a href="javascript:history.go(-1);">返回上一页</a> <a href="'.__HTTPTYPE__.'://'.__HOST__.'">返回首页</a></h1></div></div></div></body></html>';
	//判断是否强制显示
	if ($mustDisplay===true) {
		//强制显示，直接输出页面
        header("HTTP/1.1 404 Not Found");  
        header("Status: 404 Not Found");  
		exit($html);
	}else{
		//获取错误
		$error = error_get_last();
		//判断是否有错误(E_NOTICE错误除外)
		if($error['type']>0 && $error['type']!=8){
			/************************记录日志**********************************/
            if(getConfig('php_error_log')){
                //文件
                $error_log_file = APP_PATH.'/Runtime/logs/php/'.date('Y-m-d').'_error.log';
                if(!is_file($error_log_file)){
                    $file = fopen($error_log_file,'w');
                    fclose($file);
                }
                $log = 'type: '.setErrotType($error['type']).' message: '.$error['message'].' file: '.$error['file'].' line: '.$error['line'];
                error_log($log,0);
            }
			/************************判断是否是调试模式**********************************/
			if (getConfig('deBug')) {
				//调试模式，打印错误信息
                $nbsp1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $nbsp2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $echo  = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link rel="icon" href="'.__COMMON__.'/img/favicon.ico" type="image/x-icon" /><link rel="shortcut icon" href="'.__COMMON__.'/img/favicon.ico" type="image/x-icon" /><title>这个.. 页面出错了！！！</title></head><body>';
                $echo .= '<div style="margin-top:50px;margin-left:50px;">';
				$echo .= '<h1>(●′ω`●) 这个.. 页面出错了！！！</h1>';
                $echo .= '<h3>'.$nbsp1.'错误类型</h3>'.$nbsp2.setErrotType($error['type']);
                $echo .= '<h3>'.$nbsp1.'错误信息</h3>'.$nbsp2.str_replace('#','<br/>'.$nbsp2.'#',array_iconv($error['message']));
                $echo .= '<h3>'.$nbsp1.'错误位置</h3>'.$nbsp2.$error['file'];
                $echo .= '<h3>'.$nbsp1.'错误行数</h3>'.$nbsp2.$error['line'];
                $echo .= '</div></body></html>';
                exit($echo);
			}else{
				//输出页面
                header("HTTP/1.1 404 Not Found");  
                header("Status: 404 Not Found");  
				exit($html);
			}
		}
	}
}
/**
 * 定义错误类型
 * @param [type] $type 类型值
 */
function setErrotType($type){
	switch($type){
        case 1:
            $return = 'E_ERROR';
            //致命的运行时错误。这类错误一般是不可恢复的情况，例如内存分配导致的问题。后果是导致脚本终止不再继续运行。
            break;
        case 2:
            $return = 'E_WARNING';
            //运行时警告 (非致命错误)。仅给出提示信息，但是脚本不会终止运行。
            break;
        case 4:
            $return = 'E_PARSE';
            //编译时语法解析错误。解析错误仅仅由分析器产生。
            break;
        case 8:
            $return = 'E_NOTICE';
            //运行时通知。表示脚本遇到可能会表现为错误的情况，但是在可以正常运行的脚本里面也可能会有类似的通知。
            break;
        case 16:
            $return = 'E_CORE_ERROR';
            //在PHP初始化启动过程中发生的致命错误。该错误类似 E_ERROR，但是是由PHP引擎核心产生的。
            break;
        case 32:
            $return = 'E_CORE_WARNING';
            //PHP初始化启动过程中发生的警告 (非致命错误) 。类似 E_WARNING，但是是由PHP引擎核心产生的。
            break;
        case 64:
            $return = 'E_COMPILE_ERROR';
            //致命编译时错误。类似E_ERROR, 但是是由Zend脚本引擎产生的。
            break;
        case 128:
            $return = 'E_COMPILE_WARNING';
            //编译时警告 (非致命错误)。类似 E_WARNING，但是是由Zend脚本引擎产生的。
            break;
        case 256:
            $return = 'E_USER_ERROR';
            //用户产生的错误信息。类似 E_ERROR, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的。
            break;
        case 512:
            $return = 'E_USER_WARNING';
            //用户产生的警告信息。类似 E_WARNING, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的。
            break;
        case 1024:
            $return = 'E_USER_NOTICE';
            //(integer)	用户产生的通知信息。类似 E_NOTICE, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的。
            break;
        case 2048:
            $return = 'E_STRICT';
            //启用 PHP 对代码的修改建议，以确保代码具有最佳的互操作性和向前兼容性。
            break;
        case 4096:
            $return = 'E_RECOVERABLE_ERROR';
            //可被捕捉的致命错误。 它表示发生了一个可能非常危险的错误，但是还没有导致PHP引擎处于不稳定的状态。 如果该错误没有被用户自定义句柄捕获 (参见 set_error_handler())，将成为一个 E_ERROR　从而脚本会终止运行。
            break;
        case 8192:
            $return = 'E_DEPRECATED';
            //运行时通知。启用后将会对在未来版本中可能无法正常工作的代码给出警告。
            break;
        case 16384:
            $return = 'E_USER_DEPRECATED';
            //用户产少的警告信息。 类似 E_DEPRECATED, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的。
            break;
        case 30719:
            $return = 'E_ALL';
            //E_STRICT出外的所有错误和警告信息。
            break;
        default:
            $return = '未知类型';
            break;
    }
    return $return;
}
/**
 * 删除目录及目录下所有文件或删除指定文件
 * @param str $path   待删除目录路径
 * @param bool $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 * @return bool 返回删除状态
 */
function delDirAndFile($path, $delDir = false) {
    if (is_dir($path)){
        $handle = opendir($path);
    }else{
        $handle = '';
    }
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return false;
        }
    }
}
/**
 * 对数据进行编码转换
 * @param array/string  $data      数组/字符串
 * @param string        $output    转换后的编码
 */
function array_iconv($data,$output = 'utf-8') {
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
    $encoded = mb_detect_encoding($data, $encode_arr);//自动判断编码
    if (!is_array($data)) {
        return mb_convert_encoding($data, $output, $encoded);
    }
    else {
        foreach ($data as $key=>$val) {
            if(is_array($val)) {
                $data[$key] = array_iconv($val, $input, $output);
            } else {
                $data[$key] = mb_convert_encoding($data, $output, $encoded);
            }
        }
        return $data;
    }
}
/**
 * 返回uel地址 - 视图中使用
 * @param  string $url url地址
 * @return string      url地址
 */
function u($url){
    if(getConfig('urlType')==1){
        return __ROOT__.'/'.__MODULE__.'/'.ltrim($url,'/');
    }else{
        $url = ltrim($url,'?');
        $url = ltrim($url,'&');
        return __ROOT__.'?m='.__MODULE__.'&'.$url;
    }
}
/**
 * 无限极分类树状图
 * @param  array $array  二维数组，邻接列表方式组织的数据
 * @param  string $id    数组中作为主键的下标或关联键名
 * @param  string $pid   数组中作为父键的下标或关联键名
 * @return array         多维数组
 */
function genTree($array,$id='id',$pid='pid') {
    $num=1;
    $newArray = array();
    //把二维数组的下标改成从1开始递增
    foreach ($array as $key => $value) {
        $newArray[$num] = $value;
        $num++;
    }
    foreach ($newArray as $value){
        $newArray[$value[$pid]]['son'][] = &$newArray[$value[$id]];
    }
    return isset($newArray[0]['son']) ? $newArray[0]['son'] : array();
}
/**
 * 获取数组维度
 * @param  array $arr 数组
 * @return number     数组维度
 */
function getArrayLevel($arr){
    $al = array(0);
    function aL($arr,&$al,$level=0){
        if(is_array($arr)){
            $level++;
            $al[] = $level;
            foreach($arr as $v){
                aL($v,$al,$level);
            }
        }
    }
    aL($arr,$al);
    return max($al);
}
/**
 * 加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string 
 */
function encrypt($data, $key, $expire = 0) {
   $key  = md5($key);
   $data = base64_encode($data);
   $x    = 0;
   $len  = strlen($data);
   $l    = strlen($key);
   $char =  '';
   for ($i = 0; $i < $len; $i++) {
      if ($x == $l) $x=0;
      $char  .= substr($key, $x, 1);
      $x++;
   }
   $str = sprintf('%010d', $expire ? $expire + time() : 0);
   for ($i = 0; $i < $len; $i++) {
      $str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
   }
   $str = base64_encode($str);
   $str = str_replace(array('=','+','/'),array('O0O0O','o000o','oo00o'),$str);
   return $str;
}
/**
 * 解密方法
 * @param string $data 要解密的字符串 （必须是encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string 
 */
function decrypt($data, $key){
   $data = str_replace(array('O0O0O','o000o','oo00o'), array('=','+','/'),$data); 
   $key    = md5($key);
   $x      = 0;
   $data   = base64_decode($data);
   $expire = substr($data, 0, 10);
   $data   = substr($data, 10);
   if($expire > 0 && $expire < time()) {
      return null;
   }
   $len  = strlen($data);
   $l    = strlen($key);
   $char = $str = '';
   for ($i = 0; $i < $len; $i++) {
      if ($x == $l) $x = 0;
      $char  .= substr($key, $x, 1);
      $x++;
   }
   for ($i = 0; $i < $len; $i++) {
      if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
         $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
      }else{
         $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
      }
   }
   return base64_decode($str);
}
/**
 * 判断多维数据是否存在某个值
 * @param  string $value 要判断的值
 * @param  array $array 多维数组
 * @return boolean
 */
function deep_in_array($value, $array) {   
    foreach($array as $item) {   
        if(!is_array($item)) {   
            if ($item == $value) {  
                return true;  
            } else {  
                continue;   
            }  
        }   
            
        if(in_array($value, $item)) {  
            return true;      
        } else if(deep_in_array($value, $item)) {  
            return true;      
        }  
    }   
    return false;   
}
/**
 * 判断是否是ajax请求
 * @return boolean
 */
function isAjax(){
    if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
        return true;
    }else{ 
        return false;
    };
}
/**
 * 二维数组分组
 * @param  array $arr 二维数组
 * @param  string $key 要分组的键值
 * @return array      二维数组
 */
function array_group_by($arr, $key){
    $grouped = array();
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge(array($value), array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return $grouped;
}
/**
 * 显示编辑器
 * @param  string  $name   name和id值
 * @param  string  $value  默认值
 * @param  string  $width  编辑器宽度
 * @param  integer $height 编辑器高度
 * @return string          输出编辑器
 */
function showEditor($name,$value='',$toolBar='default',$width=666,$height=180){
    $items =array(
        'default'=>"[
                'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
                'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
                'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'emoticons', 'baidumap',
                'flash', 'media', 'insertfile', 'table', 'hr', 'image', 'multiimage', 'pagebreak',
                'anchor', 'link', 'unlink', '|', 'about'
            ]",
        'simple'=>"[
                'emoticons', 'link', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
                'insertunorderedlist', '|', 'fontname', 'fontsize'
            ]",
        'simpleForSource'=>"[
                'source', 'emoticons', 'link', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
                'insertunorderedlist', '|', 'fontname', 'fontsize'
            ]"
    );
    if ($toolBar=='default') {
        $minWidth = '666';
    }else{
        $minWidth = '430';
    }
    $str = '<link rel="stylesheet" href="'.__ROOT__.'/Library/extend/kindeditor/themes/default/default.css" />';
    $str.= '<script type="text/javascript" src="'.__ROOT__.'/Library/extend/kindeditor/kindeditor-all.js"></script>';
    $str.= '<script type="text/javascript" src="'.__ROOT__.'/Library/extend/kindeditor/lang/zh-CN.js"></script>';
    $str.= '<script type="text/javascript">';
    $str.= 'KindEditor.ready(function(K) {';
    $str.= '    var '.$name.' = K.create("textarea[name='.$name.']", {';
    $str.= '        items : '.$items[$toolBar].',';
    $str.= '        resizeType : "1",';/**0[不能拖动]/1[只能上下拖动]2[自由拖动]**/
    $str.= '        minWidth : '.$minWidth.',';
    $str.= '        minHeight : "150",';
    $str.= '        width : "'.(is_numeric($width)?$width.'px':$width).'",';
    $str.= '        height : "'.(is_numeric($height)?$height.'px':$height).'",';
    $str.= '        uploadJson : "'.__ROOT__.'/Library/extend/kindeditor/php/upload_json.php",';
    $str.= '        fileManagerJson : "'.__ROOT__.'/Library/extend/kindeditor/php/file_manager_json.php",';
    $str.= '        allowFileManager : true,';
    $str.= '        afterBlur: function(){this.sync();}';
    $str.= '    });';
    $str.= '});';
    $str.= '</script>';
    $str.= '<textarea name="'.$name.'" id="'.$name.'" style="width:'.(is_numeric($width)?$width.'px':$width).';height:'.(is_numeric($height)?$height.'px':$height).';visibility:hidden;">';
    $str.= $value;
    $str.= '</textarea>';
    echo $str;
}
/**
 * 数组简单过滤
 * @param  array $array 数组
 * @return array        简单过滤后的数组
 */
function arrayFilter($array=array()){
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            arrayFilter($v);
        }else{
            $array[$k] = addslashes(htmlentities($v,ENT_QUOTES));
        }
    }
    return $array;
}
/**
 * 获取客户端ip地址
 * @return string 客户端ip地址
 */
function getIP(){
    if (getenv('HTTP_CLIENT_IP')) { 
        $ip = getenv('HTTP_CLIENT_IP'); 
    } 
    elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
        $ip = getenv('HTTP_X_FORWARDED_FOR'); 
    } 
    elseif (getenv('HTTP_X_FORWARDED')) { 
        $ip = getenv('HTTP_X_FORWARDED'); 
    } 
    elseif (getenv('HTTP_FORWARDED_FOR')) { 
        $ip = getenv('HTTP_FORWARDED_FOR'); 
    } 
    elseif (getenv('HTTP_FORWARDED')) { 
        $ip = getenv('HTTP_FORWARDED'); 
    } 
    else { 
        $ip = $_SERVER['REMOTE_ADDR']; 
    } 
    return $ip; 
}
/**
 * 根据ip地址获取地理信息
 * @param  string $ip ip地址
 * @return array      地理信息
 */
function getIpLookup($ip = ''){    
    if(empty($ip)){    
        return false;
    }    
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);    
    if(empty($res)){
        return false;
    }
    $jsonMatches = array();    
    preg_match('#\{.+?\}#', $res, $jsonMatches);    
    if(!isset($jsonMatches[0])){
        return false;
    }    
    $json = json_decode($jsonMatches[0],true);    
    if(isset($json['ret']) && $json['ret'] == 1){    
        $json['ip'] = $ip;    
        unset($json['ret']);    
    }else{    
        return false;    
    }    
    return $json;    
} 
/**
 * 二维数组根据字段进行排序
 * @param array  $array 需要排序的数组
 * @param string $field 排序的字段
 * @param string $sort  排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
 * @return array        排好序的二维数组
 */
function arraySequence($array, $field, $sort = 'SORT_DESC'){
    $arrSort = array();
    foreach ($array as $uniqid => $row) {
        foreach ($row as $key => $value) {
            $arrSort[$key][$uniqid] = $value;
        }
    }
    array_multisort($arrSort[$field], constant($sort), $array);
    return $array;
}   
