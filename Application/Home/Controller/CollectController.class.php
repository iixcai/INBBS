<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/20
 * Time: 上午10:39
 */
class CollectController extends HomeController{
    public function index(){


    }

    public function like($id){
        $articleid = I('get.id');
        $data['userid'] = is_login();
        if($data['userid']) {

            $article = D('Article');
            $articleinfo = $article->articleid($articleid);
            $collect = D('Collect');
            $data['articleid'] = $articleinfo['id'];
            $data['title'] = $articleinfo['title'];
            $data['userid'] = is_login();
            $userinfo = S(cookie('auth'));
            $data['username'] = $userinfo['username'];
            $collect->create($data);
            $collect->add();

            $this->success("收藏成功！");
        }else{
            $this->error("麻烦请先登录～");
        }
    }
}