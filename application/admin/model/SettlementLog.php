<?php
namespace app\admin\model;
use think\Model;

class SettlementLog extends Model {
	//增加新数据
	public static function addData($data) {
		$SettlementLog = SettlementLog::create($data);
		return $SettlementLog;
	}

	// 查询一条数据
	public static function getDay($day) {
		$data['day'] = $day;
		$SettlementLog = SettlementLog::where($data) -> find();
		return $SettlementLog;
	}

	//获取列表
	public static function getList($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = SettlementLog::order('id', $order) -> limit(12) -> select();
		return $list;
	}

}
