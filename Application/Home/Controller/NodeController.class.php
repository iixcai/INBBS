<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/24
 * Time: 上午10:16
 */
class NodeController extends HomeController{
    public function index(){
        $id = I('get.id');
        if($id == null){
            $node = D('Node')->getnode();
            $this->assign('node',$node);
        }else{
            $nodeinfo = D('Node')->nodeinfo($id);
            if(!$nodeinfo){
                $this->error("没有该节点");
            }
            //node列表
            $article = M('Article');
            $count = $article->where(array("status"=>0,"nodeid"=>$id))->count();
            $page = new \Think\Page($count,10);
            $show = $page->show();
            $list = $article->where(array("status"=>0,"nodeid"=>$id))->order('lasttime DESC')->limit($page->firstRow.', '.$page->listRows)->getField('id,title,username,userid,creattime,lasttime,review,node,nodeid');
            foreach ($list as $arr){
                $image = M('Userinfo')->where("id = '%d'",$arr['userid'])->getField('image');
                $arr['image'] = $image;
                $data[] = $arr;
            }

            $this->assign('nodeinfo',$nodeinfo);
            $this->assign('list',$data);
            $this->assign('page',$show);


        }

        $this->display();


    }
}