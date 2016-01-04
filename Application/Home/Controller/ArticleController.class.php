<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/18
 * Time: 下午5:00
 */
class ArticleController extends HomeController{
    public function index(){
        $id = I('get.id');
        $article = D('Article');
        $data = $article->cache($id,3600)->where("id = '%d'",$id)->find();

        if(!$data){
            $this->error("翻遍了数据库，都没有这个主题～");
        }
        $data['image'] = M('Userinfo')->where("id = '%d'",$data['userid'])->getField('image');
        $article->where("id = '%d'",$id)->setInc('view');

        $data['view']= $data['view']+1;

        $review = M('Review');
        $count = $review->where("articleid = '%d'",$id)->count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $list = $review->where("status=0 AND articleid='$id'")->order('creattime ')->limit($page->firstRow.', '.$page->listRows)->getField('id,content,username,userid,creattime');
        foreach ($list as $arr){
            $image = M('Userinfo')->where("id = '%d'",$arr['userid'])->getField('image');
            $arr['image'] = $image;
            $viewdata[] = $arr;
        }
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('list',$viewdata);
        $this->assign('data',$data);
        $this->display();

    }
    public function new_article(){
        $authcode = cookie('auth');
        if(empty(cookie('auth'))){
            $this->error("请先登录，然后再操作！");
        }
        $userinfo = M(userinfo);
        $info = $userinfo->where("authcode ='$authcode'")->find();
        if($info['status'] == 1){
            $this->error("帐户已被锁定",'',5);
        }
        $userinfo = getuserinfo();
        if(empty($userinfo)){
            $userinfo = null;
        }
        $node = M('node');
        $nodedata = $node->select();
        $this->assign('node',$nodedata);
        $this->assign('userinfo',$userinfo);

        $this->display();
    }

    /**
     *处理文章的的提交
     */
    public function post_article(){
        $data = I('post.');
        $userinfo = S(cookie('auth'));
        //markdown解析
        /**
        $parser = new \Org\Util\Parser();
        $data['content'] = $parser->makeHtml($data['content']);
        **/
        
        $data['userid'] = $userinfo['id'];
        $data['username'] = $userinfo['username'];
        $data['nodeid'] = M('Node')->where("node = '%s'",$data['node'])->getField('id');
        $article = D('Article');
        $article->create($data);
        if(!$article->add()){
            exit('boom boom 服务器开小差去了～');
        }
        M('Userinfo')->where("id = '%d'",$userinfo['id'])->setInc('tnum');
        $this->success("发表成功！",U('Index/index'));
    }

    public function review(){
        $data = I('post.');
        $userid = is_login();
        if(!$userid > 0){
            $this->error("登录后才可以发表评论！");
            exit;
        }
        if(!D('Article')->articleid($data['articleid'])){
            exit("文章不存在！");
        }
        $userinfo = S(cookie('auth'));
        $data['userid'] = $userinfo['id'];
        $data['username'] = $userinfo['username'];
        $review = D('Review');
        if(!$review->create($data)){
            exit($review->getError());
        }
        $review->add();
        $date['lasttime'] = time();
        M('Article')->where("id = '%s'",$data['articleid'])->save($date);
        M('Article')->where("id = '%s'",$data['articleid'])->setInc('review');
        M('Userinfo')->where("id = '%s'",$userid)->setInc('rnum');
        $this->success("评论成功！");

    }


}