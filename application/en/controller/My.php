<?php
namespace app\en\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class My extends Controller {
	public function index() {
		if (session('en_id') == null) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("ENappid") . '&secret=' . config("ENsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
				$bodys = json_decode($body, true);
				session('en_openid', $bodys['openid']);
				$student = model('Student') -> getOpenID($bodys['openid']);
				if ($student && $student['language'] == 0) {
					session('en_id', $student['id']);
					$this -> redirect('my/index');
				}
			}
			$this -> redirect('my/login');
		} else {
			$student = model('Student') -> getOne(session('en_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($student['openid'] != session('ko_openid')) {
					session('en_id', null);
					session('en_openid', null);
					$this -> redirect('my/login');
				}
			}
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$student['balance'] = model('Balance') -> getBalanceCount(session('en_id'), 0);
			if ($student['validity_end'] < time() && $student['balance'] > 0) {
				$balance['user_id'] = session('en_id');
				$balance['user_type'] = 0;
				$balance['type'] = 0;
				$balance['num'] = $student['balance'];
				$balance['balance_num'] = 0;
				model('Balance') -> addData($balance);
				$student['balance'] = 0;
			}
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$this -> assign('timezone', $timezone['name_en']);
			$this -> assign('student', $student);
			return $this -> fetch();
		}
	}

	//登录
	public function login() {
		if ( request() -> isGET()) {
			if (session('en_id') == null) {
				return $this -> fetch();
			} else {
				$this -> redirect('my/index');
			}
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$Authentication = model('Authentication') -> getAccount($postdata['account'], 0);
			if ($Authentication) {
				if ($Authentication['password'] == $postdata['password']) {
					$info['info'] = '入驻申请中';
					$this -> redirect('base/info', $info);
				} else {
					$info['info'] = '密码错误';
					$this -> redirect('base/close', $info);
				}
			}
			$Student = model('Student') -> getAccount($postdata['account']);
			if ($Student) {
				if ($Student['password'] == $postdata['password']) {
					if ($Student['language'] != 0) {
						$info['info'] = '非英文端账号';
						$this -> redirect('base/close', $info);
					}
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
						$info['openid'] = session('en_openid');
						model('Student') -> updateData($Student['id'], $info);
					}
					session('en_id', $Student['id']);
					$this -> redirect('my/index');
				} else {
					$info['info'] = '密码错误';
					$this -> redirect('base/close', $info);
				}
			} else {
				$info['info'] = '无此用户';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//入驻
	public function enter() {
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$Authentication = model('Authentication') -> getAccount($postdata['account'], 0);
			$Student = model('Student') -> getAccount($postdata['account']);
			if ($Student || $Authentication) {
				$info['info'] = '账号重复';
				$this -> redirect('base/close', $info);
			}
			$postdata['time_zone'] = 1;
			$postdata['type'] = 0;
			$postdata['language'] = 0;
			$postdata['create_time'] = time();
			$new = model('Authentication') -> addData($postdata);
			if ($new) {
				$info['info'] = '申请成功';
				$this -> redirect('base/correct', $info);
			} else {
				$info['info'] = '申请错误';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//编辑
	public function edit() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$student = model('Student') -> getOne(session('en_id'));
			$this -> assign('student', $student);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$student = model('Student') -> updateData(session('en_id'), $postdata);
			if ($student) {
				$this -> redirect('my/index');
			} else {
				$info['info'] = '编辑错误';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//设置时区
	public function timezone() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$TimezoneList = model('Timezone') -> getAllList(1, 'desc');
			$student = model('Student') -> getOne(session('en_id'));
			$this -> assign('TimezoneList', $TimezoneList);
			$this -> assign('student', $student);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$student = model('Student') -> updateData(session('en_id'), $postdata);
			if ($student) {
				$this -> redirect('my/index');
			} else {
				$info['info'] = '编辑错误';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//订单
	public function order() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state'])) {
				$data['state'] = null;
			}
			$Overduelist = model('Order') -> getOverdueList();
			for ($i = 0; $i < count($Overduelist); $i++) {
				$Overdue['state'] = 3;
				model('Order') -> updateData($Overduelist[$i]['id'], $Overdue);
				$BalanceCount = model('Balance') -> getBalanceCount($Overduelist[$i]['student_id'], 1);
				$balance['user_id'] = $Overduelist[$i]['student_id'];
				$balance['user_type'] = 1;
				$balance['type'] = 1;
				$balance['num'] = 1;
				$balance['balance_num'] = $BalanceCount - 1;
				model('Balance') -> addData($balance);
			}
			$list = model('Order') -> getList(session('en_id'), $data['p'], $data['state'], 'desc');
			date_default_timezone_set('UTC');
			$my = model('Student') -> getOne(session('en_id'));
			$timezone = model('Timezone') -> getOne($my['time_zone']);
			$time = $timezone['time'] * 3600;
			for ($i = 0; $i < count($list); $i++) {
				$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
				$list[$i]['tid'] = $list[$i]['teacher_id'];
				$list[$i]['avatar'] = $teacher['avatar'];
				$list[$i]['nickName'] = $teacher['nickName'];
				$list[$i]['wx'] = $teacher['wx'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$list[$i]['course'] = $course['name_en'];
				$CourseType = model('CourseType') -> getOne($course['type']);
				$list[$i]['course_type'] = $CourseType['name'];
				$list[$i]['start_time'] = date("Y-m-d H:i", $list[$i]['time'] + $time);
				if ($list[$i]['state'] == 1) {
					$Evaluate = model('Evaluate') -> getOne($list[$i]['id']);
					$list[$i]['score'] = $Evaluate['score'];
				}
				if ($list[$i]['time'] < time() + 7200) {
					$list[$i]['cancel'] = 0;
				} else {
					$list[$i]['cancel'] = 1;
				}
			}
			$this -> assign('list', $list);
			$this -> assign('s', $data['state']);
			return $this -> fetch();
		}
	}

	//订单JSON
	public function order_list() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state'])) {
				$data['state'] = null;
			}
			$list = model('Order') -> getList(session('en_id'), $data['p'], $data['state'], 'desc');
			date_default_timezone_set('UTC');
			$my = model('Student') -> getOne(session('en_id'));
			$timezone = model('Timezone') -> getOne($my['time_zone']);
			$time = $timezone['time'] * 3600;
			for ($i = 0; $i < count($list); $i++) {
				$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
				$list[$i]['tid'] = $list[$i]['teacher_id'];
				$list[$i]['avatar'] = $teacher['avatar'];
				$list[$i]['nickName'] = $teacher['nickName'];
				$list[$i]['wx'] = $teacher['wx'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$list[$i]['course'] = $course['name_en'];
				$CourseType = model('CourseType') -> getOne($course['type']);
				$list[$i]['course_type'] = $CourseType['name'];
				$list[$i]['start_time'] = date("Y-m-d H:i:s", $list[$i]['time'] + $time);
				if ($list[$i]['state'] == 1) {
					$Evaluate = model('Evaluate') -> getOne($list[$i]['id']);
					$list[$i]['score'] = $Evaluate['score'];
				}
				if ($list[$i]['time'] < time() + 7200) {
					$list[$i]['cancel'] = 0;
				} else {
					$list[$i]['cancel'] = 1;
				}
			}
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	//我的账户
	public function account() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$student = model('Student') -> getOne(session('en_id'));
			$student['balance'] = model('Balance') -> getBalanceCount(session('en_id'), 0);
			$student['oldbalance'] = model('Balance') -> getOldCount(session('en_id'));
			date_default_timezone_set('UTC');
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$time = $timezone['time'] * 3600;
			$student['validity_start'] = date('Y-m-d', $student['validity_start'] + $time);
			$student['validity_end'] = date('Y-m-d', $student['validity_end'] + $time);
			$this -> assign('student', $student);
			return $this -> fetch();
		}
	}

	//修改密码
	public function password() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$data['password'] = $postdata['password'];
			$Student = model('Student') -> updateData(session('en_id'), $data);
			if ($Student) {
				$return['result'] = TRUE;
				$return['data'] = 1;
			} else {
				$return['result'] = FALSE;
				$return['data'] = 0;
			}
			return json($return);
		}
	}

}
