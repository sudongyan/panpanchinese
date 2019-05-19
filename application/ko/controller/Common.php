<?php
namespace app\ko\controller;
use think\Controller;
class Common extends Controller {
	//初始执行
	public function all() {
		if (cookie('think_var') == null) {
			cookie('think_var', 'ko-kr');
		}
	}

}
