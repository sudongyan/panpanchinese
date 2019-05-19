<?php
namespace app\admin\model;
use think\Model;

class TimezoneList extends Model { 
	//增加新数据
	public static function addData($data) {
		$TimezoneList = TimezoneList::create($data);
		return $TimezoneList;
	}

	// 按名称查询一条数据
	public static function getOne($id) {
		$TimezoneList = TimezoneList::where('id', $id) -> find();
		return $TimezoneList;
	}
	// 按名称查询一条数据
	public static function getName($name) {
		$TimezoneList = TimezoneList::where('name', $name) -> find();
		return $TimezoneList;
	}

	//获取列表
	public static function getList($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = TimezoneList::order('id', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = TimezoneList::where($data) -> count();
		return $Count;
	}
}
