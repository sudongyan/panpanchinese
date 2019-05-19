<?php
namespace app\index\model;
use think\Model;

class WxToken extends Model {
	
	// 查询一条数据
	public static function getOne($name) {
		$data['name'] = $name;
		$WxToken = WxToken::where($data) -> find();
		return $WxToken;
	}
	
	//资料更新
	public static function updateData($name, $data) {
		WxToken::where('name', $name) -> update($data);
		$WxToken = WxToken::where('name', $name) -> find();
		return $WxToken;
	}

}
