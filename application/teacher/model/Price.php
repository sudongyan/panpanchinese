<?php
namespace app\teacher\model;
use think\Model;

class Price extends Model { 
	//获取列表
	public static function getList() {
		if (!isset($order)) {
			$order = 'asc';
		}
		$list = Price::order('id', $order) -> select();
		return $list;
	}
	
	//获取全部列表
	public static function getAllList() {
		if (!isset($order)) {
			$order = 'asc';
		}
		$list = Price::order('id', $order) -> select();
		return $list;
	}
	//获取列表
	public static function getMonthList($month) {
		if (!isset($order)) {
			$order = 'asc';
		}
		$data['month'] = $month;
		$list = Price::where($data) -> order('day', $order) -> select();
		return $list;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Price = Price::where('id', $id) -> find();
		return $Price;
	}
	
}
