<?php
namespace app\admin\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
use PHPExcel_IOFactory;
use PHPExcel;
class Student extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
		if (session('student') == 0 && session('id') != 1) {
            $this->error('无权限');
		}
	}

	//学生列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['state']) || $data['state'] == '') {
				$data['state'] = null;
			}
			if(isset($data['search'])){
				$list = model('Student') -> getSearchList($data['p'], $data['search'], 'desc');
			}else{
				$list = model('Student') -> getList($data['p'], $data['state'], 'desc');
			}
			for ($i = 0; $i < count($list); $i++) {
				$timezone = model('Timezone') -> getOne($list[$i]['time_zone']);
				$list[$i]['time_zone_name'] = $timezone['name_cn'];
				$list[$i]['order_0'] = model('Order') -> getStateCount('s', $list[$i]['id'], 0);
				$list[$i]['order_1'] = model('Order') -> getStateCount('s', $list[$i]['id'], 1);
				$list[$i]['order_2'] = model('Order') -> getStateCount('s', $list[$i]['id'], 2);
				$list[$i]['order_3'] = model('Order') -> getStateCount('s', $list[$i]['id'], 3);
				$list[$i]['BalanceCount'] = 0;
				$list[$i]['overdue_BalanceCount'] = 0;
				$list[$i]['discount_BalanceCount'] = model('PriceOrder') -> getDiscount($list[$i]['id']);
				$list[$i]['PriceOrder_name'] = '————';
				$list[$i]['PriceOrder_day'] = '————';
				$list[$i]['PriceOrder_class'] = '————';
				$list[$i]['PriceOrder_payment_time'] = '————';
				$plist = model('PriceOrder') -> getList($list[$i]['id'], 1, 'desc');
				for ($s = 0; $s < count($plist); $s++) {
					if($s == 0){
						$list[$i]['PriceOrder_name'] = $plist[$s]['name'];
						$list[$i]['PriceOrder_day'] = $plist[$s]['day'] .'天';
						$list[$i]['PriceOrder_class'] = $plist[$s]['class']/($plist[$s]['day']/30) . '课时/月';
						$list[$i]['PriceOrder_payment_time'] = date('Y-m-d H:m:s',$plist[$s]['payment_time']);
					}
					if ($plist[$s]['end_time'] > time()) {
						$list[$i]['BalanceCount'] = $list[$i]['BalanceCount'] + $plist[$s]['class'] - $plist[$s]['used_class'];
					}else{
						$list[$i]['overdue_BalanceCount'] = $list[$i]['overdue_BalanceCount'] + $plist[$s]['class'] - $plist[$s]['used_class'];
					}
				}
				/**
				$list[$i]['validity_start'] = date('Y-m-d',$list[$i]['validity_start']);
				if($list[$i]['validity_end'] < time() && $list[$i]['BalanceCount'] > 0){
					$balance['user_id'] = $list[$i]['id'];	
					$balance['user_type'] = 0;
					$balance['type'] = 0;
					$balance['num'] = $list[$i]['BalanceCount'];
					$balance['balance_num'] = 0;
					model('Balance') -> addData($balance);
					$list[$i]['BalanceCount'] = 0;
				}
				$list[$i]['validity_end'] = date('Y-m-d',$list[$i]['validity_end']);
				 **/
			}
			if(isset($data['search'])){
				$total = model('Student') -> getSearchCount($data['search']);
			}else{
				$total = model('Student') -> getCount($data['state']);
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
/*
	public function up() {
		$list = model('Student') -> getAllList(1, 'desc');
		for ($i = 0; $i < count($list); $i++) {
			$theBalance = model('Balance') -> getBalanceCount($list[$i]['id'],0);
			if($theBalance > 0 && $list[$i]['validity_end'] > time()){
				    $thetime = ($list[$i]['validity_end'] - time())/86400;
					$order['order_id'] = time() . rand(1000, 9999);
					$order['student_id'] = $list[$i]['id'];
					$order['cover'] = 'http://yk.nightka.com/static/img/logo.jpeg';
					$order['name'] = '转移课程券';
					$order['day'] = ceil(($list[$i]['validity_end'] - time())/86400);
					$order['class'] = $theBalance;
					$order['money'] = 0;
					$order['discount'] = 0;
					$order['type'] = 3;
					$order['discount_money'] = 0;
					$order['USD'] = 0;
					$order['payment_time'] = time();
					$order['end_time'] = $list[$i]['validity_end'];
					$order['state'] = 1;
					model('PriceOrder') -> addData($order);
					$balance['user_id'] = $list[$i]['id'];	
					$balance['user_type'] = 0;
					$balance['type'] = 0;
					$balance['num'] = $theBalance;
					$balance['balance_num'] = 0;
					model('Balance') -> addData($balance);
			}
		}
		return 'OK';

	}
 */

	//新增学员
	public function add() {
		if ( request() -> isGET()) {
			$TimezoneList = model('Timezone') -> getAllList('desc');
			$this -> assign('TimezoneList', $TimezoneList);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			$postdata['create_time'] = time();
			$Authentication = model('Authentication') -> getAccount($postdata['account'], 0);
			$student = model('Student') -> getAccount($postdata['account']);
			if ($student || $Authentication) {
				$this -> error('账号重复');
			}
			$new = model('Student') -> addData($postdata);
			if ($new) {
				$this -> success('新增成功', 'student/index');
			} else {
				$this -> error('修改失败');
			}
		}
	}

	//账号管理
	public function edit() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			$student = model('Student') -> getOne($data['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			$TimezoneList = model('Timezone') -> getAllList('desc');
			$this -> assign('student', $student);
			$this -> assign('TimezoneList', $TimezoneList);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['id'])) {
				$this -> error('id错误');
			}
			$student = model('Student') -> getOne($postdata['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			if($postdata['password'] == ''){
				unset($postdata['password']);
			}
			$new = model('Student') -> updateData($postdata['id'], $postdata);
			if ($new) {
				$this -> success('修改成功', 'student/index');
			} else {
				$this -> error('修改失败');
			}
		}
	}

	//充值
	public function recharge() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['id'])) {
				$this -> error('id错误');
			}
			if (!isset($data['overdue'])) {
				$data['overdue'] = null;
			}
			$student = model('Student') -> getOne($data['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			$list = model('PriceOrder') -> getPList($data['id'], $data['p'], 1 ,$data['overdue'], 'desc');
			for ($i = 0; $i < count($list); $i++) {
				if($list[$i]['end_time'] > time()){
				$list[$i]['overdue'] = 0;
				}else{
				$list[$i]['overdue'] = 1;
				}
				$list[$i]['month'] = $list[$i]['day'] / 30;
				$list[$i]['all_class'] = $list[$i]['class'];
				$list[$i]['class'] = $list[$i]['class'] / ($list[$i]['day'] / 30);
				if ($list[$i]['payment_time'] != 0) {
					$list[$i]['payment_time'] = date('Y-m-d H:m:s', $list[$i]['payment_time']);
				} else {
					$list[$i]['payment_time'] = '————';
				}
				if ($list[$i]['end_time'] != 0) {
					$list[$i]['end_time'] = date('Y-m-d H:m:s', $list[$i]['end_time']);
				} else {
					$list[$i]['end_time'] = '————';
				}
				$student = model('Student') -> getOne($list[$i]['student_id']);
				$list[$i]['student_account'] = $student['account'];
				$list[$i]['student_nickName'] = $student['nickName'];
				$list[$i]['student_wx'] = $student['wx'];
				$list[$i]['student_phone'] = $student['phone'];
				$list[$i]['admin_account'] = '————';
				$list[$i]['admin_nickName'] = '————';
				if ($list[$i]['admin_id'] != 0) {
					$admin = model('Admin') -> getId($list[$i]['admin_id']);
					$list[$i]['admin_account'] = $admin['account'];
					$list[$i]['admin_nickName'] = $admin['nickName'];
				}
			}
			$classlist = model('PriceClass') -> getList();
			$this -> assign('classlist', $classlist);
			$total = model('PriceOrder') -> getStudentCount($data['id'] , 1 , $data['overdue']);
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('total', $total);
			$this -> assign('user_id', $data['id']);
			$this -> assign('overdue', $data['overdue']);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			return $this -> fetch();
		} else if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['id'])) {
				$this -> error('id错误');
			}
			if (!isset($postdata['num'])) {
				$this -> error('数额错误');
			}
			if(is_int($postdata['num'])){
				$this -> error('请输入整数');
			}
			$student = model('Student') -> getOne($postdata['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			$BalanceCount = model('Balance') -> getBalanceCount($postdata['id'],0);
			$balance['user_id'] = $postdata['id'];
			$balance['admin_id'] = session('id');
			$balance['user_type'] = 0;
			$balance['type'] = 1;
			if($postdata['num'] < 0){
				$balance['type'] = 0;
			}
			$balance['num'] = abs($postdata['num']);
			$balance['balance_num'] = $BalanceCount + $postdata['num'];
			model('Balance') -> addData($balance);
			$this->redirect('student/recharge', ['id' => $postdata['id']]);
		}
	}

	//获取月课程列表
	public function price_month_list() {
		if ( request() -> isGET()) {
			$data = input('param.');
			$list = model('Price') -> getMonthList($data['month']);
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}
	//确认充值
	function price_add(){
		if ( request() -> isPost()) {
			$data = input('post.');
			$price = model('Price') -> getOne($data['price_id']);
			if ($data['student_id'] == null) {
				return 0;
			}
			$ExchangeRate = model('ExchangeRate') -> getOne('KRW', 'USD');
			if ($ExchangeRate['time'] < time() - 3600 / 2) {
				$thedata['from'] = 'KRW';
				$thedata['to'] = 'USD';
				$thedata['key'] = config("JUHE_KEY");
				list($body) = HttpCurl::request('http://op.juhe.cn/onebox/exchange/currency', 'get', $thedata);
				$bodys = json_decode($body, true);
				if ($bodys['reason'] == 'successed') {
					$result = $bodys['result'];
					for ($i = 0; $i < count($result); $i++) {
						if ($result[$i]['currencyF'] == $thedata['from'] && $result[$i]['currencyT'] == $thedata['to']) {
							$info['price'] = $result[$i]['result'];
							$ExchangeRate = model('ExchangeRate') -> updateData('KRW', 'USD', $info);
						}
					}
				}
			}
			$order['order_id'] = time() . rand(1000, 9999);
			$order['student_id'] = $data['student_id'];
			$order['cover'] = $price['cover'];
			$order['name'] = $price['name'];
			$order['day'] = $price['day'];
			$order['class'] = $price['class'];
			$order['money'] = $price['money'];
			$order['discount'] = $price['discount'];
			$order['type'] = 1;
			$order['state'] = 1;
			$order['admin_id'] = session('id');
			$order['payment_time'] = time();
			$order['end_time'] = time() + (86400 * $order['day']);
			$order['discount_money'] = (100 - $price['discount']) * 0.01 * $order['money'];
			$order['USD'] = round($order['discount_money'] * $ExchangeRate['price'], 2);
			$order['payer_name'] = '后台充值';
			$order['payer_info'] = '后台充值';
			$theorder = model('PriceOrder') -> addData($order);
			if ($theorder) {
				$info['admin_id'] = session('id');
				$info['order_id'] = $order['order_id'];
				$info['student_id'] = $data['student_id'];
				$info['type'] = 0;
				model('PriceOrderLog') -> addData($info);
				return 1;
			} else {
				return 0;
			}
		}
	}

	//修改套餐
	public function price_edit() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			$oldPrice = model('PriceOrder') -> getOne($postdata['order_id']);
			$data['class'] = $postdata['allclass'];
			$data['used_class'] = $postdata['used_class'];
			$data['payment_time'] = strtotime($postdata['payment_time']);
			$data['end_time'] = strtotime($postdata['end_time']);
			$Price = model('PriceOrder') -> updateData($postdata['order_id'], $data);
			if ($Price) {
				if($oldPrice['class'] != $data['class'] || $oldPrice['used_class'] != $data['used_class'] || $oldPrice['payment_time'] != $data['payment_time'] ||  $oldPrice['end_time'] != $data['end_time']){
				$info['admin_id'] = session('id');
				$info['order_id'] = $oldPrice['order_id'];
				$info['student_id'] = $oldPrice['student_id'];
				$info['old_class'] = $oldPrice['class'];
				$info['old_used_class'] = $oldPrice['used_class'];
				$info['old_payment_time'] = $oldPrice['payment_time'];
				$info['old_end_time'] = $oldPrice['end_time'];
				$info['class'] = $data['class'];
				$info['used_class'] = $data['used_class'];
				$info['payment_time'] = $data['payment_time'];
				$info['end_time'] = $data['end_time'];
				$info['type'] = 1;
				model('PriceOrderLog') -> addData($info);
				}
				return 1;
			} else {
				return 0;
			}
		}
	}

	//调整时间
	public function adjustment_time() {
		if ( request() -> isPost()) {
			$postdata = input('post.');
			if (!isset($postdata['id'])) {
				$this -> error('id错误');
			}
			if (!isset($postdata['validity_start'])) {
				$this -> error('有效期错误');
			}
			if(!isset($postdata['validity_end'])){
				$this -> error('有效期错误');
			}
			$student = model('Student') -> getOne($postdata['id']);
			if (!$student) {
				$this -> error('id错误');
			}
			$balance['user_id'] = $postdata['id'];
			$balance['admin_id'] = session('id');
			$balance['user_type'] = 0;
			$balance['type'] = 2;
			$balance['old_validity_start'] = $student['validity_start'];
			$balance['old_validity_end'] = $student['validity_end'];
			$balance['validity_start'] = strtotime($postdata['validity_start']);
			$balance['validity_end'] = strtotime($postdata['validity_end']);
			model('Balance') -> addData($balance);
			$updateStudent['validity_start'] = strtotime($postdata['validity_start']);
			$updateStudent['validity_end'] = strtotime($postdata['validity_end']);
			model('Student') -> updateData($postdata['id'],$updateStudent);
			$return['result'] = TRUE;
			$return['data'] = 1;
			return json($return);
		}
	}
	//导出列表
	public function excel() {
		$list = model('Student') -> getAllList(null, 'desc');
		$path = dirname(__FILE__);
		//找到当前脚本所在路径
		$PHPExcel = new PHPExcel();
		//实例化PHPExcel类，类似于在桌面上新建一个Excel表格
		$PHPSheet = $PHPExcel -> getActiveSheet();
		//获得当前活动sheet的操作对象
		$PHPSheet -> setTitle('学员列表');
		//给当前活动sheet设置名称
		$PHPSheet -> setCellValue('A1', '学员昵称') -> setCellValue('B1', '登录账号') -> setCellValue('C1', '手机号') -> setCellValue('D1', '微信号') -> setCellValue('E1', '语言') -> setCellValue('F1', '剩余试听券') -> setCellValue('G1', '剩余课程券') -> setCellValue('H1', '预约中的课程') -> setCellValue('I1', '已完成的课程') -> setCellValue('J1', '最近一次预约') -> setCellValue('K1', '最近一次充值') -> setCellValue('L1', '所在时区') -> setCellValue('M1', '注册时间');
		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
		for ($i = 0; $i < count($list); $i++) {
				$timezone = model('Timezone') -> getOne($list[$i]['time_zone']);
				$list[$i]['time_zone_name'] = $timezone['name_cn'];
				$list[$i]['order_0'] = model('Order') -> getStateCount('s', $list[$i]['id'], 0);
				$list[$i]['order_1'] = model('Order') -> getStateCount('s', $list[$i]['id'], 1);
				$list[$i]['order_2'] = model('Order') -> getStateCount('s', $list[$i]['id'], 2);
				$list[$i]['order_3'] = model('Order') -> getStateCount('s', $list[$i]['id'], 3);
				$new_order = model('Order') -> getStudentNewOrder($list[$i]['id']);
				if(!$new_order){
					$new_order['create_time'] = '————';
				}
				$new_price_order = model('PriceOrder') -> getStudentNewOrder($list[$i]['id']);
				if(!$new_price_order){
					$new_price_order['payment_time'] = '————';
				}else{
					$new_price_order['payment_time'] = date('Y-m-d H:m:s', $new_price_order['payment_time']);
				}
				if($list[$i]['language'] == 1){
					$list[$i]['language'] = '韩语';
				}else{
					$list[$i]['language'] = '英语';
				}
				$list[$i]['BalanceCount'] = 0;
				$list[$i]['overdue_BalanceCount'] = 0;
				$list[$i]['discount_BalanceCount'] = model('PriceOrder') -> getDiscount($list[$i]['id']);
				$plist = model('PriceOrder') -> getList($list[$i]['id'], 1, 'desc');
				for ($s = 0; $s < count($plist); $s++) {
					if($s == 0){
						$list[$i]['PriceOrder_name'] = $plist[$s]['name'];
						$list[$i]['PriceOrder_day'] = $plist[$s]['day']/30 .'个月';
						$list[$i]['PriceOrder_class'] = $plist[$s]['class']/($plist[$s]['day']/30) . '课时/月';
						$list[$i]['PriceOrder_payment_time'] = date('Y-m-d H:m:s',$plist[$s]['payment_time']);
					}
					if ($plist[$s]['end_time'] > time()) {
						$list[$i]['BalanceCount'] = $list[$i]['BalanceCount'] + $plist[$s]['class'] - $plist[$s]['used_class'];
					}else{
						$list[$i]['overdue_BalanceCount'] = $list[$i]['overdue_BalanceCount'] + $plist[$s]['class'] - $plist[$s]['used_class'];
					}
				}
			$s = $i + 2;
			$PHPSheet -> setCellValue('A' . $s, $list[$i]['nickName']) -> setCellValue('B' . $s, $list[$i]['account']) -> setCellValue('C' . $s, $list[$i]['phone']) -> setCellValue('D' . $s, $list[$i]['wx']) -> setCellValue('E' . $s, $list[$i]['language']) -> setCellValue('F' . $s, $list[$i]['discount_BalanceCount']) -> setCellValue('G' . $s, $list[$i]['BalanceCount']) -> setCellValue('H' . $s, $list[$i]['order_0']) -> setCellValue('I' . $s, $list[$i]['order_1'] + $list[$i]['order_3']) -> setCellValue('J' . $s, $new_order['create_time']) -> setCellValue('K' . $s, $new_price_order['payment_time']) -> setCellValue('L' . $s, $list[$i]['time_zone_name']) -> setCellValue('M' . $s, $list[$i]['create_time']);
		}
		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
		//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//告诉浏览器输出07Excel文件
		//header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
		header('Content-Disposition: attachment;filename="' . '学生列表' . '.xlsx"');
		//告诉浏览器输出浏览器名称
		header('Cache-Control: max-age=0');
		//禁止缓存
		$PHPWriter -> save("php://output");

	}
}
