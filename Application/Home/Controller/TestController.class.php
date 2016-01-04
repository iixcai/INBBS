<?php
namespace Home\Controller;
use Think\Controller;

class TestController extends HomeController{
		function is_login(){
     $auth = cookie('auth');
     if(empty($auth)){
        echo 0;
     }
     $user = M('Userinfo');
     if($info = $user->where("authcode = '$auth'")->find()){
         echo $info['id'];
     }else{
         echo 0;
     }
}
}