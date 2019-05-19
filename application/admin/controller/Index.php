<?php
namespace app\admin\controller;
use think\Controller;
class Index extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
	}

	public function index() {
		$this -> redirect('authentication/index');
	}

}
