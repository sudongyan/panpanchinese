<?php
namespace app\teacher\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Curriculum extends Controller {
	public function index() {
		if (session('t_id') == null) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
				list($body) = HttpCurl::request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config("TEACHERappid") . '&secret=' . config("TEACHERsecret") . '&code=' . input('param.code') . '&grant_type=authorization_code', 'get');
				$bodys = json_decode($body, true);
				session('teacher_openid', $bodys['openid']);
				$teacher = model('Teacher') -> getOpenID($bodys['openid']);
				if ($teacher) {
					session('t_id', $teacher['id']);
					$this -> redirect('curriculum/index');
				}
			}
			$this -> redirect('my/login');
		}
		$teacher = model('Teacher') -> getOne(session('t_id'));
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
			if ($teacher['openid'] != session('teacher_openid')) {
				session('t_id', null);
				session('teacher_openid', null);
				$this -> redirect('my/login');
			}
		}
		$data = input('param.');
		if (!isset($data['date'])) {
			$data['date'] = date("Y-m-d");
			$time = strtotime(date('Y-m-d', time()));
		} else {
			$time = strtotime($data['date']);
		}
		$day['a'] = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
		$day['b'] = date('Y-m-d', (time() + (2 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
		$day['c'] = date('Y-m-d', (time() + (3 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
		$day['d'] = date('Y-m-d', (time() + (4 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
		$day['e'] = date('Y-m-d', (time() + (5 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
		$day['f'] = date('Y-m-d', (time() + (6 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
		$day['g'] = date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
		$nowweek = date("w");
		if ($nowweek == 0) {
			$nowweek = 7;
		}
		$nowday = date("Y-m-d");
		$list = model('Curriculum') -> getList(session('t_id'), $data['date'], 'desc');
		$this -> assign('list', $list);
		$this -> assign('day', $day);
		$this -> assign('nowweek', $nowweek);
		$this -> assign('nowday', $nowday);
		$this -> assign('datetime', $time);
		$this -> assign('datedate', $data['date']);
		return $this -> fetch();
	}

	public function curriculum() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['date'])) {
				$data['date'] = date("Y-m-d");
			}
			$list = model('Curriculum') -> getList(session('t_id'), $data['date'], 'desc');
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	public function updatedata() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$data['interval'] = $postdata['interval'];
			$data['teacher_id'] = session('t_id');
			$data['day'] = $postdata['day'];
			$data['state'] = 0;
			$Curriculum = model('Curriculum') -> getOne(session('t_id'), $data['day'], $data['interval']);
			if (!$Curriculum) {
				if (is_int($data['interval'] / 2)) {
					$data['time'] = strtotime($data['day'] . ' ' . $data['interval'] / 2 . ":00");
				} else {
					$data['time'] = strtotime($data['day'] . ' ' . floor($data['interval'] / 2) . ":30");
				}
				$data['week'] = date('w', $data['time']);
				model('Curriculum') -> addData($data);
			}
			$return['result'] = TRUE;
			$return['data'] = 1;
			return json($return);
		}
	}

	public function canceldata() {
		if (session('t_id') == null) {
			$this -> redirect('my/login');
		}
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$data['interval'] = $postdata['interval'];
			$data['day'] = $postdata['day'];
			$data['teacher_id'] = session('t_id');
			$Curriculum = model('Curriculum') -> getOne(session('t_id'), $data['day'], $data['interval']);
			if ($Curriculum && $Curriculum['state'] == 0) {
				model('Curriculum') -> delData($data);
				$return['result'] = TRUE;
				$return['data'] = 1;
				return json($return);
			} else {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
		}
	}

}
