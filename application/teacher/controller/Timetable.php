<?php
namespace app\teacher\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Timetable extends Controller {

	//我的课表
	public function index() {
		if ( request() -> isGET()) {
			if (session('t_id') == null) {
				if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
					list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("TEACHERappid") . '&secret=' . config("TEACHERsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
					$bodys = json_decode($body, true);
					session('teacher_openid', $bodys['openid']);
					$teacher = model('Teacher') -> getOpenID($bodys['openid']);
					if ($teacher) {
						session('t_id', $teacher['id']);
						$this -> redirect('timetable/index');
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
				$list = model('Order') -> getList(session('t_id'), $data['p'], '0', 'asc');
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
					$priceorder = model('PriceOrder') -> getOne($list[$i]['payid']);
					$list[$i]['PriceType'] = $priceorder['type'];
				}
				$this -> assign('list', $list);
				$this -> assign('s', 0);
				return $this -> fetch();
			}
		}
	}

	//取消订单
	public function cancel() {
		if (session('t_id') == null) {
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
				if ($Order['state'] == 0 && $Order['time'] > time()) {
					$postdata['state'] = 2;
					$postdata['reason_type'] = 1;
					$postdata['reason'] = $data['reason'];
					$Order = model('Order') -> updateData($data['id'], $postdata);
					if ($Order) {
						$curriculum = model('Curriculum') -> Reset($Order['teacher_id'], $Order['time']);
						$priceorder = model('PriceOrder') -> getOne($Order['payid']);
						$podata['used_class'] = $priceorder['used_class'] - 1;
						model('PriceOrder') -> updateData($Order['payid'],$podata);
						$info['state'] = 0;
						model('Curriculum') -> updateTeacherData(session('t_id'), $Order['time'], $info);
						$student = model('Student') -> getOne($Order['student_id']);
						$wxcancel['orderid'] = $Order['id'];
						$wxcancel['type'] = 1;
						action('Base/wxcancel', $wxcancel);
						if ($student['language'] == 0) {
							action('en/Base/wxcancel', $wxcancel);
						} else {
							action('ko/Base/wxcancel', $wxcancel);
							action('ko/Base/kakaocancel', $wxcancel);
						}
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
