<?php
namespace app\ko\model;
use think\Model;

class Course extends Model { 
	//获取列表
	public static function getList($type) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$data['type'] = $type;
		$data['state'] = 1;
		$data['del'] = 0;
		$list = Course::where($data) -> order('id', $order) -> select();
		return $list;
	}
	
	//获取列表
	public static function getAllList() {
		if (!isset($order)) {
			$order = 'desc';
		}
		$data['state'] = 1;
		$data['del'] = 0;
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
		$data['del'] = 0;
		$Course = Course::where('type', $type) -> where($data) ->  where('state', 1) -> count();
		return $Course;
	}
	
}
