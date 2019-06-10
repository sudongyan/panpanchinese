<?php
namespace app\ko\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
use think\Log;
class Base extends Controller {
	public function correct() {
		$this -> assign('info', input('param.info'));
		return $this -> fetch();
	}

	public function close() {
		$this -> assign('info', input('param.info'));
		return $this -> fetch();
	}

	public function info() {
		$this -> assign('info', input('param.info'));
		return $this -> fetch();
	}	
	
	public function kakaoreg($sid) {
		$student = model('Student') -> getOne($sid);
		$tmp_number = "6495";
		// 请输入在Orange Message网站确认模板号码.
		$kakao_sender = "0312158978";
		// 输入注册Orange Message网站时的电话号码. ( 하이픈也需要一致 )
		$kakao_name = $student['nickName'];
		// 接收人姓名
		$kakao_phone = $student['phone'];
		// 接收人电话号码
		$kakao_url = "http://m.panpanchinese.cn";
		
		$kakao_080 = "N";
		// 替代短信发送时，080免费拒绝来电，如需使用时 Y
		$kakao_res = "";
		// 如是预约发送者时 Y
		$kakao_res_date = "";
		// 只限预约者时 例) 2017-12-24 07:08:09
		$TRAN_REPLACE_TYPE = "";
		// 알림톡 失败时，发送替代短信 ( 空白:未发送, S : 以SMS发送, L : 以LMS发送 )
		// 仅限需要1~10的追加情报的数据时请输入
		$kakao_add1 = "";
		$kakao_add2 = "";
		$kakao_add3 = "";
		$kakao_add4 = "";
		$kakao_add5 = "";
		$kakao_add6 = "";
		$kakao_add7 = "";
		$kakao_add8 = "";
		$kakao_add9 = "";
		$kakao_add10 = "";

		// 使用url数据变换时주석을 풀어주세요.
		// 数据出了별수以外，需要与模板相同
		 $kakao_url1_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url1_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url2_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url2_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url3_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url3_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url4_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url4_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url5_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url5_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		// Authorization 数据请输入在Orange Message网站获取的Key数据.
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: iaYcLvtJMVuStzZAGEFvE1D4h80KqDFaLA5ubri0e9M=");

		$parameters = array("tmp_number" => $tmp_number, "kakao_url" => $kakao_url, "kakao_sender" => $kakao_sender, "kakao_name" => $kakao_name, "kakao_phone" => $kakao_phone, "kakao_add1" => $kakao_add1, "kakao_add2" => $kakao_add2, "kakao_add3" => $kakao_add3, "kakao_add4" => $kakao_add4, "kakao_add5" => $kakao_add5, "kakao_add6" => $kakao_add6, "kakao_add7" => $kakao_add7, "kakao_add8" => $kakao_add8, "kakao_add9" => $kakao_add9, "kakao_add10" => $kakao_add10, "kakao_080" => $kakao_080, "kakao_res" => $kakao_res, "kakao_res_date" => $kakao_res_date, "TRAN_REPLACE_TYPE" => $TRAN_REPLACE_TYPE, "kakao_url1_1" => $kakao_url1_1, "kakao_url1_2" => $kakao_url1_2, "kakao_url2_1" => $kakao_url2_1, "kakao_url2_2" => $kakao_url2_2, "kakao_url3_1" => $kakao_url3_1, "kakao_url3_2" => $kakao_url3_2, "kakao_url4_1" => $kakao_url4_1, "kakao_url4_2" => $kakao_url4_2, "kakao_url5_1" => $kakao_url5_1, "kakao_url5_2" => $kakao_url5_2);
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "http://www.apiorange.com/api/send/notice.do");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_NOSIGNAL, true);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($curl);
	}

	public function wxinfo($orderid) {
		$token = model('WxToken') -> getOne('KO');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_ko'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $student['openid'];
		$info['template_id'] = 'QniN__IjM-TXSgxrPF92pIgbDSZZNxVHbTPfZ87_B8A';
		$info['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid;
		$keynote['first']['value'] = "[수업 예약 성공]" . PHP_EOL . "수업이 성공적으로 예약되었습니다." . PHP_EOL;
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['nickName'];
		$keynote['keyword4']['value'] = $teacher['wx'];
		$keynote['remark']['value'] = PHP_EOL . "수업 1시간 전에 선생님의 친구 요청을 수락 하시고,수업 진행하기 바랍니다.";
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

	public function kakaotest() {
		$data = input('param.');
		action('ko/Base/kakaoremind', $data['id']);
		return 1;
	}
	public function kakaoinfo($orderid) {
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_ko'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name_ko'];
		$course_period = model('CoursePeriod') -> getNum($order['course'],$order['class']);
		$tmp_number = "6339";
		// 请输入在Orange Message网站确认模板号码.
		$kakao_sender = "0312158978";
		// 输入注册Orange Message网站时的电话号码. ( 하이픈也需要一致 )
		$kakao_name = $student['nickName'];
		// 接收人姓名
		$kakao_phone = $student['phone'];
		// 接收人电话号码
		$kakao_url = "http://m.panpanchinese.cn";
		
		$kakao_080 = "N";
		// 替代短信发送时，080免费拒绝来电，如需使用时 Y
		$kakao_res = "";
		// 如是预约发送者时 Y
		$kakao_res_date = "";
		// 只限预约者时 例) 2017-12-24 07:08:09
		$TRAN_REPLACE_TYPE = "";
		// 알림톡 失败时，发送替代短信 ( 空白:未发送, S : 以SMS发送, L : 以LMS发送 )
		// 仅限需要1~10的追加情报的数据时请输入
		$kakao_add1 = $start_time;
		$kakao_add2 = $course_type . ' ' . $course_name . ' lesson ' . $order['class'];
		$kakao_add3 = $teacher['name_en'];
		$kakao_add4 = trim($order['demand']);
		$kakao_add5 = $course_period['url'];
		$kakao_add6 = "";
		$kakao_add7 = "";
		$kakao_add8 = "";
		$kakao_add9 = "";
		$kakao_add10 = "";

		// 使用url数据变换时주석을 풀어주세요.
		// 数据出了별수以外，需要与模板相同
		 $kakao_url1_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my';  // 移动链接或ios链接
		 $kakao_url1_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my';  // pc链接或者安卓链接

		 $kakao_url2_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url2_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url3_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url3_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url4_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url4_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url5_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url5_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		// Authorization 数据请输入在Orange Message网站获取的Key数据.
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: iaYcLvtJMVuStzZAGEFvE1D4h80KqDFaLA5ubri0e9M=");

		$parameters = array("tmp_number" => $tmp_number, "kakao_url" => $kakao_url, "kakao_sender" => $kakao_sender, "kakao_name" => $kakao_name, "kakao_phone" => $kakao_phone, "kakao_add1" => $kakao_add1, "kakao_add2" => $kakao_add2, "kakao_add3" => $kakao_add3, "kakao_add4" => $kakao_add4, "kakao_add5" => $kakao_add5, "kakao_add6" => $kakao_add6, "kakao_add7" => $kakao_add7, "kakao_add8" => $kakao_add8, "kakao_add9" => $kakao_add9, "kakao_add10" => $kakao_add10, "kakao_080" => $kakao_080, "kakao_res" => $kakao_res, "kakao_res_date" => $kakao_res_date, "TRAN_REPLACE_TYPE" => $TRAN_REPLACE_TYPE, "kakao_url1_1" => $kakao_url1_1, "kakao_url1_2" => $kakao_url1_2, "kakao_url2_1" => $kakao_url2_1, "kakao_url2_2" => $kakao_url2_2, "kakao_url3_1" => $kakao_url3_1, "kakao_url3_2" => $kakao_url3_2, "kakao_url4_1" => $kakao_url4_1, "kakao_url4_2" => $kakao_url4_2, "kakao_url5_1" => $kakao_url5_1, "kakao_url5_2" => $kakao_url5_2);
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "http://www.apiorange.com/api/send/notice.do");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_NOSIGNAL, true);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($curl);
	}

	public function wxremind($orderid) {
		$token = model('WxToken') -> getOne('KO');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_ko'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $student['openid'];
		$info['template_id'] = 'QniN__IjM-TXSgxrPF92pIgbDSZZNxVHbTPfZ87_B8A';
		$info['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid;
		$keynote['first']['value'] = "[수업 진행 안내]" . PHP_EOL . "수업 30분 전입니다." . PHP_EOL;
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['nickName'];
		$keynote['keyword4']['value'] = $teacher['wx'];
		$keynote['remark']['value'] = "";
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

	public function kakaoremind($orderid) {
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_ko'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name_ko'];
		$course_period = model('CoursePeriod') -> getNum($order['course'],$order['class']);
		$tmp_number = "6340";
		// 请输入在Orange Message网站确认模板号码.
		$kakao_sender = "0312158978";
		// 输入注册Orange Message网站时的电话号码. ( 하이픈也需要一致 )
		$kakao_name = $student['nickName'];
		// 接收人姓名
		$kakao_phone = $student['phone'];
		// 接收人电话号码
		$kakao_url = "http://m.panpanchinese.cn";
		
		$kakao_080 = "N";
		// 替代短信发送时，080免费拒绝来电，如需使用时 Y
		$kakao_res = "";
		// 如是预约发送者时 Y
		$kakao_res_date = "";
		// 只限预约者时 例) 2017-12-24 07:08:09
		$TRAN_REPLACE_TYPE = "";
		// 알림톡 失败时，发送替代短信 ( 空白:未发送, S : 以SMS发送, L : 以LMS发送 )
		// 仅限需要1~10的追加情报的数据时请输入
		$kakao_add1 = $start_time;
		$kakao_add2 = $course_type . ' ' . $course_name . ' lesson ' . $order['class'];
		$kakao_add3 = $teacher['name_en'];
		$kakao_add4 = "";
		$kakao_add5 = $course_period['url'];
		$kakao_add6 = "";
		$kakao_add7 = "";
		$kakao_add8 = "";
		$kakao_add9 = "";
		$kakao_add10 = "";

		// 使用url数据变换时주석을 풀어주세요.
		// 数据出了별수以外，需要与模板相同
		 $kakao_url1_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url1_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url2_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url2_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url3_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url3_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url4_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url4_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url5_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url5_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		// Authorization 数据请输入在Orange Message网站获取的Key数据.
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: iaYcLvtJMVuStzZAGEFvE1D4h80KqDFaLA5ubri0e9M=");

		$parameters = array("tmp_number" => $tmp_number, "kakao_url" => $kakao_url, "kakao_sender" => $kakao_sender, "kakao_name" => $kakao_name, "kakao_phone" => $kakao_phone, "kakao_add1" => $kakao_add1, "kakao_add2" => $kakao_add2, "kakao_add3" => $kakao_add3, "kakao_add4" => $kakao_add4, "kakao_add5" => $kakao_add5, "kakao_add6" => $kakao_add6, "kakao_add7" => $kakao_add7, "kakao_add8" => $kakao_add8, "kakao_add9" => $kakao_add9, "kakao_add10" => $kakao_add10, "kakao_080" => $kakao_080, "kakao_res" => $kakao_res, "kakao_res_date" => $kakao_res_date, "TRAN_REPLACE_TYPE" => $TRAN_REPLACE_TYPE, "kakao_url1_1" => $kakao_url1_1, "kakao_url1_2" => $kakao_url1_2, "kakao_url2_1" => $kakao_url2_1, "kakao_url2_2" => $kakao_url2_2, "kakao_url3_1" => $kakao_url3_1, "kakao_url3_2" => $kakao_url3_2, "kakao_url4_1" => $kakao_url4_1, "kakao_url4_2" => $kakao_url4_2, "kakao_url5_1" => $kakao_url5_1, "kakao_url5_2" => $kakao_url5_2);
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "http://www.apiorange.com/api/send/notice.do");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_NOSIGNAL, true);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($curl);
		Log::info($response);
	}

	public function wxcancel($orderid, $type) {
		$token = model('WxToken') -> getOne('KO');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_ko'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $student['openid'];
		$info['template_id'] = 'GZfyfv3yOj5TuO7SdqWWiTykz6HF0Hh2baZkayOkk1U';
		$info['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid;
		if ($type == 0) {
			$keynote['first']['value'] = "[수업 취소 안내]" . PHP_EOL . "수업 예약이 취소 되었습니다." . PHP_EOL;
		} else if ($type == 1) {
			$keynote['first']['value'] = "[수업 취소 안내]" . PHP_EOL . "죄송합니다." . PHP_EOL . "선생님의 사정으로 수업 예약이 취소 되었습니다." . PHP_EOL;
		} else {
			$keynote['first']['value'] = "[수업 취소 안내]" . PHP_EOL . "수업이 취소 처리가 되었습니다." . PHP_EOL . "불편을 드려서 죄송합니다." . PHP_EOL;
		}
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $teacher['nickName'];
		$keynote['keyword3']['value'] = $start_time;
		$keynote['keyword4']['value'] = '个人原因';
		if ($type == 0) {
			$keynote['remark']['value'] = PHP_EOL . "수업을 다시 신청해주시면 감사하겠습니다.";
		} else if ($type == 1) {
			$keynote['remark']['value'] = PHP_EOL . "수업을 다시 신청해주시면 감사하겠습니다." . PHP_EOL . "바로 수업을 원하시면 연락 주시기 바랍니다.";
		} else {
			$keynote['remark']['value'] = "";
		}
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

	public function kakaocancel($orderid, $type) {
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_ko'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name_ko'];
		$course_period = model('CoursePeriod') -> getNum($order['course'],$order['class']);
		if ($type == 0) {
			$tmp_number = "6341";
		} else if($type == 1){
			$tmp_number = "6637";
		} else {
			$tmp_number = "6692";
		}
		// 请输入在Orange Message网站确认模板号码.
		$kakao_sender = "0312158978";
		// 输入注册Orange Message网站时的电话号码. ( 하이픈也需要一致 )
		$kakao_name = $student['nickName'];
		// 接收人姓名
		$kakao_phone = $student['phone'];
		// 接收人电话号码
		$kakao_url = "http://m.panpanchinese.cn";
		
		$kakao_080 = "N";
		// 替代短信发送时，080免费拒绝来电，如需使用时 Y
		$kakao_res = "";
		// 如是预约发送者时 Y
		$kakao_res_date = "";
		// 只限预约者时 例) 2017-12-24 07:08:09
		$TRAN_REPLACE_TYPE = "";
		// 알림톡 失败时，发送替代短信 ( 空白:未发送, S : 以SMS发送, L : 以LMS发送 )
		// 仅限需要1~10的追加情报的数据时请输入
		$kakao_add1 = $start_time;
		$kakao_add2 = $course_type . ' ' . $course_name . ' lesson ' . $order['class'];
		$kakao_add3 = $teacher['name_en'];
		$kakao_add4 = "";
		$kakao_add5 = $course_period['url'];
		$kakao_add6 = "";
		$kakao_add7 = "";
		$kakao_add8 = "";
		$kakao_add9 = "";
		$kakao_add10 = "";

		// 使用url数据变换时주석을 풀어주세요.
		// 数据出了별수以外，需要与模板相同
		 $kakao_url1_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url1_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url2_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url2_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url3_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url3_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url4_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url4_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		 $kakao_url5_1   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // 移动链接或ios链接
		 $kakao_url5_2   = 'http://' . $_SERVER["HTTP_HOST"] . '/ko/my/orderinfo?id=' . $orderid ;  // pc链接或者安卓链接

		// Authorization 数据请输入在Orange Message网站获取的Key数据.
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: iaYcLvtJMVuStzZAGEFvE1D4h80KqDFaLA5ubri0e9M=");

		$parameters = array("tmp_number" => $tmp_number, "kakao_url" => $kakao_url, "kakao_sender" => $kakao_sender, "kakao_name" => $kakao_name, "kakao_phone" => $kakao_phone, "kakao_add1" => $kakao_add1, "kakao_add2" => $kakao_add2, "kakao_add3" => $kakao_add3, "kakao_add4" => $kakao_add4, "kakao_add5" => $kakao_add5, "kakao_add6" => $kakao_add6, "kakao_add7" => $kakao_add7, "kakao_add8" => $kakao_add8, "kakao_add9" => $kakao_add9, "kakao_add10" => $kakao_add10, "kakao_080" => $kakao_080, "kakao_res" => $kakao_res, "kakao_res_date" => $kakao_res_date, "TRAN_REPLACE_TYPE" => $TRAN_REPLACE_TYPE, "kakao_url1_1" => $kakao_url1_1, "kakao_url1_2" => $kakao_url1_2, "kakao_url2_1" => $kakao_url2_1, "kakao_url2_2" => $kakao_url2_2, "kakao_url3_1" => $kakao_url3_1, "kakao_url3_2" => $kakao_url3_2, "kakao_url4_1" => $kakao_url4_1, "kakao_url4_2" => $kakao_url4_2, "kakao_url5_1" => $kakao_url5_1, "kakao_url5_2" => $kakao_url5_2);
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "http://www.apiorange.com/api/send/notice.do");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_NOSIGNAL, true);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($curl);
		Log::info($response);
	}

	function isMobile() {
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset($_SERVER['HTTP_VIA'])) {
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}

	public function kakaopriceorder($orderid, $type) {
		$order = model('PriceOrder') -> getOne($orderid);
		$student = model('Student') -> getOne($order['student_id']);
		if ($type == 0) {
			$tmp_number = "6348";
		} else if($type == 1){
			$tmp_number = "6345";
		}
		// 请输入在Orange Message网站确认模板号码.
		$kakao_sender = "0312158978";
		// 输入注册Orange Message网站时的电话号码. ( 하이픈也需要一致 )
		$kakao_name = $student['nickName'];
		// 接收人姓名
		$kakao_phone = $student['phone'];
		// 接收人电话号码
		$kakao_url = "http://m.panpanchinese.cn";
		
		$kakao_080 = "N";
		// 替代短信发送时，080免费拒绝来电，如需使用时 Y
		$kakao_res = "";
		// 如是预约发送者时 Y
		$kakao_res_date = "";
		// 只限预约者时 例) 2017-12-24 07:08:09
		$TRAN_REPLACE_TYPE = "";
		// 알림톡 失败时，发送替代短信 ( 空白:未发送, S : 以SMS发送, L : 以LMS发送 )
		// 仅限需要1~10的追加情报的数据时请输入
		$kakao_add1 = "";
		$kakao_add2 = "";
		$kakao_add3 = "";
		$kakao_add4 = "";
		$kakao_add5 = "";
		$kakao_add6 = "";
		$kakao_add7 = $order['name'];
		$kakao_add8 = $order['day']."day";
		$kakao_add9 = $order['discount_money']."원";
		$kakao_add10 = $student['account'];

		// 使用url数据变换时주석을 풀어주세요.
		// 数据出了별수以外，需要与模板相同
		 $kakao_url1_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url1_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url2_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url2_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url3_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url3_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url4_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url4_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		 $kakao_url5_1   = 'http://' . $_SERVER["HTTP_HOST"];  // 移动链接或ios链接
		 $kakao_url5_2   = 'http://' . $_SERVER["HTTP_HOST"];  // pc链接或者安卓链接

		// Authorization 数据请输入在Orange Message网站获取的Key数据.
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: iaYcLvtJMVuStzZAGEFvE1D4h80KqDFaLA5ubri0e9M=");

		$parameters = array("tmp_number" => $tmp_number, "kakao_url" => $kakao_url, "kakao_sender" => $kakao_sender, "kakao_name" => $kakao_name, "kakao_phone" => $kakao_phone, "kakao_add1" => $kakao_add1, "kakao_add2" => $kakao_add2, "kakao_add3" => $kakao_add3, "kakao_add4" => $kakao_add4, "kakao_add5" => $kakao_add5, "kakao_add6" => $kakao_add6, "kakao_add7" => $kakao_add7, "kakao_add8" => $kakao_add8, "kakao_add9" => $kakao_add9, "kakao_add10" => $kakao_add10, "kakao_080" => $kakao_080, "kakao_res" => $kakao_res, "kakao_res_date" => $kakao_res_date, "TRAN_REPLACE_TYPE" => $TRAN_REPLACE_TYPE, "kakao_url1_1" => $kakao_url1_1, "kakao_url1_2" => $kakao_url1_2, "kakao_url2_1" => $kakao_url2_1, "kakao_url2_2" => $kakao_url2_2, "kakao_url3_1" => $kakao_url3_1, "kakao_url3_2" => $kakao_url3_2, "kakao_url4_1" => $kakao_url4_1, "kakao_url4_2" => $kakao_url4_2, "kakao_url5_1" => $kakao_url5_1, "kakao_url5_2" => $kakao_url5_2);
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "http://www.apiorange.com/api/send/notice.do");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_NOSIGNAL, true);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($curl);
		Log::info($response);
	}

}
