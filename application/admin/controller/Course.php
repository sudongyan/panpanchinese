<?php
namespace app\admin\controller;
use think\Controller;
class Course extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('course') == 0 && session('id') != 1) {
			$this -> error('无权限');
		}
	}

	//教材管理
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			$list = model('Course') -> getList($data['p'], $data['state'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$type = model('CourseType') -> getOne($list[$i]['type']);
				$list[$i]['type_name'] = $type['name'];
			}
			$CourseType = model('CourseType') -> getList('desc');
			$total = model('Course') -> getCount($data['state']);
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('CourseType', $CourseType);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('state', $data['state']);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//新增分类
	public function add_type() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$CourseType = model('CourseType') -> getName($postdata['name']);
			if ($CourseType) {
				return 1;
			} else {
				$CourseType = model('CourseType') -> addData($postdata);
				if ($CourseType) {
					return 1;
				} else {
					return 0;
				}
			}
		}
	}

	//新增教材
	public function add() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Course = model('Course') -> addData($postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	//修改教材
	public function edit() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Course = model('Course') -> updateData($postdata['id'], $postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	//修改头图
	public function editimg() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			if($postdata['type'] == 1){
				$data['img1'] = $postdata['img'];
			}else{
				$data['img2'] = $postdata['img'];
			}
			$Course = model('Course') -> updateData($postdata['id'], $data);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	

	//删除教材
	public function delcourse() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$data['del'] = 1;
			$Course = model('Course') -> updateData($postdata['id'], $data);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	//删除分类
	public function deltype() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$list = model('Course') -> getTypeList($postdata['id']);
			for ($i = 0; $i < count($list); $i++) {
				$thecourse['state'] = 0;
				model('Course') -> updateData($list[$i]['id'], $thecourse);
			}
			$data['del'] = 1;
			$Course = model('CourseType') -> updateData($postdata['id'], $data);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	//课程
	public function period() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			$Course = model('Course') -> getOne($data['id']);
			if (!$Course) {
				$this -> error('id错误');
			}
			$list = model('CoursePeriod') -> getList($data['p'], $data['id'], 'asc');
			for ($i = 0; $i < count($list); $i++) {
			}
			$total = model('CoursePeriod') -> getCount($data['id']);
			$pageNum = ceil($total / 20);
			$this -> assign('course_id', $data['id']);
			$this -> assign('course_name', $Course['name_cn']);
			$this -> assign('course', $Course);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		}
	}

	//新增课程
	public function add_period() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$CoursePeriod = model('CoursePeriod') -> getPeriod($postdata['course_id'], $postdata['number']);
			if ($CoursePeriod) {
				return 0;
			} else {
				$CoursePeriod = model('CoursePeriod') -> addData($postdata);
				if ($CoursePeriod) {
					$data['period'] = model('CoursePeriod') -> getCount($postdata['course_id']);
					model('Course') -> updateData($postdata['course_id'], $data);
					return 1;
				} else {
					return 0;
				}
			}
		}
	}

	//删除课程
	public function del() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Period = model('CoursePeriod') -> getOne($postdata['id']);
			model('CoursePeriod') -> delData($postdata['id']);
			$data['period'] = model('CoursePeriod') -> getCount($Period['course_id']);
			model('Course') -> updateData($Period['course_id'], $data);
			return 1;
		}
	}

}
