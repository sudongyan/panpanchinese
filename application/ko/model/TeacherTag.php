<?php
namespace app\ko\model;
use think\Model;

class TeacherTag extends Model { 
	//获取全部列表
	public static function getAllList($state,$order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (isset($state)) {
			$data['state'] = $state;
		}
		$list = TeacherTag::where($data) -> order('create_time', $order) -> select();
		return $list;
	}
	
		// 查询一条数据
	public static function getOne($id) {
		$TeacherTag = TeacherTag::where('id', $id) -> find();
		return $TeacherTag;
	}
}
