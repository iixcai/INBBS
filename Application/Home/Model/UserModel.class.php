<?php
namespace Home\Model;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/17
 * Time: 下午5:28
 */
class UserModel extends Model{
    protected $_validate = array(
        array('verify','require','验证码必须!'),
        array('verify','check_verify','验证码错误！' ,3,'callback'),
        array('username','','用户名已存在！',0,'unique',1),
        array('email','','注册邮箱已存在！',0,'unique',1),
        array('repassword','password','确认密码不正确!',0,'confirm'),
        array('password','6,20','密码长度因不少于6位!',3,'length'),

    );
    protected $_auto = array(
        array('password','md5',3,'function'),
        array('registtime','creatdate',3,'callback'),
    );

    public function creatdate(){
        return date("Y/m/d H:i:s");
    }
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}