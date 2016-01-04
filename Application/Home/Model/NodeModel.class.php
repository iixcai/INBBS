<?php
namespace Home\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/24
 * Time: 上午9:52
 */
class NodeModel extends Model{
    public function getnode(){
        return $this->getField('id,node,type');
    }

    public function nodeinfo($id){
        $data =  $this->where("id = '$id'")->find();
        if(!$data){
            return false;
        }
        return $data;
    }
}