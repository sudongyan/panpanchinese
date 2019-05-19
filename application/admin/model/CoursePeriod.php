<?php
namespace app\admin\model;
use think\Model;

class CoursePeriod extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Course = CoursePeriod::create($data);
		return $Course;
	}

	// 查询一条数据
	public static function getOne($id) {
		$Course = CoursePeriod::where('id', $id) -> find();
		return $Course;
	}

	// 按课时查询一条数据
	public static function getPeriod($course_id,$number) {
		$data['course_id'] = $course_id;
		$data['number'] = $number;
		$Course = CoursePeriod::where($data) -> find();
		return $Course;
	}

	//资料更新
	public static function updateData($id, $data) {
		CoursePeriod::where('id', $id) -> update($data);
		$Course = CoursePeriod::where('id', $id) -> find();
		return $Course;
	}

	//获取列表
	public static function getList($p, $course_id, $order) {
		if (!isset($order)) {
			$order = 'asc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$data['course_id'] = $course_id;
		$list = CoursePeriod::where($data) -> page($p, 20) -> order('number', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($course_id) {
		$data['course_id'] = $course_id;
		$Count = CoursePeriod::where($data) -> count();
		return $Count;
	}
	
	//删除数据
	public static function delData($id) {
		$CoursePeriod = CoursePeriod::where('id', $id) -> delete();
		return $CoursePeriod;
	}

	// 查询一条数据
	public static function getNum($course_id,$number) {
		$CoursePeriod = CoursePeriod::where('course_id', $course_id) -> where('number', $number) -> find();
		return $CoursePeriod;
	}
}
