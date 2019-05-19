<?php
namespace app\ko\model;
use think\Model;

class Evaluate extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Evaluate = Evaluate::create($data);
		return $Evaluate;
	}
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
	
	//获取数量
	public static function getCount($tid) {
		$data['teacher_id'] = $tid;
		$Count = Evaluate::where($data) -> count();
		return $Count;
	}
	
	//计算评价平均数
	public static function evaluate($id) {
		$data['teacher_id'] = $id;
		$Evaluate = Evaluate::where($data)->avg('score');
		return $Evaluate;
	}

}
