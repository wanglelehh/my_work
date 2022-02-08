<?php
/*------------------------------------------------------ */
//-- app下载页
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\publics\controller;
use app\BaseController;

class Download  extends BaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function app(){

		return $this->fetch('app');
	}


	
}?>