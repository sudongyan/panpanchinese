<?php
namespace app\ko\validate;
use think\Validate;
use think\Exception;
class BaseValidate extends Validate
{
    protected $rule = [
        'nickName'  =>  'require|max:25',
        'email' => 'email',
        'account' => 'require',
        'phone' => '/^1[3-8]{1}[0-9]{9}$/',
        'password' => 'require|min:6',
        'wx' => '/^[a-zA-Z][a-zA-Z0-9_-]{5,19}$/ims',
        ];

    protected $message = [
        'nickName.require' =>  '필수 등록 정보를 모두 기입해주세요.', //用户信息不完整
        'email'            =>  '이메일이 유효하지 않습니다.',//邮件不有效
        'account.require'  =>  '필수 등록 정보를 모두 기입해주세요.', //用户信息不完整
        'password.require' =>  '필수 등록 정보를 모두 기입해주세요.', //用户信息不完整
        'phone'            =>  '핸드폰 번호가 틀리다', //手机号码不正确
        'password.min'     =>  '비밀번호 6자리 이하',//密码少于6位
        'wx'               =>  '미세 신호는 요구에 부합되지 않는다' ,//微信号不符合要求
    ];


}
