<?php
namespace app\teacher\model;
use think\Model;

class Order extends Model {
	//获取列表
	public static function getList($tid, $p, $state, $order) {
		$data['teacher_id'] = $tid;
		if ($state != null) {
			$data['state'] = $state;
		}
		if (!isset($p)) {
			$p = 1;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Order::where($data) -> page($p, 20) -> order('time', $order) -> select();
		return $list;
	}	
	
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Order = Order::where($data) -> find();
		return $Order;
	}
	
	//获取过期列表
	public static function getOverdueList() {
		$data['state'] = 0;
		$list = Order::where($data)->where('time','<',time()) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($tid, $state) {
		$data['teacher_id'] = $tid;
		$data['state'] = $state;
		$Count = Order::where($data) -> count();
		return $Count;
	}
	
	//获取已完成数量
	public static function getOldCount($tid) {
		$data['teacher_id'] = $tid;
		$Count = Order::where($data) -> where('state',['=',3],['=',1],'or') -> count();
		return $Count;
	}
	
	//获取已完成列表
	public static function getOldList($tid) {
		$data['teacher_id'] = $tid;
		$list = Order::where($data) -> where('state',['=',3],['=',1],'or') -> select();
		return $list;
	}
	
	//资料更新
	public static function updateData($id, $data) {
		Order::where('id', $id) -> update($data);
		$Order = Order::where('id', $id) -> find();
		return $Order;
	}

	//获取结算数量
	public static function getSettlementCount($tid, $stime, $etime) {
		$data['teacher_id'] = $tid;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$Count = Order::where($data) -> where('state',['=',3],['=',1],'or') -> whereBetween('time',$time) -> count();
		return $Count;
	}

	//获取结算列表
	public static function getSettlementList($tid, $stime, $etime) {
		$data['teacher_id'] = $tid;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$list = Order::where($data) -> where('state',['=',3],['=',1],'or') -> whereBetween('time',$time) -> select();
		return $list;
	}
}
