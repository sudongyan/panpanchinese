<?php
namespace app\admin\model;
use think\Model;

class Authentication extends Model {

	// 查询一条数据
	public static function getOne($id) {
		$Authentication = Authentication::where('id', $id) -> find();
		return $Authentication;
	}
	
	// 按账号查询一条数据
	public static function getAccount($account,$type) {
		$data['account'] = $account;
		$data['type'] = $type;
		$Authentication = Authentication::where($data) -> find();
		return $Authentication;
	}

	//删除数据
	public static function delData($id) {
		$Authentication = Authentication::where('id', $id) -> delete();
		return $Authentication;
	}

	//获取列表
	public static function getList($p,$type, $order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		if (isset($type)) {
			$data['type'] = $type;
		}
		$list = Authentication::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($type) {
		$data = array();
		if (isset($type)) {
			$data['type'] = $type;
		}
		$Count = Authentication::where($data) -> count();
		return $Count;
	}

}
