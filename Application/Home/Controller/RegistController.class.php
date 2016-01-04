<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/17
 * Time: 下午4:48
 */
class RegistController extends HomeController{
    /**
     *
     */
    public function index(){
        if(!empty(cookie('auth'))){
            $this->error("你已经登录，请先注销登录");
        }
        $this->display();
    }

    /**
     *
     */
    public function signup(){
        $user = D('User');
        $userinfo = D('Userinfo');
        $value = I('post.');
        /**
        if(!$this->check_verify($value['verify'])){
            $this->error("验证码错误！");
        }
        */
        if(!$userinfo->create($value)){
            exit($userinfo->getError());
        }
        $userinfo->add();
        if(!$user->create($value)){
            exit($user->getError());
        }



        $user->add();
        
        $info = $userinfo->where("email='%s'",$value['email'])->find();
        $content = "请点击以下链接激活账号<br>http://nnow.sinaapp.com/regist/activate/authcode/".$info['authcode']."<br>如果这不是链接请复制到浏览器中打开";
        $title = '激活链接';
        
        if(!sendMail($value['email'],$title,$content)){
            exit('邮件发送失败:( 请联系管理员');
        }
        $this->assign('mail',$value['email']);
        $this->display();
    }

    /**
     * @param $authcode
     */
    public function activate(){
        $authcode = I('get.authcode');
        $info = D('Userinfo');
        $data['status'] = 0;
        $data['authcode'] = md5(time());
        if($info->where("authcode='$authcode'")->select()){
            $info->where("authcode='$authcode'")->save($data);
            $this->success('激活成功！正在进入登录页面。。。',U('Index/index'),3);
        }else{
            echo "authcode验证失败";
        }
    }

    public function findpass(){
        v(C());
    }

    public function Verify(){
        $Verify = new \Think\Verify();
        $Verify->codeSet = '0123456789';
        $Verify->length = 4;
        $Verify->entry();
    }
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}