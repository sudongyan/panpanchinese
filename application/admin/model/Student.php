<?php
namespace app\admin\model;
use think\Model; 
 
class Student extends Model {
	//增加新数据
	public static function addData($data) {
		$Student = Student::create($data);
		return $Student;
	}
	
	// 查询一条数据
	public static function getOne($id) {
		$data['id'] = $id;
		$Student = Student::where($data) -> find();
		return $Student;
	}
	
	// 按账号查询一条数据
	public static function getAccount($account) {
		$data['account'] = $account;
		$Student = Student::where($data) -> find();
		return $Student;
	}

	//资料更新
	public static function updateData($id, $data) {
		Student::where('id', $id) -> update($data);
		$Student = Student::where('id', $id) -> find();
		return $Student;
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
		$list = Student::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}
	
	//获取列表
	public static function getAllList($state,$order) {
		$data = array();
		if (!isset($order)) {
			$order = 'desc';
		}
		if (isset($state)) {
			$data['state'] = $state;
		}
		$list = Student::where($data) -> order('create_time', $order) -> select();
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
		$list = Student::where($data) -> page($p, 20) -> order('create_time', $order) -> select();
		return $list;
	}
	
	//获取搜索总列表
	public static function getAllSearchList($s) {
		$data['nickName|account|wx|phone']=array('like','%'. $s .'%');
		$list = Student::where($data) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = Student::where($data) -> count();
		return $Count;
	}

	//获取搜索数量
	public static function getSearchCount($s) {
		$data = array();
		$data['nickName|account|wx|phone']=array('like','%'. $s .'%');
		$Count = Student::where($data) -> count();
		return $Count;
	}

}
