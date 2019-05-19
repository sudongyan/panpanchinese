<?php
namespace app\admin\controller;
use think\Controller;
class Order extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('order') == 0 && session('id') != 1) {
			$this -> error('无权限');
		}
	}

	//订单列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			if (isset($data['search'])) {
				$list = model('Order') -> getSearchList($data['p'], $data['search'], $data['state'], 'desc');
			} else {
				$list = model('Order') -> getList($data['p'], $data['state'], 'desc');
			}
			for ($i = 0; $i < count($list); $i++) {
				$student = model('Student') -> getOne($list[$i]['student_id']);
				$list[$i]['saccount'] = $student['account'];
				$list[$i]['snickName'] = $student['nickName'];
				$list[$i]['swx'] = $student['wx'];
				$teacher = model('Teacher') -> getOne($list[$i]['teacher_id']);
				$list[$i]['taccount'] = $teacher['account'];
				$list[$i]['tnickName'] = $teacher['nickName'];
				$list[$i]['twx'] = $teacher['wx'];
				$course = model('Course') -> getOne($list[$i]['course']);
				$CoursePeriod = model('CoursePeriod') -> getNum($list[$i]['course'], $list[$i]['class']);
				$list[$i]['course_period'] = $CoursePeriod['name_ko'];
				$list[$i]['course'] = $course['name_cn'];
				$coursetype = model('CourseType') -> getOne($course['type']);
				$list[$i]['courseType'] = $coursetype['name'];
				if ($list[$i]['time'] > time() - 86400 * 10 && $list[$i]['state'] != 2) {
					$list[$i]['thecancel'] = 1;
				} else {
					$list[$i]['thecancel'] = 0;
				}
				$list[$i]['time'] = date('Y-m-d H:i:s', $list[$i]['time']);
				if ($list[$i]['reason_type'] == 0) {
					$list[$i]['reason_type'] = '学生取消';
				} else if ($list[$i]['reason_type'] == 1) {
					$list[$i]['reason_type'] = '老师取消';
				} else {
					$list[$i]['reason_type'] = '管理员取消';
				}
				if ($list[$i]['state'] == 1) {
					$evaluate = model('Evaluate') -> getOne($list[$i]['id']);
					$list[$i]['evaluate_score'] = $evaluate['score'];
					$list[$i]['evaluate_info'] = $evaluate['information'];
				}
			}
			if (isset($data['search'])) {
				$total = model('Order') -> getSearchCount($data['search'], $data['state']);
			} else {
				$total = model('Order') -> getCount($data['state']);
			}
			$time = time() - 86400 * 5;
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('time', $time);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('state', $data['state']);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//取消订单
	public function cancel() {
		if ( request() -> isPost()) {
			$data = input('post.');
			if (!isset($data['id'])) {
				$return['result'] = FALSE;
				$return['data'] = 0;
				return json($return);
			}
			$Order = model('Order') -> getOne($data['id']);
			if ($Order['state'] == 0) {
				$postdata['state'] = 2;
				$postdata['reason_type'] = 2;
				$Order = model('Order') -> updateData($data['id'], $postdata);
				if ($Order) {
					$curriculum = model('Curriculum') -> Reset($Order['teacher_id'], $Order['time']);
					$priceorder = model('PriceOrder') -> getOne($Order['payid']);
					$podata['used_class'] = $priceorder['used_class'] - 1;
					model('PriceOrder') -> updateData($Order['payid'], $podata);
					$student = model('Student') -> getOne($Order['student_id']);
					$wxcancel['orderid'] = $Order['id'];
					$wxcancel['type'] = 2;
					action('teacher/Base/wxcancel', $wxcancel);
					if ($student['language'] == 0) {
						action('en/Base/wxcancel', $wxcancel);
					} else {
						action('ko/Base/wxcancel', $wxcancel);
						action('ko/Base/kakaocancel', $wxcancel);
					}
					$return['result'] = TRUE;
					$return['data'] = 1;
					return json($return);
				}
			} else if ($Order['time'] > time() - 86400 * 10) {
				$postdata['state'] = 2;
				$postdata['reason_type'] = 2;
				$Order = model('Order') -> updateData($data['id'], $postdata);
				if ($Order) {
					$curriculum = model('Curriculum') -> Reset($Order['teacher_id'], $Order['time']);
					$priceorder = model('PriceOrder') -> getOne($Order['payid']);
					$podata['used_class'] = $priceorder['used_class'] - 1;
					model('PriceOrder') -> updateData($Order['payid'], $podata);
					$BalanceCount2 = model('Balance') -> getBalanceCount($Order['teacher_id'], 1);
					$balance2['user_id'] = $Order['teacher_id'];
					$balance2['admin_id'] = 0;
					$balance2['user_type'] = 1;
					$balance2['type'] = 0;
					$balance2['num'] = 1;
					$balance2['balance_num'] = $BalanceCount2 - 1;
					model('Balance') -> addData($balance2);
					$student = model('Student') -> getOne($Order['student_id']);
					$wxcancel['orderid'] = $Order['id'];
					$wxcancel['type'] = 2;
					if ($student['language'] == 0) {
						action('en/Base/wxcancel', $wxcancel);
					} else {
						action('ko/Base/wxcancel', $wxcancel);
					}
					$return['result'] = TRUE;
					$return['data'] = 1;
					return json($return);
				}
			}
			$return['result'] = FALSE;
			$return['data'] = 0;
			return json($return);
		} else {
			$return['result'] = FALSE;
			$return['data'] = 0;
			return json($return);
		}
	}

	//备注要求
	public function demand() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			$list = model('OrderDemand') -> getList($data['p'], 'desc');
			$total = model('OrderDemand') -> getCount();
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//新增备注要求
	public function add_demand() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$OrderDemand = model('OrderDemand') -> addData($postdata);
			if ($OrderDemand) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	

	//删除备注要求
	public function del_demand() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			model('OrderDemand') -> delData($postdata['id']);
			return 1;
		}
	}

}
