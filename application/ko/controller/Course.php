<?php
namespace app\ko\controller;
use app\admin\model\Video;
use think\Controller;
use Gaoming13\HttpCurl\HttpCurl;
class Course extends Controller {
	//教材列表
	public function index() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['type'])) {
				$list = model('Course') -> getAllList();
				$type = 'all';
			} else {
				$list = model('Course') -> getList($data['type']);
				$type = $data['type'];
			}
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]['login'] = 0;
				$list[$i]['percentage'] = 0;
				$list[$i]['period_num'] = model('CoursePeriod') -> getCount($list[$i]['id']);
				if (session('ko_id') != null) {
					$list[$i]['login'] = 1;
					$orderlist = model('Order') -> getCourseList(session('ko_id'), $list[$i]['id']);
					$arr = array();
					for ($a = 0; $a < count($orderlist); $a++) {
						array_push($arr, $orderlist[$a]['class']);
					}
					$thearr = array_unique($arr);
					if (count($thearr) != 0 || $list[$i]['period_num'] != 0) {
						$list[$i]['percentage'] = round((count($thearr) / $list[$i]['period_num']) * 100);
					}
				}
				$Type = model('CourseType') -> getOne($list[$i]['type']);
				if (cookie('think_var') == 'en-us') {
					$list[$i]['TypeName'] = $Type['name_en'];
				}else{
					$list[$i]['TypeName'] = $Type['name_ko'];
				}
			}
			$typelist = array();
			$typelistdata = model('CourseType') -> getList();
			for ($i = 0; $i < count($typelistdata); $i++) {
				$ctypenum = model('Course') -> getNum($typelistdata[$i]['id']);
				if ($ctypenum > 0) {
					array_push($typelist, $typelistdata[$i]);
				}
			}
			$this -> assign('list', $list);
			$this -> assign('typelist', $typelist);
			$this -> assign('type', $type);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webindex');
			}
		}
	}

	//课程列表
	public function info() {
		if ( request() -> isGET()) {
			$data = input('param.');
			if (!isset($data['id'])) {
				$info['info'] = 'id错误';
				$this -> redirect('base/close', $info);
			}
			$show = 0;
			if (session('ko_id') != null) {
				$balance = 0;
				$plist = model('PriceOrder') -> getList(session('ko_id'), 1, 'desc');
				date_default_timezone_set('UTC');
				$my = model('Student') -> getOne(session('ko_id'));
				$timezone = model('Timezone') -> getOne($my['time_zone']);
				$time = $timezone['time'] * 3600;
				for ($i = 0; $i < count($plist); $i++) {
					if ($plist[$i]['end_time'] + $time > time() && $plist[$i]['type'] != 3) {
						$plist[$i]['surplus_class'] = $plist[$i]['class'] - $plist[$i]['used_class'];
						$balance = $balance + $plist[$i]['surplus_class'];
					}
				}
				if ($balance > 0) {
					$show = 1;
				} else {
					$orderlist = model('Order') -> getList(session('ko_id'), 1, null, 'desc');
					for ($i = 0; $i < count($orderlist); $i++) {
						if ($orderlist[$i]['time'] > time() - 1800) {
							$show = 1;
						}
					}
				}
			}
			$course = model('Course') -> getOne($data['id']);
			$list = model('CoursePeriod') -> getList($data['id']);
			for ($i = 0; $i < count($list); $i++) {
				if ($i != 0 && $i != 1 ) {
					$list[$i]['show'] = $show;
				}else{
					$list[$i]['show'] = 1;
				}
			}
			$this -> assign('course', $course);
			$this -> assign('list', $list);
			if (action('Base/isMobile') == true) {
				return $this -> fetch();
			} else {
				return $this -> fetch('webinfo');
			}
		}
	}

    public function video() {
	    $input = input('get.');
	    if (empty($input['course']) || empty($input['period'])) {
            return json([
                'code' => 1000,
                'msg'  => '需要的参数未传入',
            ]);
        }

	    $video = Video::where('course_id', $input['course'])
            ->where('period_id', $input['period'])
            ->field(['id', 'create_time', 'update_time', 'delete_time', ], true)
            ->find();

        return json([
            'code' => 0,
            'msg'  => '',
            'result' => $video,
        ]);
    }

}
