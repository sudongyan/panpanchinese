<?php
namespace app\admin\controller;
use think\Controller;
class Teacher extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('teacher') == 0 && session('id') != 1) {
            $this->error('无权限');
		}
	}

	//老师列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			if (isset($data['search'])) {
				$list = model('Teacher') -> getSearchList($data['p'], $data['search'], 'desc');
			} else {
				$list = model('Teacher') -> getList($data['p'], $data['state'], 'desc');
			}
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['evaluate_num'] = model('Evaluate') -> getCount($list[$i]['id']);
				$list[$i]['order_0'] = model('Order') -> getStateCount('t', $list[$i]['id'], 0);
				$list[$i]['order_1'] = model('Order') -> getStateCount('t', $list[$i]['id'], 1);
				$list[$i]['order_2'] = model('Order') -> getStateCount('t', $list[$i]['id'], 2);
				$list[$i]['order_3'] = model('Order') -> getStateCount('t', $list[$i]['id'], 3);
			}
			if (isset($data['search'])) {
				$total = model('Teacher') -> getSearchCount($data['search']);
			} else {
				$total = model('Teacher') -> getCount($data['state']);
			}
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('state', $data['state']);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//评价列表
	public function evaluate() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			$Teacher = model('Teacher') -> getOne($data['id']);
			if (!$Teacher) {
				$this -> error('id错误');
			}
			$list = model('Evaluate') -> getList($data['id'], $data['p'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				if ($list[$i]['student_id'] != 0) {
					$student = model('Student') -> getOne($list[$i]['student_id']);
					$list[$i]['student_avatar'] = $student['avatar'];
					$list[$i]['student_nickName'] = $student['nickName'];
					$list[$i]['student_account'] = $student['account'];
					$list[$i]['student_wx'] = $student['wx'];
					$order = model('Order') -> getOne($list[$i]['order_id']);
					$list[$i]['order_time'] = $order['create_time'];
					$list[$i]['order_class'] = $order['class'];
					$list[$i]['class_time'] = date('Y-m-d H:i:s', $order['time']);
					$course = model('Course') -> getOne($order['course']);
					$list[$i]['class_name'] = $course['name_cn'];
					$courseType = model('CourseType') -> getOne($course['type']);
					$list[$i]['class_type'] = $courseType['name'];
				}
			}
			$total = model('Evaluate') -> getCount($data['id']);
			$pageNum = ceil($total / 20);
			$this -> assign('user_id', $data['id']);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		}
	}

	//新增评价
	public function add_evaluate() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			$teacher = model('Teacher') -> getOne($data['id']);
			if (!$teacher) {
				$this -> error('id错误');
			}
			$this -> assign('teacher', $teacher);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$postdata['create_time'] = time();
			$new = model('Evaluate') -> addData($postdata);
			if ($new) {
				$updateTeacher['score'] = round(model('Evaluate') -> evaluate($postdata['teacher_id']),1);
				model('Teacher') -> updateData($postdata['teacher_id'], $updateTeacher);
				$info['id'] = $new['teacher_id'];
				$this -> redirect('teacher/evaluate', $info);
			} else {
				$this -> error('新增失败');
			}
		}
	}

	//删除评价
	public function del_evaluate() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			model('Evaluate') -> delData($postdata['id']);
			$updateTeacher['score'] = round(model('Evaluate') -> evaluate($postdata['teacher_id']),1);
			model('Teacher') -> updateData($postdata['teacher_id'], $updateTeacher);
			return 1;
		}
	}

	//新增老师
	public function add() {
		if ( request() -> isGET()) {
			$taglist = model('TeacherTag') -> getAllList('desc');
			$this -> assign('taglist', $taglist);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$postdata['create_time'] = time();
			$Authentication = model('Authentication') -> getAccount($postdata['account'], 1);
			$teacher = model('Teacher') -> getAccount($postdata['account']);
			if ($teacher || $Authentication) {
				$this -> error('账号重复');
			}
			if (isset($postdata['tag'])) {
				if (is_array($postdata['tag'])) {
					$postdata['tag'] = implode(",", $postdata['tag']);
				}
			} else {
				$postdata['tag'] = null;
			}
			$new = model('Teacher') -> addData($postdata);
			if ($new) {
				$this -> success('新增成功', 'teacher/index');
			} else {
				$this -> error('新增失败');
			}
		}
	}

	//账号管理
	public function edit() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			$teacher = model('Teacher') -> getOne($data['id']);
			if (!$teacher) {
				$this -> error('id错误');
			}
			$teacher['tag'] = str_replace("'", "", $teacher['tag']);
			$taglist = model('TeacherTag') -> getAllList('desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('teacher', $teacher);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['id'])) {
				$this -> error('id错误');
			}
			$teacher = model('Teacher') -> getOne($postdata['id']);
			if (!$teacher) {
				$this -> error('id错误');
			}
			if($postdata['password'] == ''){
				unset($postdata['password']);
			}
			if (isset($postdata['tag'])) {
				if (is_array($postdata['tag'])) {
					$postdata['tag'] = implode(",", $postdata['tag']);
				}
			} else {
				$postdata['tag'] = null;
			}
			$new = model('Teacher') -> updateData($postdata['id'], $postdata);
			if ($new) {
				$this -> success('修改成功', 'teacher/index');
			} else {
				$this -> error('修改失败');
			}
		}
	}

	//充值
	public function recharge() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			$student = model('Teacher') -> getOne($data['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			$list = model('Balance') -> getListT($data['p'], $data['id'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$admin = model('Admin') -> getId($list[$i]['admin_id']);
				$list[$i]['admin_nickName'] = $admin['nickName'];
				$list[$i]['admin_account'] = $admin['account'];
			}
			$total = model('Balance') -> getCountT($data['id']);
			$pageNum = ceil($total / 20);
			$this -> assign('user_id', $data['id']);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['id'])) {
				$this -> error('id错误');
			}
			if (!isset($postdata['num'])) {
				$this -> error('数额错误');
			}
			if(is_int($postdata['num'])){
				$this -> error('请输入整数');
			}
			$student = model('Teacher') -> getOne($postdata['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			$BalanceCount = model('Balance') -> getBalanceCount($postdata['id'],1);
			$balance['user_id'] = $postdata['id'];
			$balance['admin_id'] = session('id');
			$balance['user_type'] = 1;
			$balance['type'] = 1;
			if($postdata['num'] < 0){
				$balance['type'] = 0;
			}
			$balance['num'] = abs($postdata['num']);
			$balance['balance_num'] = $BalanceCount + $postdata['num'];
			model('Balance') -> addData($balance);
			$this->redirect('teacher/recharge', ['id' => $postdata['id']]);
		}
	}

}
