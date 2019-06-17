<?php
namespace app\ko\validate;
use think\Validate;
use think\Exception;
class BaseValidate extends Validate
{
    protected $rule = [
        'email' => 'email',
    ];

}
