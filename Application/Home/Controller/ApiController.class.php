<?php
	namespace Home\Controller;
	use Think\Controller;
/**
* ♥ Do have fun in what you're doing. Lovingly made by XCAI :)
* That's Api for Android application use.
* Notes:Code is far away from bug with the animal protecting!
* Build:2015.12.25
* Email:me@imxcai.com
*/
	class ApiController extends Controller
	{
		
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
		    	$pagecount['count'] = $count;
		        $this->ajaxReturn($data);

		        $this->ajaxReturn($pagecount);
		}
		public function count(){
			$moudle = I('get.moudle');
			if ($moudle == index) {
				$article = D('Article');
		    $count = $article->cache('num',60)->where('status=0')->count();
		    $pagecount['count'] = $count;
		    $this->ajaxReturn($pagecount);
			}else  {
				$id = I('get.id');
				$review = M('Review');
		        $count = $review->where("articleid = '%d'",$id)->count();
		        $pagecount['count'] = $count;
		    $this->ajaxReturn($pagecount);
			}
			
		} 
		public function article(){
			$id = I('get.id');
		    $article = D('Article');
		    $data = $article->cache($id,3600)->where("id = '%d'",$id)->find();
		    if(!$data){
		        $this->error("翻遍了数据库，都没有这个主题～");
		    }
		    $data['image'] = M('Userinfo')->where("id = '%d'",$data['userid'])->getField('image');
		    $article->where("id = '%d'",$id)->setInc('view');
		    $data['view']= $data['view']+1;      
		    $this->ajaxReturn($data);
		}

		public function review(){
		    $id = I('get.id');
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
		    $this->ajaxReturn($viewdata);
		}

		public function userinfo(){
			$username = I('get.user');
		    if($username){
	        $usercent = D('Userinfo');
	        $user = $usercent->where("username = '$username'")->getField('id,username,registtime,lasttime,image,userinfo,web,tnum,rnum');
	        if(!$user){
	            $this->error("找遍所有的街，都没有该用户～");
	        }
	        $this->ajaxReturn($user);
	        }
		}

		public function userarticle(){
		    $user = I('get.user');
		    $article = D('Article');
	        $userarticle = $article->username(I('get.user'));
	        $this->ajaxReturn($userarticle);
		}

		public function notice(){
			$data['notice'] = "Hello World";
			$this->ajaxReturn($data);
		}

		public function music(){
			$data['music'] = "1.mp3";
			$this->ajaxReturn($data);
		}
	}