<?php
namespace app\teacher\model;
use think\Model;

class CoursePeriod extends Model {
	 
	//获取列表
	public static function getList($course_id) {
		if (!isset($order)) {
			$order = 'asc';
		}
		$list = CoursePeriod::where('course_id', $course_id) -> order('number', $order) -> select();
		return $list;
	}

	// 查询一条数据
	public static function getOne($id) {
		$CoursePeriod = CoursePeriod::where('id', $id) -> find();
		return $CoursePeriod;
	}

	// 查询一条数据
	public static function getNum($course_id,$number) {
		$CoursePeriod = CoursePeriod::where('course_id', $course_id) -> where('number', $number) -> find();
		return $CoursePeriod;
	}
}
