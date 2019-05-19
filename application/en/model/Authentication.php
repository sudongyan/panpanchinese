<?php
namespace app\en\model;
use think\Model;

class Authentication extends Model {
	//增加新数据
	public static function addData($data) {
		$Teacher = Authentication::create($data);
		return $Teacher;
	}
	
	// 按账号查询一条数据
	public static function getAccount($account,$type) {
		$data['account'] = $account;
		$data['type'] = $type;
		$Authentication = Authentication::where($data) -> find();
		return $Authentication;
	}

}
