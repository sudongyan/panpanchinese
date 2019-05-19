<?php
namespace app\ko\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Timetable extends Controller {

	//我的课表
	public function index() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
				if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				$code = input('param.code');
				if (!isset($code)) {
					$this -> redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx94cd5f68fa803bb2&redirect_uri=http://m.panpanchinese.cn/ko/timetable&response_type=code&scope=snsapi_base&state=1#wechat_redirect');
				}
					list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("KOappid") . '&secret=' . config("KOsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
					$bodys = json_decode($body, true);
					session('ko_openid', $bodys['openid']);
					$student = model('Student') -> getOpenID($bodys['openid']);
					if ($student && $student['language'] == 1) {
						session('ko_id', $student['id']);
						$this -> redirect('timetable/index');
					}
				}
				$this -> redirect('my/login');
			} else {
				if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
					$student = model('Student') -> getOne(session('ko_id'));
					if ($student['openid'] != session('ko_openid')) {
						session('ko_id', null);
						session('ko_openid', null);
						$this -> redirect('my/login');
					}
				}
				$data = input('param.');
				if (!isset($data['p'])) {
					$data['p'] = 1;
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
				$list = model('Order') -> getList(session('ko_id'), $data['p'], '0', 'desc');
				date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
				for ($i = 0; $i < count($list); $i++) {
					$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
					$list[$i]['tid'] = $list[$i]['teacher_id'];
					$list[$i]['avatar'] = $teacher['avatar'];
					$list[$i]['nickName'] = $teacher['nickName'];
					$list[$i]['wx'] = $teacher['wx'];
					$course = model('Course') -> getOne($list[$i]['course']);
					$list[$i]['course'] = $course['name_ko'];
					$CourseType = model('CourseType') -> getOne($course['type']);
					$list[$i]['course_type'] = $CourseType['name'];
					$list[$i]['start_time'] = date("Y-m-d H:i", $list[$i]['time'] + $time);
					if ($list[$i]['time'] < time() + 7200) {
						$list[$i]['cancel'] = 0;
					} else {
						$list[$i]['cancel'] = 1;
					}
				}
				$this -> assign('list', $list);
				$this -> assign('s', 0);
				return $this -> fetch();
			}
		}
	}

	//待评价
	public function evaluate() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['p'])) {
					$data['p'] = 1;
				}
				$list = model('Order') -> getList(session('ko_id'), $data['p'], '3', 'desc');
				date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
				for ($i = 0; $i < count($list); $i++) {
					$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
					$list[$i]['tid'] = $list[$i]['teacher_id'];
					$list[$i]['avatar'] = $teacher['avatar'];
					$list[$i]['nickName'] = $teacher['nickName'];
					$list[$i]['wx'] = $teacher['wx'];
					$course = model('Course') -> getOne($list[$i]['course']);
					$list[$i]['course'] = $course['name_ko'];
					$CourseType = model('CourseType') -> getOne($course['type']);
					$list[$i]['course_type'] = $CourseType['name'];
					$list[$i]['start_time'] = date("Y-m-d H:i", $list[$i]['time'] + $time);
				}
				$this -> assign('list', $list);
				$this -> assign('s', 3);
				return $this -> fetch();
			}
		}
	}

	//评价
	public function evaluate_add() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['order_id'])) {
				$info['info'] = '订单编号未传入';
				$this -> redirect('base/close', $info);
			}
			$order = model('Order') -> getOne($data['order_id']);
			if (!$order || $order['state'] != 3) {
				$info['info'] = '订单编号错误';
				$this -> redirect('base/close', $info);
			}
			if ($order['student_id'] != session('ko_id')) {
				$info['info'] = '非本人订单';
				$this -> redirect('base/close', $info);
			}
			$this -> assign('order_id', $data['order_id']);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$data = input('post.');
			if (!isset($data['order_id']) || !isset($data['score'])) {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
			$order = model('Order') -> getOne($data['order_id']);
			if (!$order || $order['state'] != 3) {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
			if ($order['student_id'] != session('ko_id')) {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
			$add['order_id'] = $order['id'];
			$add['student_id'] = $order['student_id'];
			$add['teacher_id'] = $order['teacher_id'];
			$add['score'] = $data['score'];
			$add['information'] = $data['information'];
			model('Evaluate') -> addData($add);
			$updateOrder['state'] = 1;
			model('Order') -> updateData($order['id'], $updateOrder);
			$updateTeacher['score'] = round( model('Evaluate') -> evaluate($order['teacher_id']), 1);
			model('Teacher') -> updateData($order['teacher_id'], $updateTeacher);
			$return['result'] = TRUE;
			$return['data'] = 1;
			return json($return);
		}
	}

	//取消订单
	public function cancel() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		} else {
			if ( request() -> isPost()) {
				$data = input('post.');
				if (!isset($data['id'])) {
					$return['result'] = FALSE;
					$return['data'] = 0;
					return json($return);
				}
				$Order = model('Order') -> getOne($data['id']);
				if ($Order['state'] == 0 && $Order['time'] > time() + 7200) {
					$postdata['state'] = 2;
					$postdata['reason_type'] = 0;
					$postdata['reason'] = $data['reason'];
					$Order = model('Order') -> updateData($data['id'], $postdata);
					if ($Order) {
						$curriculum = model('Curriculum') -> Reset($Order['teacher_id'], $Order['time']);
						$priceorder = model('PriceOrder') -> getOne($Order['payid']);
						$podata['used_class'] = $priceorder['used_class'] - 1;
						model('PriceOrder') -> updateData($Order['payid'],$podata);
						$info['state'] = 0;
						model('Curriculum') -> updateTeacherData($Order['teacher_id'], $Order['time'], $info);
						$wxcancel['orderid'] = $Order['id'];
						$wxcancel['type'] = 0;
						action('teacher/Base/wxcancel', $wxcancel);
						action('Base/wxcancel', $wxcancel);
						action('Base/kakaocancel', $wxcancel);
						$return['result'] = TRUE;
						$return['data'] = 1;
						return json($return);
					}
				}
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			} else {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
		}
	}

}
