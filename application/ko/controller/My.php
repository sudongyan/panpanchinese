<?php
namespace app\ko\controller;
use app\ko\model\Student;
use app\ko\validate\email;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
use think\Exception;
use think\Loader;

class My extends Controller {
	public function index() {
		if (session('ko_id') == null) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				$code = input('param.code');
				if (!isset($code)) {
					$this -> redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx94cd5f68fa803bb2&redirect_uri=http://m.panpanchinese.cn/ko/my&response_type=code&scope=snsapi_base&state=1#wechat_redirect');
				}
				list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("KOappid") . '&secret=' . config("KOsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
				$bodys = json_decode($body, true);
				session('ko_openid', $bodys['openid']);
				$student = model('Student') -> getOpenID($bodys['openid']);
				if ($student && $student['language'] == 1) {
					session('ko_id', $student['id']);
					$this -> redirect('my/index');
				}
			}
			$this -> redirect('my/login');
		} else {
			$student = model('Student') -> getOne(session('ko_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($student['openid'] != session('ko_openid')) {
					session('ko_id', null);
					session('ko_openid', null);
					$this -> redirect('my/login');
				}
			}
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$student['balance'] = model('Balance') -> getBalanceCount(session('ko_id'), 0);
			if ($student['validity_end'] < time() && $student['balance'] > 0) {
				$balance['user_id'] = session('ko_id');
				$balance['user_type'] = 0;
				$balance['type'] = 0;
				$balance['num'] = $student['balance'];
				$balance['balance_num'] = 0;
				model('Balance') -> addData($balance);
				$student['balance'] = 0;
			}
			$list = model('Order') -> getList(session('ko_id'), null, '0', 'desc');
			date_default_timezone_set('UTC');
			$my = model('Student') -> getOne(session('ko_id'));
			$timezone = model('Timezone') -> getOne($my['time_zone']);
			$time = $timezone['time'] * 3600;
			$student['balance'] = 0;
			$plist = model('PriceOrder') -> getList(session('ko_id'), 1, 'desc');
			for ($i = 0; $i < count($plist); $i++) {
				if ($plist[$i]['end_time'] + $time > time()) {
					$plist[$i]['surplus_class'] = $plist[$i]['class'] - $plist[$i]['used_class'];
					$student['balance'] = $student['balance'] + $plist[$i]['surplus_class'];
				}

			}
			for ($i = 0; $i < count($list); $i++) {
				$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
				$list[$i]['tid'] = $list[$i]['teacher_id'];
				$list[$i]['avatar'] = $teacher['avatar'];
				$list[$i]['nickName'] = $teacher['nickName'];
				$list[$i]['wx'] = $teacher['wx'];
				$CoursePeriod = model('CoursePeriod') -> getNum($list[$i]['course'], $list[$i]['class']);
				$list[$i]['course_period'] = $CoursePeriod['name_ko'];
				$list[$i]['course_period_url'] = $CoursePeriod['url'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$list[$i]['course'] = $course['name_ko'];
				$CourseType = model('CourseType') -> getOne($course['type']);
				$list[$i]['course_type'] = $CourseType['name'];
				$list[$i]['start_time'] = date("Y-m-d H:i", $list[$i]['time'] + $time);
				$list[$i]['start_day'] = date("Y-m-d", $list[$i]['time'] + $time);
				$list[$i]['start_hour'] = date("H:i", $list[$i]['time'] + $time);
				$weekarray = array("일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일");
				$list[$i]['start_week'] = $weekarray[date("w", $list[$i]['time'] + $time)];
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
			$newuser = input('param.new');
			if(isset($newuser)){
				$this -> assign('new', 1);
			}else{
				$this -> assign('new', 0);
			}
			$order['orderNum0'] = model('Order') -> getCount(session('ko_id'), 0);
			$order['orderNum1'] = model('Order') -> getCount(session('ko_id'), 1);
			$order['orderNum3'] = model('Order') -> getCount(session('ko_id'), 3);
			$student['alltime'] = ($order['orderNum1'] + $order['orderNum3']) * 30;
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$this -> assign('timezone', $timezone['name_ko']);
			$this -> assign('student', $student);
			$this -> assign('order', $order);
			$this -> assign('list', $list);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//登录
	public function login() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
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
            //兼容旧账号中的账号和邮箱不一致的问题
            $where_emailer['emailer'] = $postdata['account'];
            $Student_emailer = model('Student') -> where($where_emailer)->find();
			if ($Student || $Student_emailer) {
				if ($Student['password'] == $postdata['password']) {
					if ($Student['language'] != 1) {
						$info['info'] = '非韩文端账号';
						$this -> redirect('base/close', $info);
					}
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
						if(session('ko_openid') != null){
							$info['openid'] = session('ko_openid');
							model('Student') -> updateData($Student['id'], $info);
						}
					}
					session('ko_id', $Student['id']);
					$this -> redirect('my/index');
				} else {
					$info['info'] = '비밀번호가 맞지 않습니다.';
					$this -> redirect('base/close', $info);
				}
			} else {
				$info['info'] = '등록되지 않은 아이디 입니다.';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//WEB登录
	public function web_login() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Student = model('Student') -> getAccount($postdata['account']);

			//兼容旧账号中的账号和邮箱不一致的问题
            $where_emailer['emailer'] = $postdata['account'];
            $Student_emailer = model('Student') -> where($where_emailer)->find();
			if ($Student || $Student_emailer) {
				if ($Student['password'] == $postdata['password']) {
					if ($Student['language'] != 1) {
						$return['result'] = FALSE;
						$return['msg'] = '非韩文端账号';
						return $return;
					}
					session('ko_id', $Student['id']);
					session('ko_nickName', $Student['nickName']);
					session('ko_avatar', $Student['avatar']);
					$return['result'] = TRUE;
					return $return;
				} else {
					$return['result'] = FALSE;
					$return['msg'] = '비밀번호가 맞지 않습니다.';
					return $return;
				}
			} else {
				$return['result'] = FALSE;
				$return['msg'] = '등록되지 않은 아이디 입니다.';
				return $return;
			}
		}
	}

	//入驻
	public function enter() {
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');

            //接受用户提交的邮箱数据并校验
            $validate = Loader::validate('BaseValidate');
            if(!$validate->check($postdata)){
                $info['info'] = $validate->getError();
                $this -> redirect('base/close', $info);
            }

			$Authentication = model('Authentication') -> getAccount($postdata['account'], 0);
			$Student = model('Student') -> getAccount($postdata['account']);
			if ($Student || $Authentication) {
				$info['info'] =  '이미 등록된 회원 아이디입니다.';
				$this -> redirect('base/close', $info);
			}

			$Student = model('Student') -> getWx($postdata['wx']);
			if ($Student) {
				$info['info'] = '위챗 아이디가 중복되었습니다.'; //微信号重复
				$this -> redirect('base/close', $info);
			}

            //新注册的用户账号和邮箱保持一致
            $postdata['emailer'] = $postdata['account'];

            $Student = model('Student') -> getmail($postdata['emailer']);
            if ($Student) {
                $return['result'] = FALSE;
                $return['msg'] = '메일함이 이미 존재함';//邮箱已经被注册
                return $return;
            }

            if ($postdata['password'] != $postdata['password2']) {
                $info['info'] = '비밀번호가 동일하지 않습니다.'; //两次输入密码不一致
                $this -> redirect('base/close', $info);
            }

            unset($postdata['password2']);
			$postdata['time_zone'] = 1;
			$postdata['language'] = 1;


			$postdata['create_time'] = time();
			$new = model('Student') -> addData($postdata);
			if ($new) {
				$Student = model('Student') -> getAccount($postdata['account']);
				if ($Student) {
					if ($Student['language'] != 1) {
						$info['info'] = '非韩文端账号';
						$this -> redirect('base/close', $info);
					}
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
						$theinfo['openid'] = session('ko_openid');
						model('Student') -> updateData($Student['id'], $theinfo);
					}
					$order['order_id'] = time() . rand(1000, 9999);
					$order['student_id'] = $Student['id'];
					$order['cover'] = 'http://yk.nightka.com/static/img/logo.jpeg';
					$order['name'] = '体验券';
					$order['day'] = 365;
					$order['class'] = 1;
					$order['money'] = 0;
					$order['discount'] = 0;
					$order['type'] = 4;
					$order['discount_money'] = 0;
					$order['USD'] = 0;
					$order['payment_time'] = time();
					$order['end_time'] = time() + 365 * 86400;
					$order['state'] = 1;
					model('PriceOrder') -> addData($order);
					session('ko_id', $Student['id']);
					session('ko_nickName', $Student['nickName']);
					session('ko_avatar', $Student['avatar']);
					action('Base/kakaoreg', $Student['id']);
					$this -> redirect('my/index', array('new' => 1));
				} else {
					$info['info'] = '登录错误';
					$this -> redirect('base/close', $info);
				}
			} else {
				$info['info'] = '申请错误';
				$this -> redirect('base/close', $info);
			}
		}
	}

	//入驻
	public function web_signup() {
		if ( request() -> isPost()) {
			$postdata = input('post.');

            //确定数据完整性
            if (!isset($postdata['nickName'])||!isset($postdata['wx'])||!isset($postdata['phone'])||!isset($postdata['account'])||!isset($postdata['password'])) {
                $return['result'] = FALSE;
                $return['msg'] = '필수 등록 정보를 모두 기입해주세요.';
                return json($return);
            }

            //接受用户提交的邮箱数据并校验
            $validate = Loader::validate('BaseValidate');
            if(!$validate->check($postdata)){
                $return['result'] = false;
                $return['msg'] = $validate->getError();
                return $return;
            }

			$Authentication = model('Authentication') -> getAccount($postdata['account'], 0);
			$Student = model('Student') -> getAccount($postdata['account']);
			if ($Student || $Authentication) {
				$return['result'] = FALSE;
				$return['msg'] = '이미 등록된 회원 아이디입니다.';
				return $return;
			}

			$Student = model('Student') -> getWx($postdata['wx']);
			if ($Student) {
				$return['result'] = FALSE;
				$return['msg'] = '위챗 아이디가 중복되었습니다.';
				return $return;
			}

            //新注册的用户账号和邮箱保持一致
            $postdata['emailer'] = $postdata['account'];

            $Student = model('Student') -> getmail($postdata['emailer']);
            if ($Student) {
                $return['result'] = FALSE;
                $return['msg'] = '메일함이 이미 존재함';//邮箱已经被注册
                return $return;
            }

			$postdata['time_zone'] = 1;
			$postdata['language'] = 1;

			$postdata['create_time'] = time();
			$new = model('Student') -> addData($postdata);
			if ($new) {
				$Student = model('Student') -> getAccount($postdata['account']);
				if ($Student) {
					if ($Student['language'] != 1) {
						$info['info'] = '非韩文端账号';
						$this -> redirect('base/close', $info);
					}
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
						$info['openid'] = session('ko_openid');
						model('Student') -> updateData($Student['id'], $info);
					}
					$order['order_id'] = time() . rand(1000, 9999);
					$order['student_id'] = $Student['id'];
					$order['cover'] = 'http://yk.nightka.com/static/img/logo.jpeg';
					$order['name'] = '体验券';
					$order['day'] = 365;
					$order['class'] = 1;
					$order['money'] = 0;
					$order['discount'] = 0;
					$order['type'] = 4;
					$order['discount_money'] = 0;
					$order['USD'] = 0;
					$order['payment_time'] = time();
					$order['end_time'] = time() + 365 * 86400;
					$order['state'] = 1;
					model('PriceOrder') -> addData($order);
					session('ko_id', $Student['id']);
					session('ko_nickName', $Student['nickName']);
					session('ko_avatar', $Student['avatar']);
					action('Base/kakaoreg', $Student['id']);
					$return['result'] = TRUE;
					$return['msg'] = '注册成功';
					return $return;
				} else {
					$return['result'] = FALSE;
					$return['msg'] = '注册错误';
					return $return;
				}
			} else {
				$return['result'] = FALSE;
				$return['msg'] = '注册错误';
				return $return;
			}
		}
	}

	//检测微信
	public function validate_wx() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Student = model('Student') -> getWx($postdata['wx']);
			if ($Student) {
				$return['result'] = TRUE;
				$return['data'] = 1;
				return $return;
			} else {
				$return['result'] = TRUE;
				$return['data'] = 0;
				return $return;
			}
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '查询错误';
			return $return;
		}
	}

	//微信id说明
	public function wxid() {
		return $this -> fetch();
	}

	//编辑
	public function edit() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$student = model('Student') -> getOne(session('ko_id'));
			$order['orderNum1'] = model('Order') -> getCount(session('ko_id'), 1);
			$order['orderNum3'] = model('Order') -> getCount(session('ko_id'), 3);
			$student['alltime'] = ($order['orderNum1'] + $order['orderNum3']) * 30;
			$TimezoneList = model('Timezone') -> getAllList(1, 'desc');
			$this -> assign('TimezoneList', $TimezoneList);
			$this -> assign('student', $student);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webedit');
			}
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$student = model('Student') -> updateData(session('ko_id'), $postdata);
			session('ko_avatar', $postdata['avatar']);
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
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$TimezoneList = model('Timezone') -> getAllList(1, 'desc');
			$student = model('Student') -> getOne(session('ko_id'));
			$this -> assign('TimezoneList', $TimezoneList);
			$this -> assign('student', $student);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$student = model('Student') -> updateData(session('ko_id'), $postdata);
			if ($student) {
				$this -> redirect('my/index');
			} else {
				$info['info'] = '编辑错误';
				$this -> redirect('base/close', $info);
			}
		}
	}
	
	//设置语言
	public function lang() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		return $this -> fetch();
	}

	//订单
	public function order() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			$student = model('Student') -> getOne(session('ko_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($student['openid'] != session('ko_openid')) {
					session('ko_id', null);
					session('ko_openid', null);
					$this -> redirect('my/login');
				}
			}
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$student['balance'] = model('Balance') -> getBalanceCount(session('ko_id'), 0);
			if ($student['validity_end'] < time() && $student['balance'] > 0) {
				$balance['user_id'] = session('ko_id');
				$balance['user_type'] = 0;
				$balance['type'] = 0;
				$balance['num'] = $student['balance'];
				$balance['balance_num'] = 0;
				model('Balance') -> addData($balance);
				$student['balance'] = 0;
			}
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
			$list = model('Order') -> getList(session('ko_id'), $data['p'], $data['state'], 'desc');
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
				$CoursePeriod = model('CoursePeriod') -> getNum($list[$i]['course'], $list[$i]['class']);
				$list[$i]['course_period'] = $CoursePeriod['name_ko'];
				$list[$i]['course_period_url'] = $CoursePeriod['url'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$list[$i]['course'] = $course['name_ko'];
				$CourseType = model('CourseType') -> getOne($course['type']);
				$list[$i]['course_type'] = $CourseType['name'];
				$list[$i]['start_time'] = date("Y-m-d H:i", $list[$i]['time'] + $time);
				$list[$i]['start_day'] = date("Y-m-d", $list[$i]['time'] + $time);
				$list[$i]['start_hour'] = date("H:i", $list[$i]['time'] + $time);
				if(cookie('think_var') == 'ko-kr'){
					$weekarray = array("일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일");
				}else if(cookie('think_var') == 'zh-cn'){
					$weekarray = array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
				}else if(cookie('think_var') == 'en-us'){
					$weekarray = array("SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT");
				}
				$list[$i]['start_week'] = $weekarray[date("w", $list[$i]['time'] + $time)];
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
			$order['orderNum0'] = model('Order') -> getCount(session('ko_id'), 0);
			$order['orderNum1'] = model('Order') -> getCount(session('ko_id'), 1);
			$order['orderNum3'] = model('Order') -> getCount(session('ko_id'), 3);
			$student['alltime'] = ($order['orderNum1'] + $order['orderNum3']) * 30;
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$this -> assign('timezone', $timezone['name_ko']);
			$this -> assign('student', $student);
			$this -> assign('order', $order);
			$this -> assign('list', $list);
			$this -> assign('s', $data['state']);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('weborder');
			}
		}
	}

	//订单详情
	public function orderinfo() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['id'])) {
				$info['info'] = 'id错误';
				$this -> redirect('base/close', $info);
			}
			$order = model('Order') -> getOne($data['id']);
			if ($order['student_id'] != session('ko_id')) {
				$info['info'] = '非自有订单';
				$this -> redirect('base/close', $info);
			}
			$student = model('Student') -> getOne($order['student_id']);
			$teacher = model('Teacher') -> getOne($order['teacher_id']);
			date_default_timezone_set('UTC');
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$time = $timezone['time'] * 3600;
			$CoursePeriod = model('CoursePeriod') -> getNum($order['course'], $order['class']);
			$order['course_period'] = $CoursePeriod['name_ko'];
			$order['course_url'] = $CoursePeriod['url'];
			$course = model('Course') -> getOne($order['course']);
			$order['course'] = $course['name_ko'];
			$CourseType = model('CourseType') -> getOne($course['type']);
			$order['course_type'] = $CourseType['name'];
			$order['start_time'] = date("Y-m-d H:i", $order['time'] + $time);
			$order['start_day'] = date("Y-m-d", $order['time'] + $time);
			$order['start_hour'] = date("H:i", $order['time'] + $time);
			$weekarray = array("일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일");
			$order['start_week'] = $weekarray[date("w", $order['time'] + $time)];
			if ($order['state'] == 1) {
				$Evaluate = model('Evaluate') -> getOne($order['id']);
				$order['score'] = $Evaluate['score'];
				$order['score_info'] = $Evaluate['information'];
			}
			if ($order['time'] < time() + 7200) {
				$order['cancel'] = 0;
			} else {
				$order['cancel'] = 1;
			}
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$this -> assign('timezone', $timezone['name_ko']);
			$this -> assign('student', $student);
			$this -> assign('teacher', $teacher);
			$this -> assign('order', $order);
			return $this -> fetch();
		}
	}

	//订单JSON
	public function order_list() {
		if (session('ko_id') == null) {
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
			$list = model('Order') -> getList(session('ko_id'), $data['p'], $data['state'], 'desc');
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
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$student = model('Student') -> getOne(session('ko_id'));
			$student['balance'] = model('Balance') -> getBalanceCount(session('ko_id'), 0);
			$student['oldbalance'] = model('Balance') -> getOldCount(session('ko_id'));
			date_default_timezone_set('UTC');
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$time = $timezone['time'] * 3600;
			$student['validity_start'] = date('Y-m-d', $student['validity_start'] + $time);
			$student['validity_end'] = date('Y-m-d', $student['validity_end'] + $time);
			$this -> assign('student', $student);
			return $this -> fetch();
		}
	}

	//课程券订单
	public function price_order() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			$student = model('Student') -> getOne(session('ko_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				if ($student['openid'] != session('ko_openid')) {
					session('ko_id', null);
					session('ko_openid', null);
					$this -> redirect('my/login');
				}
			}
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$student['balance'] = model('Balance') -> getBalanceCount(session('ko_id'), 0);
			if ($student['validity_end'] < time() && $student['balance'] > 0) {
				$balance['user_id'] = session('ko_id');
				$balance['user_type'] = 0;
				$balance['type'] = 0;
				$balance['num'] = $student['balance'];
				$balance['balance_num'] = 0;
				model('Balance') -> addData($balance);
				$student['balance'] = 0;
			}
			date_default_timezone_set('UTC');
			$my = model('Student') -> getOne(session('ko_id'));
			$timezone = model('Timezone') -> getOne($my['time_zone']);
			$time = $timezone['time'] * 3600;
			$allsurplus_class = 0;
			$list = model('PriceOrder') -> getList(session('ko_id'), 1, 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$end_time = $list[$i]['end_time'] + $time;
				$list[$i]['start_time'] = date("Y-m-d H:i", $list[$i]['payment_time'] + $time);
				$list[$i]['end_time'] = date("Y-m-d H:i", $list[$i]['end_time'] + $time);
				$list[$i]['timeend_class'] = 0;
				$list[$i]['surplus_class'] = 0;
				$list[$i]['surplus_day'] = 0;
				if ($end_time <= time()) {
					$list[$i]['timeend_class'] = $list[$i]['class'] - $list[$i]['used_class'];
				} else {
					$list[$i]['surplus_class'] = $list[$i]['class'] - $list[$i]['used_class'];
					$list[$i]['surplus_day'] = floor(($end_time - time() - $time) / 86400);
					$allsurplus_class = $allsurplus_class + $list[$i]['surplus_class'];
				}

			}
			$order['orderNum0'] = model('Order') -> getCount(session('ko_id'), 0);
			$order['orderNum1'] = model('Order') -> getCount(session('ko_id'), 1);
			$order['orderNum3'] = model('Order') -> getCount(session('ko_id'), 3);
			$student['alltime'] = ($order['orderNum1'] + $order['orderNum3']) * 30;
			$student['introduction'] = str_replace(PHP_EOL, '<br />', $student['introduction']);
			$timezone = model('Timezone') -> getOne($student['time_zone']);
			$this -> assign('timezone', $timezone['name_ko']);
			$this -> assign('student', $student);
			$this -> assign('allsurplus_class', $allsurplus_class);
			$this -> assign('order', $order);
			$this -> assign('list', $list);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webprice_order');
			}
		}
	}

	//修改密码
	public function password() {
		if (session('ko_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$data['password'] = $postdata['password'];
			$Student = model('Student') -> updateData(session('ko_id'), $data);
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

    //发送找回密码连接到用户邮箱
    public function find_password_mailer()
    {

        if (request()->isGET()) {
            if (action('Base/isMobile') == true) {
                return $this -> fetch('forgot_pwd');
            } else {
                return $this -> fetch('webforgot_pwd');
            }
        } else if (request()->isPost()) {

            //接受用户提交的邮箱数据并校验
            $postdata = input('post.');
            $data['email'] = $postdata['emailer'];
            $validate = Loader::validate('FindPasswordValidate');
            if(!$validate->check($data)){
                $return['result'] = false;
                $return['msg'] = $validate->getError();
//                $return['msg'] = '잘못 쓰셨습니다';//您填写的邮箱有误
                return $return;
            }

            //查询数据库中是否存在当前用户邮箱
            $student = new Student();
            $emailer = $postdata['emailer'];
            $where['emailer'] = $emailer;
            $user =  model('Student')->where($where)-> find();
            if (!$user) {
                $return['result'] = false;
                $return['msg'] = '메일함이 존재하지 않음';//邮箱不存在
                return $return;
            } else {
                if ($user['language'] != 1) {
                    $return['msg'] = '非韩文端账号';//非韩文端账号
                    return $return;
                }

                //定制邮件发送内容
                $desc_url = 'http://dev.panpanchinese.net/ko/my/change_password/id/'.$user['id'].'/time/'.time();
                $html = "<!DOCTYPE html>
            <html><table style='border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;height: 100%;margin: 0;padding: 0;width: 100%;background-color: #FAFAFA;'>
            <tbody><tr><td style='mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;height: 100%;margin: 0;padding: 10px;width: 100%;border-top: 0;'>
                <div style='background: #fff;padding: 20px;margin: 40px auto 20px auto;border: 0;max-width: 600px !important;'>
                    <div>
                          <a href='' style='mso-line--rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;text-decoration: none;' rel='noreferrer noopener' target='_blank'><img src='http://m.panpanchinese.cn/static/img/web_logo.png' style='border: 0;height: 35px; margin-bottom:10px; outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;'></a>                        
                          <h2 style='display: block;margin: 0;padding: 0;color: #444;font-family: Helvetica;font-style: normal;letter-spacing: normal;font-size: 22px;line-height: 1.6em !important;font-weight: inherit !important;'>密码变更通知 <!-- 비밀번호 변경 안내 --> <!-- Password Change Guidance --></h2>
                        <h5 style='display: block;margin: 0;padding: 0;color: #666 !important;font-family: Helvetica;font-style: normal;letter-spacing: normal;font-size: 15px;line-height: 1.6em !important;font-weight: inherit !important;margin-top: 10px !important;'>
                            <!-- 你好, 会员!  --> 안녕하세요, 회원님! <!-- Hello, member! --><br>
                        </h5>
                    </div>
                    <div style='margin-top: 20px;background: #f8f7ff;padding: 10px;'>
                        <h6 style='display: block;margin: 0; margin-bottom:5px; padding: 0;color: #444;font-family: Helvetica;font-style: normal;letter-spacing: normal;font-size: 13px;line-height: 1.6em !important;font-weight: inherit !important;margin-top: 15px !important;'><b>| <!--请确认! -->꼭 확인해주세요!   <!-- Please check! --></b></h6>
                        <h6 style='display: block;margin: 0; margin-bottom:15px; padding: 0;color: #444;font-family: Helvetica;font-style: normal;letter-spacing: normal;font-size: 13px;line-height: 1.6em !important;font-weight: inherit !important;'>

                            *  <!-- 请通过以下链接变更为新的密码。 -->아래 링크를 통해 새로운 비밀번호로 변경해주세요. <!-- Please change the password to a new one through the link below. --> <br>
                            *  <!--下面的链接是找回密码申请后3小时就消失了。-->아래 링크는 비밀번호 찾기를 신청한 후 3시간이 지나면 소멸됩니다.   <!-- The link below will expire after 3 hours of applying for a password. --><br>
                           
                        </h6>
                    </div>
                    <div style='text-align: center;'>
                        <div style='text-align: center;padding: 10px 15px;background: #9981b4;display: inline-block;margin-top: 25px;'>
                            <a href='$desc_url' style='mso-line--rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;text-decoration: none;color: #fff !important;'  target='_blank'><!--密码变更  --> 비밀번호 변경  <!-- Change Password --></a>
                        </div>
                    </div>
                </div>
                   </td></tr>
    </tbody></table></html>";

                $res = sendEmail($html, $emailer, $desc_url);//汉语教学
                if ($res == 1) {
                    $return['result'] = TRUE;
                    $return['msg'] = '전송에 성공했습니다.';//发送成功
                    return $return;
                } else {
                    $return['result'] = false;
                    $return['msg'] = '보내기 실패';//发送失败
                    return $return;
                }
            }
        }
    }


    //用户通过邮箱修改密码
    public function change_password(){
        if (request()->isGET()) {
            $getdata = input();
            $timediff =time() - $getdata['time'];
            $remain = $timediff%86400;
            $hours = intval($remain/3600);
            if($hours > 3){
                return '링크가 유효하지 않음';//链接已超时失效
            }
//            $student = model('Student') -> getOne($getdata['id']);
            $this->assign('student_id',$getdata['id']);
            if (action('Base/isMobile') == true) {
                return $this -> fetch('password');
            } else {
                return $this -> fetch('webpassword');
            }

        } else if (request()->isPost()) {
            $postdata = input('post.');
            $data['password'] = $postdata['password'];
            $Student = model('Student') -> updateData($postdata['id'], $data);
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

	//退出登录
	public function out() {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
			$info['openid'] = 0;
			model('Student') -> updateData(session('ko_id'), $info);
		}
		session('ko_id', null);
		session('ko_openid', null);
		$this -> redirect('my/login');
	}

	//退出登录
	public function web_out() {
		session('ko_id', null);
		session('ko_nickName', null);
		session('ko_avatar', null);
		$this -> redirect('teacher/index');
	}

}
