<?php
namespace app\en\model;
use think\Model; 
 
class Student extends Model {
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Student = Student::where($data) -> find();
		return $Student;
	}
	
	// OPENID查询一条数据
	public static function getOpenID($openid) {
		$data['openid'] = $openid;
		$Student = Student::where($data) -> find();
		return $Student;
	}
	
	// 按账号查询一条数据
	public static function getAccount($account) {
		$data['account'] = $account;
		$Student = Student::where($data) -> find();
		return $Student;
	}

	//资料更新
	public static function updateData($id, $data) {
		Student::where('id', $id) -> update($data);
		$Student = Student::where('id', $id) -> find();
		return $Student;
	}

}
