<?php
namespace app\admin\controller;
use think\Controller;
class Timezone extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('timezone') == 0 && session('id') != 1) {
            $this->error('无权限');
		}
	}

	//时区管理
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			$list = model('Timezone') -> getList($data['p'], $data['state'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$timezone = model('TimezoneList') -> getOne($list[$i]['time_zone']);
				$list[$i]['time_zone_name'] = $timezone['name'];
			}
			$total = model('Timezone') -> getCount($data['state']);
			$TimezoneList = model('TimezoneList') -> getList('desc');
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('TimezoneList', $TimezoneList);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('state', $data['state']);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//新增时区
	public function add() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$thetime = model('TimezoneList') -> getOne($postdata['time_zone']);
			$postdata['time'] = $thetime['time'];
			$Course = model('Timezone') -> addData($postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	//修改时区
	public function edit() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$thetime = model('TimezoneList') -> getOne($postdata['time_zone']);
			$postdata['time'] = $thetime['time'];
			$Course = model('Timezone') -> updateData($postdata['id'], $postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}

}
