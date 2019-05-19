<?php
namespace app\admin\controller;
use think\Controller;
class Recharge extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
	}

	//充值管理
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('PriceOrder') -> getRechargeList($data['p'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['month'] = $list[$i]['day'] / 30;
				$list[$i]['class'] = $list[$i]['class'] / ($list[$i]['day'] / 30);
				if ($list[$i]['payment_time'] != 0) {
					$list[$i]['payment_time'] = date('Y-m-d H:m:s', $list[$i]['payment_time']);
				} else {
					$list[$i]['payment_time'] = '————';
				}
				if ($list[$i]['end_time'] != 0) {
					$list[$i]['end_time'] = date('Y-m-d H:m:s', $list[$i]['end_time']);
				} else {
					$list[$i]['end_time'] = '————';
				}
				$student = model('Student') -> getOne($list[$i]['student_id']);
				$list[$i]['student_account'] = $student['account'];
				$list[$i]['student_nickName'] = $student['nickName'];
				$list[$i]['student_wx'] = $student['wx'];
				$list[$i]['student_phone'] = $student['phone'];
				$list[$i]['admin_account'] = '————';
				$list[$i]['admin_nickName'] = '————';
				if ($list[$i]['admin_id'] != 0) {
					$admin = model('Admin') -> getId($list[$i]['admin_id']);
					$list[$i]['admin_account'] = $admin['account'];
					$list[$i]['admin_nickName'] = $admin['nickName'];
				}
			}
			$total = model('PriceOrder') -> getCount();
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//确认收款
	public function confirm() {

		if ( request() -> isPost()) {
			$data = input('post.');
			if (!isset($data['order_id'])) {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
			$Order = model('PriceOrder') -> getOne($data['order_id']);
			if($Order['state'] != 1 && $Order['type'] == 1){
				$info['state'] = 1;
				$info['admin_id'] = session('id');
				$info['payment_time'] = time();
				$info['end_time'] = time() + (86400 * $Order['day']);
				model('PriceOrder') -> updateData($data['order_id'],$info);
					$kakao['orderid'] = $data['order_id'];
					$kakao['type'] = 1;
					action('ko/Base/kakaopriceorder', $kakao);
			}
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
