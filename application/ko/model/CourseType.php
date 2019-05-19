<?php
namespace app\ko\model;
use think\Model;

class CourseType extends Model {
	 
	//获取列表
	public static function getList() {
		if (!isset($order)) {
			$order = 'asc';
		}
		$data['del'] = 0;
		$list = CourseType::where($data) -> order('id', $order) -> select();
		return $list;
	}

	// 查询一条数据
	public static function getOne($id) {
		$data['del'] = 0;
		$CourseType = CourseType::where($data) -> where('id', $id) -> find();
		return $CourseType;
	}
}
