<?php
namespace app\admin\controller;
use think\Controller;
class Authentication extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('authentication') == 0 && session('id') != 1) {
            $this->error('无权限');
		}
	}

	//申请列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['type']) || $data['type'] == '') {
				$data['type'] = null;
			}
			$list = model('Authentication') -> getList($data['p'],$data['type'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$timezone = model('Timezone') -> getOne($list[$i]['time_zone']);
				$list[$i]['time_zone_name'] = $timezone['name_cn'];
			}
			$total = model('Authentication') -> getCount($data['type']);
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('type', $data['type']);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//审核通过
	public function pass() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Authentication = model('Authentication') -> getOne($postdata['id']);
			$data['avatar'] = $Authentication['avatar'];
			$data['nickName'] = $Authentication['nickName'];
			$data['account'] = $Authentication['account'];
			$data['password'] = $Authentication['password'];
			$data['wx'] = $Authentication['wx'];
			$data['phone'] = $Authentication['phone'];
			$data['introduction'] = $Authentication['introduction'];
			$data['time_zone'] = $Authentication['time_zone'];
			$data['create_time'] = strtotime($Authentication['create_time']);
			if ($Authentication['type'] == 1) {
				$data['name_en'] = $Authentication['nickName'];
				$data['name'] = $Authentication['name'];
				$data['bank'] = $Authentication['bank'];
				$data['bank_account'] = $Authentication['bank_account'];
				$data['identification1'] = $Authentication['identification1'];
				$data['identification2'] = $Authentication['identification2'];
				model('Teacher') -> addData($data);
			} else {
				$data['language'] = $Authentication['language'];
				model('Student') -> addData($data);
			}
			model('Authentication') -> delData($postdata['id']);
			return 1;
		}
	}

	//审核拒绝
	public function refuse() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Authentication = model('Authentication') -> getOne($postdata['id']);
			model('Authentication') -> delData($postdata['id']);
			return 1;
		}
	}

}
