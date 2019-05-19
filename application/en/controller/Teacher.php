<?php
namespace app\en\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Teacher extends Controller {

	//老师列表
	public function index() {
		if (session('en_id') == null) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("ENappid") . '&secret=' . config("ENsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
				$bodys = json_decode($body, true);
				session('en_openid', $bodys['openid']);
				$student = model('Student') -> getOpenID($bodys['openid']);
				if ($student && $student['language'] == 0) {
					session('en_id', $student['id']);
					$this -> redirect('teacher/index');
				}
			}
			$this -> redirect('my/login');
		} else {
			$student = model('Student') -> getOne(session('en_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($student['openid'] != session('en_openid')) {
					session('en_id', null);
					session('en_openid', null);
					$this -> redirect('my/login');
				}
			}
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
			$data = input('param.');
			if (isset($data['search'])) {
				$list = model('Teacher') -> getSearchList($data['search'], 1, 'desc');
			} else {
				$list = model('Teacher') -> getList(1, 'desc');
			}
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
			}
			$this -> assign('list', $list);
			return $this -> fetch();
		}
	}

	//筛选
	public function filter() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		} else {
			if ( request() -> isGET()) {
				$data = input('param.');
				if (!isset($data['week']) && !isset($data['time'])) {
					return $this -> fetch();
				}
				$teacherlist = model('Teacher') -> getList(1, 'desc');
				date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('en_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
				$interval = array();
				$week = array();
				$list1 = array();
				$list2 = array();
				if (isset($data['week'])) {
					for ($i = 0; $i < count($data['week']); $i++) {
						array_push($week, $data['week'][$i]);
					}
					for ($i = 0; $i < count($teacherlist); $i++) {
						$Curriculum = model('Curriculum') -> getWeek($teacherlist[$i]['id'], time(), implode(",", $week));
						if ($Curriculum) {
							array_push($list1, $teacherlist[$i]);
						}
					}
					$list = $list1;
				}
				if (isset($data['time'])) {
					for ($i = 0; $i < count($data['time']); $i++) {
						$theinterval = explode(",", $data['time'][$i]);
						for ($s = 0; $s < count($theinterval); $s++) {
							$theint = $theinterval[$s] - 16 + $timezone['time'] * 2;
							$theinterval[$s] = $theinterval[$s] + $theinterval[$s] - $theint;
							if ($theinterval[$s] > 47) {
								$theinterval[$s] = $theinterval[$s] - 47;
							}
							if ($theinterval[$s] < 0) {
								$theinterval[$s] = $theinterval[$s] + 47;
							}
							array_push($interval, $theinterval[$s]);
						}
					}
					if (isset($data['week'])) {
						$teacherlist = $list1;
					}
					for ($i = 0; $i < count($teacherlist); $i++) {
						if (isset($data['week'])) {
							$Curriculum = model('Curriculum') -> getWeekTime($teacherlist[$i]['id'], time(), implode(",", $interval), implode(",", $week));
						} else {
							$Curriculum = model('Curriculum') -> getTime($teacherlist[$i]['id'], time(), implode(",", $interval));
						}
						if ($Curriculum) {
							array_push($list2, $teacherlist[$i]);
						}
					}
					$list = $list2;
				}
				if (!isset($data['time']) && !isset($data['week'])) {
					$list = $teacherlist;
				}
				for ($i = 0; $i < count($list); $i++) {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
				}
				$this -> assign('list', $list);
				return $this -> fetch('index');
			}
		}
	}

	//老师信息
	public function info() {
		if ( request() -> isGET()) {
			if (session('en_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['tid'])) {
					$info['info'] = '教师id错误';
					$this -> redirect('base/close', $info);
				}
				$teacher = model('Teacher') -> getOne($data['tid']);
				$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
				$teacher['evaluate_num'] = model('Evaluate') -> getCount($data['tid']);
				$evaluatelist = model('Evaluate') -> getList($data['tid'], 1, 'desc');
				for ($i = 0; $i < count($evaluatelist); $i++) {
					if ($evaluatelist[$i]['student_id'] != 0) {
						$Student = model('Student') -> getOne($evaluatelist[$i]['student_id']);
						$evaluatelist[$i]['avatar'] = $Student['avatar'];
						$evaluatelist[$i]['nickName'] = $Student['nickName'];
					} else {
						$evaluatelist[$i]['avatar'] = $evaluatelist[$i]['student_avatar'];
						$evaluatelist[$i]['nickName'] = $evaluatelist[$i]['student_nickName'];
					}
				}
				date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('en_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
				$day['a'] = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
				$day['b'] = date('Y-m-d', (time() + (2 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
				$day['c'] = date('Y-m-d', (time() + (3 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
				$day['d'] = date('Y-m-d', (time() + (4 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
				$day['e'] = date('Y-m-d', (time() + (5 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
				$day['f'] = date('Y-m-d', (time() + (6 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
				$day['g'] = date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
				$nowweek = date("w", time() + $time);
				if ($nowweek == 0) {
					$nowweek = 7;
				}
				$nowday = date("Y-m-d", time() + $time);
				$this -> assign('day', $day);
				$this -> assign('nowweek', $nowweek);
				$this -> assign('nowday', $nowday);
				$this -> assign('teacher', $teacher);
				$this -> assign('evaluatelist', $evaluatelist);
				return $this -> fetch();
			}
		}
	}

	//用户评价JSON
	public function evaluate_list() {
		if (session('en_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('Evaluate') -> getList($data['teacher_id'], $data['p'], 'desc');
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

	//课程
	public function curriculum() {
		if ( request() -> isGET()) {
			if (session('en_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['tid'])) {
					$info['info'] = '教师id错误';
					$this -> redirect('base/close', $info);
				}
				if (!isset($data['date'])) {
					$info['info'] = '日期未传入';
					$this -> redirect('base/close', $info);
				}
				date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('en_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
				$time1 = strtotime($data['date']) - $time;
				if (strtotime(date("Y-m-d", time() + $time)) == strtotime($data['date'])) {
					$time1 = time();
				}
				$time2 = strtotime($data['date']) - $time + 86350;
				$list = model('Curriculum') -> getList($data['tid'], $time1, $time2, 'asc');
				for ($i = 0; $i < count($list); $i++) {
					$list[$i]['interval'] = date("H:i", $list[$i]['time'] + $time);
					$list[$i]['time1'] = $time1;
					$list[$i]['time2'] = $time2;
					$list[$i]['time3'] = date("Y-m-d H:i:s", time());
					$list[$i]['time4'] = date("Y-m-d", time() + $time);
					$list[$i]['time5'] = $data['date'];
					$list[$i]['time6'] = $timezone['time'];
				}
				$return['result'] = TRUE;
				$return['data'] = $list;
				return json($return);
			}
		}
	}

	//报名
	public function enroll() {
		if ( request() -> isGET()) {
			if (session('en_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['id'])) {
					$info['info'] = 'id错误';
					$this -> redirect('base/close', $info);
				}
				$my = model('Student') -> getOne(session('en_id'));
				if (time() < $my['validity_start'] || time() > $my['validity_end']) {
					$info['info'] = '课程券已过期,请联系管理员';
					$this -> redirect('base/close', $info);
				}
				$BalanceCount = model('Balance') -> getBalanceCount(session('en_id'), 0);
				if ($BalanceCount <= 0) {
					$info['info'] = '余额不足';
					$this -> redirect('base/close', $info);
				}
				$Curriculum = model('Curriculum') -> getId($data['id']);
				if ($Curriculum['time'] < time() + 1800) {
					$info['info'] = 'Booking&nbsp;class&nbsp;can&nbsp;be&nbsp;made&nbsp;up&nbsp;to&nbsp;30&nbsp;minutes&nbsp;before&nbsp;begins.';
					$this -> redirect('base/close', $info);
				}
				if ($Curriculum['state'] != 0) {
					$info['info'] = '课程已被其他人占用/预约';
					$this -> redirect('base/close', $info);
				}
				date_default_timezone_set('UTC');
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$Curriculum['thetime'] = date('Y-m-d H:i', $Curriculum['time'] + ($timezone['time'] * 3600));
				$time1 = strtotime(date('Y-m-d', $Curriculum['time'] + ($timezone['time'] * 3600)));
				$time2 = $time1 + 86400;
				$limit = model('Order') -> getDayNum(session('en_id'), $time1, $time2);
				if ($limit >= 3) {
					$info['info'] = 'Class&nbsp;is&nbsp;only&nbsp;available&nbsp;three&nbsp;times&nbsp;a&nbsp;day.';
					$this -> redirect('base/close', $info);
				}
				$CourseTypeOld = model('CourseType') -> getList();
				$CourseType = array();
				for ($i = 0; $i < count($CourseTypeOld); $i++) {
					$num = model('Course') -> getNum($CourseTypeOld[$i]['id']);
					if ($num > 0) {
						array_push($CourseType, $CourseTypeOld[$i]);
					}
				}
				$this -> assign('id', $data['id']);
				$this -> assign('Curriculum', $Curriculum);
				$this -> assign('CourseType', $CourseType);
				return $this -> fetch();
			}
		} else if ( request() -> isPost()) {
			$data = input('post.');
			if (!isset($data['id'])) {
				$info['info'] = 'id未传入';
				$this -> redirect('base/close', $info);
			}
			if (!isset($data['course'])) {
				$info['info'] = 'course未传入';
				$this -> redirect('base/close', $info);
			}
			if (!isset($data['class'])) {
				$info['info'] = 'class未传入';
				$this -> redirect('base/close', $info);
			}
			$Curriculum = model('Curriculum') -> getId($data['id']);
			if ($Curriculum['state'] != 0) {
				$info['info'] = '此时间已被预订';
				$this -> redirect('base/close', $info);
			}
			if ($Curriculum['time'] < time() + 1800) {
				$info['info'] = 'Booking&nbsp;class&nbsp;can&nbsp;be&nbsp;made&nbsp;up&nbsp;to&nbsp;30&nbsp;minutes&nbsp;before&nbsp;begins.';
				$this -> redirect('base/close', $info);
			}
			$BalanceCount = model('Balance') -> getBalanceCount(session('en_id'), 0);
			if ($BalanceCount <= 0) {
				$info['info'] = '余额不足';
				$this -> redirect('base/close', $info);
			}
			$theCurriculum['state'] = 1;
			$theCurriculum['student_id'] = session('en_id');
			$Curriculum = model('Curriculum') -> updateData($data['id'], $theCurriculum);
			$post['student_id'] = session('en_id');
			$post['teacher_id'] = $Curriculum['teacher_id'];
			$post['course'] = $data['course'];
			$post['class'] = $data['class'];
			$post['day'] = $Curriculum['day'];
			$post['interval'] = $Curriculum['interval'];
			$post['time'] = $Curriculum['time'];
			$student = model('Student') -> getOne(session('en_id'));
			$post['time_zone'] = $student['time_zone'];
			$Order = model('Order') -> addData($post);
			if ($Order) {
				$balance['user_id'] = session('en_id');
				$balance['user_type'] = 0;
				$balance['type'] = 0;
				$balance['num'] = 1;
				$balance['balance_num'] = $BalanceCount - 1;
				model('Balance') -> addData($balance);
				$info['tid'] = $Curriculum['teacher_id'];
				action('teacher/Base/wxinfo', $Order['id']);
				action('Base/wxinfo', $Order['id']);
				$return['result'] = TRUE;
				$return['data'] = 1;
			} else {
				$return['result'] = FALSE;
				$return['data'] = 0;
			}
			return json($return);
		}
	}

	//报名检测
	public function enroll_validate() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['id'])) {
					$info['info'] = 'id错误';
					$this -> redirect('base/close', $info);
				}
				$my = model('Student') -> getOne(session('ko_id'));
				$BalanceCount = model('Balance') -> getBalanceCount(session('ko_id'), 0);
				if ($BalanceCount <= 0) {
					$return['data'] = 2;
					return json($return);
				}
				$Curriculum = model('Curriculum') -> getId($data['id']);
				if ($Curriculum['time'] < time() + 1800) {
					$return['data'] = 4;
					return json($return);
				}
				if ($Curriculum['state'] != 0) {
					$return['data'] = 5;
					return json($return);
				}
				date_default_timezone_set('UTC');
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$Curriculum['thetime'] = date('Y-m-d H:i', $Curriculum['time'] + ($timezone['time'] * 3600));
				$time1 = strtotime(date('Y-m-d', $Curriculum['time'] + ($timezone['time'] * 3600)));
				$time2 = $time1 + 86400;
				$limit = model('Order') -> getDayNum(session('ko_id'), $time1, $time2);
				if ($limit >= 3) {
					$return['data'] = 3;
					return json($return);
				}
				$return['data'] = 1;
				return json($return);
			}
		}
	}

	//获取教材
	public function course() {
		if ( request() -> isGET()) {
			if (session('en_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['type'])) {
					$return['result'] = FALSE;
					$return['msg'] = 'type未传入';
					return json($return);
				}
				$list = model('Course') -> getList($data['type']);
				$return['result'] = TRUE;
				$return['data'] = $list;
				return json($return);
			}
		}
	}

}
