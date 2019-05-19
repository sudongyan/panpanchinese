<?php
namespace app\admin\controller;
use think\Controller;
class Teachertag extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('teacher') == 0 && session('id') != 1) {
			$this -> error('无权限');
		}
	}

	//老师标签列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			$list = model('TeacherTag') -> getList($data['p'], $data['state'], 'desc');
			$total = model('TeacherTag') -> getCount($data['state']);
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
	
	//新增标签
	public function add() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Course = model('TeacherTag') -> addData($postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	//修改标签
	public function edit() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Course = model('TeacherTag') -> updateData($postdata['id'], $postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}

}
