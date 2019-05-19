<?php
namespace app\index\model;
use think\Model; 
 
class Teacher extends Model {
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Teacher = Teacher::where($data) -> find();
		return $Teacher;
	}

}
