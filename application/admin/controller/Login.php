<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller {
	public function index() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$admin = model('Admin') -> Login($postdata['account'], $postdata['password']);
			if ($admin) {
				$token = md5($postdata['account'] . time());
				$newToken = model('Admin') -> newToken($admin['id'], $token);
				session('token', $token);
				session('account', $admin['account']);
				session('id', $admin['id']);
				session('authentication', $admin['authentication']);
				session('order', $admin['order']);
				session('teacher', $admin['teacher']);
				session('student', $admin['student']);
				session('curriculum', $admin['curriculum']);
				session('course', $admin['course']);
				session('timezone', $admin['timezone']);
				$this -> redirect('index/index');
			} else {
				$this -> error('登录失败');
			} 
		} else {
			return $this -> fetch();
		}
	}

	public function out() {
		session('token', null);
		session('account', null);
		session('id', null);
		$this -> redirect('login/index');
	}

}
