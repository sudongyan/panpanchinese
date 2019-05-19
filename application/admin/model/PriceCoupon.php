<?php
namespace app\admin\model;
use think\Model;

class PriceCoupon extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Price = PriceCoupon::create($data);
		return $Price;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Price = PriceCoupon::where('id', $id) -> find();
		return $Price;
	}

	//资料更新
	public static function updateData($id, $data) {
		PriceCoupon::where('id', $id) -> update($data);
		$Price = PriceCoupon::where('id', $id) -> find();
		return $Price;
	}

	//获取列表
	public static function getList($p,$order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$list = PriceCoupon::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount() {
		$Count = PriceCoupon::count();
		return $Count;
	}
}
