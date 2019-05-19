<?php
namespace app\admin\model;
use think\Model;

class PriceClass extends Model { 	 
	//获取列表
	public static function getList() {
		if (!isset($order)) {
			$order = 'asc';
		}
		$list = PriceClass::order('period', $order) -> select();
		return $list;
	}
	//增加新数据
	public static function addData($period) {
		$data['period'] = $period;
		$Price = PriceClass::create($data);
		return $Price;
	}
	//资料更新
	public static function updateData($id, $num) {
		$data['num'] = $num;
		PriceClass::where('period', $id) -> update($data);
		$Price = PriceClass::where('id', $id) -> find();
		return $Price;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Price = PriceClass::where('id', $id) -> find();
		return $Price;
	}
	
	// 按月课时查询一条数据
	public static function getPeriod($id) {
		$Price = PriceClass::where('period', $id) -> find();
		return $Price;
	}
	//删除数据
	public static function delData($id) {
		$Price = PriceClass::where('period', $id) -> delete();
		return $Price;
	}
}
