<?php

namespace app\admin\controller;

use app\admin\model\Exercise;
use think\Controller;
use think\Exception;
use think\Request;

class Recognition extends Controller
{
    //初始执行
    protected function _initialize()
    {
        action('Common/all');
        if (session('recognition') == 0 && session('id') != 1) {
            $this->error('无权限');
        }
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $input = input('param.');
        if (!isset($input['p'])) {
            $input['p'] = 1;
        }
        $total = Exercise::count();
        $list = Exercise::page($input['p'], 20)->order('update_time', 'desc')->select();
        $pageNum = ceil($total / 20);
        return view('recognition/exercise', [
            'p' => $input['p'],
            'list' => $list,
            'total' => $total,
            'pageNum' => $pageNum,
        ]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     * u6
     */
    public function create()
    {
        return view('recognition/add_exercise');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $exercise = new Exercise($_POST);
        $result = $exercise->allowField(true)->save();
        if($result){
            $this->success('新增成功', '/admin/recognition');
        } else {
            $this->error('新增失败');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $exercise = Exercise::get($id);
        return view('recognition/edit_exercise', $exercise->toArray());
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $exercise = new Exercise();
            $exercise->allowField(true)->save($_POST,['id' => $id]);
            $this->success('修改成功', '/admin/recognition');
        } catch (Exception $e) {
            $this->error('出错了');
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $ret = Exercise::destroy($id);
        $code = $ret ? 0 : 1;
        return json([
            'code' => $code,
            'msg'  => '',
        ]);
    }
}
