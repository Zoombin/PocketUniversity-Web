<?php

class PubackAction extends Action {

    private $menulist;
    private $menuUrl;
    private $menuNode;

    public function _initialize() {
        // $this->success(); 和 $this->error();通过isAdmin变量决定是否加载头部
        $this->assign('isAdmin', 1);
        // 检查用户是否登录管理后台, 有效期为$_SESSION的有效期
        if (!service('Passport')->isLoggedPu())
            redirect(U('home/Public/pulogin'));

        $this->menulist = array('乐购','广告','旅游','通知公告','APP市场','课件','消费','微博','导航','建设银行','培训','爱心校园');
        $this->menuUrl = array('shop/Admin/index','home/Ad/ad','travel/Admin/index'
            ,'announce/Admin/index','appstore/Admin/index','document/Admin/index'
            ,'coupon/Admin/index','weibo/Admin/index','home/Ditu/ditus','home/Bank/ccb','train/Admin/index','home/Donate/index');
        $this->menuNode = array('shop/Admin','home/Ad','travel/Admin'
            ,'announce/Admin','appstore/Admin','document/Admin'
            ,'coupon/Admin','weibo/Admin','home/Ditu','home/Bank','train/Admin','home/Donate');
        $this->assign('menuname', $this->menulist);
        $this->assign('menuurl', $this->menuUrl);
        $pubackMenu = $this->cachePubackMenu();
        $this->assign('pubackMenu', $pubackMenu);

        // 如果是应用的后台，检查用户是否具有节点权限
        $current = APP_NAME.'/'.MODULE_NAME;
        if($current!='home/Puback'){
            $key = array_search($current,$this->menuNode);
            if(false===$key || !in_array($key, $pubackMenu)){
                $this->assign('jumpUrl', U('home/Puback/index'));
                $this->error('您无权限查看!');
            }
        }
    }

    public function index(){
        $this->display();
    }

    private function cachePubackMenu(){
        $userInfo = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
        //$userInfo = S('S_userInfo_'.$this->mid);
        if(!isset($userInfo['pubackMenu'])){
            $userInfo['pubackMenu'] = array();
            foreach ($this->menuUrl as $k=>$v) {
                if ( service('SystemPopedom')->hasPopedom($this->mid, $v, false) ) {
                    $userInfo['pubackMenu'][] = $k;
                }
            }
            S('S_userInfo_'.$this->mid, $userInfo);
        }
        return $userInfo['pubackMenu'];
    }

}