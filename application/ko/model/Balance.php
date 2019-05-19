<?php
namespace app\ko\model;
use think\Model;

class Balance extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Teacher = Balance::create($data);
		return $Teacher;
	}
	
	//获取余额
	public static function getBalanceCount($id,$user_type) {
		$data['user_id'] = $id;
		$data['user_type'] = $user_type;
		$data['type'] = 1;
		$Count1 = Balance::where($data)->sum('num');
		$data['type'] = 0;
		$Count2 = Balance::where($data)->sum('num');
		$Count = $Count1 - $Count2;
		return $Count;
	}
	
	//获取已使用
	public static function getOldCount($id) {
		$data['user_id'] = $id;
		$data['user_type'] = 0;
		$data['type'] = 0;
		$Count = Balance::where($data)->sum('num');
		return $Count;
	}
	
}
