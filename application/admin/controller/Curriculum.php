<?php
namespace app\admin\controller;
use think\Controller;
class Curriculum extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
	}

	//课程列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			if (isset($data['stime']) && $data['stime'] != '' && isset($data['etime']) && $data['etime'] != ''|| isset($data['week'])  && $data['week'] != ''|| isset($data['time']) && $data['time'] != '') {
				$interval = null;
				$week = null;
				$time1 = null;
				$time2 = null;
				if (isset($data['stime']) && $data['stime'] != '' && isset($data['etime']) && $data['etime'] != '' ) {
					$time1 = strtotime($data['stime']);
					$time2 = strtotime($data['etime']);
				}
				if (isset($data['week'])  && $data['week'] != '') {
					$week = array();
					for ($i = 0; $i < count($data['week']); $i++) {
						array_push($week, $data['week'][$i]);
					}
					$week = implode(",",$week);
				}
				if (isset($data['time'])  && $data['time'] != '') {
					$interval = array();
					for ($i = 0; $i < count($data['time']); $i++) {
						$theinterval = explode(",", $data['time'][$i]);
						for ($s = 0; $s < count($theinterval); $s++) {
							array_push($interval, $theinterval[$s]);
						}
					}
					$interval = implode(",",$interval);
				}
				$list = model('Curriculum') -> getScreen($time1,$time2,$week,$interval,$data['p'], $data['state'], 'desc');
			}else{
				$list = model('Curriculum') -> getList($data['p'], $data['state'], 'desc');
			}
			for ($i = 0; $i < count($list); $i++) {
				$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
				$list[$i]['teacher_account'] = $teacher['account'];
				$list[$i]['teacher_nickName'] = $teacher['nickName'];
				$list[$i]['teacher_wx'] = $teacher['wx'];
				$list[$i]['interval'] = date("Y-m-d H:i", $list[$i]['time']);
				if ($list[$i]['student_id'] == 0) {
					$list[$i]['stateinfo'] = '待预约';
				} else {
					$student = model('Student') -> getOne($list[$i]['student_id']);
					$list[$i]['student_account'] = $student['account'];
					$list[$i]['student_nickName'] = $student['nickName'];
					$list[$i]['student_wx'] = $student['wx'];
					$order = model('Order') -> getCurriculum($list[$i]['time'], $list[$i]['teacher_id']);
					$list[$i]['order_id'] = $order['id'];
					$list[$i]['order_create_time'] = $order['create_time'];
					if ($order['state'] == 0) {
						$list[$i]['order_stateinfo'] = '待开始';
					} else if ($order['state'] == 1) {
						$list[$i]['order_stateinfo'] = '已完成';
					} else if ($order['state'] == 2) {
						$list[$i]['order_stateinfo'] = '已取消';
					} else if ($order['state'] == 3) {
						$list[$i]['order_stateinfo'] = '待评价';
					}
					$list[$i]['stateinfo'] = $list[$i]['order_stateinfo'];
					$course = model('Course') -> getOne($order['course']);
					$list[$i]['course'] = $course['name_cn'];
					$coursetype = model('CourseType') -> getOne($course['type']);
					$list[$i]['courseType'] = $coursetype['name'];
					$list[$i]['class'] = $order['class'];
				}
			}
			if (isset($data['stime']) && $data['stime'] != '' && isset($data['etime']) && $data['etime'] != ''|| isset($data['week'])  && $data['week'] != ''|| isset($data['time']) && $data['time'] != '') {
				$interval = null;
				$week = null;
				$time1 = null;
				$time2 = null;
				if (isset($data['stime']) && $data['stime'] != '' && isset($data['etime']) && $data['etime'] != '' ) {
					$time1 = strtotime($data['stime']);
					$time2 = strtotime($data['etime']);
				}
				if (isset($data['week'])  && $data['week'] != '') {
					$week = array();
					for ($i = 0; $i < count($data['week']); $i++) {
						array_push($week, $data['week'][$i]);
					}
					$week = implode(",",$week);
				}
				if (isset($data['time'])  && $data['time'] != '') {
					$interval = array();
					for ($i = 0; $i < count($data['time']); $i++) {
						$theinterval = explode(",", $data['time'][$i]);
						for ($s = 0; $s < count($theinterval); $s++) {
							array_push($interval, $theinterval[$s]);
						}
					}
					$interval = implode(",",$interval);
				}
				$total = model('Curriculum') -> getScreenCount($time1,$time2,$week,$interval,$data['state']);
			}else{
				$total = model('Curriculum') -> getCount($data['state']);
			}
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('state', $data['state']);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

}
