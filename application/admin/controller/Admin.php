<?php
namespace app\admin\controller;
use think\Controller;
class Admin extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
	}

	//新增管理员
	public function add() {
		if (session('id') != 1) {
			$this -> error('请使用总管理员账号');
		}
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$admin = model('Admin') -> getOne($postdata['account']);
			if (!$admin) {
				$add = model('Admin') -> addData($postdata);
				if ($add) {
					$this -> redirect('admin/index');
				} else {
					$this -> error('新增失败');
				}
			} else {
				$this -> error('账号重复');
			}
		}
	}

	//管理员列表
	public function index() {
		if (session('id') != 1) {
			$this -> error('请使用总管理员账号');
		}
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('Admin') -> getList($data['p'], 'desc');
			$total = model('Admin') -> getCount();
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//修改密码
	public function password() {
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['password'])) {
				$this -> error('未输入密码');
			}
			if (!isset($postdata['new_password'])) {
				$this -> error('未输入新密码');
			}
			$admin = model('Admin') -> getToken(session('token'));
			if ($admin && $admin['password'] == md5($postdata['password'] . 'ZackSun')) {
				$data['password'] = md5($postdata['new_password'] . 'ZackSun');
				$new = model('Admin') -> updateData($admin['id'], $data);
				if ($new) {
					session('token', null);
					session('account', null);
					session('id', null);
					$this -> success('修改成功，请重新登录', 'login/index');
				} else {
					$this -> error('修改失败');
				}
			} else {
				$this -> error('密码错误');
			}
		}
	}

	//修改权限
	public function edit_authority() {
		if ( request() -> isGET()) {
			$data = input('param.');
			$admin = model('Admin') -> getId($data['id']);
			$this -> assign('admin', $admin);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$new = model('Admin') -> updateData($postdata['id'], $postdata);
			if ($new) {
				$this -> success('修改成功', 'admin/index');
			} else {
				$this -> error('修改失败');
			}
		}
	}

	//总管理员修改密码
	public function edit_pwd() {
		if (session('id') != 1) {
			$this -> error('请使用总管理员账号');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if ($data['aid'] == 1) {
				$this -> redirect('admin/password');
			}
			$this -> assign('aid', $data['aid']);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['id'])) {
				$this -> error('id错误');
			}
			if (!isset($postdata['new_password'])) {
				$this -> error('未输入新密码');
			}
			$data['password'] = md5($postdata['new_password'] . 'ZackSun');
			$new = model('Admin') -> updateData($postdata['id'], $data);
			if ($new) {
				$this -> success('修改成功', 'admin/index');
			} else {
				$this -> error('修改失败');
			}
		}
	}

}
