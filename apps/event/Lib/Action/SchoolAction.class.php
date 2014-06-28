<?php

/**
 * SchoolAction
 * 校方活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class SchoolAction extends SchoolbaseAction {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
    }

    public function adminlogin() {
        if ($_SESSION['TeacherAdmin'] == '123456' || $_SESSION['TeacherAdmin'] == $this->sid) {
            redirect(U('event/Readme/index'));
        }
        $this->display();
    }

    public function download() {
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=help.pdf");
        echo file_get_contents(SITE_PATH . '/apps/event/Tpl/default/Public/help.pdf');
    }

    public function doLogin() {
        // 提示消息不显示头部
        $this->assign('isAdmin', '1');
        // 检查验证码
        $opt_verify = $this->_isVerifyOn('login');
        if ($opt_verify && md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }
        $_POST['number'] = t($_POST['number']);
        //选择学校登录
        $username = $_POST['number'] . $this->school['email'];
        //
        $password = $_POST['password'];

        if (!$password) {
            $this->error(L('please_input_password'));
        }
        service('Passport')->logoutSchoolAdmin();
        $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));

        //检查全局管理员或全局观察员
        if (!$result && service('Passport')->getLastError() != '用户未激活') {
            $username = $_POST['number'] . '@test.com';
            $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));
        }

        //检查是否激活
        if (!$result && service('Passport')->getLastError() == '用户未激活') {
            $this->assign('jumpUrl', U('event/School/index'));
            $this->error('该用户尚未激活，请更换帐号或激活帐号！');
            exit;
        }

        if ($result) {
            if ($_SESSION['md5pass']) {
                $this->assign('jumpUrl', U('home/Account/security'));
                $this->error('系统安全策略升级，您的账号存在安全隐患，请修改密码。');
                exit;
            }
            redirect(U('event/School/index'));
        } else {
            $this->error(L('login_error'));
        }
    }

    public function doAdminLogin() {
        // 提示消息不显示头部
        $this->assign('isAdmin', '1');
        // 检查验证码
        if (md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }
        $_POST['email'] = t($_POST['email']);
        if (isset($_POST['email'])) {
            if (!strpos($_POST['email'], '@')) {
                $_POST['email'] = $_POST['email'] . $this->school['email'];
            }
        }
        // 数据检查
        if (empty($_POST['password'])) {
            $this->error(L('password_notnull'));
        }
        if (isset($_POST['email']) && !isValidEmail($_POST['email'])) {
            $this->error(L('email_format_error'));
        }

        // 检查帐号/密码
        $is_logged = false;
        if (isset($_POST['email'])) {
            $is_logged = service('Passport')->loginSchoolAdmin($_POST['email'], $_POST['password'], $this->sid);
        } else if ($this->mid > 0) {
            $is_logged = service('Passport')->loginSchoolAdmin($this->mid, $_POST['password'], $this->sid);
        } else {
            $this->error(L('parameter_error'));
        }

        if ($is_logged) {
            redirect(U('event/Readme/index'));
        } else {
            $this->assign('jumpUrl', U('event/School/adminlogin'));
            $this->error(L('login_error'));
        }
    }

    public function logoutAdmin() {
        // 成功消息不显示头部
        $this->assign('isAdmin', '1');

        service('Passport')->logoutSchoolAdmin();
        $this->assign('jumpUrl', U('event/School/adminlogin'));
        $this->success(L('exit_success'));
    }

    public function logout() {
        service('Passport')->logoutLocal();
        $domain = parse_url($_SERVER['SERVER_NAME']);
        $url = "http://" . get_host_needle() . "/index.php?app=home&mod=Public&act=logout2&back=" . $domain['path'];
        redirect($url);
//        $this->assign('jumpUrl', $url);
//        $this->success(L('exit_success'));
    }

    public function school() {
        $selectedArea = explode(',', $_GET['selected']);
        if (!empty($selectedArea[0])) {
            $this->assign('selectedarea', $_GET['selected']);
        }
        $pNetwork = model('Schools');
        $list = $pNetwork->makeLevelParentTree($_GET['sid']);
        $this->assign('list', json_encode($list));
        $this->display();
    }

    private function _isVerifyOn($type = 'login') {
        // 检查验证码
        if ($type != 'login' && $type != 'register')
            return false;
        $opt_verify = $GLOBALS['ts']['site']['site_verify'];
        return in_array($type, $opt_verify);
    }

    private function _rightSide() {
        global $ts;
        $install_app = $ts['install_apps'];
        $this->assign('install_app', $install_app);
        if ($this->sid != 505 || $this->jyrcOut) {
            //读取最新新闻
            $map['is_school_event'] = $this->sid;
            $map['isDel'] = 0;
            $map['status'] = 1;
            $eids = getSubByKey(D('Event')->field('id')->where($map)->findAll(), 'id');
            $map = array();
            $map['eventId'] = array('in', $eids);
            $map['isDel'] = 0;
            $news = D('EventNews')->where($map)->order('id Desc')->limit(5)->select();
            $this->assign('rightNews', $news);
        } else {
            //菁英人才新闻
            $map['sid'] = 505;
            $map['isDel'] = 0;
            $news = M('school_news')->where($map)->order('id Desc')->limit(5)->select();
            $this->assign('jyrcNews', $news);
            //菁英人才作业
            $work = M('school_work')->where($map)->order('id Desc')->limit(5)->select();
            $this->assign('jyrcWork', $work);
            //是否有待做的
            $done = M('school_workback')->where('uid='.$this->uid)->field('wid')->findAll();
            if($done){
                $doneIds = getSubByKey($done, 'wid');
                $map['id'] = array('not in', $doneIds);
            }
            $map['eTime'] = array('gt', time());
            $newWork = M('school_work')->where($map)->field('id')->find();
            $this->assign('newWork', $newWork);
        }
        //读取推荐积分
        $map = array();
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $hotList = M('jf')->where($map)->order("RAND()")->limit(3)->select();
        $this->assign('rightGift', $hotList);
        //排名
        $map = array();
        $map['tj_sid'] = $this->sid;
        $map['credit1'] = array('neq',0);
        $mon = M('tj_event')->where($map)->field('tj_uid as uid,credit1 as note')->order('note DESC')->limit(10)->select();
        $this->assign('mon', $mon);
        $map = array();
        $map['tj_sid'] = $this->sid;
        $map['credit2'] = array('neq',0);
        $mon = M('tj_event')->where($map)->field('tj_uid as uid,credit2 as note')->order('note DESC')->limit(10)->select();
        $this->assign('semester', $mon);
        $map = array();
        $map['tj_sid'] = $this->sid;
        $map['credit3'] = array('neq',0);
        $mon = M('tj_event')->where($map)->field('tj_uid as uid,credit3 as note')->order('note DESC')->limit(10)->select();
        $this->assign('year', $mon);
    }

    public function fav() {
        $fav = D('EventFav')->getFav($this->mid);
        if ($fav['cid']) {
            $this->assign('cid', explode(',', $fav['cid']));
        }
        if ($fav['sid']) {
            $this->assign('sid', explode(',', $fav['sid']));
        }
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->sid));
        $this->assign('addSchool', model('Schools')->makeLevel0Tree($this->sid));
        $this->display();
    }

    private function _getSearch() {
        $this->assign('cat1', json_encode(D('SchoolOrga')->getAll($this->sid, 1)));
        $this->assign('cat2', json_encode(D('SchoolOrga')->getAll($this->sid, 2)));
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('addSchool', json_encode(sortByCol($school,'display_order')));
    }

    public function ajaxSearch() {
        $cat = intval($_POST['cat']);
        if ($cat) {
            $field = 'id, title';
            $info = D('SchoolOrga')->getAll($this->sid, $cat, $field);
        } else {
            $info = model('Schools')->makeLevel0Tree($this->sid);
        }
        echo json_encode($info);
    }

    public function index() {
        unset($_SESSION['schoo_searchCat']);
        $this->setTitle('活动首页');
        $this->assign('top1', $this->event->getProv($this->sid));

        //菁英人才 并且 未登录前 或 登录用户不是菁英
        $template = 'index';
        if ($this->jyrcOut) {
            $template = 'jyHome';
        } else {
            $daoFav = D('EventFav');
            if (isset($_POST['fav'])) {
                $data['cid'] = implode(',', $_POST['favCid']);
                $data['sid'] = implode(',', $_POST['favSid']);
                $daoFav->addFav($this->mid, $data);
            }
            $this->_getSearch();
            //置顶的
            $map['isTop'] = 1;
            $map['is_school_event'] = $this->sid;
            $this->assign('top', $this->event->getSchoolIndex($map));
            //感兴趣的
            unset($map['isTop']);
            $fav = $daoFav->getFav($this->mid);
            if ($fav['cid']) {
                $map['_string'] = 'typeId IN (' . $fav['cid'] . ')';
            }
            if ($fav['sid']) {
                if ($map['_string']) {
                    $map['_string'] .= ' OR sid IN (' . $fav['sid'] . ')';
                } else {
                    $map['_string'] = 'sid IN (' . $fav['sid'] . ')';
                }
            }
            $this->assign('fav', $this->event->getSchoolIndex($map, 4));
            //搜索组织
            $this->assign('sidCss', 0);
            $this->assign('sid', 0);
        }
        $this->_rightSide();
        //是否加载引导页
        $guide = false;
        if($this->smid && $this->user['can_add_event'] && !$this->user['is_guided']){
            $guide = true;
        }
        $this->assign('guide', $guide);
        $this->display($template);
    }

    public function endGuide(){
        $uid = $this->smid;
        if($uid){
            M('user')->setField('is_guided', 1, 'uid='.$uid);
            $userLoginInfo = S('S_userInfo_' . $uid);
            if (!empty($userLoginInfo)) {
                $userLoginInfo['is_guided'] = 1;
                S('S_userInfo_' . $uid, $userLoginInfo);
                if ($_SESSION['userInfo']['uid'] == $uid) {
                    $_SESSION['userInfo'] = $userLoginInfo;
                }
            }
        }
    }


    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function board() {
        if ($_GET['cid'] == 'all') {
            unset($_SESSION['schoo_searchCat']['cid']);
        } elseif ($_GET['cid']) {
            $_SESSION['schoo_searchCat']['cid'] = intval($_GET['cid']);
        }
        if ($_GET['sid'] == 'all') {
            unset($_SESSION['schoo_searchCat']['sid']);
        } elseif ($_GET['sid']) {
            $_SESSION['schoo_searchCat']['sid'] = intval($_GET['sid']);
        }
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['schoo_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['schoo_searchCat']['cat'] = t($_GET['cat']);
        }
        if(isset($_POST['title'])){
            $searchTitle = t($_POST['title']);
            if (mb_strlen($searchTitle, 'utf8') < 1) {
                unset($_SESSION['schoo_searchCat']['title']);
            }else{
                $_SESSION['schoo_searchCat']['title'] = $searchTitle;
            }
        }
        $this->_getSearch();
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['is_school_event'] = $this->sid;
        //查询
        if($_SESSION['schoo_searchCat']['title']){
            $map['title'] = array('like', "%" . $_SESSION['schoo_searchCat']['title'] . "%");
            $this->assign('searchTitle', $_SESSION['schoo_searchCat']['title']);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_SESSION['schoo_searchCat']['cid']) {
            $map['typeId'] = $_SESSION['schoo_searchCat']['cid'];
        }
        $sidCss = 0;
        $sid = 0;
        if ($_SESSION['schoo_searchCat']['sid']) {
            $map['sid'] = $_SESSION['schoo_searchCat']['sid'];
            $sid = $_SESSION['schoo_searchCat']['sid'];
            if ($_SESSION['schoo_searchCat']['sid'] > 0) {
                $sidCss = 2;
            } else {
                $var = 0 - $_SESSION['schoo_searchCat']['sid'];
                $cat = D('SchoolOrga')->getField('cat', 'id=' . $var);
                $sidCss = 1;
                if ($cat == 1) {
                    $sidCss = 3;
                }
            }
        }
        $this->assign('sidCss', $sidCss);
        $this->assign('sid', $sid);
        switch ($_SESSION['schoo_searchCat']['cat']) {
            case 'jy':    //全省菁英
                $map['is_prov_event'] = 1;
                unset($map['is_school_event']);
                if ($this->sid != 505) {
                    $eventIds = D('EventSchool')->getEventIds($this->sid);
                    $map['id'] = array('in', $eventIds);
                }
                $this->setTitle("全省菁英活动");
                break;
            case 'top':    //热门推荐
                $map['isTop'] = 1;
                $this->setTitle("热门推荐的活动");
                break;
            case 'join':    //参与的
                $map_join['status'] = array('neq', 0);
                $map_join['uid'] = $this->uid;
                $eventIds = D('EventUser')->field('eventId')->where($map_join)->findAll();
                foreach ($eventIds as $v) {
                    $in_arr[] = $v['eventId'];
                }
                $map['id'] = array('in', $in_arr);
                $this->setTitle("我参与的活动");
                break;
            case 'nofinish':    //待完结
                $map['uid'] = $this->uid;
                $map['is_prov_event'] = 0;
                $map['school_audit'] = array('in', '2,3,4');
                $this->setTitle("待我完结的活动");
                break;
            case 'fav':    //感兴趣的
                $fav = D('EventFav')->getFav($this->mid);
                if ($fav['cid']) {
                    $map['_string'] = 'typeId IN (' . $fav['cid'] . ')';
                }
                if ($fav['sid']) {
                    if ($map['_string']) {
                        $map['_string'] .= ' OR sid IN (' . $fav['sid'] . ')';
                    } else {
                        $map['_string'] = 'sid IN (' . $fav['sid'] . ')';
                    }
                }
                $this->setTitle("感兴趣的活动");
                break;
            case 'launch':    //发起的
                //$map['status'] = array('in', '0,1');
                unset($map['status']);
                unset($map['is_school_event']);
                $map['uid'] = $this->uid;
                $this->setTitle("我发起的活动");
                break;
            default:      //活动首页
        }
        $result = $this->event->getEventList($map, $this->mid);
        $this->assign($result);
        $this->_rightSide();
        $this->display();
    }

    public function area() {
        //已选地区
        $selectedArea = explode(',', $_GET['selected']);
        if (!empty($selectedArea[0])) {
            $this->assign('selectedarea', $_GET['selected']);
        }
        $pNetwork = model('Schools');
        $list = $pNetwork->makeLevelTree(0);
        $this->assign('list', json_encode($list));
        $this->display();
    }

    /**
     * addEvent
     * 发起活动
     * @access public
     * @return void
     */
    public function addEvent() {
        $this->_createLimit();
        //$this->_checkSchoolUser();
        //审核人
        $map['can_event'] = 1;
        $map['sid'] = $this->sid;
        $audit = M('user')->where($map)->field('uid,uname,event_role_info')->findAll();
        if (!$audit) {
            $this->error('暂无审核人员，请耐心等待！');
        }
        //部落活动
        $group = M('event_group')->where('uid=' . $this->mid)->findAll();
        if (count($group) == 1) {
            $this->assign('isgroup', 1);
            $this->assign('groupId', $group[0]['gid']);
        } elseif (count($group) > 1) {
            $daogroup = M('group');
            foreach ($group as $k => $v) {
                $group[$k]['name'] = $daogroup->getField('name', 'id = ' . $v['gid']);
            }
            $this->assign('group', $group);
        }

        $this->assign('audit', $audit);
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->sid));
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('addSchool', $school);
        $typeDao = D('EventType');
        $type = $typeDao->getType2();
        $this->assign('type', $type);
        $this->setTitle('发起' . $this->appName);
        $this->_rightSide();
        $this->display();
    }

    //发起全省活动
    public function addProv() {
        $this->_checkSchoolUser();
        if (!$this->user['can_prov_event']) {
            $this->error('您没有权限发布全省活动！');
        }
        $typeDao = D('EventType');
        $type = $typeDao->getType2();
        $this->assign('type', $type);
        $this->setTitle('发起全省活动');
        $this->_rightSide();
        $this->display();
    }

    private function _createLimit() {
        $this->_checkSchoolUser();
        if (!$this->user['can_add_event']) {
            $this->error('您没有权限发布活动！');
        }
    }

    /**
     * doAddEvent
     * 添加活动
     * @access public
     * @return void
     */
    public function doAddEvent() {
        $this->_createLimit();

        //参数合法性检查
        $required_field = array(
            'title' => '活动名称',
            'audit_uid' => '审核人',
            'address' => '活动地点',
            'typeId' => '活动分类',
            'sTime' => '活动开始时间',
            'eTime' => '活动结束时间',
            'deadline' => '截止报名时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 30) {
            $this->error('活动名称最大30个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 250) {
            $this->error("活动简介1到250字!");
        }
        //发起部落活动判断
        if (isset($_POST['group']) && !$_POST['group']) {
            $this->error('归属部落不能为空！');
        }
        $map['sid'] = intval($_POST['sid']);
        if($map['sid']==0){
            $this->error('归属组织不能为空！');
        }
        $map['audit_uid'] = intval($_POST['audit_uid']);
        if($map['audit_uid']==0){
            $this->error('审核人不能为空！');
        }
        if (isset($_POST['group'])&&$_POST['group']) {
            $res = M('event_group')->where('gid='.$_POST['group'].' AND uid ='.$this->mid)->find();
            if($res){
            $map['gid'] = $_POST['group'];
            }else{
              $this->error("您无法发起该部落活动");
            }
        }


        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);

        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
//        if ($map['sTime'] < mktime(0, 0, 0, date('M'), date('D'), date('Y'))) {
//            $this->error("开始时间不得早于当前时间");
//        }
//        if ($map['deadline'] < time()) {
//            $this->error("报名截止时间不得早于当前时间");
//        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }

        if (empty($_FILES['cover']['name'])) {
            $this->error('请上传活动图标');
        }

        $defaultBanner = intval($_POST['banner']);
        if (empty($_FILES['logo']['name']) && !$defaultBanner) {
            $this->error('请上传活动海报');
        }

        //得到上传的图片
        if(!isImg($_FILES['cover']['tmp_name'])){
             $this->error('活动图标文件格式不对');
        }
        if ($_FILES['logo']['name']&&(!isImg($_FILES['logo']['tmp_name']))) {
            $this->error('活动海报文件格式不对');
        }
        $images = X('Xattach')->upload('event' );
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error($images['info']);
        }

        $map['uid'] = $this->mid;
        $map['default_banner'] = $defaultBanner;
        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        if(t($_POST['limitCount']) == '无限制'){
            $map['limitCount'] = 6000000;
        }else{
            $map['limitCount'] = intval($_POST['limitCount']);
        }
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        //$map['show_in_xyh'] = isset($_POST['show_in_xyh']) ? 1 : 0;
        $map['show_in_xyh'] = 1;
        //test大学不同步到
        if ($this->sid == 473) {
            $map['show_in_xyh'] = 0;
        }
        $map['is_school_event'] = $this->sid;

        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['description'] = $textarea;
        //发布审核
        $map['status'] = 0;

        if ($addId = $this->event->doAddEvent($map, $images,$attach)) {
            X('Credit')->setUserCredit($this->mid, 'add_event');
            //显示于学校
            D('EventSchool')->addBySids($addId, $this->sid);
            $this->assign('jumpUrl', U('/School/board', array('cat' => 'launch')));
            //发短信给初审人
            $isSend = M('user_privacy')->where("`key`='active' AND uid=" . $map['audit_uid'])->field('value')->find();
            if ($isSend['value'] == 1) {
                $active = M('user')->where('uid=' . $map['audit_uid'])->field('mobile,sid')->find();
                if ($active['sid'] == $this->sid && $active['mobile']) {
                    $msg = '亲爱的PocketUni用户,有新的活动"' . $map['title'] . '"等待您的审核。';
                    service('Sms')->sendsms($active['mobile'], $msg);
                }
            }
            $this->success($this->appName . '创建成功，请等待审核');
        } else {
            $this->error($this->appName . '添加失败');
        }
    }

    public function doAddProv() {
        $this->_checkSchoolUser();
        if (!$this->user['can_prov_event']) {
            $this->error('您没有权限发布全省活动！');
        }

        //参数合法性检查
        $required_field = array(
            'title' => '活动名称',
            'typeId' => '活动分类',
            'sTime' => '活动开始时间',
            'eTime' => '活动结束时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 30) {
            $this->error('活动名称最大30个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 250) {
            $this->error("活动简介1到250字!");
        }
        $sids = substr($_POST['showSids'], 2, strlen($_POST['showSids']) - 3);
        if (!$sids) {
            $this->error('请选择活动显示于哪些学校');
        }
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        $map['deadline'] = _paramDate($_POST['deadline']);

        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }
        if (empty($_FILES['cover']['name'])) {
            $this->error('请上传活动海报');
        }
        $defaultBanner = intval($_POST['banner']);
        if (empty($_FILES['logo']['name']) && !$defaultBanner) {
            $this->error('请上传活动logo');
        }

        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $images = X('Xattach')->upload('event', $options);
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error($images['info']);
        }
        $map['uid'] = $this->mid;
        $map['default_banner'] = $defaultBanner;
        $map['title'] = $title;
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['show_in_xyh'] = 1;
        $map['school_audit'] = 2;
        $map['is_prov_event'] = 1;
        $map['is_school_event'] = 505;
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['description'] = $textarea;
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        //发布审核
        $map['status'] = 1;

        if ($addId = $this->event->doAddEvent($map, $images)) {
            X('Credit')->setUserCredit($this->mid, 'add_event');
            //显示于学校
            D('EventSchool')->addBySids($addId, $sids);
            $this->assign('jumpUrl', U('/School/board', array('cat' => 'launch')));
            $this->success($this->appName . '创建成功');
        } else {
            $this->error($this->appName . '添加失败');
        }
    }

    /**
     * doAction
     * 本人取消申请
     * @access public
     * @return void
     */
    public function doDelAction() {
        $data['eventId'] = intval($_POST['id']);
        $data['uid'] = $this->mid;
        $res = $this->event->doDelUser($data);
        if ($res['status']) {
            $this->success($res['msg']);
        }
        $this->error($res['msg']);
    }

    public function playFlash() {
        $id = intval($_REQUEST['id']);
        $app = D('EventFlash')->where("`id`={$id}")->find();
        if (!$app) {
            $this->error('视频不存在或已被删除！');
        }
        $app['url'] = get_flash_url($app['host'], $app['flashvar']);
        $this->assign($app);
        $this->display();
    }

    public function ajaxNote() {
        $this->_checkSchoolUser();
        $note = intval($_POST['note']);
        if ($note < 0 || $note > 6) {
            $this->error('操作失败');
        }
        if ($data = $this->event->doAddNote(intval($_POST['id']), $this->mid, $note)) {
            $this->ajaxReturn($data, $info = '操作成功', $status = 1, $type = 'JSON');
        }
        $this->error('操作失败');
    }

//////////////////////////////////////////////////////////////////////////////

    public function jf() {
        $this->setTitle('积分商城');
        $map['isDel'] = 0;
        $map['number'] = array('gt', 0);
        $map['sid'] = $this->sid;
        $res = M('jf')->where($map)->order('id DESC')->findPage(12);
        $this->assign($res);
        $this->_rightSide();
        $this->display();
    }

    public function gift() {
        $this->setTitle('积分商城');
        $id = intval($_REQUEST['id']);
        $obj = M('jf')->where("`id`={$id} AND isDel=0")->find();
        if (!$obj) {
            $this->error('物品不存在或已被删除！');
        }
        $this->assign('obj', $obj);
        $scoreRest = 0;
        if ($this->mid) {
            $scoreRest = $this->user['school_event_score'] - $this->$user['school_event_score_used'];
        }
        $this->assign('scoreRest', $scoreRest);
        $this->_rightSide();
        $this->display();
    }

    public function duihuan() {
        $id = intval($_REQUEST['id']);
        $count = intval($_REQUEST['count']);
        $obj = M('jf')->where("`id`={$id} AND isDel=0")->find();
        if (!$obj) {
            $this->assign('errorMsg', '物品不存在或已被删除！');
        } elseif ($count <= 0) {
            $this->assign('errorMsg', '兑换数量不合法！');
        } elseif ($obj['sid'] != $this->user['sid']) {
            $this->assign('errorMsg', '您不是该校学生，没有权力兑换此物品！');
        } elseif ($obj['cost'] * $count > ($this->user['school_event_score'] - $this->user['school_event_score_used'])) {
            $this->assign('errorMsg', '积分不足，请选择其他礼品进行兑换！');
        } elseif ($obj['number'] < $count) {
            $this->assign('errorMsg', '库存不足，请刷新页面更新礼品数量！');
        } else {
            $test = 0;
            $date = date('ymd', time());
            require_once(SITE_PATH . '/addons/libs/String.class.php');
            $randval = String::rand_string(4, 5);
            $code = $date . $randval;
            $dao = D('Jfdh');
            while ($test <= 9999 && $dao->isCodeUesed($code)) {
                $code = $date . String::rand_string(4, 5);
                $test++;
            }
            if ($test == 10000) {
                $this->assign('errorMsg', '服务器忙，请改日再试！');
            } else {
                $data['uid'] = $this->mid;
                $data['jfid'] = $obj['id'];
                $data['number'] = $count;
                $data['cost'] = $obj['cost'];
                $data['code'] = $code;
                $data['sid'] = $obj['sid'];
                if (!$dao->doAdd($data)) {
                    $this->assign('errorMsg', '服务器忙，请稍后再试！');
                } else {
                    $this->assign('rest', $obj['number'] - $count);
                    $this->assign('code', $code);
                }
            }
        }
        $this->display();
    }

    //我兑换的物品列表
    public function myjf() {
        $this->_checkSchoolUser();
        $map['uid'] = $this->mid;
        $map['sid'] = $this->sid;
        $res = D('Jfdh')->getList($map);
        //var_dump($res);die;
        $this->assign($res);
        $this->setTitle('我的兑换');
        $this->_rightSide();
        $this->display();
    }

    public function addPrint() {
        $this->_checkSchoolUser();
        $this->assign('type', 'add');
        $orga = 0;
        if ($_GET['orga']) {
            $orga = 1;
        }
        $this->assign('orga', $orga);
        $this->_rightSide();
        $this->assign('event', $this->_getJoinList());
        $this->display('editPrint');
    }

    public function doAddPrint() {
        $this->_checkSchoolUser();
        $data['is_orga'] = ($_POST['is_orga']) ? 1 : 0;
        if ($data['is_orga'] == 0) {
            //参数合法性检查
            $data['title'] = t($_POST['title']);
            $required_field = array(
                'title' => '标题',
            );
            foreach ($required_field as $k => $v) {
                if (empty($data[$k]))
                    $this->error($v . '不可为空');
            }
            $data['content'] = t(h($_POST['content']));
        }

        if (is_array($_POST['checkbox'])) {
            $data['eids'] = implode(',', $_POST['checkbox']);
        } else {
            $this->error('请选择活动！');
        }
        $data['cTime'] = time();
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;

        $id = M('event_print')->add($data);
        if (!$id) {
            $this->error('抱歉：添加失败，请稍后重试');
            exit;
        }
        if ($data['is_orga']) {
            $this->assign('jumpUrl', U('event/School/myprint'));
        } else {
            $this->assign('jumpUrl', U('event/School/printView', array('id' => $id)));
        }
        $this->success('添加成功');
    }

    public function doEditPrint() {
        $id = intval($_POST['id']);
        $map['id'] = $id;
        $map['uid'] = $this->smid;
        if (!$obj = M('event_print')->where($map)->find()) {
            $this->error('打印记录不存在或已删除');
        }
        if (!$obj['is_orga']) {
            $data['title'] = t($_POST['title']);
            //参数合法性检查
            $required_field = array(
                'title' => '标题',
            );
            foreach ($required_field as $k => $v) {
                if (empty($data[$k]))
                    $this->error($v . '不可为空');
            }
            $data['content'] = t(h($_POST['content']));
        }

        if (is_array($_POST['checkbox'])) {
            $data['eids'] = implode(',', $_POST['checkbox']);
        } else {
            $this->error('请选择活动！');
        }
        $data['cTime'] = time();

        $uid = M('event_print')->where('id = ' . $id)->save($data);
        if (!$uid) {
            $this->error('抱歉：修改失败，请稍后重试');
            exit;
        }
        if ($obj['is_orga']) {
            $this->assign('jumpUrl', U('event/School/editPrint', array('id' => $id)));
        } else {
            $this->assign('jumpUrl', U('event/School/printView', array('id' => $id)));
        }
        $this->success('修改成功');
    }

    public function printView() {
        $this->_checkSchoolUser();
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        if (!$this->rights['allAdmin'] && !$this->user['can_print']) {
            $map['is_orga'] = 0;
            $map['uid'] = $this->smid;
        }
        $obj = M('event_print')->where($map)->find();
        if (!$obj) {
            $this->error('打印记录不存在或已被删除！');
        }
        $this->assign($obj);
        $map = array();
        $map['isDel'] = 0;
        $map['is_school_event'] = $this->sid;
        $map['id'] = array('in', $obj['eids']);
        $list = $this->event->where($map)->field('title,description,sid,sTime')->findAll();
        $this->assign('list', $list);
        $this->display();
    }

    public function exportExcel() {

        $this->_checkSchoolUser();
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        if (!$this->rights['allAdmin'] && !$this->user['can_print']) {
            $map['is_orga'] = 0;
            $map['uid'] = $this->smid;
        }
        $obj = M('event_print')->where($map)->find();
        if (!$obj) {
            $this->error('打印记录不存在或已被删除！');
        }
        $this->assign($obj);
        $map = array();
        $map['isDel'] = 0;
        $map['is_school_event'] = $this->sid;
        $map['id'] = array('in', $obj['eids']);
        $list = $this->event->where($map)->findAll();
        $this->assign('list', $list);
        $this->display();
    }

    public function myprint() {
        $this->_checkSchoolUser();
        $map['sid'] = $this->sid;
        $map['uid'] = $this->smid;
        $res = M('event_print')->where($map)->order('id desc')->findPage(10);
        $this->assign($res);
        $this->_rightSide();
        $this->display();
    }

    public function editPrint() {
        $this->_checkSchoolUser();
        $_GET['id'] = intval($_GET['id']);
        if ($_GET['id'] <= 0)
            $this->error('参数错误');
        $map['id'] = $_GET['id'];
        $map['uid'] = $this->smid;
        $obj = M('event_print')->where($map)->find();
        if (!$obj)
            $this->error('无此打印记录');
        if ($obj['eids']) {
            $obj['eids'] = explode(',', $obj['eids']);
        } else {
            $obj['eids'] = array();
        }
        $this->assign($obj);
        $this->assign('event', $this->_getJoinList());
        $this->assign('type', 'edit');
        $orga = 0;
        if ($obj['is_orga']) {
            $orga = 1;
        }
        $this->assign('orga', $orga);
        $this->_rightSide();
        $this->display();
    }

    private function _getJoinList() {
        $map_join['status'] = 2;
        $map_join['uid'] = $this->uid;
        $eventIds = D('EventUser')->field('eventId')->where($map_join)->findAll();
        $eids = getSubByKey($eventIds, 'eventId');
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['is_school_event'] = $this->sid;
        $map['id'] = array('in', $eids);
        return M('event')->field('id,title')->where($map)->findAll();
    }

    public function printPdf() {

    }

    private function _checkSchoolUser() {
        if (!$this->smid) {
            $this->assign('jumpUrl', U('event/School/index'));
            $this->error('请先登录!');
        }
    }

    public function doDelPrint() {
        $id = intval($_REQUEST['id']);
        if ($id <= 0) {
            $this->error('操作失败');
        }
        $map['id'] = $id;
        $map['uid'] = $this->mid;
        $dao = M('event_print');
        if ($dao->where($map)->delete()) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    public function rank() {
        $k = isset($_GET['k']) ? intval($_GET['k']) : 1;
        $map['tj_sid'] = $this->sid;
        if ($k == 2) {
            $map['credit2'] = array('neq',0);
            $list = M('tj_event')->where($map)->field('tj_uid as uid,credit2 as note')->order('note DESC')->findPage(20);
            $map['tj_uid'] = $this->mid;
            $self = M('tj_event')->where($map)->field('credit2 as note')->find();
            if(!$self || $self['note']==0){
                $pai = '无';
            }else{
                $map['credit2'] = array('egt',$self['note']);
                $pai = M('tj_event')->where($map)->count();
            }
        } elseif ($k == 3) {
            $map['credit3'] = array('neq',0);
            $list = M('tj_event')->where($map)->field('tj_uid as uid,credit3 as note')->order('note DESC')->findPage(20);
            $map['tj_uid'] = $this->mid;
            $self = M('tj_event')->where($map)->field('credit3 as note')->find();
            if(!$self || $self['note']==0){
                $pai = '无';
            }else{
                $map['credit3'] = array('egt',$self['note']);
                $pai = M('tj_event')->where($map)->count();
            }
        } else {
            $k = 1;
            $map['credit1'] = array('neq',0);
            $list = M('tj_event')->where($map)->field('tj_uid as uid,credit1 as note')->order('note DESC')->findPage(20);
            $map['tj_uid'] = $this->mid;
            $self = M('tj_event')->where($map)->field('credit1 as note')->find();
            if(!$self || $self['note']==0){
                $pai = '无';
            }else{
                $map['credit1'] = array('egt',$self['note']);
                $pai = M('tj_event')->where($map)->count();
            }
        }
        $this->assign('self', $pai);
        $this->assign('topMenu', $k);
        $this->assign($list);
        //$map = $this->_getTopMap($time);

        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        $ctitle = '全部';
//        $category = $this->get('category');
//        if ($_GET['cid'] == 's') {
//            unset($_SESSION['school_top_search']);
//            unset($_SESSION['school_top_map']);
//            unset($_GET['cid']);
//        } elseif ($_SESSION['school_top_search'] && (empty($_GET['cid']) || $_SESSION['school_top_search'] == intval($_GET['cid']))) {
//            $eventIds = unserialize($_SESSION['school_top_map']);
//            $map['eventId'] = array('in', $eventIds);
//            $ctitle = $category[$_SESSION['school_top_search']];
//        } elseif (!empty($_GET['cid'])) {
//            $_SESSION['school_top_search'] = intval($_GET['cid']);
//            $ctitle = $category[$_GET['cid']];
//            $eventIds = getSubByKey(M('event')->where('typeId =' . intval($_GET['cid']))->field('id')->findAll(), 'id');
//            $map['eventId'] = array('in', $eventIds);
//            $_SESSION['school_top_map'] = serialize($eventIds);
//        }
        $this->assign('cTitle', $ctitle);

//        $dao = M('event_user');
//        $list = $dao->where($map)->group('uid')->field('uid,sum(credit) as note')->order('note DESC, uid ASC')->findPage(20);
//        $map['uid'] = $this->mid;
//        $self = $dao->where($map)->group('uid')->field('sum(credit) as note')->find();
//        if ($self['note'] <= 0) {
//            $this->assign('self', '无');
//        } else {
//            unset($map['uid']);
//            $pai = $dao->where($map)->group('uid')->field('uid')
//                            ->having('sum(credit) > ' . $self['note'] . ' OR (sum(credit) = ' . $self['note'] . ' AND uid <' . $this->mid . ')')->findAll();
//            $top = count($pai) + 1;
//            $this->assign('self', $top);
//        }
        //var_dump($list);die;

        $this->_rightSide();
        $this->setTitle('排行榜');
        $this->display();
    }

    public function csOrga() {
        $cs = intval($_GET['sid']);
        $audit = false;
        if ($cs != 0) {
            $uidArr = M('event_csorga')->where('orga='.$cs)->field('uid')->findAll();
            if($uidArr){
                $map['uid'] = array('in',getSubByKey($uidArr,'uid'));
                $audit = M('user')->where($map)->field('uid,realname,event_role_info')->findAll();
            }
        }
        if ($audit) {
            echo json_encode($audit);
        } else {
            echo 0;
        }
    }

    //帮助中心
    public function help() {
        $this->display();
    }

//    private function _getTopMap($time) {
//        $map['fTime'] = array('egt', $time);
//        $map['usid'] = $this->sid;
//        $map['credit'] = array('gt', 0);
//        return $map;
//    }

    private function _getMonTime() {
        $mon = intval(date('m'));
        $m = 9;
        if ($mon >= 3 && $mon <= 8) {
            $m = 3;
        }
        return mktime(0, 0, 0, $m, 1, date("Y"));
    }

    public function xls() {
        $file_name = 'events';
        $data = $result['data'];
        header('Content-Type: text/xls');
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');
        header('Content-Disposition: attachment;filename="' . $str . '.xls"');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $table_data = '<table border="1">';
        $table_data .= '<tr>';
        foreach ($data[0] as $key => &$item) {
            $key = mb_convert_encoding($key, 'gbk', 'utf-8');
            $table_data .= '<td>' . $key . '</td>';
        }
        $table_data .= '</tr>';

        foreach ($data as $line) {
            $table_data .= '<tr>';
            foreach ($line as $key => &$item) {
                $item = mb_convert_encoding($item, 'gbk', 'utf-8');
                $table_data .= '<td>' . $item . '</td>';
            }
            $table_data .= '</tr>';
        }
        $table_data .='</table>';
        echo $table_data;
        die();
        $this->display();
    }

    public function newsList() {
        $this->setTitle("新闻公告");
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $news = M('school_news')->where($map)->order('id Desc')->findPage(10);
        $this->assign($news);
        $this->_rightSide();
        $this->display();
    }

    public function news() {
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['isDel'] = 0;
        $dao = D('school_news');
        $app = $dao->where($map)->find();
        if (!$app) {
            $this->error('新闻不存在或已被删除！');
        }
        $app['content'] = htmlspecialchars_decode($app['content']);
        //增加浏览计数
        $dao->setInc('readCount', 'id='.$id);
        $app['readCount']+=1;
        $this->assign('app', $app);
        $this->_rightSide();
        $this->display();
    }

    public function workList() {
        $this->setTitle("作业");
        if (!$this->smid && !$this->rights['allAdmin']) {
            $this->error('无权查看！');
        }
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $list = M('school_work')->where($map)->order('id Desc')->findPage(15);
        if ($list['data']) {
            $wids = getSubByKey($list['data'], 'id');
            $map = array();
            $map['wid'] = array('in', $wids);
            $map['uid'] = $this->mid;
            $back = M('school_workback')->where($map)->field('wid,note,status')->findAll();
            $workBack = orderArray($back, 'wid');
            $this->assign('workBack', $workBack);
        }
        $this->assign($list);
        $this->_rightSide();
        $this->display();
    }

    public function work() {
        $this->setTitle("作业");
        if (!$this->smid && !$this->rights['allAdmin']) {
            $this->error('无权查看！');
        }
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $work = M('school_work')->where($map)->find();
        if (!$work) {
            $this->error('作业不存在或已被删除！');
        }
        $this->assign('work', $work);
        $map = array();
        $map['wid'] = $id;
        $map['uid'] = $this->mid;
        $back = M('school_workback')->where($map)->find();
        $attach = array();
        if($back['attach']){
            $attIds = unserialize($back['attach']);
            $_attach_map['id'] = array('IN', $attIds);
            $attach = D('WorkAttach', 'event')->field('id,name,fileurl')->where($_attach_map)->findAll();
        }
        $this->assign('attach', $attach);
        $this->assign('back', $back);
        $canEdit = true;
        //截止的或已经评分的不可再提交
        if($work['eTime'] <= time() || ($back && $back['status'] == 2)){
            $canEdit = false;
        }
        $this->assign('canEdit', $canEdit);
        $this->_rightSide();
        $this->display();
    }

    public function doWorkBack() {
        if (!$this->smid) {
            $this->error('无权查看！');
        }
        $wid = intval($_REQUEST['wid']);
        $map['id'] = $wid;
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $work = M('school_work')->where($map)->field('eTime,uid')->find();
        if (!$work) {
            $this->error('作业不存在或已被删除！');
        }
        if ($work['eTime'] <= time()) {
            $this->error('作业已经截止提交！');
        }
        $daoBack = M('school_workback');
        $backMap['wid'] = $wid;
        $backMap['uid'] = $this->mid;
        $backMap['status'] = array('neq', 2);
        $back = $daoBack->where($backMap)->field('id')->find();

        $data['attach'] = $this->_setWorkAttach(); // 附件信息
        $data['content'] = t(h($_POST['content']));
        if ($data['attach'] == 'N;' && !$data['content']) {
            $this->error('无内容，无附件！');
        }
        //添加
        if(!$back){
            $data['wid'] = $wid;
            $data['uid'] = $this->uid;
            $data['autor'] = $work['uid'];
            $data['sid'] = $this->sid;
            $data['status'] = 1;
            $data['cTime'] = time();
            $res = $daoBack->add($data);
        //修改
        }else{
            $data['cTime'] = time();
            $res = $daoBack->where('id='.$back['id'])->save($data);
        }
        if ($res) {
            $this->assign('jumpUrl', U('event/School/work', array('id' => $wid)));
            $this->success('提交作业成功');
        } else {
            $this->error('提交失败');
        }
    }

    public function surveyList(){
        $this->setTitle("问卷调查");
        $map['status'] = 1;
        $map['sid'] = $this->sid;
        $res = M('survey')->where($map)->order('id DESC')->findPage(15);
        $this->assign($res);
        $this->_rightSide();
        $this->display();
    }

    public function survey(){
        $this->setTitle("问卷调查");
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['status'] = 1;
        $map['sid'] = $this->sid;
        $res = M('survey')->where($map)->find();
        if (!$res) {
            $this->error('问卷不存在或已被删除！');
        }
        $this->assign('survey',$res);
        //是否已参与
        $voted = false;
        if($this->mid){
            $voteMap['uid'] = $this->mid;
            $voteMap['survey_id'] = $id;
            $voted = M('survey_user')->where($voteMap)->field('id')->find();
        }
        $this->assign("voted", $voted);
        //进行中
        if(!$voted && $res['deadline'] > time()){
            //投票选项
            $mapVote['suid'] = $id;
            $mapVote['isDel'] = 0;
            $vote = D("SurveyVote",'event')->where($mapVote)->order("display_order asc")->findAll();
            if($vote){
                $dao = M('survey_opt');
                foreach ($vote as $k=>$v) {
                    $vote[$k]['opt'] = $dao->where('vote_id='.$v['id'])->field('id,name')->findAll();
                }
            }
            $this->assign("vote", $vote);
        }
        $this->_rightSide();
        $this->display();
    }

    public function doSurvey(){
        $this->checkHash();
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['status'] = 1;
        $map['sid'] = $this->sid;
        $survey = M('survey')->where($map)->field('deadline')->find();
        if (!$survey) {
            $this->error('问卷不存在或已被删除！');
        }
        if($survey['deadline'] <= time()){
            $this->error('该调查已结束！');
        }
        $mapVote['suid'] = $id;
        $mapVote['isDel'] = 0;
        $vote = D("SurveyVote",'event')->where($mapVote)->field('id,type')->findAll();
        $userOpt = array();
        foreach ($vote as $v) {
            if(!isset($_POST['opt_'.$v['id']][0])){
                $this->error('请确保每个问题至少选择一项');
            }

            $input = $_POST['opt_'.$v['id']];
            //单选
            if($v['type']==0){
                $userOpt[] = intval($input[0]);
            }else{
                foreach ($input as $value) {
                    $userOpt[] = intval($value);
                }
            }
        }
        //写入数据库
        $data['survey_id'] = $id;
        $data['cTime'] = time();
        if($this->mid){
            $data['uid'] = $this->mid;
        }
        $res = D('Survey')->addUserVote($data, $userOpt);
        if($res){
            $this->assign('jumpUrl', U('event/School/surveyList'));
            $this->success('感谢您的参与');
        }
        $this->error('提交失败，请稍后再试！');
    }

    // 处理表单附件信息
    private function _setWorkAttach($old_attach = '') {
        // 添加附件
        $dao = D('WorkAttach', 'event');
        if ($_POST['attach']) {
            if (count($_POST['attach']) > 3) {
                $this->error('附件数量不能超过3个');
            }
            array_map('intval', $_POST['attach']);
            $map['id'] = array('in', $_POST['attach']);
            $dao->setField('is_del', 0, $map);
        }
        // 处理删除的附件的
        if($old_attach){
            $old_attach = unserialize($old_attach);
            if (is_array($_POST['attach'])) {
                $del_attach = array_diff($old_attach, $_POST['attach']);
            } else {
                $del_attach = $old_attach;
            }
            $dao->remove($del_attach);
        }
        return serialize($_POST['attach']);
    }

    //上传文件
    public function workBackUpload() {
        if(!$this->smid){
            exit(json_encode(array('status' => 0, 'info' => '没有权限上传')));
        }
        //权限判读 用户没有加入该部落
        $wid = intval($_REQUEST['wid']);
        $map['id'] = $wid;
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $work = M('school_work')->where($map)->field('eTime')->find();
        if (!$work) {
            exit(json_encode(array('status' => 0, 'info' => '作业不存在或已被删除')));
        }
        if ($work['eTime'] <= time()) {
            exit(json_encode(array('status' => 0, 'info' => '作业已经截止提交')));
        }
        if ($_FILES['uploadfile']['size'] <= 0) {
            exit(json_encode(array('status' => 0, 'info' => '请选择上传文件')));
        }

        //上传参数
        $upload['max_size'] = 2 * 1024 * 1024;
        //$upload['allow_exts'] = str_replace('|',',',$this->config['uploadFileType']);
        $upload['allow_exts'] = '';
        //$upload['save_path']  = UPLOAD_PATH.'/groupFile/';
        $info = X('Xattach')->upload('school_work', $upload);

        //执行上传操作
        if ($info['status']) {  //上传成功
            list($uploadFileInfo) = $info['info'];

            $attchement['wid'] = $wid;
            $attchement['uid'] = $this->mid;
            $attchement['attachId'] = $uploadFileInfo['id'];
            $attchement['name'] = $uploadFileInfo['name'];
            $attchement['filesize'] = $uploadFileInfo['size'];
            $attchement['filetype'] = $uploadFileInfo['extension'];
            $attchement['fileurl'] = $uploadFileInfo['savepath'] . $uploadFileInfo['savename'];
            $attchement['ctime'] = time();
            if ($_GET['ajax'] == 1) {
                $attchement['is_del'] = 1; // 异步上传的文件默认为删除状态，等异步信息保存时候再设定为非删除
            }
            $result = D('WorkAttach', 'event')->add($attchement);

            if ($result) {
                $info['info']['0']['id'] = $result;
                exit(json_encode($info));
            } else {
                exit(json_encode(array('status' => 0, 'info' => '保存文件失败')));
            }
        } else {
            exit(json_encode($info));
        }
    }

    public function toWap() {
        $_SESSION['wap_to_normal'] = '0';
        cookie('wap_to_normal', '0', 3600 * 24 * 365);
        U('wap/School/index', '', true);
    }

    public function toW3g() {
        $_SESSION['wap_to_normal'] = '0';
        cookie('wap_to_normal', '0', 3600 * 24 * 365);
        U('w3g/School/index', '', true);
    }

    //前台用户学分积分明细
    public function credit(){
        $map{'uid'} = $this->mid;
        $map{'status'} = array('gt',0);
        $list = M('event_user')->where($map)->field('id,eventId,credit,score,addCredit,addScore,cTime,status')->order('id DESC')->findPage(15);
        foreach($list['data'] as $k=>$v){
            $list['data'][$k]['title'] = D('Event')->getField('title','id='.$v['eventId']);
        }
        $this->assign($list);
        $this->setTitle('活动学分积分明细');
        $this->display();
    }

}
