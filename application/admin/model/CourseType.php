<?php
namespace app\admin\model;
use think\Model;

class CourseType extends Model { 
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$CourseType = CourseType::create($data);
		return $CourseType;
	}

	//资料更新
	public static function updateData($id, $data) {
		CourseType::where('id', $id) -> update($data);
		$CourseType = CourseType::where('id', $id) -> find();
		return $CourseType;
	}

	// 按名称查询一条数据
	public static function getOne($id) {
		$data['del'] = 0;
		$CourseType = CourseType::where($data) -> where('id', $id) -> find();
		return $CourseType;
	}
	// 按名称查询一条数据
	public static function getName($name) {
		$data['del'] = 0;
		$CourseType = CourseType::where($data) -> where('name', $name) -> find();
		return $CourseType;
	}

	//获取列表
	public static function getList($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$data['del'] = 0;
		$list = CourseType::where($data) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$data['del'] = 0;
		$Count = CourseType::where($data) -> count();
		return $Count;
	}
}
