<?php
namespace app\index\model;
use think\Model;

class Timezone extends Model {
	
		// 查询一条数据
	public static function getOne($id) {
		$Timezone = Timezone::where('id', $id) -> find();
		return $Timezone;
	}
}
