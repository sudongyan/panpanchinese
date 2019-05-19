<?php
namespace app\admin\controller;
use think\Controller;
use PHPExcel_IOFactory;
use PHPExcel;
class Settlement extends Controller {
	//初始执行
	protected function _initialize() {
		action('Common/all');
	}
	

	//结算列表
	public function index() {
		if ( request() -> isGet()) {
			$data = input('param.');
			if (!isset($data['p'])) {
				$data['p'] = 1;
			}
			if (!isset($data['day']) || strtotime($data['day']) > time()) {
				$data['day'] = date("Y-m-d", time());
			}
			if (strtotime($data['day']) < time() - 100 * 86400) {
				$data['day'] = date("Y-m-d", time());
			}
			if (strtotime($data['day']) >= strtotime(date("Y-m-21", strtotime($data['day'])))) {
				$day = date("Y-m-d", strtotime(date('Y-m-01', strtotime($data['day']))));
				$day2 = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day']))));
				$day3 = date("Y-m-d", strtotime(date('Y-m-21', strtotime($data['day']))));
			} else if (strtotime($data['day']) >= strtotime(date("Y-m-06", strtotime($data['day'])))) {
				$day = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day'])) . ' -1 month'));
				$day2 = date("Y-m-d", strtotime(date('Y-m-1', strtotime($data['day']))));
				$day3 = date("Y-m-d", strtotime(date('Y-m-06', strtotime($data['day']))));
			} else {
				$day = date("Y-m-d", strtotime(date('Y-m-01', strtotime($data['day'])) . ' -1 month'));
				$day2 = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day'])) . ' -1 month'));
				$day3 = date("Y-m-d", strtotime(date('Y-m-21', strtotime($data['day'])) . ' -1 month'));
			}
			$SettlementLog = model('SettlementLog') -> getDay($day);
			if (!$SettlementLog) {
				$tlist = model('Teacher') -> getAllList('asc');
				for ($i = 0; $i < count($tlist); $i++) {
					$thedata['teacher_id'] = $tlist[$i]['id'];
					$thedata['day'] = $day;
					$thedata['order_num'] = model('Order') -> getSettlementCount($tlist[$i]['id'], strtotime($day), strtotime($day2));
					$thedata['recharge_num'] = model('Balance') -> getSettlementCount($tlist[$i]['id'], strtotime($day), strtotime($day2));
					$thedata['num'] = $thedata['order_num'] + $thedata['recharge_num'];
					$thedata['time'] = strtotime($day);
					if ($thedata['order_num'] != 0 || $thedata['recharge_num'] != 0) {
						$thedata['ty_num'] = 0;
						$oldlist = model('Order') -> getSettlementList($tlist[$i]['id'], strtotime($day), strtotime($day2));
						for ($s = 0; $s < count($oldlist); $s++) {
							$priceorder = model('PriceOrder') -> getOne($oldlist[$s]['payid']);
							if($priceorder['type'] == 3){
								$thedata['ty_num'] = $thedata['ty_num'] + 1;
							}
						}
						model('Settlement') -> addData($thedata);
					}
				}
				$info['day'] = $day;
				$info['time'] = strtotime($day);
				model('SettlementLog') -> addData($info);
			}
			$Settlementlist = model('Settlement') -> getList($data['p'], $day, 'desc');
			$list = array();
			for ($i = 0; $i < count($Settlementlist); $i++) {
				$list[$i] = model('Teacher') -> getOne($Settlementlist[$i]['teacher_id']);
				$list[$i]['order_num'] = $Settlementlist[$i]['order_num'];
				$list[$i]['recharge_num'] = $Settlementlist[$i]['recharge_num'];
				$list[$i]['num'] = $Settlementlist[$i]['num'];
				$list[$i]['ty_num'] = $Settlementlist[$i]['ty_num'];
			}
			$SettlementLogList = model('SettlementLog') -> getList('desc');
			for ($i = 0; $i < count($SettlementLogList); $i++) {
				if (strtotime($SettlementLogList[$i]['day']) >= strtotime(date("Y-m-16", strtotime($SettlementLogList[$i]['day'])))) {
					$SettlementLogList[$i]['theday'] = date("Y-m-d", strtotime(date('Y-m-16', strtotime($SettlementLogList[$i]['day'])) . ' +1 month'));
					$SettlementLogList[$i]['endday'] = date("Y-m-d", strtotime(date('Y-m-1', strtotime($SettlementLogList[$i]['day'])) . ' +1 month') - 1);
				} else {
					$SettlementLogList[$i]['theday'] = date("Y-m-d", strtotime(date('Y-m-1', strtotime($SettlementLogList[$i]['day'])) . ' +1 month'));
					$SettlementLogList[$i]['endday'] = date("Y-m-d", strtotime(date('Y-m-16', strtotime($SettlementLogList[$i]['day']))) - 1);
				}
			}
			if (strtotime($data['day']) >= strtotime(date("Y-m-21", strtotime($data['day'])))) {
				$day2 = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day']))) - 1);
			} else if (strtotime($data['day']) >= strtotime(date("Y-m-06", strtotime($data['day'])))) {
				$day2 = date("Y-m-d", strtotime(date('Y-m-1', strtotime($data['day']))) - 1);
			} else {
				$day2 = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day'])) . ' -1 month') - 1);
			}
			$total = model('Settlement') -> getCount($day);
			$pageNum = ceil($total / 20);
			$this -> assign('list', $list);
			$this -> assign('SettlementLogList', $SettlementLogList);
			$this -> assign('total', $total);
			$this -> assign('p', $data['p']);
			$this -> assign('pageNum', $pageNum);
			$this -> assign('day', $day);
			$this -> assign('day2', $day2);
			$this -> assign('day3', $day3);
			return $this -> fetch();
		} else {
			$this -> error('提交方式错误');
		}
	}

	//导出列表
	public function excel() {
		if (!isset($data['day'])) {
			$data['day'] = date("Y-m-d", time());
		}
		if (strtotime($data['day']) >= strtotime(date("Y-m-21", strtotime($data['day'])))) {
			$day = date("Y-m-d", strtotime(date('Y-m-01', strtotime($data['day']))));
			$day2 = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day']))));
			$day3 = date("Y-m-d", strtotime(date('Y-m-21', strtotime($data['day']))));
		} else if (strtotime($data['day']) >= strtotime(date("Y-m-06", strtotime($data['day'])))) {
			$day = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day'])) . ' -1 month'));
			$day2 = date("Y-m-d", strtotime(date('Y-m-1', strtotime($data['day']))));
			$day3 = date("Y-m-d", strtotime(date('Y-m-06', strtotime($data['day']))));
		} else {
			$day = date("Y-m-d", strtotime(date('Y-m-01', strtotime($data['day'])) . ' -1 month'));
			$day2 = date("Y-m-d", strtotime(date('Y-m-16', strtotime($data['day'])) . ' -1 month'));
			$day3 = date("Y-m-d", strtotime(date('Y-m-21', strtotime($data['day'])) . ' -1 month'));
		}
		$SettlementLog = model('SettlementLog') -> getDay($day);
		if (!$SettlementLog) {
			$tlist = model('Teacher') -> getAllList('asc');
			for ($i = 0; $i < count($tlist); $i++) {
				$thedata['teacher_id'] = $tlist[$i]['id'];
				$thedata['day'] = $day;
				$thedata['order_num'] = model('Order') -> getSettlementCount($tlist[$i]['id'], strtotime($day), strtotime($day2));
				$thedata['recharge_num'] = model('Balance') -> getSettlementCount($tlist[$i]['id'], strtotime($day), strtotime($day2));
				$thedata['num'] = $thedata['order_num'] + $thedata['recharge_num'];
				if ($thedata['order_num'] != 0 || $thedata['recharge_num'] != 0) {
						$thedata['ty_num'] = 0;
						$oldlist = model('Order') -> getSettlementList($tlist[$i]['id'], strtotime($day), strtotime($day2));
						for ($s = 0; $s < count($oldlist); $s++) {
							$priceorder = model('PriceOrder') -> getOne($oldlist[$s]['payid']);
							if($priceorder['type'] == 3){
								$thedata['ty_num'] = $thedata['ty_num'] + 1;
							}
						}
					model('Settlement') -> addData($thedata);
				}
			}
			$info['day'] = $day;
			model('SettlementLog') -> addData($info);
		}
		$Settlementlist = model('Settlement') -> getAllList($day, 'desc');
		$path = dirname(__FILE__);
		//找到当前脚本所在路径
		$PHPExcel = new PHPExcel();
		//实例化PHPExcel类，类似于在桌面上新建一个Excel表格
		$PHPSheet = $PHPExcel -> getActiveSheet();
		//获得当前活动sheet的操作对象
		$PHPSheet -> setTitle('demo');
		//给当前活动sheet设置名称
		$PHPSheet -> setCellValue('A1', '登录账号') -> setCellValue('B1', '昵称') -> setCellValue('C1', '微信') -> setCellValue('D1', '电话') -> setCellValue('E1', '结算银行') -> setCellValue('F1', '结算账户') -> setCellValue('G1', '真实姓名') -> setCellValue('H1', '课程券') -> setCellValue('I1', '体验券') -> setCellValue('J1', '充值/扣除') -> setCellValue('K1', '总结算金额') -> setCellValue('L1', '结算时间');
		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
		for ($i = 0; $i < count($Settlementlist); $i++) {
			$teacher = model('Teacher') -> getOne($Settlementlist[$i]['teacher_id']);
			$s = $i + 2;
			$PHPSheet -> setCellValue('A' . $s, $teacher['account']) -> setCellValue('B' . $s, $teacher['nickName']) -> setCellValue('C' . $s, $teacher['wx']) -> setCellValue('D' . $s, $teacher['phone']) -> setCellValue('E' . $s, $teacher['bank']) -> setCellValue('F' . $s, $teacher['bank_account']) -> setCellValue('G' . $s, $teacher['name']) -> setCellValue('H' . $s, $Settlementlist[$i]['order_num'] - $Settlementlist[$i]['ty_num']) -> setCellValue('I' . $s, $Settlementlist[$i]['ty_num']) -> setCellValue('J' . $s, $Settlementlist[$i]['recharge_num']) -> setCellValue('K' . $s, $Settlementlist[$i]['num']) -> setCellValue('L' . $s, $day3);
		}
		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
		//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//告诉浏览器输出07Excel文件
		//header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
		header('Content-Disposition: attachment;filename="' . $day . '_' . $day2 . '.xlsx"');
		//告诉浏览器输出浏览器名称
		header('Cache-Control: max-age=0');
		//禁止缓存
		$PHPWriter -> save("php://output");

	}

}
