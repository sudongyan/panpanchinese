<?php
namespace app\teacher\model;
use think\Model;

class Balance extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Teacher = Balance::create($data);
		return $Teacher;
	}
	
	//获取余额
	public static function getBalanceCount($id) {
		$data['user_id'] = $id;
		$data['user_type'] = 1;
		$data['type'] = 1;
		$Count1 = Balance::where($data)->sum('num');
		$data['type'] = 0;
		$Count2 = Balance::where($data)->sum('num');
		$Count = $Count1 - $Count2;
		return $Count;
	}
	
	//获取全部累计
	public static function getAllCount($id) {
		$data['user_id'] = $id;
		$data['user_type'] = 1;
		$data['type'] = 1;
		$Count = Balance::where($data)->sum('num');
		return $Count;
	}
	
	
	//获取结算数量
	public static function getSettlementCount($tid, $stime, $etime, $type) {
		$data['user_id'] = $tid;
		$data['user_type'] = 1;
		$data['admin_id'] = array('neq',0);
		$data['type'] = $type;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$Count = Balance::where($data) -> whereBetween('create_time',$time) -> sum('num');
		return $Count;
	}
	
}
