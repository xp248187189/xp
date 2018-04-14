<?php
return array(
    /*应用设定*/
    'urlType'               => 1,//url类型 1(pathinfo)/2(get)
    'deBug'                 => true,//调试模式 true/false
    'session_time'          => 21600,//session过期时间 (秒)
    'php_error_log'         => true,//是否记录php错误日志 true/false
    'sql_log'               => true,//是否记录sql日志 true/false
    'key'                   => 'yxp',//加密密匙
    /*数据库配置*/
    'dataBase_host'         => 'localhost',//数据库地址
    'dataBase_name'         => 'xp',//数据库名称
    'dataBase_user'         => 'root',//用户名
    'dataBase_pass'         => 'root',//用户密码
    'dataBase_qian'         => 'xp',//表前缀
    /*缓存*/
    'cache_isDataCache'     => false,//数据缓存 true/false
    'cache_isViewCache'     => false,//页面缓存 true/false
    'cache_notCacheModule'  => 'Admin',//不需要缓存的模块(如后台管理模块,多个用,隔开)
    'cache_type'            => 'file',//缓存方式 file/memcache
    'cache_host'            => '127.0.0.1',//memcache地址
    'cache_port'            => '11211',//memcache端口
    'cache_time'            => '60',//缓存时间 (秒)
);