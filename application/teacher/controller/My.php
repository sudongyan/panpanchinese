<?php
namespace app\teacher\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class My extends Controller {
	public function index() {
		if (session('t_id') == null) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("TEACHERappid") . '&secret=' . config("TEACHERsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
				$bodys = json_decode($body, true);
				session('teacher_openid', $bodys['openid']);
				$teacher = model('Teacher') -> getOpenID($bodys['openid']);
				if ($teacher) {
					session('t_id', $teacher['id']);
					$this -> redirect('my/index');
				}
			}
			$this -> redirect('my/login');
		} else {
			$teacher = model('Teacher') -> getOne(session('t_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($teacher['openid'] != session('teacher_openid')) {
					session('t_id', null);
					session('teacher_openid', null);
					$this -> redirect('my/login');
				}
			}
			$teacher['balance'] = model('Balance') -> getBalanceCount(session('t_id'));
			$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
			$timezone = model('Timezone') -> getOne($teacher['time_zone']);
			$total['a'] = model('Order') -> getCount(session('t_id'), 0);
			$total['b'] = model('Order') -> getCount(session('t_id'), 3);
			$total['c'] = model('Order') -> getCount(session('t_id'), 1);
			$this -> assign('total', $total);
			$this -> assign('timezone', $timezone['name_cn']);
			$this -> assign('teacher', $teacher);
			return $this -> fetch();
		}
	}

	//登录
	public function login() {
		if ( request() -> isGET()) {
			if (session('t_id') == null) {
				return $this -> fetch();
			} else {
				$this -> redirect('my/index');
			}
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$Authentication = model('Authentication') -> getAccount($postdata['account'], 1);
			if ($Authentication) {
				if ($Authentication['password'] == $postdata['password']) {
					$info['info'] = '入驻申请中';
					$this -> redirect('base/info', $info);
				} else {
					$info['info'] = '密码错误';
					$this -> redirect('base/close', $info);
				}
			}
			$teacher = model('Teacher') -> getAccount($postdata['account']);
			if ($teacher) {
				if ($teacher['password'] == $postdata['password']) {
					if (session('teacher_openid') != null) {
						$info['openid'] = session('teacher_openid');
						model('Teacher') -> updateData($teacher['id'], $info);
					}
					session('t_id', $teacher['id']);
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
			$Authentication = model('Authentication') -> getAccount($postdata['account'], 1);
			$teacher = model('Teacher') -> getAccount($postdata['account']);
			if ($teacher || $Authentication) {
				$info['info'] = '账号重复';
				$this -> redirect('base/close', $info);
			}
			$postdata['time_zone'] = 1;
			$postdata['type'] = 1;
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
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$teacher = model('Teacher') -> getOne(session('t_id'));
			$this -> assign('teacher', $teacher);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$Teacher = model('Teacher') -> updateData(session('t_id'), $postdata);
			if ($Teacher) {
				$this -> redirect('my/index');
			} else {
				$info['info'] = '编辑错误';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//设置时区
	public function timezone() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$TimezoneList = model('Timezone') -> getAllList(1, 'desc');
			$teacher = model('Teacher') -> getOne(session('t_id'));
			$this -> assign('TimezoneList', $TimezoneList);
			$this -> assign('teacher', $teacher);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$Teacher = model('Teacher') -> updateData(session('t_id'), $postdata);
			if ($Teacher) {
				$this -> redirect('my/index');
			} else {
				$info['info'] = '编辑错误';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//用户评价
	public function evaluate() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('Evaluate') -> getList(session('t_id'), $data['p'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				if ($list[$i]['student_id'] != 0) {
					$student = model('Student') -> getOne($list[$i]['student_id']);
					$list[$i]['avatar'] = $student['avatar'];
					$list[$i]['nickName'] = $student['nickName'];
				} else {
					$list[$i]['avatar'] = $list[$i]['student_avatar'];
					$list[$i]['nickName'] = $list[$i]['student_nickName'];
				}
			}
			$this -> assign('list', $list);
			return $this -> fetch();
		}
	}

	//用户评价JSON
	public function evaluate_list() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('Evaluate') -> getList(session('t_id'), $data['p'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				if ($list[$i]['student_id'] != 0) {
					$student = model('Student') -> getOne($list[$i]['student_id']);
					$list[$i]['avatar'] = $student['avatar'];
					$list[$i]['nickName'] = $student['nickName'];
				} else {
					$list[$i]['avatar'] = $list[$i]['student_avatar'];
					$list[$i]['nickName'] = $list[$i]['student_nickName'];
				}
			}
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	//订单
	public function order() {
		if (session('t_id') == null) {
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
				$BalanceCount = model('Balance') -> getBalanceCount($Overduelist[$i]['teacher_id'], 1);
				$balance['user_id'] = $Overduelist[$i]['teacher_id'];
				$balance['user_type'] = 1;
				$balance['type'] = 1;
				$balance['num'] = 1;
				$balance['balance_num'] = $BalanceCount - 1;
				model('Balance') -> addData($balance);
			}
			$list = model('Order') -> getList(session('t_id'), $data['p'], $data['state'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$student = model('Student') -> getOne($list[$i]['student_id']);
				$list[$i]['avatar'] = $student['avatar'];
				$list[$i]['nickName'] = $student['nickName'];
				$list[$i]['wx'] = $student['wx'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$list[$i]['course'] = $course['name_cn'];
				$CourseType = model('CourseType') -> getOne($course['type']);
				$list[$i]['course_type'] = $CourseType['name'];
				if (is_int($list[$i]['interval'] / 2)) {
					$interval = $list[$i]['interval'] / 2 . ":00";
				} else {
					$interval = floor($list[$i]['interval'] / 2) . ":30";
				}
				$list[$i]['start_time'] = $list[$i]['day'] . " " . $interval;
				if ($list[$i]['state'] == 1) {
					$Evaluate = model('Evaluate') -> getOne($list[$i]['id']);
					$list[$i]['score'] = $Evaluate['score'];
				}
				$priceorder = model('PriceOrder') -> getOne($list[$i]['payid']);
				$list[$i]['PriceType'] = $priceorder['type'];
			}
			$this -> assign('list', $list);
			$this -> assign('s', $data['state']);
			return $this -> fetch();
		}
	}

	//订单详情
	public function orderinfo() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['id'])) {
				$info['info'] = 'id错误';
				$this -> redirect('base/close', $info);
			}
			$order = model('Order') -> getOne($data['id']);
			if ($order['teacher_id'] != session('t_id')) {
				$info['info'] = '非自有订单';
				$this -> redirect('base/close', $info);
			}
			$student = model('Student') -> getOne($order['student_id']);
			$teacher = model('Teacher') -> getOne($order['teacher_id']);
			$CoursePeriod = model('CoursePeriod') -> getNum($order['course'], $order['class']);
			$order['course_period'] = $CoursePeriod['name_cn'];
			$order['course_url'] = $CoursePeriod['url'];
			$course = model('Course') -> getOne($order['course']);
			$order['course'] = $course['name_cn'];
			$CourseType = model('CourseType') -> getOne($course['type']);
			$order['course_type'] = $CourseType['name'];
			$order['start_time'] = date("Y-m-d H:i", $order['time']);
			if ($order['state'] == 1) {
				$Evaluate = model('Evaluate') -> getOne($order['id']);
				$order['score'] = $Evaluate['score'];
				$order['score_info'] = $Evaluate['information'];
			}
			if ($order['reason_type'] == 0) {
				$order['reason_type'] = '学生取消';
			} else if ($order['reason_type'] == 1) {
				$order['reason_type'] = '老师取消';
			} else {
				$order['reason_type'] = '管理员取消';
			}
			if ($order['time'] < time()) {
				$order['cancel'] = 0;
			} else {
				$order['cancel'] = 1;
			}
			$priceorder = model('PriceOrder') -> getOne($order['payid']);
			$order['PriceType'] = $priceorder['type'];
			$this -> assign('student', $student);
			$this -> assign('teacher', $teacher);
			$this -> assign('order', $order);
			return $this -> fetch();
		}
	}

	//订单JSON
	public function order_list() {
		if (session('t_id') == null) {
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
			$list = model('Order') -> getList(session('t_id'), $data['p'], $data['state'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$student = model('Student') -> getOne($list[$i]['student_id']);
				$list[$i]['avatar'] = $student['avatar'];
				$list[$i]['nickName'] = $student['nickName'];
				$list[$i]['wx'] = $student['wx'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$list[$i]['course'] = $course['name_cn'];
				$CourseType = model('CourseType') -> getOne($course['type']);
				$list[$i]['course_type'] = $CourseType['name'];
				$list[$i]['start_time'] = date("Y-m-d H:i:s", $list[$i]['time']);
				if ($list[$i]['state'] == 1) {
					$Evaluate = model('Evaluate') -> getOne($list[$i]['id']);
					$list[$i]['score'] = $Evaluate['score'];
				}
				$priceorder = model('PriceOrder') -> getOne($list[$i]['payid']);
				$list[$i]['PriceType'] = $priceorder['type'];
			}
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	//我的账户
	public function account() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			$teacher = model('Teacher') -> getOne(session('t_id'));
			if (!isset($data['day']) || strtotime($data['day']) > time()) {
				$theday = date("Y-m-1", time());
			} else {
				$theday = date("Y-m-1", strtotime($data['day']));
			}
			$day = date("Y-m-d", strtotime(date('Y-m-01', strtotime($theday))));
			$day2 = date("Y-m-d", strtotime(date('Y-m-01', strtotime($theday)) . ' +1 month') - 1);
			$day3 = date("Y-m-d", strtotime(date('Y-m-06', strtotime($theday)) . ' +1 month'));
			$thedata['order_num'] = model('Order') -> getSettlementCount(session('t_id'), strtotime($day), strtotime($day2) + 86400 - 1);
			$thedata['recharge_num1'] = model('Balance') -> getSettlementCount(session('t_id'), strtotime($day), strtotime($day2) + 86400 - 1, 1);
			$thedata['recharge_num0'] = model('Balance') -> getSettlementCount(session('t_id'), strtotime($day), strtotime($day2) + 86400 - 1, 0);
			$thedata['recharge_num'] = $thedata['recharge_num1'] - $thedata['recharge_num0'];
			$thedata['num'] = $thedata['order_num'] + $thedata['recharge_num'];
			$thedata['time'] = strtotime($day);
			$thedata['ty_num'] = 0;
			if ($thedata['order_num'] != 0 || $thedata['recharge_num'] != 0) {
				$oldlist = model('Order') -> getSettlementList(session('t_id'), strtotime($day), strtotime($day2) + 86400 - 1);
				for ($s = 0; $s < count($oldlist); $s++) {
					$priceorder = model('PriceOrder') -> getOne($oldlist[$s]['payid']);
					if ($priceorder['type'] == 4) {
						$thedata['ty_num'] = $thedata['ty_num'] + 1;
					}
				}
			}
			$thedata['order_num'] = $thedata['order_num'] - $thedata['ty_num'];

			for ($i = 0; $i < 12; $i++) {
				$listendday = date("Y-m-d", strtotime(date('Y-m-1', time()) . ' -' . $i . ' month'));
				$listtheday = date("Y-m-d", strtotime(date('Y-m-1', time()) . ' -' . ($i - 1) . ' month') - 1);
				if(strtotime($teacher['create_time']) < strtotime($listtheday)&&strtotime($teacher['create_time']) > strtotime($listendday)){
					$SettlementLogList[$i]['endday'] = date("Y-m-d", strtotime($teacher['create_time']));
					$SettlementLogList[$i]['theday'] = $listtheday;
					$SettlementLogList[$i]['day'] = $listendday;
				}elseif(strtotime($teacher['create_time']) < strtotime($listendday)) {
					$SettlementLogList[$i]['endday'] = $listendday;
					$SettlementLogList[$i]['theday'] = $listtheday;
					$SettlementLogList[$i]['day'] = $listendday;
				}else{
					break;
				}
			}
			$this -> assign('Teacher', $thedata);
			$this -> assign('SettlementLogList', $SettlementLogList);
			$this -> assign('day', $day);
			$this -> assign('day2', $day2);
			$this -> assign('day3', $day3);
			return $this -> fetch();
		}
	}

	//修改密码
	public function password() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$data['password'] = $postdata['password'];
			$Teacher = model('Teacher') -> updateData(session('t_id'), $data);
			if ($Teacher) {
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
