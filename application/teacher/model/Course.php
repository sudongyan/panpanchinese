<?php
namespace app\teacher\model;
use think\Model;

class Course extends Model { 
	
	// 查询一条数据
	public static function getOne($id) {
		$Course = Course::where('id', $id) -> find();
		return $Course;
	}
	
}
