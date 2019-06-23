<?php

namespace app\admin\model;


use think\Model;
use traits\model\SoftDelete;

class Exercise extends Model
{
    use SoftDelete;

    protected $type = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'video_src' => 'array',
        'video_srt' => 'array',
    ];

    protected $deleteTime = 'delete_time';

    protected $autoWriteTimestamp = 'datetime';
}