<?php
namespace app\admin\model;
use think\Model;

class PriceOrder extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$PriceOrder = PriceOrder::insertGetId($data);
		return $PriceOrder;
	}

	//获取列表
	public static function getList($sid, $state, $order) {
		$data['student_id'] = $sid;
		if ($state != null) {
			$data['state'] = $state;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = PriceOrder::where($data) -> order('payment_time', $order) -> select();
		return $list;
	}

	//获取分页列表
	public static function getPList($sid, $p, $state, $overdue, $order) {
		$data['student_id'] = $sid;
		if (!isset($p)) {
			$p = 1;
		}
		if ($state != null) {
			$data['state'] = $state;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		if ($overdue == 0) {
			$data['end_time'] = array('>', time());
		} else if ($overdue == 1) {
			$data['end_time'] = array('<', time());
		}
		$list = PriceOrder::where($data) -> page($p, 20) -> order('payment_time', $order) -> select();
		return $list;
	}

	//学生获取数量
	public static function getStudentCount($sid, $state, $overdue) {
		$data['student_id'] = $sid;
		if ($state != null) {
			$data['state'] = $state;
		}
		if ($overdue == 0) {
			$data['end_time'] = array('>', time());
		} else if ($overdue == 1) {
			$data['end_time'] = array('<', time());
		}
		$Count = PriceOrder::where($data) -> count();
		return $Count;
	}

	//获取充值列表
	public static function getRechargeList($p, $order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$list = PriceOrder::where($data) -> where('type', '<>', 4) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取列表
	public static function getEndtimeList($sid) {
		$data['student_id'] = $sid;
		$data['state'] = 1;
		$order = 'asc';
		$list = PriceOrder::where('end_time', '>', time()) -> where($data) -> order('end_time', $order) -> select();
		return $list;
	}
	
	//获取数量
	public static function getCount() {
		$Count = PriceOrder::where('type', '<>', 3) -> count();
		return $Count;
	}

	// 查询一条数据
	public static function getOne($id) {
		$data['order_id'] = $id;
		$PriceOrder = PriceOrder::where($data) -> find();
		return $PriceOrder;
	}

	// 查询一条优惠券
	public static function getDiscount($id) {
		$data['student_id'] = $id;
		$data['type'] = 4;
		$PriceOrder = PriceOrder::where($data) -> find();
		if (!$PriceOrder || $PriceOrder['used_class'] > 0) {
			return 0;
		} else {
			return 1;
		}
	}

	//资料更新
	public static function updateData($id, $data) {
		PriceOrder::where('order_id', $id) -> update($data);
		$PriceOrder = PriceOrder::where('order_id', $id) -> find();
		return $PriceOrder;
	}

	//获取最新订单
	public static function getStudentNewOrder($sid) {
		$data['student_id'] = $sid;
		$data['state'] = 1;
		$Order = PriceOrder::where($data) -> where('type', 'neq', 4) -> order('create_time', 'desc') -> find();
		return $Order;
	}
	
	//优惠券获取数量
	public static function getCouponNum($coupon_id, $state) {
		$data['coupon_id'] = $coupon_id;
		if ($state != null) {
			$data['state'] = $state;
		}
		$Count = PriceOrder::where($data) -> count();
		return $Count;
	}
}
