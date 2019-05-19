<?php
namespace app\admin\model;
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
		$Count1 = Balance::where($data) -> sum('num');
		$data['type'] = 0;
		$Count2 = Balance::where($data) -> sum('num');
		$Count = $Count1 - $Count2;
		return $Count;
	}

	//获取已使用
	public static function getOldCount($id) {
		$data['user_id'] = $id;
		$data['user_type'] = 0;
		$data['type'] = 0;
		$Count = Balance::where($data) -> sum('num');
		return $Count;
	}

	//获取列表
	public static function getList($p, $user_id, $order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$data['user_id'] = $user_id;
		$data['user_type'] = 0;
		$list = Balance::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($user_id) {
		$data['user_id'] = $user_id;
		$data['user_type'] = 0;
		$Count = Balance::where($data) -> count();
		return $Count;
	}

	//获取列表
	public static function getListT($p, $user_id, $order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$data['user_id'] = $user_id;
		$data['user_type'] = 1;
		$list = Balance::where($data) ->where('admin_id','>',0) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCountT($user_id) {
		$data['user_id'] = $user_id;
		$data['user_type'] = 1;
		$Count = Balance::where($data) ->where('admin_id','>',0)  -> count();
		return $Count;
	}

	//获取结算数量
	public static function getSettlementCount($tid, $stime, $etime) {
		$data['user_id'] = $tid;
		$data['user_type'] = 1;
		$data['admin_id'] = array('neq',0);
		$data['type'] = 1;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$Count1 = Balance::where($data) -> whereBetween('create_time',$time) -> sum('num');
		$data['type'] = 0;
		$Count2 = Balance::where($data) -> whereBetween('create_time',$time) -> sum('num');
		return $Count1 - $Count2;
	}

}
