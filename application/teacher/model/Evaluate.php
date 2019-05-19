<?php
namespace app\teacher\model;
use think\Model;

class Evaluate extends Model {
	//获取列表
	public static function getList($tid, $p, $order) {
		$data['teacher_id'] = $tid;
		if (!isset($p)) {
			$p = 1;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Evaluate::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}
	
	// 按order_id查询一条数据
	public static function getOne($oid) {
		$Evaluate = Evaluate::where('order_id', $oid) -> find();
		return $Evaluate;
	}

}
