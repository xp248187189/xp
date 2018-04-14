<?php
class AdminLoginModel extends Model{
	//自定义表名
	public function __construct(){
		parent::__construct('admin_login');
	}
}