<?php
namespace app\teacher\controller;
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
		$token = model('WxToken') -> getOne('TEACHER');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		$start_time = date("Y-m-d H:i", $order['time']);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_cn'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $teacher['openid'];
		$info['template_id'] = 'pspG2ZqjlVRvb97xRl6gv2pbjXkKqKq-KLyzBJJSQ4w';
		$info['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/teacher/my/orderinfo?id=' . $orderid;
		$keynote['first']['value'] = "亲爱的老师，您接到了一个预约课程".PHP_EOL;
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $student['wx'];
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['wx'];
		$keynote['keyword4']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['remark']['value'] = PHP_EOL."请在至少上课前1个小时添加学生微信，并准时上课。";
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

	public function wxremind($orderid) {
		$token = model('WxToken') -> getOne('TEACHER');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		date_default_timezone_set('UTC');
		$time = 8 * 3600;
		$start_time = date("Y-m-d H:i", $order['time'] + $time);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_cn'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $teacher['openid'];
		$info['template_id'] = 'pspG2ZqjlVRvb97xRl6gv2pbjXkKqKq-KLyzBJJSQ4w';
		$info['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/teacher/my/orderinfo?id=' . $orderid;
		$keynote['first']['value'] = "亲爱的老师，您有一门预约课程即将开课，请按时上课。".PHP_EOL;
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $student['wx'];
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['wx'];
		$keynote['keyword4']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['remark']['value'] = PHP_EOL."请添加学生微信号，并提前熟知教材";
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

	public function wxcancel($orderid,$type) {
		$token = model('WxToken') -> getOne('TEACHER');
		$order = model('Order') -> getOne($orderid);
		$teacher = model('Teacher') -> getOne($order['teacher_id']);
		$student = model('Student') -> getOne($order['student_id']);
		$start_time = date("Y-m-d H:i", $order['time']);
		$course = model('Course') -> getOne($order['course']);
		$course_name = $course['name_cn'];
		$CourseType = model('CourseType') -> getOne($course['type']);
		$course_type = $CourseType['name'];
		$info['touser'] = $teacher['openid'];
		$info['template_id'] = 'CcPCCSXs0qNqUa5nEf6QI3TJ6_R1o3Mm-HwolOqv7K4';
		$info['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/teacher/my/orderinfo?id=' . $orderid;
		if($type == 0){
			$keynote['first']['value'] = "[课程取消提醒]" . PHP_EOL . "亲爱的" . $teacher['nickName'] . "老师，您的学员已经取消课程。课程信息如下：".PHP_EOL;
		}else if($type == 1){
			$keynote['first']['value'] = "您已取消了您的课程".PHP_EOL;
		}else{
			$keynote['first']['value'] = "您的课程已经取消".PHP_EOL;
		}
		$keynote['first']['color'] = '#515392';
		$keynote['keyword1']['value'] = $course_type . ' ' . $course_name . ' 第' . $order['class'] . '课';
		$keynote['keyword2']['value'] = $start_time;
		$keynote['keyword3']['value'] = $teacher['nickName'];
		$keynote['keyword4']['value'] = $student['wx'];
		if($type == 0){
			$keynote['remark']['value'] = PHP_EOL."请注意！课程已经取消了哦！";
		}else if($type == 1){
			$keynote['remark']['value'] = PHP_EOL."因为您取消了被预约的课程，按照panpan中国语的规定，将在12小时内收回您的1张课程券(相当于1节课的薪资)。";
		}else{
			$keynote['remark']['value'] = "";
		}
		$info['data'] = $keynote;
		list($body) = HttpCurl::request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token['token'], 'post', json_encode($info));
	}

}
