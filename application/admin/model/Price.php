<?php
namespace app\admin\model;
use think\Model;

class Price extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['update_time'] = time();
		$data['create_time'] = time();
		$Price = Price::create($data);
		return $Price;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Price = Price::where('id', $id) -> find();
		return $Price;
	}

	//资料更新
	public static function updateData($id, $data) {
		Price::where('id', $id) -> update($data);
		$Price = Price::where('id', $id) -> find();
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
		$list = Price::where($data) -> page($p, 20) -> order('update_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount() {
		$Count = Price::count();
		return $Count;
	}
	
	//获取月课时数量
	public static function getMonthCount($month) {
		$data['month'] = $month;
		$Count = Price::where($data) -> count();
		return $Count;
	}
	
	//获取月列表
	public static function getMonthList($month) {
		if (!isset($order)) {
			$order = 'asc';
		}
		$data['month'] = $month;
		$list = Price::where($data) -> order('day', $order) -> select();
		return $list;
	}
}
