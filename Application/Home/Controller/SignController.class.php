<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/18
 * Time: 上午10:25
 */
class SignController extends HomeController{
    public function index(){
        if(!empty(cookie('auth'))){
            $this->error("你已经登录，请先注销登录");
        }
        $this->display();
    }
    public function sign(){
        $data = I('post.');
        if(!$this->check_verify($data['verify'])){
            $this->error("验证码错误！");
        }
        $data['password'] = md5($data['password']);
        $user = M('user');
        if(!$user->create($data)){
            exit($user->getError());
        }
        if(!$info=$user->where($data)->find()){
            $this->error("邮箱或密码错误！");
        }

        $data['id'] = $info['id'];
        $userinfo = D('Userinfo');
        $userinfo->create($data);
        $userinfo->save();
        $userinfo = $userinfo->where("id='%s'",$data['id'])->find();

        if($data['remember'] == 'on'){
            cookie('auth',$userinfo['authcode'],1296000);
        }else{
            cookie('auth',$userinfo['authcode']);
        }
        //缓存用户信息
        userinfo($userinfo['authcode'],$data['remember']);
        $this->success("登录成功，正在进入首页:)",U('Index/index'));
    }

    public function loginout(){
        cookie('auth',null);
        S(cookie('auth',null));
        $this->success("注销成功！",U('Sign/index'));
    }

    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}