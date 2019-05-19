<?php
namespace app\index\controller;
use think\Controller;
class Index extends controller
{
    public function index()
    {
    		$this->redirect('/ko/teacher/index.html');
    }
}
