<?php
namespace app\teacher\model;
use think\Model;

class Curriculum extends Model {
	//增加新数据
	public static function addData($data) {
		$Curriculum = Curriculum::create($data);
		return $Curriculum;
	}
	
	//获取列表
	public static function getList($tid, $date, $order) {
		$data['teacher_id'] = $tid;
		$data['day'] = $date;
		if (!isset($order)) {
			$order = 'desc';
		}
		$list = Curriculum::where($data) -> order('id', $order) -> select();
		return $list;
	}
	
	// 查询一条数据
	public static function getOne($tid, $date,$interval) {
		$data['teacher_id'] = $tid;
		$data['day'] = $date;
		$data['interval'] = $interval;
		$Curriculum = Curriculum::where($data) -> find();
		return $Curriculum;
	}
	
	//删除数据
	public static function delData($data) {
		$Curriculum = Curriculum::where($data) -> where('student_id','EQ',0) -> delete();
		return $Curriculum;
	}

	//资料更新
	public static function updateTeacherData($tid,$time,$data) {
		Curriculum::where('teacher_id', $tid) -> where('time', $time) -> update($data);
		$Curriculum = Curriculum::where('teacher_id', $tid) -> where('time', $time) -> find();
		return $Curriculum;
	}
	
	//重置
	public static function Reset($tid,$time) {
		$data['teacher_id'] = $tid;
		$data['time'] = $time;
		$info['student_id'] = 0;
		$info['state'] = 0;
		$Curriculum = Curriculum::where($data) -> update($info);
		return $Curriculum;
	}

}
