<?php
namespace app\admin\model;
use think\Model;

class Timezone extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Timezone = Timezone::create($data);
		return $Timezone;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Timezone = Timezone::where('id', $id) -> find();
		return $Timezone;
	}

	//资料更新
	public static function updateData($id, $data) {
		Timezone::where('id', $id) -> update($data);
		$Timezone = Timezone::where('id', $id) -> find();
		return $Timezone;
	}

	//获取列表
	public static function getList($p,$state,$order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		if (isset($state)) {
			$data['state'] = $state;
		}
		$list = Timezone::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}
	//获取全部列表
	public static function getAllList($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Timezone::order('create_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = Timezone::where($data) -> count();
		return $Count;
	}
}
