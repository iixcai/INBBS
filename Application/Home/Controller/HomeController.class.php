<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Created by PhpStorm.
 * User: XCAI
 * Date: 15/8/20
 * Time: 上午9:30
 */
class HomeController extends Controller{
    /**
     *
     */
    protected function _initialize(){
        G('begin');
        /* 读取站点配置 */
        //S('config',null);
        $config = S('config');

        if(empty($config)){
            $config = config();
            S('config',$config,86400);
        }
        C($config);

        $bbsinfo = S('bbsinfo');
        if(empty($bbsinfo)){
            $bbsinfo = bbsinfo();
            S('bbsinfo',$bbsinfo,86400);
        }
        if(C('WEB_LOCK') == '1'){
            exit("网站暂时关闭");
        }
        $userinfo = getuserinfo();
        if(empty($userinfo)){
            $userinfo = null;
        }
        $listdata = S('listdata');
        if(empty($listdata)){
            $listarticle = M('Article');
            $listdata = $listarticle->where('status = 0')->order('view')->limit(15)->getField('id,title,review');
            S('listdata',$listdata,600);
        }
        $this->assign('listdata',$listdata);
        $this->assign('userinfo',$userinfo);
        $this->assign('bbsinfo',$bbsinfo);
    }
}
