<?php
namespace app\ko\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Teacher extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
	}

	//老师列表
	public function index() {
		if (session('ko_id') == null) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				$code = input('param.code');
				if (!isset($code)) {
					$this -> redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx94cd5f68fa803bb2&redirect_uri=http://m.panpanchinese.cn/ko/teacher&response_type=code&scope=snsapi_base&state=1#wechat_redirect');
				}
				list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("KOappid") . '&secret=' . config("KOsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
				$bodys = json_decode($body, true);
				session('ko_openid', $bodys['openid']);
				$student = model('Student') -> getOpenID($bodys['openid']);
				if ($student && $student['language'] == 1) {
					session('ko_id', $student['id']);
					$this -> redirect('teacher/index');
				}
			}
			$data = input('param.');
			if (isset($data['search'])) {
				$list = model('Teacher') -> getSearchList($data['search'], 1, 'desc');
			} else {
				if (!isset($data['order'])) {
					$data['order'] = 'asc';
				}
				$clist = model('Curriculum') -> getTimeOrder($data['order']);
				$tidlist = array();
				for ($i = 0; $i < count($clist); $i++) {
					array_push($tidlist, $clist[$i]['teacher_id']);
				}
				$alllist = model('Teacher') -> getList(1, 'desc');
				for ($i = 0; $i < count($alllist); $i++) {
					array_push($tidlist, $alllist[$i]['id']);
				}
				$tidlist = array_merge(array_unique($tidlist));
				$list = array();
				for ($i = 0; $i < count($tidlist); $i++) {
					$teacher = model('Teacher') -> getOne($tidlist[$i]);
					if ($teacher && $teacher['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction_en']);
						} else {
							$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
						}
						$teacher['tag'] = str_replace("'", "", $teacher['tag']);
						$arr = explode(",", $teacher['tag']);
						$teacher['taglist'] = '';
						for ($a = 0; $a < count($arr); $a++) {
							$tag = model('TeacherTag') -> getOne($arr[$a]);
							if ($tag['state'] == 1) {
								if (cookie('think_var') == 'en-us') {
									$thetagtext = $tag['name_en'];
								} else if (cookie('think_var') == 'zh-cn') {
									$thetagtext = $tag['name_cn'];
								} else {
									$thetagtext = $tag['name_ko'];
								}
								$teacher['taglist'] = $teacher['taglist'] . '#' . $thetagtext . ' ';
							}
						}
						array_push($list, $teacher);
					}
				}
			}
			$teacherlist = array();
			for ($i = 0; $i < count($list); $i++) {
				if (cookie('think_var') == 'en-us') {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction_en']);
				} else {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
				}
				$list[$i]['tag'] = str_replace("'", "", $list[$i]['tag']);
				$arr = explode(",", $list[$i]['tag']);
				$list[$i]['taglist'] = '';
				for ($a = 0; $a < count($arr); $a++) {
					$tag = model('TeacherTag') -> getOne($arr[$a]);
					if ($tag['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$thetagtext = $tag['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$thetagtext = $tag['name_cn'];
						} else {
							$thetagtext = $tag['name_ko'];
						}
						$list[$i]['taglist'] = $list[$i]['taglist'] . '#' . $thetagtext . ' ';
					}
				}
				if (isset($data['tag'])) {
					array_unshift($arr, 0);
					$tag = $data['tag'];
					$tag_v = 1;
					for ($s = 0; $s < count($tag); $s++) {
						$thetag = array_search($tag[$s], $arr);
						if (!$thetag && $tag_v == 1) {
							$tag_v = 0;
						}
					}
				}
				if (isset($data['tag']) && $tag_v == 1 || !isset($data['tag'])) {
					array_push($teacherlist, $list[$i]);
				}
			}
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('list', $teacherlist);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webindex');
			}
		} else {
			$student = model('Student') -> getOne(session('ko_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($student['openid'] != session('ko_openid')) {
					session('ko_id', null);
					session('ko_openid', null);
					$this -> redirect('my/login');
				}
			}
			$data = input('param.');
			if (isset($data['search'])) {
				$list = model('Teacher') -> getSearchList($data['search'], 1, 'desc');
			} else {
				if (!isset($data['order'])) {
					$data['order'] = 'asc';
				}
				$clist = model('Curriculum') -> getTimeOrder($data['order']);
				$tidlist = array();
				for ($i = 0; $i < count($clist); $i++) {
					array_push($tidlist, $clist[$i]['teacher_id']);
				}
				$alllist = model('Teacher') -> getList(1, 'desc');
				for ($i = 0; $i < count($alllist); $i++) {
					array_push($tidlist, $alllist[$i]['id']);
				}
				$tidlist = array_merge(array_unique($tidlist));
				$list = array();
				for ($i = 0; $i < count($tidlist); $i++) {
					$teacher = model('Teacher') -> getOne($tidlist[$i]);
					if ($teacher && $teacher['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction_en']);
						} else {
							$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
						}
						$teacher['tag'] = str_replace("'", "", $teacher['tag']);
						$arr = explode(",", $teacher['tag']);
						$teacher['taglist'] = '';
						for ($a = 0; $a < count($arr); $a++) {
							$tag = model('TeacherTag') -> getOne($arr[$a]);
							if ($tag['state'] == 1) {
								if (cookie('think_var') == 'en-us') {
									$thetagtext = $tag['name_en'];
								} else if (cookie('think_var') == 'zh-cn') {
									$thetagtext = $tag['name_cn'];
								} else {
									$thetagtext = $tag['name_ko'];
								}
								$teacher['taglist'] = $teacher['taglist'] . '#' . $thetagtext . ' ';
							}
						}
						array_push($list, $teacher);
					}
				}
			}
			$teacherlist = array();
			for ($i = 0; $i < count($list); $i++) {
				if (cookie('think_var') == 'en-us') {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction_en']);
				} else {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
				}
				$list[$i]['tag'] = str_replace("'", "", $list[$i]['tag']);
				$arr = explode(",", $list[$i]['tag']);
				$list[$i]['taglist'] = '';
				for ($a = 0; $a < count($arr); $a++) {
					$tag = model('TeacherTag') -> getOne($arr[$a]);
					if ($tag['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$thetagtext = $tag['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$thetagtext = $tag['name_cn'];
						} else {
							$thetagtext = $tag['name_ko'];
						}
						$list[$i]['taglist'] = $list[$i]['taglist'] . '#' . $thetagtext . ' ';
					}
				}
				if (isset($data['tag'])) {
					array_unshift($arr, 0);
					$tag = $data['tag'];
					$tag_v = 1;
					for ($s = 0; $s < count($tag); $s++) {
						$thetag = array_search($tag[$s], $arr);
						if (!$thetag && $tag_v == 1) {
							$tag_v = 0;
						}
					}
				}
				if (isset($data['tag']) && $tag_v == 1 || !isset($data['tag'])) {
					array_push($teacherlist, $list[$i]);
				}
			}
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('list', $teacherlist);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//日历
	public function index2() {
		if ( request() -> isGET()) {
			$data = input('param.');
			date_default_timezone_set('UTC');
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
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
			$nowtime = date("Y-m-d H:i:s", time() + $time);
			$this -> assign('day', $day);
			$this -> assign('nowweek', $nowweek);
			$this -> assign('nowday', $nowday);
			$this -> assign('nowtime', $nowtime);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webindex2');
			}
		}
	}

	//DAY筛选
	public function day() {
		if ( request() -> isGET()) {
			$data = input('param.');
			date_default_timezone_set('UTC');
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
			$teacheridlist = array();
			$time1 = strtotime($data['day']) + $time;
			$time2 = $time1 + 86400 - 1;
			$theint = $data['times'] - 16 + ($time / 3600) * 2;
			$data['times'] = $data['times'] + $data['times'] - $theint;
			if ($data['times'] > 47) {
				$data['times'] = $data['times'] - 47;
			}
			if ($data['times'] < 0) {
				$data['times'] = $data['times'] + 47;
			}
			$theint = $data['timee'] - 16 + ($time / 3600) * 2;
			$data['timee'] = $data['timee'] + $data['timee'] - $theint;
			if ($data['timee'] > 47) {
				$data['timee'] = $data['timee'] - 47;
			}
			if ($data['timee'] < 0) {
				$data['timee'] = $data['timee'] + 47;
			}
			$clist = model('Curriculum') -> getAllList($time1, $time2, 'asc');
			for ($i = 0; $i < count($clist); $i++) {
				if (!in_array($clist[$i]['teacher_id'], $teacheridlist) && $clist[$i]['interval'] >= $data['times'] && $clist[$i]['interval'] <= $data['timee']) {
					array_push($teacheridlist, $clist[$i]['teacher_id']);
				}
			}
			$list = model('Teacher') -> getSomeList(implode(",", $teacheridlist));
			$teacherlist = array();
			for ($i = 0; $i < count($list); $i++) {
				if (cookie('think_var') == 'en-us') {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction_en']);
				} else {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
				}
				$list[$i]['tag'] = str_replace("'", "", $list[$i]['tag']);
				$arr = explode(",", $list[$i]['tag']);
				$list[$i]['taglist'] = '';
				for ($a = 0; $a < count($arr); $a++) {
					$tag = model('TeacherTag') -> getOne($arr[$a]);
					if ($tag['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$thetagtext = $tag['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$thetagtext = $tag['name_cn'];
						} else {
							$thetagtext = $tag['name_ko'];
						}
						$list[$i]['taglist'] = $list[$i]['taglist'] . '#' . $thetagtext . ' ';
					}
				}
				if (isset($data['tag'])) {
					$tag = $data['tag'];
					$tag_v = 1;
					for ($s = 0; $s < count($tag); $s++) {
						$thetag = array_search($tag[$s], $arr);
						if (!$thetag && $tag_v == 1) {
							$tag_v = 0;
						}
					}
				}
				if (isset($data['tag']) && $tag_v == 1 || !isset($data['tag'])) {
					array_push($teacherlist, $list[$i]);
				}
			}
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('list', $teacherlist);
			if (action('Base/isMobile') == true) {
				return $this -> fetch('index');
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//date筛选
	public function thedate() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['time1']) && !isset($data['time2'])) {
				$taglist = model('TeacherTag') -> getAllList(1, 'desc');
				$this -> assign('taglist', $taglist);
				return $this -> fetch();
			}
			date_default_timezone_set('UTC');
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
			$teacheridlist = array();
			$time1 = strtotime($data['time1']) + $time;
			$time2 = strtotime($data['time2']) + $time + 86400 - 1;
			$theint = $data['times'] - 16 + ($time / 3600) * 2;
			$data['times'] = $data['times'] + $data['times'] - $theint;
			if ($data['times'] > 47) {
				$data['times'] = $data['times'] - 47;
			}
			if ($data['times'] < 0) {
				$data['times'] = $data['times'] + 47;
			}
			$theint = $data['timee'] - 16 + ($time / 3600) * 2;
			$data['timee'] = $data['timee'] + $data['timee'] - $theint;
			if ($data['timee'] > 47) {
				$data['timee'] = $data['timee'] - 47;
			}
			if ($data['timee'] < 0) {
				$data['timee'] = $data['timee'] + 47;
			}
			$clist = model('Curriculum') -> getAllList($time1, $time2, 'asc');
			for ($i = 0; $i < count($clist); $i++) {
				if (!in_array($clist[$i]['teacher_id'], $teacheridlist) && $clist[$i]['interval'] >= $data['times'] && $clist[$i]['interval'] <= $data['timee']) {
					array_push($teacheridlist, $clist[$i]['teacher_id']);
				}
			}
			$list = model('Teacher') -> getSomeList(implode(",", $teacheridlist));
			$teacherlist = array();
			for ($i = 0; $i < count($list); $i++) {
				if (cookie('think_var') == 'en-us') {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction_en']);
				} else {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
				}
				$list[$i]['tag'] = str_replace("'", "", $list[$i]['tag']);
				$arr = explode(",", $list[$i]['tag']);
				$list[$i]['taglist'] = '';
				for ($a = 0; $a < count($arr); $a++) {
					$tag = model('TeacherTag') -> getOne($arr[$a]);
					if ($tag['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$thetagtext = $tag['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$thetagtext = $tag['name_cn'];
						} else {
							$thetagtext = $tag['name_ko'];
						}
						$list[$i]['taglist'] = $list[$i]['taglist'] . '#' . $thetagtext . ' ';
					}
				}
				if (isset($data['tag'])) {
					$tag = $data['tag'];
					$tag_v = 1;
					for ($s = 0; $s < count($tag); $s++) {
						$thetag = array_search($tag[$s], $arr);
						if (!$thetag && $tag_v == 1) {
							$tag_v = 0;
						}
					}
				}
				if (isset($data['tag']) && $tag_v == 1 || !isset($data['tag'])) {
					array_push($teacherlist, $list[$i]);
				}
			}
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('list', $teacherlist);
			if (action('Base/isMobile') == true) {
				return $this -> fetch('index');
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//筛选
	public function filter() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['week']) && !isset($data['time'])) {
				return $this -> fetch();
			}
			$teacherlist = model('Teacher') -> getList(1, 'desc');
			date_default_timezone_set('UTC');
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
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
			$tlist = array();
			for ($i = 0; $i < count($list); $i++) {
				if (cookie('think_var') == 'en-us') {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction_en']);
				} else {
					$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $list[$i]['introduction']);
				}
				$list[$i]['tag'] = str_replace("'", "", $list[$i]['tag']);
				$arr = explode(",", $list[$i]['tag']);
				$list[$i]['taglist'] = '';
				for ($a = 0; $a < count($arr); $a++) {
					$tag = model('TeacherTag') -> getOne($arr[$a]);
					if ($tag['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$thetagtext = $tag['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$thetagtext = $tag['name_cn'];
						} else {
							$thetagtext = $tag['name_ko'];
						}
						$list[$i]['taglist'] = $list[$i]['taglist'] . '#' . $thetagtext . ' ';
					}
				}
				if (isset($data['tag'])) {
					$tag = $data['tag'];
					$tag_v = 1;
					for ($s = 0; $s < count($tag); $s++) {
						$thetag = array_search($tag[$s], $arr);
						if (!$thetag && $tag_v == 1) {
							$tag_v = 0;
						}
					}
				}
				if (isset($data['tag']) && $tag_v == 1 || !isset($data['tag'])) {
					array_push($tlist, $list[$i]);
				}
			}
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('list', $tlist);
			if (action('Base/isMobile') == true) {
				return $this -> fetch('index');
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//筛选
	public function thetag() {
		if ( request() -> isGET()) {
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			return $this -> fetch();
		}
	}

	//按时间排序
	public function timelist() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['order'])) {
				$data['order'] = 'asc';
			}
			$clist = model('Curriculum') -> getTimeOrder($data['order']);
			$tidlist = array();
			for ($i = 0; $i < count($clist); $i++) {
				array_push($tidlist, $clist[$i]['teacher_id']);
			}
			$tidlist = array_merge(array_unique($tidlist));
			$list = array();
			for ($i = 0; $i < count($tidlist); $i++) {
				$teacher = model('Teacher') -> getOne($tidlist[$i]);
				if ($teacher) {
					if (cookie('think_var') == 'en-us') {
						$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction_en']);
					} else {
						$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
					}
					$teacher['tag'] = str_replace("'", "", $teacher['tag']);
					$arr = explode(",", $teacher['tag']);
					$teacher['taglist'] = '';
					for ($a = 0; $a < count($arr); $a++) {
						$tag = model('TeacherTag') -> getOne($arr[$a]);
						if ($tag['state'] == 1) {
							if (cookie('think_var') == 'en-us') {
								$thetagtext = $tag['name_en'];
							} else if (cookie('think_var') == 'zh-cn') {
								$thetagtext = $tag['name_cn'];
							} else {
								$thetagtext = $tag['name_ko'];
							}
							$teacher['taglist'] = $teacher['taglist'] . '#' . $thetagtext . ' ';
						}
					}
					array_push($list, $teacher);
				}
			}
			$taglist = model('TeacherTag') -> getAllList(1, 'desc');
			$this -> assign('taglist', $taglist);
			$this -> assign('list', $list);
			if (action('Base/isMobile') == true) {
				return $this -> fetch('index');
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//老师信息
	public function info() {
		if ( request() -> isGET()) {
			if (action('Base/isMobile') == true) {
				if (session('ko_id') == null) {
					$this -> redirect('my/login');
				} else {
					$data = input('param.');
					if (!isset($data['tid'])) {
						$info['info'] = '教师id错误';
						$this -> redirect('base/close', $info);
					}
					$teacher = model('Teacher') -> getOne($data['tid']);
					if (cookie('think_var') == 'en-us') {
						$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction_en']);
					} else {
						$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
					}
					$teacher['tag'] = str_replace("'", "", $teacher['tag']);
					$arr = explode(",", $teacher['tag']);
					$teacher['taglist'] = '';
					for ($a = 0; $a < count($arr); $a++) {
						$tag = model('TeacherTag') -> getOne($arr[$a]);
						if ($tag['state'] == 1) {
							if (cookie('think_var') == 'en-us') {
								$thetagtext = $tag['name_en'];
							} else if (cookie('think_var') == 'zh-cn') {
								$thetagtext = $tag['name_cn'];
							} else {
								$thetagtext = $tag['name_ko'];
							}
							$teacher['taglist'] = $teacher['taglist'] . '#' . $thetagtext . ' ';
						}
					}
					$teacher['evaluate_num'] = model('Evaluate') -> getCount($data['tid']);
					$evaluatelist = model('Evaluate') -> getNotNullList($data['tid'], 1, 'desc');
					for ($i = 0; $i < count($evaluatelist); $i++) {
						if ($evaluatelist[$i]['student_id'] != 0) {
							$Student = model('Student') -> getOne($evaluatelist[$i]['student_id']);
							$evaluatelist[$i]['avatar'] = $Student['avatar'];
							$evaluatelist[$i]['nickName'] = $Student['nickName'];
							$order = model('Order') -> getOne($evaluatelist[$i]['order_id']);
							$Course = model('Course') -> getOne($order['course']);
							if (cookie('think_var') == 'en-us') {
								$evaluatelist[$i]['course'] = $Course['name_en'];
							} else if (cookie('think_var') == 'zh-cn') {
								$evaluatelist[$i]['course'] = $Course['name_cn'];
							} else {
								$evaluatelist[$i]['course'] = $Course['name_ko'];
							}
							if ($evaluatelist[$i]['course'] == null) {
								$evaluatelist[$i]['course'] = ' ';
							}
						} else {
							$evaluatelist[$i]['avatar'] = $evaluatelist[$i]['student_avatar'];
							$evaluatelist[$i]['nickName'] = $evaluatelist[$i]['student_nickName'];
							$evaluatelist[$i]['course'] = ' ';
						}
					}
					date_default_timezone_set('UTC');
					$my = model('Student') -> getOne(session('ko_id'));
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
					return $this -> fetch('info2');
				}
			} else {
				$data = input('param.');
				if (!isset($data['tid'])) {
					$info['info'] = '教师id错误';
					$this -> redirect('base/close', $info);
				}
				$teacher = model('Teacher') -> getOne($data['tid']);
				if (cookie('think_var') == 'en-us') {
					$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction_en']);
				} else {
					$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
				}
				$teacher['tag'] = str_replace("'", "", $teacher['tag']);
				$arr = explode(",", $teacher['tag']);
				$teacher['taglist'] = '';
				for ($a = 0; $a < count($arr); $a++) {
					$tag = model('TeacherTag') -> getOne($arr[$a]);
					if ($tag['state'] == 1) {
						if (cookie('think_var') == 'en-us') {
							$thetagtext = $tag['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$thetagtext = $tag['name_cn'];
						} else {
							$thetagtext = $tag['name_ko'];
						}
						$teacher['taglist'] = $teacher['taglist'] . '#' . $thetagtext . ' ';
					}
				}
				$teacher['evaluate_num'] = model('Evaluate') -> getCount($data['tid']);
				$evaluatelist = model('Evaluate') -> getNotNullList($data['tid'], 1, 'desc');
				for ($i = 0; $i < count($evaluatelist); $i++) {
					if ($evaluatelist[$i]['student_id'] != 0) {
						$Student = model('Student') -> getOne($evaluatelist[$i]['student_id']);
						$evaluatelist[$i]['avatar'] = $Student['avatar'];
						$evaluatelist[$i]['nickName'] = $Student['nickName'];
						$order = model('Order') -> getOne($evaluatelist[$i]['order_id']);
						$Course = model('Course') -> getOne($order['course']);
						if (cookie('think_var') == 'en-us') {
							$evaluatelist[$i]['course'] = $Course['name_en'];
						} else if (cookie('think_var') == 'zh-cn') {
							$evaluatelist[$i]['course'] = $Course['name_cn'];
						} else {
							$evaluatelist[$i]['course'] = $Course['name_ko'];
						}
						if ($evaluatelist[$i]['course'] == null) {
							$evaluatelist[$i]['course'] = ' ';
						}
					} else {
						$evaluatelist[$i]['avatar'] = $evaluatelist[$i]['student_avatar'];
						$evaluatelist[$i]['nickName'] = $evaluatelist[$i]['student_nickName'];
						$evaluatelist[$i]['course'] = ' ';
					}
				}
				date_default_timezone_set('UTC');
				if (session('ko_id') == null) {
					$time = $data['thetimezone'] * 3600;
				} else {
					$my = model('Student') -> getOne(session('ko_id'));
					$timezone = model('Timezone') -> getOne($my['time_zone']);
					$time = $timezone['time'] * 3600;
				}
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
				$nowtime = date("Y-m-d H:i:s", time() + $time);
				$this -> assign('day', $day);
				$this -> assign('nowweek', $nowweek);
				$this -> assign('nowday', $nowday);
				$this -> assign('nowtime', $nowtime);
				$this -> assign('teacher', $teacher);
				$this -> assign('evaluatelist', $evaluatelist);
				return $this -> fetch('webinfo');
			}
		}
	}

	//老师信息
	public function info2() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['tid'])) {
				$info['info'] = '教师id错误';
				$this -> redirect('base/close', $info);
			}
			$teacher = model('Teacher') -> getOne($data['tid']);
			if (cookie('think_var') == 'en-us') {
				$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction_en']);
			} else {
				$teacher['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
			}
			$teacher['tag'] = str_replace("'", "", $teacher['tag']);
			$arr = explode(",", $teacher['tag']);
			$teacher['taglist'] = '';
			for ($a = 0; $a < count($arr); $a++) {
				$tag = model('TeacherTag') -> getOne($arr[$a]);
				if ($tag['state'] == 1) {
					if (cookie('think_var') == 'en-us') {
						$thetagtext = $tag['name_en'];
					} else if (cookie('think_var') == 'zh-cn') {
						$thetagtext = $tag['name_cn'];
					} else {
						$thetagtext = $tag['name_ko'];
					}
					$teacher['taglist'] = $teacher['taglist'] . '#' . $thetagtext . ' ';
				}
			}
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
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
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
			$nowtime = date("Y-m-d H:i:s", time() + $time);
			$this -> assign('day', $day);
			$this -> assign('nowweek', $nowweek);
			$this -> assign('nowday', $nowday);
			$this -> assign('nowtime', $nowtime);
			$this -> assign('teacher', $teacher);
			$this -> assign('evaluatelist', $evaluatelist);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webinfo');
			}
		}
	}

	//用户评价JSON
	public function evaluate_list() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['new'])) {
				$data['new'] = 1;
			} else {
				$data['new'] = 0;
			}
			if ($data['new'] == 1) {
				$list = model('Evaluate') -> getList($data['teacher_id'], $data['p'], 'desc');
			} else {
				$list = model('Evaluate') -> getNotNullList($data['teacher_id'], $data['p'], 'desc');
			}
			for ($i = 0; $i < count($list); $i++) {
				if ($list[$i]['student_id'] != 0) {
					$student = model('Student') -> getOne($list[$i]['student_id']);
					$list[$i]['avatar'] = $student['avatar'];
					$list[$i]['nickName'] = $student['nickName'];
					$order = model('Order') -> getOne($list[$i]['order_id']);
					$Course = model('Course') -> getOne($order['course']);
					if (cookie('think_var') == 'en-us') {
						$list[$i]['course'] = $Course['name_en'];
					} else if (cookie('think_var') == 'zh-cn') {
						$list[$i]['course'] = $Course['name_cn'];
					} else {
						$list[$i]['course'] = $Course['name_ko'];
					}
					if ($list[$i]['course'] == null) {
						$list[$i]['course'] = ' ';
					}
				} else {
					$list[$i]['avatar'] = $list[$i]['student_avatar'];
					$list[$i]['nickName'] = $list[$i]['student_nickName'];
					$list[$i]['course'] = ' ';
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
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
			$time1 = strtotime($data['date']) - $time;
			if (strtotime(date("Y-m-d", time() + $time)) == strtotime($data['date'])) {
				$time1 = time();
			}
			$time2 = strtotime($data['date']) - $time + 86350;
			$list = model('Curriculum') -> getList($data['tid'], $time1, $time2, 'asc');
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['interval'] = date("H:i", $list[$i]['time'] + $time);
				$theinterval = explode(":", $list[$i]['interval']);
				$theinterval1 = intval($theinterval[0]) * 2;
				if ($theinterval[1] == '30') {
					$theinterval1 = $theinterval1 + 1;
				}
				$list[$i]['number'] = $theinterval1;
				$list[$i]['time1'] = $time1;
				$list[$i]['time2'] = $time2;
				$list[$i]['time3'] = date("Y-m-d H:i:s", time());
				$list[$i]['time4'] = date("Y-m-d", time() + $time);
				$list[$i]['time5'] = $data['date'];
				$list[$i]['time6'] = $timezone['time'];
				if (session('ko_id') == null) {
					$list[$i]['uid'] = 0;
				} else {
					$list[$i]['uid'] = session('ko_id');
				}
			}
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	//课程
	public function curriculum_all() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['date'])) {
				$info['info'] = '日期未传入';
				$this -> redirect('base/close', $info);
			}
			date_default_timezone_set('UTC');
			if (session('ko_id') == null) {
				$time = $data['thetimezone'] * 3600;
			} else {
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
			}
			$time1 = strtotime($data['date']) - $time;
			if (strtotime(date("Y-m-d", time() + $time)) == strtotime($data['date'])) {
				$time1 = time();
			}
			$time2 = strtotime($data['date']) - $time + 86350;
			$list1 = model('Curriculum') -> getAllList($time1, $time2, 'asc');
			$list = array();
			for ($i = 0; $i < count($list1); $i++) {
				$theteacher = model('Teacher') -> getOne($list1[$i]['teacher_id']);
				if ($theteacher['state'] == 1) {
					array_push($list, $list1[$i]);
				}
			}
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['interval'] = date("H:i", $list[$i]['time'] + $time);
				$theinterval = explode(":", $list[$i]['interval']);
				$theinterval1 = intval($theinterval[0]) * 2;
				if ($theinterval[1] == '30') {
					$theinterval1 = $theinterval1 + 1;
				}
				$list[$i]['number'] = $theinterval1;
				$list[$i]['time1'] = $time1;
				$list[$i]['time2'] = $time2;
				$list[$i]['time3'] = date("Y-m-d H:i:s", time());
				$list[$i]['time4'] = date("Y-m-d", time() + $time);
				$list[$i]['time5'] = $data['date'];
				$list[$i]['time6'] = $timezone['time'];
				if (session('ko_id') == null) {
					$list[$i]['uid'] = 0;
				} else {
					$list[$i]['uid'] = session('ko_id');
				}
			}
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	//报名
	public function enroll() {
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
				$allsurplus_class = 0;
				$plist = model('PriceOrder') -> getList(session('ko_id'), 1, 'desc');
				for ($i = 0; $i < count($plist); $i++) {
					if ($plist[$i]['end_time'] > time()) {
						$plist[$i]['surplus_class'] = $plist[$i]['class'] - $plist[$i]['used_class'];
						$allsurplus_class = $allsurplus_class + $plist[$i]['surplus_class'];
					}
				}
				if ($allsurplus_class <= 0) {
					$info['info'] = '余额不足';
					$this -> redirect('base/close', $info);
				}
				$Curriculum = model('Curriculum') -> getId($data['id']);
				if ($Curriculum['time'] < time() + 3600) {
					$info['info'] = '수업&nbsp;시작&nbsp;1시간&nbsp;전까지&nbsp;예약&nbsp;가능합니다.';
					$this -> redirect('base/close', $info);
				}
				if ($Curriculum['state'] != 0) {
					$info['info'] = '예약이 방금 마감 되었습니다.' . PHP_EOL . '다른 예약을 해주시기 바랍니다.';
					$this -> redirect('base/close', $info);
				}
				date_default_timezone_set('UTC');
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$Curriculum['thetime'] = date('Y-m-d H:i', $Curriculum['time'] + ($timezone['time'] * 3600));
				$time1 = strtotime(date('Y-m-d', $Curriculum['time'] + ($timezone['time'] * 3600)));
				$time2 = $time1 + 86400;
				$limit = model('Order') -> getDayNum(session('ko_id'), $time1, $time2);
				/*
				 if ($limit >= 3) {
				 $info['info'] = '수업&nbsp;예약은&nbsp;하루&nbsp;3회까지&nbsp;가능합니다.';
				 $this -> redirect('base/close', $info);
				 }
				 */

				$CourseTypeOld = model('CourseType') -> getList();
				$CourseType = array();
				for ($i = 0; $i < count($CourseTypeOld); $i++) {
					$num = model('Course') -> getNum($CourseTypeOld[$i]['id']);
					if ($num > 0) {
						array_push($CourseType, $CourseTypeOld[$i]);
					}
				}
				$demand = model('OrderDemand') -> getList('asc');
				$this -> assign('id', $data['id']);
				$this -> assign('Curriculum', $Curriculum);
				$this -> assign('CourseType', $CourseType);
				$this -> assign('demand', $demand);
				if (action('Base/isMobile') == true) {
					return $this -> fetch();
				} else {
					return $this -> fetch('webenroll');
				}
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
				$return['result'] = FALSE;
				$return['data'] = 0;
				$return['msg'] = '此时间已被预订';
				return json($return);
			}
			if ($Curriculum['time'] < time() + 3600) {
				$info['info'] = '수업 시작 1시간 전까지 예약 가능합니다.';
				$this -> redirect('base/close', $info);
			}
			$allsurplus_class = 0;
			$plist = model('PriceOrder') -> getList(session('ko_id'), 1, 'desc');
			for ($i = 0; $i < count($plist); $i++) {
				if ($plist[$i]['end_time'] > time()) {
					$plist[$i]['surplus_class'] = $plist[$i]['class'] - $plist[$i]['used_class'];
					$allsurplus_class = $allsurplus_class + $plist[$i]['surplus_class'];
				}
			}
			$payid = 0;
			if ($allsurplus_class <= 0) {
				$return['result'] = FALSE;
				$return['data'] = 0;
				$return['msg'] = '余额不足';
				return json($return);
			} else {
				$pelist = model('PriceOrder') -> getEndtimeList(session('ko_id'));
				for ($i = 0; $i < count($pelist); $i++) {
					if ($pelist[$i]['class'] > $pelist[$i]['used_class'] && $payid == 0) {
						$pedata['used_class'] = $pelist[$i]['used_class'] + 1;
						model('PriceOrder') -> updateData($pelist[$i]['order_id'], $pedata);
						$payid = $pelist[$i]['order_id'];
					}
				}
			}
			$theCurriculum['state'] = 1;
			$theCurriculum['student_id'] = session('ko_id');
			$Curriculum = model('Curriculum') -> updateData($data['id'], $theCurriculum);
			$post['student_id'] = session('ko_id');
			$post['teacher_id'] = $Curriculum['teacher_id'];
			$post['course'] = $data['course'];
			$post['class'] = $data['class'];
			$demand = model('OrderDemand') -> getList('asc');
			$thedemand = explode(",", $data['demand']);
			$post['demand'] = '';
			$post['demand_cn'] = '';
			for ($s = 0; $s < count($thedemand); $s++) {
				$demandinfo = model('OrderDemand') -> getOne($thedemand[$s]);
				$post['demand'] = $post['demand'] . ' ' . $demandinfo['info_ko'];
				$post['demand_cn'] = $post['demand_cn'] . ' ' . $demandinfo['info_cn'];
			}
			$post['day'] = $Curriculum['day'];
			$post['interval'] = $Curriculum['interval'];
			$post['time'] = $Curriculum['time'];
			$post['payid'] = $payid;
			$student = model('Student') -> getOne(session('ko_id'));
			$post['time_zone'] = $student['time_zone'];
			$Order = model('Order') -> addData($post);
			if ($Order) {
				action('teacher/Base/wxinfo', $Order['id']);
				action('Base/wxinfo', $Order['id']);
				action('Base/kakaoinfo', $Order['id']);
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
				$allsurplus_class = 0;
				$plist = model('PriceOrder') -> getList(session('ko_id'), 1, 'desc');
				for ($i = 0; $i < count($plist); $i++) {
					if ($plist[$i]['end_time'] > time()) {
						$plist[$i]['surplus_class'] = $plist[$i]['class'] - $plist[$i]['used_class'];
						$allsurplus_class = $allsurplus_class + $plist[$i]['surplus_class'];
					}
				}
				if ($allsurplus_class <= 0) {
					$return['data'] = 2;
					return json($return);
				}
				$Curriculum = model('Curriculum') -> getId($data['id']);
				if ($Curriculum['time'] < time() + 3600) {
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
				/*
				 $time1 = strtotime(date('Y-m-d', $Curriculum['time'] + ($timezone['time'] * 3600)));
				 $time2 = $time1 + 86400;
				 $limit = model('Order') -> getDayNum(session('ko_id'), $time1, $time2);
				 if ($limit >= 3) {
				 $return['data'] = 3;
				 return json($return);
				 }
				 *
				 */
				$return['data'] = 1;
				return json($return);
			}
		}
	}

	//日历老师列表
	public function enroll_time() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				$list = model('Curriculum') -> getTimeList($data['time'], '0', 'asc');
				$thelist = array();
				for ($i = 0; $i < count($list); $i++) {
					$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
					if ($teacher['state'] == 1) {
						if (action('Base/isMobile') != true) {
							$list[$i]['teacher_avatar'] = $teacher['avatar'];
							$list[$i]['teacher_nickName'] = $teacher['nickName'];
							$list[$i]['teacher_score'] = $teacher['score'];
							$list[$i]['teacher_introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
							$tag = str_replace("'", "", $teacher['tag']);
							$arr = explode(",", $tag);
							$list[$i]['teacher_taglist'] = '';
							for ($a = 0; $a < count($arr); $a++) {
								$tag = model('TeacherTag') -> getOne($arr[$a]);
								if ($tag['state'] == 1) {
									if (cookie('think_var') == 'en-us') {
										$thetagtext = $tag['name_en'];
									} else if (cookie('think_var') == 'zh-cn') {
										$thetagtext = $tag['name_cn'];
									} else {
										$thetagtext = $tag['name_ko'];
									}
									$list[$i]['teacher_taglist'] = $list[$i]['teacher_taglist'] . '#' . $thetagtext . ' ';
								}
							}
						} else {
							$list[$i]['avatar'] = $teacher['avatar'];
							$list[$i]['nickName'] = $teacher['nickName'];
							$list[$i]['score'] = $teacher['score'];
							$list[$i]['introduction'] = str_replace(PHP_EOL, '<br />', $teacher['introduction']);
							$tag = str_replace("'", "", $teacher['tag']);
							$arr = explode(",", $tag);
							$list[$i]['taglist'] = '';
							for ($a = 0; $a < count($arr); $a++) {
								$tag = model('TeacherTag') -> getOne($arr[$a]);
								if ($tag['state'] == 1) {
									if (cookie('think_var') == 'en-us') {
										$thetagtext = $tag['name_en'];
									} else if (cookie('think_var') == 'zh-cn') {
										$thetagtext = $tag['name_cn'];
									} else {
										$thetagtext = $tag['name_ko'];
									}
									$list[$i]['taglist'] = $list[$i]['taglist'] . '#' . $thetagtext . ' ';
								}
							}
						}
						array_push($thelist, $list[$i]);
					}
				}
				if (action('Base/isMobile') == true) {
					$this -> assign('list', $thelist);
					return $this -> fetch('index');
				} else {
					$return['data'] = $thelist;
					return json($return);
				}
			}
		}
	}

	//获取教材
	public function course() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
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

	//获取课时
	public function period() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['type'])) {
					$return['result'] = FALSE;
					$return['msg'] = 'type未传入';
					return json($return);
				}
				$list = model('CoursePeriod') -> getList($data['type']);
				$return['result'] = TRUE;
				$return['data'] = $list;
				return json($return);
			}
		}
	}

}
