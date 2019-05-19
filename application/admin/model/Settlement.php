<?php
namespace app\admin\model;
use think\Model;

class Settlement extends Model {
	//增加新数据
	public static function addData($data) {
		$Settlement = Settlement::create($data);
		return $Settlement;
	}

	//获取列表
	public static function getList($p, $day, $order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$data['day'] = $day;
		$list = Settlement::where($data) -> page($p, 20) -> order('id', $order) -> select();
		return $list;
	}

	//获取全部列表
	public static function getAllList($day, $order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		$data['day'] = $day;
		$list = Settlement::where($data) -> order('id', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($day) {
		$data['day'] = $day;
		$Count = Settlement::where($data) -> count();
		return $Count;
	}
}
