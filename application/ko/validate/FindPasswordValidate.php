<?php
namespace app\ko\validate;
use think\Validate;
use think\Exception;
class FindPasswordValidate extends Validate
{
    protected $rule = [
        'emailer' => 'email',
        ];

    protected $message = [
        'emailer'            =>  '이메일이 유효하지 않습니다.',//邮件不有效
    ];


}
