<?php
namespace app\admin\model;
use think\Model;

class ExchangeRate extends Model {
	// 查询一条数据
	public static function getOne($original,$conversion) {
		$data['original'] = $original;
		$data['conversion'] = $conversion;
		$ExchangeRate = ExchangeRate::where($data) -> find();
		return $ExchangeRate;
	}
	
	//资料更新
	public static function updateData($original,$conversion,$data) {
		$info['original'] = $original;
		$info['conversion'] = $conversion;
		$data['time'] = time();
		ExchangeRate::where($info) -> update($data);
		$ExchangeRate = ExchangeRate::where($info) -> find();
		return $ExchangeRate;
	}
}
