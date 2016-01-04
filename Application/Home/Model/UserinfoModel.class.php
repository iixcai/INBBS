<?php
namespace Home\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/17
 * Time: 下午6:37
 */
class UserinfoModel extends Model{
    /**
     * @param $data
     */
    
    protected $_validate = array(
        array('verify','require','验证码必须!'),
        //array('verify','check_verify','验证码错误！',0,'callback'),
        array('username','','用户名已存在！',0,'unique',1),
        array('email','','注册邮箱已存在！',0,'unique',1),
        array('repassword','password','确认密码不正确!',0,'confirm'),
        array('password','6,20','密码长度因不少于6位!',3,'length'),

    );
    
    protected $_auto = array(
        array('registtime','creatdate',1,'callback'),
        array('authcode','authcode',1,'callback'),
        array('lasttime','creatdate',2,'callback'),
        array('ip','getip',2,'callback'),
        array('image','getimage',1,'callback'),
    );

    public function creatdate(){
        return date("Y/m/d H:i:s");
    }

    public function authcode(){
        return md5(time());
    }

    public function getip(){
        return get_client_ip();
    }

    public function getimage(){
        $image = rand(1,10).".jpg";
        return $image;
    }

    public  function getuserid($id){
        $info =  $this->where("id = '$id'")->find();
        if($info == null){
            return false;
        }
        return $info;
    }

    public  function getusername($user){
        $info =  $this->where("username = '$user'")->find();
        if($info == null){
            return false;
        }
        return $info;
    }
    
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

}