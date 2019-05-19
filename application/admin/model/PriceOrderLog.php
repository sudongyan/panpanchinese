<?php
namespace app\admin\model;
use think\Model;

class PriceOrderLog extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$PriceOrderLog = PriceOrderLog::create($data);
		return $PriceOrderLog;
	}
}
