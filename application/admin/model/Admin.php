<?php
namespace app\admin\model;
use think\Model; 
 
class Admin extends Model {
	//增加新数据
	public static function addData($data) {
		$data['create_time'] = time();
		$Admin = Admin::create($data);
		return $Admin;
	}

	// 登录
	public static function Login($account, $password) {
		$data['account'] = $account;
		$data['password'] = md5($password . 'ZackSun');
		$data['state'] = 1;
		$Admin = Admin::where($data) -> find();
		return $Admin;
	}
	
	// 查询一条数据
	public static function getOne($account) {
		$data['account'] = $account;
		$Admin = Admin::where($data) -> find();
		return $Admin;
	}
		
	// ID查询一条数据
	public static function getId($id) {
		$data['id'] = $id;
		$Admin = Admin::where($data) -> find();
		return $Admin;
	}

	//资料更新
	public static function updateData($id, $data) {
		Admin::where('id', $id) -> update($data);
		$Admin = Admin::where('id', $id) -> find();
		return $Admin;
	}

	// 更新token
	public static function newToken($id, $token) {
		$data['token'] = $token;
		$Admin = Admin::where('id', $id) -> update($data);
		return $Admin;
	}

	// 查询token
	public static function getToken($token) {
		$Admin = Admin::where('token', $token) -> find();
		return $Admin;
	}

	//获取管理员列表
	public static function getList($p, $order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$list = Admin::page($p, 20) -> order('id', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount() {
		$Count = Admin::count();
		return $Count;
	}

}
