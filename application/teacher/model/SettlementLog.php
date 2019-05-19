<?php
namespace app\teacher\model;
use think\Model;

class SettlementLog extends Model {

	//获取列表
	public static function getList() {
		$list = SettlementLog::order('time', 'desc') -> limit(2) -> select();
		return $list;
	}

}
