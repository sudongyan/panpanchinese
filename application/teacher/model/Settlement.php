<?php
namespace app\teacher\model;
use think\Model;

class Settlement extends Model {

	//获取数量
	public static function getNum($tid,$time) {
		$data['teacher_id'] = $tid;
		$data['time'] = $time;
		$num = Settlement::where($data) -> value('num');
		return $num;
	}
}
