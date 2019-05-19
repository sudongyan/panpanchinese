<?php
namespace app\teacher\model;
use think\Model;

class WxToken extends Model {
	
	// 查询一条数据
	public static function getOne($name) {
		$data['name'] = $name;
		$WxToken = WxToken::where($data) -> find();
		return $WxToken;
	}

}
