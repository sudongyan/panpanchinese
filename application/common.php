<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Config;
use Wechat\Loader;
use think\Lang;

// 设置允许的语言，如果需要多种，在这里自行添加
Lang::setAllowLangList(['zh-cn','ko-kr','en-us']);

function get_now_lang(){
    $lang = lang::range();
    return $lang;
}
/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type
 * @return WechatReceive
 */
function & load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        $config = Config::get('wechat');
        $config['cachepath'] = CACHE_PATH . 'Data/';
        $wechat[$index] = Loader::get($type, $config);
    }
    return $wechat[$index];
}

/**
 * 找回密码发送邮件函数
 * 如果我们要使用我们定义的公共函数，直接在我们想用的地方直接调用函数即可。
 * */
function sendEmail($desc_content, $toemail,  $desc_url){
    require_once(dirname(__FILE__).'/../extend/phpmailer/class.phpmailer.php');
    $mail = new \mailer\PHPMailer();
    $mail->isSMTP();// 使用SMTP服务
    $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
    $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
    $mail->SMTPAuth = true;// 是否使用身份验证
    $mail->Username = "chinabizchinabiz@163.com";// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱</span><span style="color:#333333;">
    $mail->Password = "asd321";// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！</span><span style="color:#333333;">
    $mail->SMTPSecure = "ssl";// 使用ssl协议方式</span><span style="color:#333333;">
    $mail->Port = 994;// 163邮箱的ssl协议方式端口号是465/994
    $mail->setFrom("chinabizchinabiz@163.com","panpanChinese");// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示
    $mail->addAddress($toemail,'panpanChinese回复消息');// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
    $mail->addReplyTo("","Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
    //$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
    //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
    //$mail->addAttachment("bug0.jpg");// 添加附件
    $mail->Subject = 'papanChinese';// 邮件标题
    $mail->Body = "panpanChinese密码找回:".$desc_content."点击进入密码找回链接（请勿将该链接转发送给其他人）:".$desc_url;// 邮件正文
    //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

    if(!$mail->send()){// 发送邮件
        return $mail->ErrorInfo;
        // echo "Message could not be sent.";
        // echo "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息
    }else{
        return 1;
    }
}