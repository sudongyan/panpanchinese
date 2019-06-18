<?php
namespace app\ko\model;
use think\Model; 
 
class Student extends Model {
	
	//增加新数据
	public static function addData($data) {
		$Student = Student::create($data);
		return $Student;
	}
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
	
	// 	wx查询一条数据
	public static function getWx($wx) {
		$data['wx'] = $wx;
		$Student = Student::where($data) -> find();
		return $Student;
	}

    // 	emaier查询一条数据
    public static function getmail($emailer) {
        $data['emailer'] = $emailer;
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
