<?php
namespace app\teacher\controller;
use Qiniu\Auth;
class Image {

	//获取上传token
	public function gettoken() {
		if ( request() -> isGet()) {
			$qiniuConfig = config('qiniu');
			$bucket = $qiniuConfig['BUCKET'];
			$accessKey = $qiniuConfig['ACCESSKEY'];
			$secretKey = $qiniuConfig['SECRETKEY'];
			$auth = new Auth($accessKey, $secretKey);
			$upToken = $auth -> uploadToken($bucket);
			$return['uptoken'] = $upToken;
			return json($return);
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '提交方式错误';
			return json($return);
		}
	}

}
