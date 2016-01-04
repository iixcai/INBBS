<?php
namespace Home\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/20
 * Time: 上午9:29
 */
class ReviewModel extends Model{
    protected $_validate = array(
        array('content','1,1000','内容不能为空!',3,'length'),
    );
    protected $_auto = array(
        array('creattime','creattime',3,'callback'),
    );

    protected function creattime(){
        return time();
    }




}