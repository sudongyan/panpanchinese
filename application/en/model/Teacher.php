<?php
namespace app\en\model;
use think\Model;

class Teacher extends Model {

	//资料更新
	public static function updateData($id, $data) {
		Teacher::where('id', $id) -> update($data);
		$Teacher = Teacher::where('id', $id) -> find();
		return $Teacher;
	}
	
	//获取列表
	public static function getList($p, $order) {
		$data['state'] = 1;
		if (!isset($p)) {
			$p = 1;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Teacher::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取搜索列表
	public static function getSearchList($s, $p, $order) {
		$data['state'] = 1;
		if (!isset($p)) {
			$p = 1;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Teacher::where($data) -> where('nickName', 'like', '%' . $s . '%') -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Teacher = Teacher::where($data) -> find();
		return $Teacher;
	}

	// 按账号查询一条数据
	public static function getAccount($account) {
		$data['account'] = $account;
		$Teacher = Teacher::where($data) -> find();
		return $Teacher;
	}

}
