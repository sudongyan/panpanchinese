<?php
namespace app\ko\model;
use think\Model;

class Curriculum extends Model {
	//获取列表
	public static function getList($tid, $time1, $time2, $order) {
		$data['teacher_id'] = $tid;
		if (!isset($order)) {
			$order = 'desc';
		}
		$time = $time1.','.$time2;
		$list = Curriculum::where($data) ->where('time','between',$time) -> order('time', $order) -> select();
		return $list;
	}
	//获取全部列表
	public static function getAllList($time1, $time2, $order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$time = $time1.','.$time2;
		$list = Curriculum::where('time','between',$time) -> order('time', $order) -> select();
		return $list;
	}
	
		//按时间获取全部列表
	public static function getTimeList($time, $state, $order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$data['time'] = $time;
		$data['state'] = $state;
		$list = Curriculum::where($data) -> order('id', $order) -> select();
		return $list;
	}
	
	//按排序获取未预约列表
	public static function getTimeOrder($order) {
		if (!isset($order)) {
			$order = 'desc';
		}
		$data['time'] = array('>',time()+3600);
		$data['state'] = 0;
		$list = Curriculum::where($data) -> order('time', $order) -> select();
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
	
	// ID查询一条数据
	public static function getId($id) {
		$data['id'] = $id;
		$Curriculum = Curriculum::where($data) -> find();
		return $Curriculum;
	}
	
	// 时段查询数据
	public static function getTime($tid, $time, $interval) {
		$data['teacher_id'] = $tid;
		$data['state'] = 0;
		$Curriculum = Curriculum::where($data) -> where('time','>',$time) -> where('interval','in',$interval) -> find();
		return $Curriculum;
	}
	
	// 星期查询数据
	public static function getWeek($tid, $time,$week) {
		$data['teacher_id'] = $tid;
		$data['state'] = 0;
		$Curriculum = Curriculum::where($data) -> where('time','>',$time) -> where('week','in',$week) -> find();
		return $Curriculum;
	}

	// 星期时段查询数据
	public static function getWeekTime($tid, $time, $interval, $week) {
		$data['teacher_id'] = $tid;
		$data['state'] = 0;
		$Curriculum = Curriculum::where($data) -> where('time','>',$time) -> where('interval','in',$interval) -> where('week','in',$week) -> find();
		return $Curriculum;
	}
	
	//资料更新
	public static function updateData($id, $data) {
		Curriculum::where('id', $id) -> update($data);
		$Curriculum = Curriculum::where('id', $id) -> find();
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
