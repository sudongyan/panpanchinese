<?php
namespace app\en\model;
use think\Model;

class CourseType extends Model {
	 
	//获取列表
	public static function getList() {
		if (!isset($order)) {
			$order = 'asc';
		}
		$list = CourseType::order('id', $order) -> select();
		return $list;
	}

	// 查询一条数据
	public static function getOne($id) {
		$CourseType = CourseType::where('id', $id) -> find();
		return $CourseType;
	}
}
