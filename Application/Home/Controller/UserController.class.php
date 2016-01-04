<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/22
 * Time: 下午4:58
 */
class UserController extends HomeController{
    public function index(){
        $userinfo = getuserinfo();
        /**
         if(I('get.id')){
            $usercent = D('Userinfo');
            $user = $usercent->getuserid(I('get.id'));
            $article = D('Article');
            $userarticle = $article->userid(I('get.id'));
            if(!$user){
                $this->error("找遍所有的街，都没有该用户～");
            }
            if(I('get.id') == $userinfo['id']){
                $modify = 'yes';
                $this->assign('modify',$modify);
            }
        }else
        **/
            if(I('get.user')){
            $usercent = D('Userinfo');
            $user = $usercent->getusername(I('get.user'));
            $article = D('Article');
            $userarticle = $article->username(I('get.user'));
            if(!$user){
                $this->error("找遍所有的街，都没有该用户～");
            }
            if(I('get.user') == $userinfo['username']){
                $modify = 'yes';
                $this->assign('modify',$modify);
            }
        }else{
            $user = S(cookie('auth'));

            if(empty($user)){
                $this->error("先去登录吧～",U('Sign/index'));
            }
            $article = D('Article');
            $userarticle = $article->userid($user['id']);
            $modify = 'yes';
            $this->assign('modify',$modify);

            }



        $userinfo = getuserinfo();
        if(empty($userinfo)){
            $userinfo = null;
        }
        $this->assign('user',$user);
        $this->assign('userarticle',$userarticle);

        $this->assign('userinfo',$userinfo);
        $this->display();
    }

    public function collect(){
        //$userid = is_login();
        $vo = S(cookie('auth'));
        $userid = $vo['id'];
        $collect = D('Collect');
        $usercollect = $collect->userid($userid);
        $this->assign('usercollect',$usercollect);
        $this->display();
    }

    public function update(){
        $user = S(cookie('auth'));
        $this->assign('user',$user);
        $this->display();
    }

    public function image(){
        $userid = is_login();
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg','png','jpeg','gif');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     'image/'; // 设置附件上传（子）目录
        $upload->autoSub = false;
        $upload->replace = true;
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $this->success('上传成功！');
        }

        $image = D('Userinfo');
        //$info['image']['savename'] = $userid;
        $data['image'] = $info['image']['savename'];
        $image->where("id = '$userid'")->save($data);

    }

    public function update_do(){
        $data = I('post.');
        $vo = S(cookie('auth'));
        $data['id'] = $vo['id'];
        $info = D('Userinfo');
        $info->create($data);
        		if($info->where("id = '%d'",$data['id'])->save()){
            $this->success("修改成功！");
        	}
       
        
    }

    public function setpasswd(){
        $data = I('post.');
        $user = D('User');
        if(!$user->create($data)){
           $this->error($user->getError());
        }
        $oldpassword = md5($data['oldpassword']);
        $data['password'] = md5($data['password']);
        if($user->where("password = '$oldpassword'")->find()){
            $user->where("password = '$oldpassword'")->save($data);
            S(cookie('auth'),null);
            cookie('auth',null);
            $this->success("密码修改成功！请重新登录！",U('Sign/index'));
        }

    }
}