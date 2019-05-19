<?php
namespace app\teacher\model;
use think\Model; 
 
class Student extends Model {
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Student = Student::where($data) -> find();
		return $Student;
	}
	
	// 按账号查询一条数据
	public static function getAccount($account) {
		$data['account'] = $account;
		$Student = Student::where($data) -> find();
		return $Student;
	}

}
