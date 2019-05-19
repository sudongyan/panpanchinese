<?php
namespace app\admin\model;
use think\Model;

class OrderDemand extends Model {
 	//获取列表
	public static function getList($p, $order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$list = OrderDemand::page($p, 20) -> order('id', $order) -> select();
		return $list;
	}
	
	//获取数量
	public static function getCount() {
		$Count = OrderDemand::count();
		return $Count;
	}
	
	//增加新数据
	public static function addData($data) {
		$OrderDemand = OrderDemand::create($data);
		return $OrderDemand;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$OrderDemand = OrderDemand::where('id', $id) -> find();
		return $OrderDemand;
	}
	
	//删除数据
	public static function delData($id) {
		$OrderDemand = OrderDemand::where('id', $id) -> delete();
		return $OrderDemand;
	}
}
