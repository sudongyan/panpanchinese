<?php
namespace app\admin\model;
use think\Model;

class Course extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Course = Course::create($data);
		return $Course;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$Course = Course::where('id', $id) -> find();
		return $Course;
	}

	//资料更新
	public static function updateData($id, $data) {
		Course::where('id', $id) -> update($data);
		$Course = Course::where('id', $id) -> find();
		return $Course;
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
		$data['del'] = 0;
		$list = Course::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}
	//获取列表
	public static function getTypeList($type) {
		if (!isset($order)) {
			$order = 'asc';
		}
		$data['type'] = $type;
		$data['del'] = 0;
		$list = Course::where($data) -> order('id', $order) -> select();
		return $list;
	}

	//获取搜索列表
	public static function getAllSearchList($s) {
		$data['name_cn|name_en|name_ko']=array('like','%'. $s .'%');
		$data['del'] = 0;
		$list = Course::where($data) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$data['del'] = 0;
		$Count = Course::where($data) -> count();
		return $Count;
	}
}
