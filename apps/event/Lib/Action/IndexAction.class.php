<?php

/**
 * IndexAction
 * 活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class IndexAction extends Action {

    private $appName;
    private $event;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        //应用名称
        global $ts;
        $this->appName = $ts['app']['app_alias'];
        //设置活动的数据处理层
        $this->event = D('Event');
        // 活动分类
        $cate = D('EventType')->getType();
        $this->assign('category', $cate);
        // 院校
        $schools = model('Schools')->__getCategory();
        $this->assign('schools', $schools);
    }

    public function sj() {
        $this->assign('id1', C('SJ_GROUP'));
        $this->assign('id2', C('SJ_PERSON'));
        $this->assign('id3', C('SJ_FS'));
        $showBm = true;
        if(isset($this->user['schoolEvent']['domain'])){
            if(!isSjSchool($this->user['sid'])){
                $showBm = false;
            }
            $sqUrl = getDomainLink($this->user['schoolEvent']['domain']).'/index.php?app=event&mod=Sjsq&act=index';
        }else{
            $sqUrl = U('home/Public/Login');
        }
        $this->assign('sqUrl', $sqUrl);
        $this->assign('showBm', $showBm);
        $this->display();
    }

    private function _rightSide() {
        //读取最新新闻
        $map['show_in_xyh'] = 1;
        $map['isDel'] = 0;
        $eids = getSubByKey(D('Event')->field('id')->where($map)->findAll(), 'id');
        $map = array();
        $map['eventId'] = array('in', $eids);
        $map['isDel'] = 0;
        $news = D('EventNews')->where($map)->order('id Desc')->limit(8)->select();
        $this->assign('rightNews', $news);
        //读取推荐活动
        $map = array();
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['show_in_xyh'] = 1;
        $hotList = $this->event->getHotList($map);
        $this->assign('hotList', $hotList);
    }

    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
        if (!isset($_GET['school'])) {
            if (!isset($_SESSION['event_sid'])) {
                $user = $this->get('user');
                $_SESSION['event_sid'] = $user['sid'];
            }
        } else {
            $_SESSION['event_sid'] = intval($_GET['school']);
        }

        $schools2 = $this->get('schools');
        $schools[0] = array('title' => '全部学校', 'pid' => 0);
        foreach ($schools2 as $key => $value) {
            $schools[$key] = $value;
        }
        //array_unshift($schools, array('title'=>'全部学校','pid'=>0));
        $this->assign('school', $schools[$_SESSION['event_sid']]['title']);
        $this->assign('topSchools', $schools);

        $this->setTitle('活动首页');
        $this->assign('cTitle', '全部');

        if ($_SESSION['event_sid'] > 0) {
            //$child = model('Schools')->_makeTree($_SESSION['event_sid']);
            //$sids[] = $_SESSION['event_sid'];
            //foreach ($child as $value) {
            //    $sids[] = $value['a'];
            //}
            //$map['sid'] = array('in', $sids);
            $eventIds = D('EventSchool')->getEventIds($_SESSION['event_sid']);
            $map['id'] = array('in', $eventIds);
        }
        $map['isDel'] = 0;
        $map['status'] = 1;
        //查询
        $title = t($_POST['title']);
        if ($_POST['title']) {
            $searchTitle = t($_POST['title']);
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_GET['cid']) {
            $cid = intval($_GET['cid']);
            $map['typeId'] = $cid;
            $category = $this->get('category');
            $this->assign('cTitle', $category[$cid]);
            $this->setTitle('分类浏览');
        }

        switch ($_GET['action']) {
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
            case 'launch':    //发起的
                $map['status'] = array('in', '0,1');
                $map['uid'] = $this->uid;
                $this->setTitle("我发起的活动");
                break;
            case 'collect'://收藏的
                $uid = $this->uid;

                $result = M('event_collection')->field('eid')->where("uid=$uid")->select();
                $eids = getSubByKey($result, eid);
                $map['id'] = array('in', $eids);
                $this->setTitle("我收藏的活动");


            default:      //活动首页
        }
        $map['show_in_xyh'] = 1;
        $this->assign('action', isset($_GET['action']) ? $_GET['action'] : 'index');
        $result = $this->event->getEventList($map, $this->mid);
        $this->assign($result);
        //是否可以管理活动
        $user = D('User', 'home')->getUserByIdentifier($this->mid);
        if (service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)) {
            $this->assign('can_admin_event', 1);
        }
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
//        $this->_createLimit($this->mid);
//        $user = $this->get('user');
//        $school = model('Schools')->getEventSelect($user['sid']);
//        $this->assign('addSchool', $school);
//        $typeDao = D('EventType');
//        $this->assign('type', $typeDao->getType2());
//        $this->setTitle('发起' . $this->appName);
//        $this->_rightSide();
//        $this->display();
        $this->error('发布活动暂时关闭！');
    }

    /**
     * _creatLimit
     * 条件限制判断
     * @access public
     * @return void
     */
    private function _createLimit($uid) {
        $config = getConfig();

        if (!$config['canCreate']) {
            $this->error('禁止发起' . $this->appName);
        }
        if ($config['credit']) {
            $userCredit = X('Credit')->getUserCredit($uid);
            if ($userCredit[$config['credit_type']]['credit'] < $config['credit']) {
                $this->error($userCredit[$config['credit_type']]['alias'] . '小于' . $config['credit'] . '，不允许发起' . $this->appName);
            }
        }
        if ($timeLimit = $config['limittime']) {
            $regTime = M('user')->getField('ctime', "uid={$uid}");
            $difference = (time() - $regTime) / 3600;

            if ($difference < $timeLimit) {
                $this->error('账户创建时间小于' . $timeLimit . '小时，不允许发起' . $this->appName);
            }
        }
    }

    /**
     * doAddEvent
     * 添加活动
     * @access public
     * @return void
     */
    public function doAddEvent() {
//        $this->_createLimit($this->mid);
//        $textarea = t($_POST['description']);
//        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
//            $this->error("活动简介1到200字!");
//        }
//        $title = t($_POST['title']);
//        if (mb_strlen($title, 'UTF8') > 20) {
//            $this->error('活动名称最大20个字！');
//        }
//
//        $map['deadline'] = _paramDate($_POST['deadline']);
//        $map['sTime'] = _paramDate($_POST['sTime']);
//        $map['eTime'] = _paramDate($_POST['eTime']);
//
//        if ($map['sTime'] > $map['eTime']) {
//            $this->error("结束时间不得早于开始时间");
//        }
////        if ($map['sTime'] < mktime(0, 0, 0, date('M'), date('D'), date('Y'))) {
////            $this->error("开始时间不得早于当前时间");
////        }
////        if ($map['deadline'] < time()) {
////            $this->error("报名截止时间不得早于当前时间");
////        }
//        if ($map['deadline'] > $map['eTime']) {
//            $this->error('报名截止时间不能晚于结束时间');
//        }
//        $sids = substr($_POST['showSids'], 2, strlen($_POST['showSids']) - 3);
//        if (!$sids) {
//            $this->error('请选择活动显示于哪些学校');
//        }
//        $defaultBanner = intval($_POST['banner']);
//        if (empty($_FILES['logo']['name']) && !$defaultBanner) {
//            $this->error('请上传活动logo');
//        }
//        //得到上传的图片
//        $config = getPhotoConfig();
//        $options['userId'] = $this->mid;
//        $options['max_size'] = $config['photo_max_size'];
//        $options['allow_exts'] = $config['photo_file_ext'];
//        $images = X('Xattach')->upload('event', $options);
//        if(!$images['status'] && $images['info']!='没有选择上传文件'){
//            $this->error($images['info']);
//        }
//        $map['default_banner'] = $defaultBanner;
//        $map['uid'] = $this->mid;
//        $map['title'] = $title;
//        $map['address'] = t($_POST['address']);
//        $map['limitCount'] = intval(t($_POST['limitCount']));
//        $map['typeId'] = intval($_POST['typeId']);
//        $map['contact'] = t($_POST['contact']);
//        $map['cost'] = intval($_POST['cost']);
//        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
//        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
//        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
//        $map['sid'] = intval($_POST['sid']);
//        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
//        $map['description'] = $textarea;
//
//
//        //发布审核
//        $map['status'] = 0;
//        $configM = getConfig();
//        if ($configM['createAudit'] != 1) {
//            $map['status'] = 1;
//        }
//
//        if ($addId = $this->event->doAddEvent($map, $images)) {
//            X('Credit')->setUserCredit($this->mid, 'add_event');
//            //显示于学校
//            D('EventSchool')->addBySids($addId, $sids);
//            $this->assign('jumpUrl', U('/Index/index', array('id' => $addId, 'uid' => $this->mid)));
//            if (1 == $configM['createAudit']) {
//                $this->success($this->appName . '创建成功，请等待审核');
//            } else {
//                $this->success($this->appName . '创建成功');
//            }
//        } else {
//            $this->error($this->appName . '添加失败');
//        }
        $this->error('发布活动暂时关闭！');
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

    public function playTsFlash() {
        $id = intval($_REQUEST['id']);
        $app = M('flash')->where("`id`={$id}")->find();
        if (!$app) {
            $this->error('视频不存在或已被删除！');
        }
        $app['url'] = get_flash_url($app['host'], $app['flashvar']);
        $this->assign($app);
        $this->display('playFlash');
    }

    public function ajaxNote() {
        $note = intval($_POST['note']);
        if ($note < 0 || $note > 6) {
            $this->error('操作失败');
        }
        if ($data = $this->event->doAddNote(intval($_POST['id']), $this->mid, $note)) {
            $this->ajaxReturn($data, $info = '操作成功', $status = 1, $type = 'JSON');
        }
        $this->error('操作失败');
    }

    public function school() {
        $tree = M('school')->where('pid=0')->field("id,title as name,pid as pId")->findAll();
        if ($tree) {
            array_unshift($tree, array('id' => 0, 'name' => '选择全部', 'pid' => -1, 'open' => true));
        }
        $str = substr($_REQUEST['selected'], 0, strlen($_REQUEST['selected']) - 1);
        if ($tree && $str) {
            $selected = explode(',', $str);
        }
        if ($selected) {
            foreach ($tree as $k => $vo) {
                if (in_array($vo['id'], $selected)) {
                    $tree[$k]['checked'] = true;
                }
            }
        }
        $this->assign('tree', json_encode($tree));
        $this->display();
    }

    //jun  收藏活动
    public function editCollect() {
        if (D('EventCollection')->fav($this->mid, t($_REQUEST['id']), t($_REQUEST['type']))){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

////////////////////////////////////////////////////////////////
}
