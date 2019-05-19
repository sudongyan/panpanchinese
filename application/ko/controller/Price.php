<?php
namespace app\ko\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Price extends Controller {

	//充值
	public function index() {
		if ( request() -> isGET()) {
			$classlist = model('PriceClass') -> getList();
			$list = model('Price') -> getAllList();
			$this -> assign('classlist', $classlist);
			$this -> assign('list', $list);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webindex');
			}
		}
	}
	
	//获取月课程列表
	public function month_list() {
		if ( request() -> isGET()) {
			$data = input('param.');
			$list = model('Price') -> getMonthList($data['month']);
			$return['result'] = TRUE;
			$return['data'] = $list;
			return json($return);
		}
	}

	//下单
	public function info() {
		if ( request() -> isGET()) {
			if (session('ko_id') == null) {
				$this -> redirect('my/login');
			} else {
				$data = input('param.');
				if (!isset($data['id'])) {
					$info['info'] = 'id错误';
					$this -> redirect('base/close', $info);
				}
				$price = model('Price') -> getOne($data['id']);
				$this -> assign('price', $price);
				if (action('Base/isMobile') == true) {
					return $this -> fetch();
				} else {
					return $this -> fetch('webinfo');
				}
			}
		}
	}

}
