<?php
namespace Home\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/20
 * Time: 上午10:51
 */
class CollectModel extends Model{



    public function userid($id){
        $data = $this->where("userid = '$id'")->limit('10')->getField('id,articleid,title');
        if(empty($data)){
            return false;
        }
        return $data;
    }

    public function username($user){
        $data = $this->where("username = '$user'")->limit('10')->getField('id,articleid,title');
        if(empty($data)){
            return false;
        }
        return $data;
    }
}