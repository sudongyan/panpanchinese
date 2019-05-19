<?php
namespace app\teacher\model;
use think\Model;

class PriceClass extends Model {
	 
	//获取列表
	public static function getList() {
		if (!isset($order)) {
			$order = 'asc';
		}
		$list = PriceClass::order('period', $order) -> select();
		return $list;
	}
}
