<?php
namespace app\index\controller;
use Gaoming13\HttpCurl\HttpCurl;
class Wx {
	public function remind() {
		$accessTokenKo = model('WxToken') -> getOne('KO');
		if($accessTokenKo['time'] < time() - 3600){
			list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . config("KOappid") . '&secret=' . config("KOsecret"), 'get');
			$bodys = json_decode($body, true);
			$info['token'] = $bodys['access_token'];
			$info['time'] = time();
			model('WxToken') -> updateData('KO',$info);
		}
		$accessTokenKo = model('WxToken') -> getOne('EN');
		if($accessTokenKo['time'] < time() - 3600){
			list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . config("ENappid") . '&secret=' . config("ENsecret"), 'get');
			$bodys = json_decode($body, true);
			$info['token'] = $bodys['access_token'];
			$info['time'] = time();
			model('WxToken') -> updateData('EN',$info);
		}
		$accessTokenKo = model('WxToken') -> getOne('TEACHER');
		if($accessTokenKo['time'] < time() - 3600){
			list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . config("TEACHERappid") . '&secret=' . config("TEACHERsecret"), 'get');
			$bodys = json_decode($body, true);
			$info['token'] = $bodys['access_token'];
			$info['time'] = time();
			model('WxToken') -> updateData('TEACHER',$info);
		}
		$list = model('Order') -> getRemind();
		for ($i = 0; $i < count($list); $i++) {
			$remind['remind'] = 1;
			$order = model('Order') -> updateData($list[$i]['id'],$remind);
			action('teacher/Base/wxremind', $list[$i]['id']);
			$student = model('Student') -> getOne($list[$i]['student_id']);
			if ($student['language'] == 0) {
				action('en/Base/wxremind', $list[$i]['id']);
			} else {
				action('ko/Base/wxremind', $list[$i]['id']);
				action('ko/Base/kakaoremind', $list[$i]['id']);
			}
		}
	}

}
