<?php 
/**
* 框架核心类
*/
class Core{
	private $modules;//模块
	private $controller;//控制器
	private $action;//方法
	//运行程序
	public function run(){
		//加载核心函数库文件
		$this->load_core_function();
		//注册一个会在php中止时执行的函数，showError(),输出错误页面
        //就算ajax交互，程序出错也会返回错误页面，会进入ajax的error回调函数，在回调函数直接自定义输出即可
        register_shutdown_function('showError');
        //数组简单过滤
        $this->filter();
		//session配置
		$this->setSession();
		//php错误配置
		$this->setPhpError();
		//注册给定的函数作为 __autoload 的实现,就是根据下面要实例化的类来加载对应的类
		spl_autoload_register(array($this,'loadClass'));
		//路由处理
		$this->route();
		//模块和域名定义常量
		$this->defined_module_url();
		//判断域名是否带www
		$this->hostIsWww();
		//加载公用和模块函数文件
		$this->load_function();
		//删除过期缓存文件
		$this->delOverdueFile();
		//执行
		$this->execute();
	}
	/**
	 * 加载核心函数库
	 */
	public function load_core_function(){
		include APP_PATH.'/Library/functions/functions.php';
	}
	/**
	 * 路由获取控制器和方法
	 * array_shift()删除数组中的第一个元素，并返回被删除元素的值，这里就不获取返回的值了
	 */
	private function route(){
		if (!isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO']) ){
			$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
		}
		//判断url地址格式
		if (getConfig('urlType')==1) {
			//pathinfo类型
			if (empty($_SERVER['PATH_INFO'])) {
				// header('location:'.__ROOT__.'/'.getConfig('url_modules').'/'.getConfig('url_controller').'/'.getConfig('url_action'));
				showError(true);
				exit();
			}
			//当前url,并去掉首尾的'/'
			$url = trim($_SERVER['PATH_INFO'],'/');
			//去掉前面的项目文件夹名称
			$url = ltrim($url,APP_FOLDER);
			//去掉去掉前面的/
			$url = ltrim($url,'/');
			//去掉前面的index.php
			$url = ltrim($url,'index.php');
			//去掉去掉前面的/
			$url = ltrim($url,'/');
			//上面这样做是为了避免用localhost访问，把项目文件夹，入口文件都去掉，获取需要的url
			//url数组
			$urlArray = explode('/',$url);
			//模块
			$this->modules = empty($urlArray[0])?'':$urlArray[0];
			//控制器
			array_shift($urlArray);
			$this->controller = empty($urlArray[0])?'':$urlArray[0];
			//方法
			array_shift($urlArray);
			$this->action = empty($urlArray[0])?'':$urlArray[0];
			//判断模块控制器方法
			if (empty($this->modules)||empty($this->controller)||empty($this->action)) {
				// header('location:'.__ROOT__.'/'.getConfig('url_modules').'/'.getConfig('url_controller').'/'.getConfig('url_action'));
				showError(true);
				exit();
			}
			//参数
			array_shift($urlArray);
			if (!empty($urlArray)) {
				if (count($urlArray) == 1) {
					$_GET[$urlArray[0]] = '';
				}else{
					for ($i=0; $i < count($urlArray); $i++) {
						if ($i%2==0) {
							if(is_numeric($urlArray[$i+1]) && $urlArray[$i+1]==0){
								$_GET[$urlArray[$i]] = 0;
							}else{
								$val = empty($urlArray[$i+1])?'':$urlArray[$i+1];
								$_GET[$urlArray[$i]] = $val;
							}
							
						}
					}
				}
			}
		}else{
			//get类型
			if (empty($_GET['m'])||empty($_GET['c'])||empty($_GET['a'])) {
				// header('location:'.__ROOT__.'?m='.getConfig('url_modules').'&c='.getConfig('url_controller').'&a='.getConfig('url_action'));
				showError(true);
				exit();
			}
			//模块
			$this->modules = empty($_GET['m'])?'':$_GET['m'];
			//控制器
			$this->controller = empty($_GET['c'])?'':$_GET['c'];
			//方法
			$this->action = empty($_GET['a'])?'':$_GET['a'];
			unset($_GET['m']);
			unset($_GET['c']);
			unset($_GET['a']);
		}
	}
	/**
	 * 模块和域名定义常量
	 */
	private function defined_module_url(){
		//把模块设置为常量，方便后面用
		defined('__MODULE__') or define('__MODULE__',$this->modules);
		//把模块下的Public文件夹设置为常量，方便后面用
		defined('__PUBLIC__') or define('__PUBLIC__',__ROOT__.'/'.$this->modules.'/Public');
		//把控制器设置为常量，方便后面用
		defined('__CONTROLLER__') or define('__CONTROLLER__',$this->controller);
		//把方法设置为常量，方便后面用
		defined('__ACTION__') or define('__ACTION__',$this->action);
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
	}
	/**
	 * 判断域名是否带www
	 */
	public function hostIsWww(){
		if (substr(__HOST__,0,3) != 'www') {
			//判断地址后面是否有参数     
			$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
			//发出301头部
			header('HTTP/1.1 301 Moved Permanently');
			//跳转到你希望的地址格式
       		header('Location:'.__HTTPTYPE__.'://www.'.__HOST__.$request_uri);
       		exit();
		}
	}
	/**
	 * 加载公用和模块函数文件
	 */
	private function load_function(){
		//加载自定义公用函数库文件Common,如果有的话
		if (is_file(APP_PATH.'/Common/functions/functions.php')) {
			include APP_PATH.'/Common/functions/functions.php';
		}
		//加载自定义模块函数框架文件，如果有的话
		if (is_file(APP_PATH.'/'.__MODULE__.'/functions/functions.php')) {
			include APP_PATH.'/'.__MODULE__.'/functions/functions.php';
		}
	}
	/**
	 * 删除过期缓存文件
	 */
	private function delOverdueFile(){
		$path = APP_PATH.'/Runtime/cache/'.__MODULE__;
		Cache::delOverdue($path);
	}
	/**
	 * 执行
	 */
	private function execute(){
		//拼接完整的项目控制器名称
		$projectController = $this->controller.'Controller';
		//实例化项目控制器，并传入模块名，控制器名和方法名，用于渲染视图
		$p = new $projectController($this->modules,$this->controller,$this->action);		
        //调用$p类的$this->Action方法，并传入参数param
        //参数param是个数组，在$this->action方法直接写上参数就可使用，
        //控制器直接用GET/POST获取参数了，这里就直接传空数组就行
        call_user_func_array(array($p, $this->action), array());
	}
	/**
	 * 数组简单过滤
	 */
	private function filter(){
		$_POST = arrayFilter($_POST);
		$_GET = arrayFilter($_GET);
	}
	/**
	 * 自动加载
	 */
	private function loadClass($class){
		//框架核心类(控制器，模型，视图)
		$corePath = APP_PATH.'/Library/mvc/'.$class.'.class.php';
		//扩展类
		$extendPath = APP_PATH.'/Library/extend/'.$class.'.class.php';
		//项目的控制器
		$controllerPath =  APP_PATH.'/'.$this->modules.'/Controller/'.$class.'.class.php';
		//项目的模型
		$modelPath =  APP_PATH.'/'.$this->modules.'/Model/'.$class.'.class.php';
		if (file_exists($corePath)){
			// 加载框架核心类
			include $corePath;
        }
        if (file_exists($extendPath)){
			// 加载扩展类
			include $extendPath;
        }
        if(file_exists($controllerPath)) {
        	// 加载应用控制器类
        	include $controllerPath;
        }
        if(file_exists($modelPath)){
        	//加载应用模型类
        	include $modelPath;
        }
	}
	/**
	 * 错误设置
	 */
	private function setPhpError(){
		//设置中国时区
		date_default_timezone_set('PRC');
        //开启所有错误
        error_reporting(E_ALL);
        //关闭错误显示
        ini_set('display_errors','off');
        //关闭系统日志
		//这里关闭是因为自动调用的showError函数里面自定义了错误，为了避免日志里面既有系统写的日志又有自定义的日志，所以关闭
		ini_set('log_errors','off');
        //判断是否记录错误日志
		if(!getConfig('php_error_log')){
			return ;
		}
		//目录
	    $error_log_path = APP_PATH.'/Runtime/logs/php/';
	    //文件
	    $error_log_file = APP_PATH.'/Runtime/logs/php/'.date('Y-m-d').'_error.log';
		if (!is_dir($error_log_path)) {
        	//如果目录不存在就创建目录
        	mkdir($error_log_path,0777,true);
		}
		//设置日志文件
	    ini_set('error_log',$error_log_file);
	}
	/**
	 * session设置
	 */
	private function setSession(){
		/********************设置session目录并开启session****************************/
		//session目录
        $session_path = APP_PATH.'/Runtime/session/';
        //先判断是否存在目录，不存在就创建
        if (!is_dir($session_path)) {
        	//创建目录
	        mkdir($session_path,0777,true);
		}
		session_save_path($session_path);
		//设置session有效时间
		ini_set('session.gc_maxlifetime',getConfig('session_time')); 
		//开启session
		session_start();
	}
}