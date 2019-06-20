<?php
namespace app\ko\controller;

use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
use sdk\SASRsdk;

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

	public function asr()
    {
        return view('asr');
    }

    public function sendvoice ()
    {
        $input = input('post.');

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                // echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getFilename();
                //用户需要修改为自己腾讯云官网账号中的appid，secretid与secret_key
                $secretKey = 'SjkvvfEXMzxjYbP5D9dt5CJCq0wf9b3H';
                $SecretId = 'AKIDp9DQXPFUfm9sOnHud8O0MR3kFUTBKTEn';
                // 识别引擎 8k or 16k
                $EngSerViceType = '16k';
                // 语音数据来源 0:语音url，1:语音数据bodydata
                $SourceType = 1;
                // 语音数据地址
                $URI = $info->getPathname();
                //$URI='http://liqiansunvoice-1255628450.cosgz.myqcloud.com/30s.wav';
                // 音频格式 mp3 or wav
                $VoiceFormat = 'wav';
                //调用SASRsdk中的sendvoice函数获得识别结果
                $ret = SASRsdk::sendvoice($secretKey, $SecretId, $EngSerViceType, $SourceType, $URI, $VoiceFormat);
                return json([
                    'code' => 0,
                    'msg'  => '',
                    'result' => $ret['Result'] ?? '',
                ]);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }

    }
}
