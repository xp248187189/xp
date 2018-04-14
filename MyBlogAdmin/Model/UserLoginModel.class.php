<?php
class UserLoginModel extends Model{
	//自定义表名
	public function __construct(){
		parent::__construct('user_login');
	}
}