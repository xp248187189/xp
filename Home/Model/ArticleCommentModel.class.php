<?php
class ArticleCommentModel extends Model{
	//自定义表名
	public function __construct(){
		parent::__construct('article_comment');
	}
}