<?php 
//入口文件，定义一些常量，并加载框架核心
//首先设置utf-8编码
header("Content-type: text/html; charset=utf-8");
//__FILE__ 指向当前 文件 的绝对路径；也就是写在哪个文件里就是哪里。
//__DIR__ 当前文件 所在的 文件夹 的全路径，这里就是 根目录 文件夹的全路径
//dirname — 返回路径中的目录部分
//定义项目目录(把盘符当做根目录) 如 C:/wamp/www/xp
//创建文件或者文件夹就要用这个，项目内引用文件就用下面的__ROOT__，因为__ROOT__是把项目文件夹当根目录，创建文件的时候是把盘符当根目录
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
//定义项目的根目录 (把项目文件夹当做根目录)
$_root  =   rtrim(dirname(rtrim($_SERVER['SCRIPT_NAME'],'/')),'/');
defined('__ROOT__') or define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
//定义Common(公用)文件夹
defined('__COMMON__') or define('__COMMON__', __ROOT__.'/Common');
//basename — 返回路径中的文件名部分,DIR因为是文件夹，不是文件名，所以就返回文件夹的名字
//定义项目文件夹名称
defined('APP_FOLDER') or define('APP_FOLDER', basename(__DIR__));
//加载框架核心
include APP_PATH.'/Library/Core.class.php';
// 实例化核心类
$core = new Core;
$core -> run();