<?php
namespace app\admin\model;
use think\Model;

class Curriculum extends Model {

	//获取列表
	public static function getList($p, $state, $order) {
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
		$list = Curriculum::where($data) -> page($p, 20) -> order('time', $order) -> select();
		return $list;
	}

	//获取筛选列表
	public static function getScreen($time1, $time2, $week, $interval, $p, $state, $order) {
		$data = array();
		if (isset($time1) && isset($time2)  && $time1 != null  && $time2 != null) {
			$data['time'] = array('between', $time1 . ',' . $time2);
		}
		if (isset($week)  && $week != null) {
			$data['week'] = array('in', $week);
		}
		if (isset($interval)  && $interval != null) {
			$data['interval'] = array('in', $interval);
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		if (isset($state)) {
			$data['state'] = $state;
		}
		$list = Curriculum::where($data) -> page($p, 20) -> order('time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = Curriculum::where($data) -> count();
		return $Count;
	}

	//获取筛选数量
	public static function getScreenCount($time1, $time2, $week, $interval, $state) {
		$data = array();
		if (isset($time1) && isset($time2)  && $time1 != null  && $time2 != null) {
			$data['time'] = array('between', $time1 . ',' . $time2);
		}
		if (isset($week)  && $week != null) {
			$data['week'] = array('in', $week);
		}
		if (isset($interval)  && $interval != null) {
			$data['interval'] = array('in', $interval);
		}
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = Curriculum::where($data) -> count();
		return $Count;
	}

	//重置
	public static function Reset($tid, $time) {
		$data['teacher_id'] = $tid;
		$data['time'] = $time;
		$info['student_id'] = 0;
		$info['state'] = 0;
		$Curriculum = Curriculum::where($data) -> update($info);
		return $Curriculum;
	}

}
