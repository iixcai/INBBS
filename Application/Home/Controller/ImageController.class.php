<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/27
 * Time: 上午10:24
 */
class ImageController extends HomeController{
    public function index(){
        $id = I('get.id');
        $image = M('Userinfo')->where("id = '%d'",$id)->getField('image');
        echo $image;
    }
}