<?php
namespace app\en\model;
use think\Model;

class Order extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Order = Order::create($data);
		return $Order;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Order = Order::where($data) -> find();
		return $Order;
	}

	//获取列表
	public static function getList($sid, $p, $state, $order) {
		$data['student_id'] = $sid;
		if ($state != null) {
			$data['state'] = $state;
		}
		if (!isset($p)) {
			$p = 1;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Order::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取过期列表
	public static function getOverdueList() {
		$data['state'] = 0;
		$list = Order::where($data) -> where('time', '<', time()) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($sid, $state) {
		$data['student_id'] = $sid;
		$data['state'] = $state;
		$Count = Order::where($data) -> count();
		return $Count;
	}

	//资料更新
	public static function updateData($id, $data) {
		Order::where('id', $id) -> update($data);
		$Order = Order::where('id', $id) -> find();
		return $Order;
	}

	//获取时段内数量
	public static function getDayNum($sid, $stime, $etime) {
		$data['student_id'] = $sid;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$Count = Order::where($data) ->where('state','<>',2) -> whereBetween('time',$time) -> count();
		return $Count;
	}

}
