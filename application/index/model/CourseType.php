<?php
namespace app\index\model;
use think\Model;

class CourseType extends Model { 

	// 查询一条数据
	public static function getOne($id) {
		$CourseType = CourseType::where('id', $id) -> find();
		return $CourseType;
	}
}
