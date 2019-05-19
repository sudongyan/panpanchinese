<?php
namespace app\ko\model;
use think\Model;

class PriceOrder extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$PriceOrder = PriceOrder::create($data);
		return $PriceOrder;
	}

	//获取列表
	public static function getList($sid, $state, $order) {
		$data['student_id'] = $sid;
		if ($state != null) {
			$data['state'] = $state;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = PriceOrder::where($data) -> order('payment_time', $order) -> select();
		return $list;
	}
	
	//获取列表
	public static function getEndtimeList($sid) {
		$data['student_id'] = $sid;
		$data['state'] = 1;
		$order = 'asc';
		$list = PriceOrder::where('end_time','>',time()) -> where($data) -> order('end_time', $order) -> select();
		return $list;
	}
	// 查询一条数据
	public static function getOne($id) {
		$data['order_id'] = $id;
		$PriceOrder = PriceOrder::where($data) -> find();
		return $PriceOrder;
	}
	// wxid查询一条数据
	public static function getWxId($id) {
		$data['prepay_id'] = $id;
		$PriceOrder = PriceOrder::where($data) -> find();
		return $PriceOrder;
	}

	//资料更新
	public static function updateData($id, $data) {
		PriceOrder::where('order_id', $id) -> update($data);
		$PriceOrder = PriceOrder::where('order_id', $id) -> find();
		return $PriceOrder;
	}

}
