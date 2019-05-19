<?php
namespace app\en\model;
use think\Model;

class Timezone extends Model { 
	//获取全部列表
	public static function getAllList($state,$order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (isset($state)) {
			$data['state'] = $state;
		}
		$list = Timezone::where($data) -> order('create_time', $order) -> select();
		return $list;
	}
	
		// 查询一条数据
	public static function getOne($id) {
		$Timezone = Timezone::where('id', $id) -> find();
		return $Timezone;
	}
}
