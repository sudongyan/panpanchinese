<?php
namespace app\ko\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Test extends Controller {
		//筛选
	public function index() {
		if ( request() -> isGET()) {
			return $this -> fetch();
		}
	}
}
