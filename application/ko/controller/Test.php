<?php
namespace app\ko\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Test extends Controller {
		//筛选
	public function index() {
		date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				return strtotime('2019-06-03') - $timezone['time'] * 3600;
				return date("Y-m-d H:m:s");
		return date("Y-m-d H:m:s", strtotime('2019-06-03') - $timezone['time'] * 3600);
	}
}
