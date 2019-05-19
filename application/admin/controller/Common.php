<?php
namespace app\admin\controller; 
use think\Controller; 
class Common extends Controller {
	//初始执行
	public function all() {
		//判断token
		if (session('token') == null) {
			$this -> redirect('login/index');
		} else {
			$admin = model('Admin') -> getToken(session('token'));
			if (!$admin) {
				session('token', null);
				$this -> redirect('login/index');
			}
		}
	}

}
