<?php
namespace app\en\controller;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
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

	public function wxinfo($orderid) {
		$token = model('WxToken') -> getOne('EN');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_en'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $student['openid'];
		$info['template_id'] = 'QniN__IjM-TXSgxrPF92pIgbDSZZNxVHbTPfZ87_B8A';
		$keynote['first']['value'] = "[수업 예약 성공]".PHP_EOL."수업이 성공적으로 예약되었습니다.".PHP_EOL;
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['nickName'];
		$keynote['keyword4']['value'] = $teacher['wx'];
		$keynote['remark']['value'] = PHP_EOL."수업 1시간 전에 선생님의 친구 요청을 수락 하시고,수업 진행하기 바랍니다.";
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' .$token['token'], 'post', json_encode($info));
	}

	public function wxremind($orderid) {
		$token = model('WxToken') -> getOne('EN');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_en'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $student['openid'];
		$info['template_id'] = 'QniN__IjM-TXSgxrPF92pIgbDSZZNxVHbTPfZ87_B8A';
		$keynote['first']['value'] = "[수업 진행 안내]" . PHP_EOL . "수업 30분 전입니다.".PHP_EOL;
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['nickName'];
		$keynote['keyword4']['value'] = $teacher['wx'];
		$keynote['remark']['value'] = "";
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

	public function wxcancel($orderid,$type) {
		$token = model('WxToken') -> getOne('EN');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$timezone = model('Timezone') -> getOne($student['time_zone']);
		$time = $timezone['time'] * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_en'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $student['openid'];
		$info['template_id'] = 'GZfyfv3yOj5TuO7SdqWWiTykz6HF0Hh2baZkayOkk1U';
		if($type == 0){
			$keynote['first']['value'] = "[수업 취소 안내]" . PHP_EOL . "수업 예약이 취소 되었습니다.".PHP_EOL;
		}else if($type == 1){
			$keynote['first']['value'] = "[수업 취소 안내]" . PHP_EOL . "죄송합니다." . PHP_EOL . "선생님의 사정으로 수업 예약이 취소 되었습니다.".PHP_EOL;
		}else{
			$keynote['first']['value'] = "[수업 취소 안내]" . PHP_EOL . "수업이 취소 처리가 되었습니다." . PHP_EOL . "불편을 드려서 죄송합니다.".PHP_EOL;
		}
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $teacher['nickName'];
		$keynote['keyword3']['value'] = $start_time;
		$keynote['keyword4']['value'] = '个人原因';
		if($type == 0){
			$keynote['remark']['value'] = PHP_EOL."수업을 다시 신청해주시면 감사하겠습니다.";
		}else if($type == 1){
			$keynote['remark']['value'] = PHP_EOL."수업을 다시 신청해주시면 감사하겠습니다." . PHP_EOL . "바로 수업을 원하시면 연락 주시기 바랍니다.";
		}else{
			$keynote['remark']['value'] = "";
		}
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

}
