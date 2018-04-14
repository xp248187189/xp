<?php
/**
 * 模型基类
 */
class Model{
    protected $_model;
    protected $_table;
    protected static $_pdo = null;
    private $where = '';
    private $filed = '*';
    private $having = '';
    private $group = '';
    private $order = '';
    private $limit = '';
    public function __construct($trueTable=''){
        //获取数据库连接配置
        $this->db_host = getConfig('dataBase_host');
        $this->db_user = getConfig('dataBase_user');
        $this->db_pass = getConfig('dataBase_pass');
        $this->db_name = getConfig('dataBase_name');
        $this->db_qian = getConfig('dataBase_qian');
        if (!empty($this->db_qian)) {
            $this->db_qian = $this->db_qian.'_';
        }
        //把传入的表名赋值给变量
        $this->trueTable = $trueTable;
        // 获取模型类名
        $this->_model = get_class($this);
        // 删除类名最后的 Model 字符
        $this->_model = substr($this->_model, 0, -5);
        //如果传入了表名，就用传入的表,否则就把类名当表名，strtolower(),将字符串转换为小写
        if ($this->trueTable) {
            $this->_table = strtolower($this->db_qian.$this->trueTable);
        }else{
            $this->_table = strtolower($this->db_qian.$this->_model);
        }
    }
    // 连接数据库
    protected function connect(){
        $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $this->db_host, $this->db_name);
        //指定查询结果集为关联数组
        $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
        if (!self::$_pdo) {
            self::$_pdo = new PDO($dsn, $this->db_user, $this->db_pass, $option);
        }
    }
    //获取当前模型名
    public function getModelName(){
        return $this->_model;
    }
    //获取当前表名
    public function getTableName(){
        return $this->_table;
    }
    /**
     * where 条件
     * $where = array('id'=>array('3,5','not in'));
     * $where = array('id'=>array('3,5','in'));
     * $where = array('id'=>array('3,5','between and'));
     * $where = array('id'=>array('3,5','not between and'));
     * $where = array('id'=>array('3','>='));
     * $where = array('id'=>'is not null');
     */
    public function where($where=''){
        if (!empty($where)) {
            $this->where .= ' where ';
            if (is_string($where) || is_numeric($where)) {
                $this->where .= $where;
                return $this;
            }
            foreach ($where as $key => $value) {
                $end_key = key(array_slice($where,-1, 1));
                if (is_array($value)) {
                    if ($value[1]=='in' || $value[1]=='not in') {
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' '.$value[1]. ' ('.$value[0].')';
                            }else{
                                $this->where .= '`'.$key.'` '.$value[1]. ' ('.$value[0].')';
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' '.$value[1]. ' ('.$value[0].') and ';
                            }else{
                                $this->where .= '`'.$key.'` '.$value[1]. ' ('.$value[0].') and ';
                            }
                        }
                    }else if($value[1]=='between and') {
                        $t = explode(',',$value[0]);
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' between '.$t[0].' and '.$t[1];
                            }else{
                                $this->where .= '`'.$key.'` between '.$t[0].' and '.$t[1];
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' between '.$t[0].' and '.$t[1].' and ';
                            }else{
                                $this->where .= '`'.$key.'` between '.$t[0].' and '.$t[1].' and ';
                            }
                        }
                    }else if($value[1]=='not between and') {
                        $t = explode(',',$value[0]);
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' not between '.$t[0].' and '.$t[1];
                            }else{
                                $this->where .= '`'.$key.'` not between '.$t[0].' and '.$t[1];
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' not between '.$t[0].' and '.$t[1].' and ';
                            }else{
                                $this->where .= '`'.$key.'` not between '.$t[0].' and '.$t[1].' and ';
                            }
                        }
                    }else{
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' '.$value[1]. ' "'.$value[0].'"';
                            }else{
                                $this->where .= '`'.$key.'` '.$value[1]. ' "'.$value[0].'"';
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' '.$value[1]. ' "'.$value[0].'" and ';
                            }else{
                                $this->where .= '`'.$key.'` '.$value[1]. ' "'.$value[0].'" and ';
                            }
                        }
                    }
                }else{
                    if ($value==='is null' || $value==='is not null') {
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' '.$value;
                            }else{
                                $this->where .= '`'.$key.'` '.$value;
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' '.$value.' and ';
                            }else{
                                $this->where .= '`'.$key.'` '.$value.' and ';
                            }
                        }
                    }else{
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' = "'.$value.'"';
                            }else{
                                $this->where .= '`'.$key.'` = "'.$value.'"';
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->where .= $key.' = "'.$value.'" and ';
                            }else{
                                $this->where .= '`'.$key.'` = "'.$value.'" and ';
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }
    /**
     * group by 分组
     * $group = array('sex','banji');
     */
    public function group(array $group=array()){
        if (!empty($group)) {
            $this->group .= ' group by ';
            $newFiled = array();
            foreach ($group as $key => $value) {
                $newFiled[] = sprintf("`%s`", $value);
            }
            $this->group .= implode(',',$newFiled);
        }
        return $this;
    }
    /**
     * order by 排序
     * $order = array('sex','banji'=>'desc');
     */
    public function order(array $order=array()){
        if (!empty($order)) {
            $this->order .= ' order by ';
            foreach ($order as $key => $value) {
                if (is_int($key)) {
                    if(stripos($value,'(') !== false){
                        $this->order .= $value.',';
                    }else{
                        $this->order .= '`'.$value.'` asc,';
                    }
                }else{
                    $this->order .= '`'.$key.'` '.$value.',';
                }
            }
            $this->order = substr($this->order,0,strlen($this->order)-1); 
        }
        return $this;
    }
    /**
     * haning 筛选
     * 和where一样
     */
    public function having($having=''){
        if (!empty($having)) {
            $this->having .= ' having ';
            if (is_string($having) || is_numeric($having)) {
                $this->having .= $having;
                return $this;
            }
            foreach ($having as $key => $value) {
                $end_key = key(array_slice($having,-1, 1));
                if (is_array($value)) {
                    if ($value[1]=='in' || $value[1]=='not in') {
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' '.$value[1]. ' ('.$value[0].')';
                            }else{
                                $this->having .= '`'.$key.'` '.$value[1]. ' ('.$value[0].')';
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' '.$value[1]. ' ('.$value[0].') and ';
                            }else{
                                $this->having .= '`'.$key.'` '.$value[1]. ' ('.$value[0].') and ';
                            }
                        }
                    }else if($value[1]=='between and') {
                        $t = explode(',',$value[0]);
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' between '.$t[0].' and '.$t[1];
                            }else{
                                $this->having .= '`'.$key.'` between '.$t[0].' and '.$t[1];
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' between '.$t[0].' and '.$t[1].' and ';
                            }else{
                                $this->having .= '`'.$key.'` between '.$t[0].' and '.$t[1].' and ';
                            }
                        }
                    }else if($value[1]=='not between and') {
                        $t = explode(',',$value[0]);
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' not between '.$t[0].' and '.$t[1];
                            }else{
                                $this->having .= '`'.$key.'` not between '.$t[0].' and '.$t[1];
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' not between '.$t[0].' and '.$t[1].' and ';
                            }else{
                                $this->having .= '`'.$key.'` not between '.$t[0].' and '.$t[1].' and ';
                            }
                        }
                    }else{
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' '.$value[1]. ' "'.$value[0].'"';
                            }else{
                                $this->having .= '`'.$key.'` '.$value[1]. ' "'.$value[0].'"';
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' '.$value[1]. ' "'.$value[0].'" and ';
                            }else{
                                $this->having .= '`'.$key.'` '.$value[1]. ' "'.$value[0].'" and ';
                            }
                        }
                    }
                }else{
                    if ($value==='is null' || $value==='is not null') {
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' '.$value;
                            }else{
                                $this->having .= '`'.$key.'` '.$value;
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' '.$value.' and ';
                            }else{
                                $this->having .= '`'.$key.'` '.$value.' and ';
                            }
                        }
                    }else{
                        if ($key === $end_key) {
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' = "'.$value.'"';
                            }else{
                                $this->having .= '`'.$key.'` = "'.$value.'"';
                            }
                        }else{
                            if(stripos($key,'(') !== false){
                                $this->having .= $key.' = "'.$value.'" and ';
                            }else{
                                $this->having .= '`'.$key.'` = "'.$value.'" and ';
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }
    /**
     * limit 分页
     * $limit = array(0,10);
     */
    public function limit(array $limit=array()){
        if (!empty($limit)) {
            $this->limit .= ' limit ';
            $this->limit .= implode(',',$limit);
        }
        return $this;
    }
    /**
     * filed 字段
     * $filed = array('id','name'=>'myname');
     */
    public function filed(array $filed=array()){
        if (!empty($filed)) {
            $this->filed = '';
            $newFiled = array();
            foreach ($filed as $key => $value) {
                if (is_int($key)) {
                    if(stripos($key,'(') !== false){
                        $newFiled[] = sprintf("%s", $value);
                    }else{
                        $newFiled[] = sprintf("`%s`", $value);
                    }
                }else{
                    if(stripos($key,'(') !== false){
                        $newFiled[] =$key.' as '.$value;
                    }else{
                        $newFiled[] = $key.' as '.$value;
                    }
                }
            }
            $this->filed .= implode(',',$newFiled);
        }
        return $this;
    }
    //查询总数据
    public function getCount(){
        //组合sql语句
        $sql = 'select count(1) as totle from `'.$this->_table.'`'.$this->where.$this->group.$this->having.$this->order.$this->limit;
        if($this->getCacheData(md5($sql)) !== false){
            return $this->getCacheData(md5($sql));
        }
        //执行连接数据库
        $this->connect();
        //手动还原参数
        $this->close();
        //预处理语句
        $sth = self::$_pdo->prepare($sql);
        //写日志
        $this->writeMysqlLog($sql);
        //执行预处理sql语句
        if ($sth->execute()) {
            $count = $sth->fetch();
            //写缓存
            $this->setCacheDate(md5($sql),$count['totle']);
            //返回数据
            return $count['totle'];
        }else{
            if(getConfig('deBug')){
                //错误输出
                $errorInfo = $sth->errorInfo();
                exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
            }else{
                showError(true);
            }
        }
    }
    /**
     * select 查询
     */
    public function select(){
        //组合sql语句
        $sql = 'select '.$this->filed.' from `'.$this->_table.'`'.$this->where.$this->group.$this->having.$this->order.$this->limit;
        if($this->getCacheData(md5($sql)) !== false){
            return $this->getCacheData(md5($sql));
        }
        //执行连接数据库
        $this->connect();
        //手动还原参数
        $this->close();
        //预处理语句
        $sth = self::$_pdo->prepare($sql);
        //写日志
        $this->writeMysqlLog($sql);
        //执行预处理sql语句
        if ($sth->execute()) {
            $res = $sth->fetchAll();
            //写缓存
            $this->setCacheDate(md5($sql),$res);
            return $res;
        }else{
            if(getConfig('deBug')){
                //错误输出
                $errorInfo = $sth->errorInfo();
                exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
            }else{
                showError(true);
            }
        }
    }
    /**
     * selectOne 查询
     */
    public function selectOne(){
        //组合sql语句
        $sql = 'select '.$this->filed.' from `'.$this->_table.'`'.$this->where.$this->group.$this->having.$this->order.$this->limit;
        if($this->getCacheData(md5($sql)) !== false){
            return $this->getCacheData(md5($sql));
        }
        //执行连接数据库
        $this->connect();
        //手动还原参数
        $this->close();
        //预处理语句
        $sth = self::$_pdo->prepare($sql);
        //写日志
        $this->writeMysqlLog($sql);
        //执行预处理sql语句
        if ($sth->execute()) {
            $res = $sth->fetch();
            //写缓存
            $this->setCacheDate(md5($sql),$res);
            return $res;
        }else{
            if(getConfig('deBug')){
                //错误输出
                $errorInfo = $sth->errorInfo();
                exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
            }else{
                showError(true);
            }
        }
    }
    /**
     * add 添加
     */
    public function add($data){
        //先执行连接数据库
        $this->connect();
        $fields = array();
        $values = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s`", $key);
            $values[] = sprintf("'%s'", $value);
        }
        $field = implode(',', $fields);
        $value = implode(',', $values);
        $v = sprintf("(%s) values (%s)", $field, $value);
        $sql = sprintf("insert into `%s` %s", $this->_table, $v);
        //手动还原参数
        $this->close();
        //预处理语句
        $sth = self::$_pdo->prepare($sql);
        //写日志
        $this->writeMysqlLog($sql);
        //执行预处理sql语句
        if ($sth->execute()) {
            //返回最后插入行的ID或序列值
            return self::$_pdo->lastInsertId();
        }else{
            if(getConfig('deBug')){
                //错误输出
                $errorInfo = $sth->errorInfo();
                exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
            }else{
                showError(true);
            }
        }
    }
    // 修改数据
    public function update($data){
        //先执行连接数据库
        $this->connect();
        //拼接数组
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s` = '%s'", $key, $value);
        }
        $v =  implode(',', $fields);
        $sql = 'update `'.$this->_table.'` set '.$v.$this->where;
        //手动还原参数
        $this->close();
        //预处理语句
        $sth = self::$_pdo->prepare($sql);
        //写日志
        $this->writeMysqlLog($sql);
        //执行预处理sql语句
        if ($sth->execute()) {
            //返回受影响的行数
            return $sth->rowCount();
        }else{
            if(getConfig('deBug')){
                //错误输出
                $errorInfo = $sth->errorInfo();
                exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
            }else{
                showError(true);
            }
        }
    }
    //删除数据
    public function delete(){
        //先执行连接数据库
        $this->connect();
        //sql
        $sql = 'delete from '.$this->_table.$this->where;
        //手动还原参数
        $this->close();
        //预处理语句
        $sth = self::$_pdo->prepare($sql);
        //写日志
        $this->writeMysqlLog($sql);
        //执行预处理sql语句
        if ($sth->execute()) {
            //返回受影响的行数
            return $sth->rowCount();
        }else{
            if(getConfig('deBug')){
                //错误输出
                $errorInfo = $sth->errorInfo();
                exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
            }else{
                showError(true);
            }
        }
    }
    //自定义sql
    public function query($sql){
        if (stripos($sql,'select') !== false) {
            $type = 'select';
        }else{
            $type = 'qita';
        }
        //写日志
        $this->writeMysqlLog($sql);
        //判断sql语句类型
        if ($type == 'select') {
            if($this->getCacheData(md5($sql)) !== false){
                return $this->getCacheData(md5($sql));
            }
            //执行连接数据库
            $this->connect();
            //预处理语句
            $sth = self::$_pdo->prepare($sql);
            //执行预处理sql语句
            if($sth->execute()){
                //查询，返回数据
                $res = $sth->fetchAll();
                //写缓存
                $this->setCacheDate(md5($sql),$res);
                return $res;
            }else{
                if(getConfig('deBug')){
                    //错误输出
                    $errorInfo = $sth->errorInfo();
                    exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
                }else{
                    showError(true);
                }
            }
        }else{
            //执行连接数据库
            $this->connect();
            //预处理语句
            $sth = self::$_pdo->prepare($sql);
            //执行预处理sql语句
            if($sth->execute()){
                //其他，返回受影响的行数
                return $sth->rowCount();
            }else{
                if(getConfig('deBug')){
                    //错误输出
                    $errorInfo = $sth->errorInfo();
                    exit('SQL : '.$sql.'<br/>ERR : ['.$errorInfo[0].'] ['.$errorInfo[1].'] '.$errorInfo[2]);
                }else{
                    showError(true);
                }
            }
        }
    }
    //日志记录
    private function writeMysqlLog($txt){
        if(!getConfig('sql_log')){
            return ;
        }
        if (empty($txt)) {
            return false;
        }
        $this->mysqlLog();
        $ip = $_SERVER["REMOTE_ADDR"];
        $newTxt = '['.date('Y-m-d H:i:s').'] ['.$ip.'] ['.$this->db_host.'] ['.$this->db_name.'] '.$txt."\r\n\r\n";
        $mysql_log_file = APP_PATH.'/Runtime/logs/sql/'.__MODULE__.'/'.date('Y-m-d').'.log';
        $file = fopen($mysql_log_file,'a');
        fwrite($file, $newTxt);
        fclose($file);
    }
    /**
     * 创建log文件
     */
    private function mysqlLog(){
        //目录
        $mysql_log_path = APP_PATH.'/Runtime/logs/sql/'.__MODULE__.'/';
        //文件
        $mysql_log_file = APP_PATH.'/Runtime/logs/sql/'.__MODULE__.'/'.date('Y-m-d').'.log';
        //先判断是否存在目录，不存在就创建
        if (!is_dir($mysql_log_path)) {
            //创建目录
            mkdir($mysql_log_path,0777,true);
            $file = fopen($mysql_log_file,'w');
            fwrite($file, '[时间] [用户ip] [数据库地址] [数据库名称] sql语句'."\r\n\r\n");
            fclose($file);
        }else{
            if(!is_file($mysql_log_file)){
                $file = fopen($mysql_log_file,'w');
                fwrite($file, '[时间] [用户ip] [数据库地址] [数据库名称] sql语句'."\r\n\r\n");
                fclose($file);
            }
        }
    }
    //获取缓存数据
    public function getCacheData($name){
        //判断是否开启数据缓存
        if (getConfig('cache_isDataCache')) {
            $cache = new Cache;
            //判断key是否存在
            if($cache->check_key_exists($name)){
                return $cache->getCache($name);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    //设置缓存
    public function setCacheDate($name,$value){
        //判断是否开启数据缓存
        if (getConfig('cache_isDataCache')) {
            $notCacheModule = getConfig('cache_notCacheModule');
            $notCacheModuleArray = explode(',',$notCacheModule);
            if (!in_array(__MODULE__, $notCacheModuleArray)) {
                $cache = new Cache;
                $cache->setCache($name,$value);
            }
        }
    }
    //参数还原，避免 没有重新实例化 时 再次使用的时候参数不对
    public function close(){
        $this->where = '';
        $this->filed = '*';
        $this->having = '';
        $this->group = '';
        $this->order = '';
        $this->limit = '';
    }
    //析构方法
    public function __destruct(){
        self::$_pdo = null;
    }
}