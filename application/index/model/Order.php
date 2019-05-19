<?php
namespace app\index\model;
use think\Model;

class Order extends Model {

	//获取通知列表
	public static function getRemind() {
		$data['state'] = 0;
		$data['Remind'] = 0;
		$list = Order::where($data) -> where('time', '<', time() + 1800) -> where('time', '>', time()) -> select();
		return $list;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Order = Order::where($data) -> find();
		return $Order;
	}
	
	//资料更新
	public static function updateData($id, $data) {
		Order::where('id', $id) -> update($data);
		$Order = Order::where('id', $id) -> find();
		return $Order;
	}

}
