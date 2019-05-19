<?php
namespace app\ko\model;
use think\Model;

class PriceCoupon extends Model {
	// 查询一条数据
	public static function getOne($id) {
		$Price = PriceCoupon::where('id', $id) -> find();
		return $Price;
	}

	// code查询一条数据
	public static function getCode($code) {
		$Price = PriceCoupon::where('code', $code) -> find();
		return $Price;
	}

	//获取列表
	public static function getCodeList($code, $type, $paymoney) {
		if (!isset($order)) {
			$order = 'desc';
		}
		if ($type == 1 || $type == 3) {
			$data['min_money'] = array('elt', $paymoney);
		} else if ($type == 0) {
			$data['min_usd_money'] = array('elt', $paymoney);
		} else {
			$data['min_cny_money'] = array('elt', $paymoney);
		}
		$data['code'] = $code;
		$list = PriceCoupon::where($data) -> where('end_time', '>', time()) -> order('money', $order) -> select();
		return $list;
	}

}
