<?php
namespace app\admin\model;
use think\Model;

class Order extends Model {

	// 查询一条数据
	public static function getOne($id) {
		$Order = Order::where('id', $id) -> find();
		return $Order;
	}

	// 按时间教师查询一条数据
	public static function getCurriculum($time, $teacher_id) {
		$data['time'] = $time;
		$data['teacher_id'] = $teacher_id;
		$Order = Order::where($data) -> find();
		return $Order;
	}

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
		$list = Order::where($data) -> page($p, 20) -> order('time', $order) -> select();
		return $list;
	}

	//获取搜索列表
	public static function getSearchList($p, $s, $state, $order) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		if (!isset($order)) {
			$order = 'desc';
		}
		if (!isset($p)) {
			$p = 1;
		}
		$data['id'] = array('like', '%' . $s . '%');
		$teacher = model('Teacher') -> getAllSearchList($s);
		$teacherid = array();
		for ($i = 0; $i < count($teacher); $i++) {
			array_push($teacherid, $teacher[$i]['id']);
		}
		$teacherData = implode(",", $teacherid);
		$student = model('Student') -> getAllSearchList($s);
		$studentid = array();
		for ($i = 0; $i < count($student); $i++) {
			array_push($studentid, $student[$i]['id']);
		}
		$studentData = implode(",", $studentid);
		$course = model('Course') -> getAllSearchList($s);
		$courseid = array();
		for ($i = 0; $i < count($course); $i++) {
			array_push($courseid, $course[$i]['id']);
		}
		$courseData = implode(",", $courseid);
		$list = Order::whereOr($data) -> whereOr('teacher_id', 'in', $teacherData) -> whereOr('student_id', 'in', $studentData) -> whereOr('course', 'in', $courseData) -> page($p, 20) -> order('time', $order) -> select();
		return $list;
	}

	//获取数量
	public static function getCount($state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$Count = Order::where($data) -> count();
		return $Count;
	}

	//获取搜索数量
	public static function getSearchCount($s, $state) {
		$data = array();
		if (isset($state)) {
			$data['state'] = $state;
		}
		$data['id'] = array('like', $s);
		$teacher = model('Teacher') -> getAllSearchList($s);
		$teacherid = array();
		for ($i = 0; $i < count($teacher); $i++) {
			array_push($teacherid, $teacher[$i]['id']);
		}
		$teacherData = implode(",", $teacherid);
		$student = model('Student') -> getAllSearchList($s);
		$studentid = array();
		for ($i = 0; $i < count($student); $i++) {
			array_push($studentid, $student[$i]['id']);
		}
		$studentData = implode(",", $studentid);
		$course = model('Course') -> getAllSearchList($s);
		$courseid = array();
		for ($i = 0; $i < count($course); $i++) {
			array_push($courseid, $course[$i]['id']);
		}
		$courseData = implode(",", $courseid);
		$Count = Order::whereOr($data) -> whereOr('teacher_id', 'in', $teacherData) -> whereOr('student_id', 'in', $studentData) -> whereOr('course', 'in', $courseData) -> count();
		return $Count;
	}

	//资料更新
	public static function updateData($id, $data) {
		Order::where('id', $id) -> update($data);
		$Order = Order::where('id', $id) -> find();
		return $Order;
	}

	//获取状态数量
	public static function getStateCount($type, $id, $state) {
		if ($type == 't') {
			$data['teacher_id'] = $id;
		} else {
			$data['student_id'] = $id;
		}
		$data['state'] = $state;
		$Count = Order::where($data) -> count();
		return $Count;
	}

	//获取结算数量
	public static function getSettlementCount($tid, $stime, $etime) {
		$data['teacher_id'] = $tid;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$Count = Order::where($data) -> where('state',['=',3],['=',1],'or') -> whereBetween('time',$time) -> count();
		return $Count;
	}
	
	//获取结算列表
	public static function getSettlementList($tid, $stime, $etime) {
		$data['teacher_id'] = $tid;
		$etime = intval($etime) - 1;
		$time = $stime . ',' . $etime;
		$list = Order::where($data) -> where('state',['=',3],['=',1],'or') -> whereBetween('time',$time) -> select();
		return $list;
	}
	
	
	//获取最新订单
	public static function getStudentNewOrder($sid) {
		$data['student_id'] = $sid;
		$Order = Order::where($data) -> order('create_time', 'desc') -> find();
		return $Order;
	}

}
