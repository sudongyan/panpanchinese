<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\model\Video as VideoModel;
use Gaoming13\HttpCurl\HttpCurl;
use think\Request;

class Video extends Controller
{
    //初始执行
    protected function _initialize()
    {
        action('Common/all');
        if (session('video') == 0 && session('id') != 1) {
            $this->error('无权限');
        }
    }

    //视频列表
    public function index($period)
    {
    }

    public function create()
    {
        return view('course/add_video', [
            'period_id' => input('period'),
            'course_id' => input('course'),
            ]);

    }

    public function save()
    {
        $input = input('post.');
        $video = new VideoModel;
        $video->period_id = $input['period_id'];
        $video->course_id = $input['course_id'];
        $video->title_cn = $input['title_cn'];
        $video->title_en = $input['title_en'];
        $video->title_ko = $input['title_ko'];
        $video->video_src = $input['video_src'];
        $video->video_srt = $input['video_srt'];
        if ($video->save()) {
            $this -> success('添加成功', "/admin/course/period/id/{$video->course_id}");
        } else {
            $this -> error('添加失败');
        }
    }

    public function read($id)
    {
        $videoModel = VideoModel::where('period_id', $id)->find();
        return view('course/video', ['video' => $videoModel]);
    }

    public function edit($id)
    {
        // halt(VideoModel::get(['period_id' => $id,]));
        return view('course/edit_video', [
            'period_id' => $id,
            'course_id' => input('course'),
            'video' => VideoModel::get(['period_id' => $id,]),
        ]);

    }

    public function update($id)
    {
        $input = input('put.');
        $video = VideoModel::get(['period_id' => $id,]);
        $video->title_cn = $input['title_cn'];
        $video->title_en = $input['title_en'];
        $video->title_ko = $input['title_ko'];
        $video->video_src = $input['video_src'];
        $video->video_srt = $input['video_srt'];
        if ($video->save()) {
            $this -> success('添加成功', "/admin/course/period/id/{$input['course_id']}");
        } else {
            $this -> error('添加失败');
        }

    }

    public function delete()
    {}
}

