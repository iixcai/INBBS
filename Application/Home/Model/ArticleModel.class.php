<?php
namespace Home\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/18
 * Time: 下午5:05
 */
class ArticleModel extends Model{
    protected $_auto = array(
        array('creattime','creattime',1,'callback'),
        array('lasttime','creattime',3,'callback'),
    );

    protected function creattime(){
        return time();
    }




    /**
     * @param $id
     * @return bool
     */
    public function articleid($id){
        if($info = $this->where("id = '$id'")->find()){
            return $info;
        }else{
            return false;
        }
    }

    public function lists(){
        $data = $this->getField('id,title,username,userid,creattime,lasttime');
        return $data;
    }

    private function listmap(){

    }
    public function userid($id){
        $data = $this->where("userid = '$id'")->order('creattime DESC')->limit('10')->getField('id,title,review,creattime,node,nodeid');
        if(empty($data)){
            return false;
        }
        return $data;
    }

    public function username($user){
        $data = $this->where("username = '$user'")->order('creattime DESC')->limit('10')->getField('id,title,review,creattime,node,nodeid,username,userid');
        if(empty($data)){
            return false;
        }
        return $data;
    }

    public function nodeid($id){
        return $this->where("nodeid = '$id'")->getField('id,title,userid,username,lasttime,review');
    }
}