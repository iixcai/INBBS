<?php
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/17
 * Time: 下午5:10
 */

function v($value){
    echo '<pre>';
    echo var_dump($value);
    echo '</pre>';
}

/**
 * 邮件发送函数
 */
function sendMail($to, $title, $content) {

    Vendor('PHPMailer.PHPMailerAutoload');
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,"尊敬的客户");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    return($mail->Send());
}

/**
 * 获取用户的信息，并进行缓存
 */
 function userinfo($auth,$remember){
     $user = M('Userinfo');
     $info = $user->where("authcode = '$auth'")->find();
     if($remember == 'on'){
         S($auth,$info,1296000);
     }else{
         S($auth,$info,86400);
     }

 }

/**
 *检测用户是否登录
 */
function is_login(){
     $auth = cookie('auth');
     if(empty($auth)){
         return 0;
     }
     $user = M('Userinfo');
     if($info = $user->where("authcode = '$auth'")->find()){
         return $info['id'];
     }else{
         return 0;
     }
}

function config(){
    $config = M('config');
    $data = $config->select();
    $configdata = array();

    $shuju=arr_foreach($data);

    $a = count($shuju);
    for($i = 0;$i < $a;$i++){
        $configdata[$shuju[$i+1]] = $shuju[$i+2];
        $i = $i+2;
    }

    return $configdata;

}

function arr_foreach ($arr) {
    static $data;
    if (!is_array ($arr)) {
        return $data;
    }
    foreach ($arr as $key => $val ) {
        if (is_array ($val)) {
            arr_foreach ($val);
        } else {
            $data[]=$val;
        }
    }
    return $data;
}

function getuserinfo(){
    if(empty(cookie('auth'))){
        return false;
    }else{
        return S(cookie('auth'));
    }
}

function bbsinfo(){
    $data['usernum'] = M('Userinfo')->count();
    $data['articlenum'] = M('Article')->count();
    $data['reviewnum'] = M('Review')->count();
    return $data;
}

function geshidate($time){
    $rtime = date ( "m-d H:i", $time );
    $htime = date ( "H:i", $time );

    $time = time () - $time;

    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor ( $time / 60 );
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor ( $time / (60 * 60) );
        $str = $h . '小时前 ' . $htime;
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor ( $time / (60 * 60 * 24) );
        if ($d == 1)
            $str = '昨天 ' . $rtime;
        else
            $str = '前天 ' . $rtime;
    } else {
        $str = $rtime;
    }
    return $str;
}

