<?php
namespace app\admin\model;
use think\Model; 
 
class Teacher extends Model {
	//增加新数据
	public static function addData($data) {
		$Teacher = Teacher::create($data);
		return $Teacher;
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

	//资料更新
	public static function updateData($id, $data) {
		Teacher::where('id', $id) -> update($data);
		$Teacher = Teacher::where('id', $id) -> find();
		return $Teacher;
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
		$list = Teacher::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}

	//获取全部列表
	public static function getAllList($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Teacher::order('create_time', $order) -> select();
		return $list;
	}
	
	//获取搜索列表
	public static function getSearchList($p,$s,$order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$data['nickName|account|wx|phone']=array('like','%'. $s .'%');
		$list = Teacher::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}
	
	//获取搜索总列表
	public static function getAllSearchList($s) {
		$data['nickName|account|wx|phone']=array('like','%'. $s .'%');
		$list = Teacher::where($data) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = Teacher::where($data) -> count();
		return $Count;
	}

	//获取搜索数量
	public static function getSearchCount($s) {
		$data = array();
		$data['nickName|account|wx|phone']=array('like','%'. $s .'%');
		$Count = Teacher::where($data) -> count();
		return $Count;
	}

}
