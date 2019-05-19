<?php
namespace app\en\model;
use think\Model;

class Course extends Model { 
	//获取列表
	public static function getList($type) {
		if (!isset($order)) {
			$order = 'asc';
		}
		$data['type'] = $type;
		$data['state'] = 1;
		$list = Course::where($data) -> order('id', $order) -> select();
		return $list;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Course = Course::where('id', $id) -> find();
		return $Course;
	}
	
	// 查询开启数量
	public static function getNum($type) {
		$Course = Course::where('type', $type) -> where('	state', 1) -> count();
		return $Course;
	}
	
}
