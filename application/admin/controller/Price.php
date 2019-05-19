<?php
namespace app\admin\controller;
use think\Controller;
class price extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('course') == 0 && session('id') != 1) {
			$this -> error('无权限');
		}
	}

	//套餐管理
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('Price') -> getList($data['p'], 'desc');
			$total = model('Price') -> getCount();
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

	//新增套餐
	public function add() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Course = model('Price') -> addData($postdata);
			$month = model('Price') -> getMonthCount($postdata['month']);
			if ($month == 1) {
				model('PriceClass') -> addData($postdata['month']);
			}
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	//修改套餐
	public function edit() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$oldPrice = model('Price') -> getOne($postdata['id']);
			$Price = model('Price') -> updateData($postdata['id'], $postdata);
			if ($oldPrice['month'] != $postdata['month']) {
				$oldmonth = model('Price') -> getMonthCount($oldPrice['month']);
				if ($oldmonth == 0) {
					model('PriceClass') -> delData($oldPrice['month']);
				}
				$month = model('Price') -> getMonthCount($postdata['month']);
				if ($month == 1) {
					model('PriceClass') -> addData($postdata['month']);
				}
			}
			if ($Price) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	//删除套餐
	public function del() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Period = model('CoursePeriod') -> getOne($postdata['id']);
			model('CoursePeriod') -> delData($postdata['id']);
			$data['period'] = model('CoursePeriod') -> getCount($Period['course_id']);
			model('Course') -> updateData($Period['course_id'], $data);
			return 1;
		}
	}
	
	//优惠券管理
	public function coupon() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('PriceCoupon') -> getList($data['p'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['end_time'] = date('Y-m-d H:i:s', $list[$i]['end_time']);
				$list[$i]['order_num'] = model('PriceOrder') -> getCouponNum($list[$i]['id'],1);
			}
			$total = model('PriceCoupon') -> getCount();
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
	
	
	//新增优惠券
	public function add_coupon() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$postdata['end_time'] = strtotime($postdata['end_time']);
			$Course = model('PriceCoupon') -> addData($postdata);
			if ($Course) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	//修改优惠券
	public function edit_coupon() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$Coupon = model('PriceCoupon') -> getOne($postdata['id']);
			$postdata['end_time'] = strtotime($postdata['end_time']);
			$theCoupon = model('PriceCoupon') -> updateData($postdata['id'], $postdata);
			if ($theCoupon) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	//开启关闭优惠券
	public function on_off_coupon() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$oldCoupon = model('PriceCoupon') -> getOne($postdata['id']);
			if($oldCoupon['state'] == 1){
				$data['state'] = 0;
			}else{
				$data['state'] = 1;
			}
			$Coupon = model('PriceCoupon') -> updateData($postdata['id'], $data);
			if ($Coupon) {
				return 1;
			} else {
				return 0;
			}
		}
	}

}
