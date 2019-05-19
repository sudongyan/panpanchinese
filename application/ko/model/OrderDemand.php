<?php
namespace app\ko\model;
use think\Model;

class OrderDemand extends Model {
 	//获取列表
	public static function getList($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = OrderDemand::order('id', $order) -> select();
		return $list;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$OrderDemand = OrderDemand::where('id', $id) -> find();
		return $OrderDemand;
	}
}
