<?php
namespace Home\Controller;
use Home\Controller\HomeController;
use Think\Model\ViewModel;

class IndexController extends HomeController {
    /**
     *获取首页文件数据
     */
    public function index(){
        $article = D('Article');
        $count = $article->cache('num',60)->where('status=0')->count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $list = $article->where('status=0')->order('lasttime DESC')->limit($page->firstRow.', '.$page->listRows)->getField('id,title,username,userid,creattime,lasttime,review,node,nodeid');
        //处理数组

        foreach ($list as $arr){
            $image = M('Userinfo')->where("id = '%d'",$arr['userid'])->getField('image');
            $arr['image'] = $image;
            $data[] = $arr;
        }

        if(empty(S('node'))){
            $node = D('Node')->getnode();
            S('node',$node,86400);
        }
        $node = S('node');




        $this->assign('node',$node);
        $this->assign('list',$data);
        $this->assign('page',$show);
        $this->display();
    }

}