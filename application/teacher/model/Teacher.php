<?php
namespace app\teacher\model;
use think\Model; 
 
class Teacher extends Model {
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Teacher = Teacher::where($data) -> find();
		return $Teacher;
	}
	// OPENID查询一条数据
	public static function getOpenID($openid) {
		$data['openid'] = $openid;
		$Teacher = Teacher::where($data) -> find();
		return $Teacher;
	}
	
	// 按账号查询一条数据
	public static function getAccount($account) {
		$data['account'] = $account;
		$Teacher = Teacher::where($data) -> find();
		return $Teacher;
	}

	//资料更新
	public static function updateData($id, $data) {
		Teacher::where('id', $id) -> update($data);
		$Teacher = Teacher::where('id', $id) -> find();
		return $Teacher;
	}

}
