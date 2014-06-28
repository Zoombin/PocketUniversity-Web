<?php

/**
 * EventAction
 * 活动管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */

class EventAction extends TeacherAction {

    /**
     * event
     * EventModel的实例化对象
     * @var mixed
     * @access private
     */
    private $event;
    private $eventAdmin;

    /**
     * config
     * EventConfig的实例化对象
     * @var mixed
     * @access private
     */
    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        $this->event = D('Event');
        if($this->rights['can_event'] != 1 && $this->rights['can_event2'] != 1){
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限管理活动！');
        }
        //待审核数
        if($this->rights['can_admin'] || $this->rights['can_event']){
            $map['is_school_event'] = $this->sid;
            $map['isDel'] = 0;
            $map['status'] = 0;
            $map['school_audit'] = 0;
            if(!$this->rights['can_admin']){
                $map['audit_uid'] = $this->mid;
            }
            $auditCount1 = $this->event->where($map)->count();
            $this->assign('auditCount1', $auditCount1);
            $this->assign('showAudit1', 1);
        }
        $map = array();
        $this->eventAdmin = false;
        //超管和终审 终审个数
        if($this->rights['can_admin'] || $this->rights['can_event2']){
            $this->eventAdmin = true;
            $map['is_school_event'] = $this->sid;
            $map['isDel'] = 0;
            $map['status'] = 0;
            $map['school_audit'] = 1;
            if(!$this->rights['can_admin'] && $this->user['event_level']!=10){
                $this->eventAdmin = false;
                $map['sid'] = $this->user['sid1'];
            }
            $auditCount2 = $this->event->where($map)->count();
            $this->assign('auditCount2', $auditCount2);
            $this->assign('showAudit2', 1);
        }
        $this->assign('eventAdmin', $this->eventAdmin);
        //end待审核数
        $map = array();
        $map['is_school_event'] = $this->sid;
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['school_audit'] = 3;
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $finishCount = $this->event->where($map)->count();
        $this->assign('finishCount', $finishCount);
    }

    /**
     * basic
     * 基础设置管理
     * @access public
     * @return void
     */
    public function index() {
        $this->display();
    }

    /**
     * doChangeBase
     * 修改全局设置
     * @access public
     * @return void
     */
    public function doChangeBase() {
        //变量过滤 todo:更细致的过滤
        foreach ($_POST as $k => $v) {
            $config[$k] = t($v);
        }
        //$config['limitsuffix'] = preg_replace("/bmp\|||\|bmp/",'',$config['photo_file_ext']);//过滤bmp
        if (model('Xdata')->lput('event', $config)) {
            $this->assign('jumpUrl', U('event/Event/index'));
            $this->success('设置成功！');
        } else {
            $this->error('设置失败！');
        }
    }

    /**
     * eventlist
     * 获得所有人的eventlist
     * @access public
     * @return void
     */
    public function eventlist() {
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $this->_getEventList($map);
        $this->display();
    }

    /**
     * 活动详情
     */
    public function event(){
        $this->_getEvent();
        $this->display();
    }

    /**
     * audit
     * 待审核的活动
     * @access public
     * @return void
     */
//    public function audit() {
//        $map['status'] = 0;
//        //超管显示所有
//        if($this->rights['can_admin']){
//            $map['school_audit'] = array('in','0,1');
//        }elseif($this->rights['can_event']==1&&$this->rights['can_event2']==1){
//            $map['_string'] = ' (audit_uid = '.$this->mid.' AND school_audit = 0)  OR ( school_audit = 1) ';
//        }elseif($this->rights['can_event']==1){
//            $map['audit_uid'] = $this->mid;
//            $map['school_audit'] = 0;
//        }else{
//            $map['school_audit'] = 1;
//        }
//        $this->_getEventList($map);
//        $this->display();
//    }
    //初审
    public function audit1() {
        $map['status'] = 0;
        $map['school_audit'] = 0;
        //超管显示所有
        if(!$this->rights['can_admin']){
            $map['audit_uid'] = $this->mid;
        }
        $this->_getEventList($map);
        $this->display();
    }
    //终审
    public function audit2() {
        $map['status'] = 0;
        $map['school_audit'] = 1;
        //超管显示所有
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']==1){
            $this->error('您没有终审权力');
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $this->_getEventList($map);
        $this->display();
    }
    public function finish() {
        $map['status'] = 1;
        $map['school_audit'] = 3;
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $this->_getEventList($map,'fTime DESC');
        $this->display();
    }

    public function doAudit() {
        $res = D('Event')->doSchoolAudit(intval($_REQUEST ['gid']),$this->mid,$this->sid,
                intval($_REQUEST ['credit']),intval($_REQUEST ['score']),intval($_REQUEST ['codelimit']),$this->rights['can_admin']); // 通过审核
        if ($res) {
            echo 2;
        } else {
            echo 0;
        }
    }
    public function doAuditScore() {
        $id = intval($_REQUEST['id']);
        $event = M('event')->field('id,title,credit,score,sTime,eTime,codelimit')->where('id='.$id)->find();
        $this->assign($event);
        $this->display();
    }
    public function doFinish() {
        $res = D('Event')->doFinish($_POST ['gid'],$_POST ['code'],$_POST ['give']); // 通过审核
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

   public function doAuditReason() {
        $id=intval($_GET['id']);
        $this->assign('id',$id);
        $del= $_GET['del']?1:0;
        $this->assign('del',$del);
        $this->display();
    }
    //jun  驳回活动
    public function doDismissed() {
        $reason = t($_POST['reject']);
        $id = intval($_POST ['id']);
        if (empty($reason)) {
            $this->ajaxReturn(0, "请填写驳回原因", 1);
        }
        $res = D('Event')->doDismissed($id, $reason,intval($_POST ['del']));

        if ($res) {
            $this->ajaxReturn(0, "驳回成功！", 2);
        } else {
            $this->ajaxReturn(0, "驳回失败！", 0);
        }
    }

   public function doFinishAudit() {

        $id=intval($_GET['id']);
        $this->assign('id',$id);
        $this->display();
    }


    //jun  完结驳回活动
   public function doFinishBack() {
       $reason=t($_POST['reject']);
       $id=intval($_POST ['id']);
      if (empty($reason)){
           $this->ajaxReturn(0,"请填写驳回原因",1);
            }
        $res = D('Event')->doFinishBack($id,$reason);
        if($res){
             $this->ajaxReturn(0,"驳回成功！",2);
        }else{
             $this->ajaxReturn(0,"驳回失败！",0);
        }
    }

    private function _getEventList($map=array(),$orig_order='cTime DESC'){
        $map['is_school_event'] = $this->sid;
        //get搜索参数转post
        if (!empty($_GET['typeId'])) {
            $_POST['typeId'] = $_GET['typeId'];
        }
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchEvent'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchEvent']);
        } else {
            unset($_SESSION['es_searchEvent']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $map['isDel'] = 0;
        if(intval($_POST[sid1])){
            $res=M('user')->where('sid1 ='.$_POST['sid1'])->field('uid')->findAll();
            $uids= getSubByKey($res,'uid');
            $map['uid'] =array('in',$uids);
        }
        $_POST['typeId'] && $map['typeId'] = intval($_POST['typeId']);
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        if (isset($_POST['status'])&&$_POST['status']!=='') {
            $map['school_audit'] = intval($_POST['status']);
        }
        if (isset($_POST['isTop']) && $_POST['isTop'] != '')
            $map['isTop'] = intval($_POST['isTop']);
        if (isset($_POST['isHot']) && $_POST['isHot'] != '')
            $map['isHot'] = intval($_POST['isHot']);

        //处理时间
//            $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t( $_POST['sTime'] ),t( $_POST['eTime'] ) );
        $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t(date("Ymd", strtotime($_POST['sTime']))), t(date("Ymd", strtotime($_POST['eTime']))));
        //处理排序过程
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder']) : $orig_order;
        $_POST['limit'] && $limit = intval(t($_POST['limit']));

        $order && $list = $this->event->getList($map, $order, $limit);
        $type_list = D('EventType')->getType();
        $this->assign($_POST);
        $this->assign($list);
        $this->assign('editSid',$this->sid);
        $this->assign('type_list', $type_list);
    }


    /**
     * transferEventTab
     * 转移活动
     * @access public
     * @return void
     */
    public function transferEventTab() {
        $type_list = D('EventType')->getType();
        $this->assign('type_list', $type_list);
        $this->assign('id', $_GET['id']);
        $this->display();
    }

    /**
     * doDeleteEvent
     * 执行转移活动
     * @access public
     * @return void
     */
    public function doTransferEvent() {
        $id['id'] = array('in', t($_POST['id']));
        $data['typeId'] = intval($_POST['type']);
        if (!$_POST['id'] || !$data['typeId']) {
            echo -2;
            exit;
        }
        if ($this->event->where($id)->save($data)) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只操作一个
            } else {
                echo 1;            //操作多个
            }
        } else {
            echo -1;               //操作失败
        }
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @access public
     * @return int
     */
    public function doDeleteEvent() {
        $eventid['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $result = $this->event->doDeleteEvent($eventid);
        if (false != $result) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo -1;               //删除失败
        }
    }

    //推荐操作
    public function doChangeIsHot() {
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $this->error('对不起,你无权操作');
        }
        $act = $_REQUEST['type'];  //推荐动作
        //只可幻灯5个
        if($act=='recommend'){
            $top = $this->event->where('isHot=1 and isDel=0 and is_school_event='.$this->sid)->count();
            if($top>=5){
                $this->error('最多幻灯5个活动，请【搜索】后【取消】其它幻灯再试');
            }
        }
        $event['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $result = $this->event->doIsHot($event, $act);
         if (false != $result) {
            $this->success('操作成功');        //成功
        } else {
            $this->error('设置失败');       //失败
        }
    }
    //置顶操作
    public function doChangeIsTop() {
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $this->error('对不起,你无权操作');
        }
        $act = $_REQUEST['type'];  //动作
        //只可置顶5个
        if($act=='top'){
            $top = $this->event->where('isTop=1 and isDel=0 and is_school_event='.$this->sid)->count();
            if($top>=5){
                $this->error('最多推荐5个活动，请【搜索】后【取消】其它推荐再试');
            }
        }
        $event['id'] = intval($_REQUEST['id']);        //要推荐的id.
        $result = $this->event->doIsTop($event, $act);
        if (false != $result) {
            $this->success('操作成功');        //成功
        } else {
            $this->error('设置失败');       //失败
        }
    }

    //重新激活操作
    public function doChangeActiv() {
        $id = (int) $_REQUEST['id'];        //要激活的id.
        $result = $this->event->doIsActiv($id);

        if (false != $result) {
            echo 1;            //激活成功
            // 发送通知
            $obj = $this->event->find($id);
            $notify_dao = service('Notify');
            $notify_data = array('title' => $obj['title'], 'event_id' => $obj['id'], 'event_uid' => $obj['uid']);
            $notify_dao->sendIn($obj['uid'], 'event_reactiv', $notify_data);
        } else {
            echo -1;               //激活失败
        }
    }

    /**
     * eventtype
     * 活动类型列表
     * @access public
     * @return void
     */
    public function eventtype() {
        $type = D('EventType');
        $type = $type->order('id ASC')->findAll();
        $this->assign('type_list', $type);

        $count = D('Event')->field('typeId,count(typeId) as count')->group('typeId')->findAll();
        foreach ($count as $k => $v) {
            // unset($count[$k]);
            $count[$v['typeId']] = $v['count'];
        }
        $this->assign('count', $count);

        $this->display();
    }

    /**
     * editEventTab
     * 添加分类
     * @access public
     * @return void
     */
    public function editEventTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $name = D('EventType')->getField('name', "id={$id}");
            $this->assign('id', $id);
            $this->assign('name', $name);
        }
        $this->display();
    }

    /**
     * doAddType
     * 添加分类
     * @access public
     * @return void
     */
    public function doAddType() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['name']));
        $type = D('EventType');
        $name = M('EventType')->where(array('name' => $isnull))->getField('name');
        if (empty($isnull)) {
            echo -2;
        }
        if ($name !== null) {
            echo 0;
        } else {
            if ($result = $type->addType($_POST)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }

    /**
     * doEditType
     * 修改分类
     * @access public
     * @return void
     */
    public function doEditType() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['name'] = t($_POST['name']);
        $_POST['name'] = preg_replace("/[ ]+/si", "", $_POST['name']);
        if (empty($_POST['name'])) {
            echo -2;
        }
        $type = D('EventType');
        $name = M('EventType')->where(array('name' => t($_POST['name'])))->getField('name');
        if ($name !== null) {
            echo 0; //分类名称重复
        } else {
            if ($result = $type->editType($_POST)) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }

    /**
     * doEditType
     * 删除分类
     * @access public
     * @return void
     */
    public function doDeleteType() {
        $id['id'] = array("in", $_POST['id']);
        $type = D('EventType');
        if ($result = $type->deleteType($id)) {
            if (!strpos($_POST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo $result;
        }
    }

    /**
     * 编辑活动
     */
    public function editEvent(){
        $this->_getEvent();
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->sid));
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('addSchool', $school);
        $typeDao = D('EventType');
        $type = $typeDao->getType2();
        $this->assign('type', $type);
        $this->display();
    }

    private function _getEvent(){
        //活动
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id <= 0) {
            $this->assign('jumpUrl', U('/Event/eventlist'));
            $this->error("错误的访问页面，请检查链接");
        }
        $map['id'] = $id;
        $map['is_prov_event'] = 0;
        if ($result = $this->event->where($map)->find()) {
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('活动不存在');
        }
        // 活动分类
        $cate = D('EventType')->getType();
        $this->assign('category', $cate);
    }

    public function doEditEvent() {
        $id = intval($_POST['id']);
        if (!$obj = $this->event->where(array('id'=>$id,'is_prov_event'=>0))->find()) {
            $this->error('活动不存在或已删除');
        }
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            if($obj['audit_uid']!=$this->mid){
                $this->error('您没有权限');
            }
        }elseif($this->user['event_level']!=10){
            if($obj['sid'] != $this->user['sid1']){
                $this->error('您没有权限');
            }
        }
        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 30) {
            $this->error('活动名称最大30个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 250) {
            $this->error("活动简介1到250字!");
        }
        $score = intval($_POST['score']);
//        if ($score <= 0) {
//            $this->error('可得积分不能小于0！');
//        }
        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }

        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('event', $options);
        if(!$cover['status'] && $cover['info']!='没有选择上传文件'){
            $this->error($cover['info']);
        }
        $defaultBanner = intval($_POST['banner']);
        if($defaultBanner){
            $map['logoId'] = 0;
            $map['default_banner'] = intval($_POST['banner']);
        }
        $map['score'] = $score;
        $map['credit'] = intval($_POST['credit']);
        $map['codelimit'] = intval($_POST['codelimit']);
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
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        //$map['show_in_xyh'] = isset($_POST['show_in_xyh']) ? 1 : 0;
        if(intval($_POST['sid'])!=0){
            $map['sid'] = intval($_POST['sid']);
        }
        $map['description'] = $textarea;

        if ($this->event->doEditEvent($map, $cover, $obj)) {
            $this->assign('jumpUrl', U('/Event/editEvent', array('id' => $id, 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    public function newsList() {
        if(!$this->eventAdmin){
            $this->error('您没有权限');
        }
        if (!empty($_POST)) {
            $_SESSION['admin_event_news_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_news_search']);
        } else {
            unset($_SESSION['admin_event_news_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $map['b.audit_uid'] = $this->mid;
        }
        $db_prefix = C('DB_PREFIX');
        $list = D('EventNews')->table("{$db_prefix}event_news AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.* , b.uid')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 删除新闻
     */
    public function deleteNews() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventNews')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function flashList() {
        if(!$this->eventAdmin){
            $this->error('您没有权限');
        }
        if (!empty($_POST)) {
            $_SESSION['admin_event_flash_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_flash_search']);
        } else {
            unset($_SESSION['admin_event_flash_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['b.uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $map['b.audit_uid'] = $this->mid;
        }
        $db_prefix = C('DB_PREFIX');
        $list = D('EventFlash')->table("{$db_prefix}event_flash AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.id,a.eventId,a.path,a.title, a.cTime, b.uid')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }
    /**
     * 删除视频
     */
    public function deleteFlash() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventFlash')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function imgList() {
        if(!$this->eventAdmin){
            $this->error('您没有权限');
        }
        if (!empty($_POST)) {
            $_SESSION['admin_event_img_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_img_search']);
        } else {
            unset($_SESSION['admin_event_img_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['b.uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $map['b.audit_uid'] = $this->mid;
        }
        $db_prefix = C('DB_PREFIX');
        $list = D('EventImg')->table("{$db_prefix}event_img AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.id,a.eventId,a.path,a.title, a.cTime, b.uid')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }
    /**
     * 删除视频
     */
    public function deleteImg() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventImg')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function finishAudit(){
        $id = intval($_GET['id']);
        $event = M('event')->where('id='.$id)->field('id,title,print_img,print_text,joinCount,pay')->find();
        $this->assign($event);
        if($event){
            $map['eventId'] = $id;
            $map['status'] = 2;
            $attCount = M('event_user')->where($map)->count();
            $this->assign('attCount', $attCount);
        }
        $this->display();
    }

    //给全体报名
    public function allJoin(){
        if($_SESSION['ThinkSNSAdmin'] != '1'){
            $this->error('您没有权限');
        }
        $eventId = intval($_REQUEST['id']);
        $map['id'] = $eventId;
        $map['isDel'] = 0;
        $map['is_school_event'] = $this->sid;
        $event = $this->event->where($map)->field('status')->find();
        if(!$event){
            $this->error('活动不存在或已删除');
        }
        if($event['status']==0){
            $this->error('活动尚未通过审核');
        }
        if($event['status']==2){
            $this->error('活动已完结');
        }
        set_time_limit(0);
        $data['eventId'] = $eventId;
        $data['cTime'] = time();
        $data['status'] = 1;
        $map = array();
        $map['sid'] = $this->sid;
        $users = M('user')->where($map)->field('uid,realname,sex,mobile')->findAll();
        $userDao = D('EventUser');
        $succ = 0;
        foreach($users as $user){
            $data['uid'] = $user['uid'];
            $data['realname'] = $user['realname'];
            $data['sex'] = $user['sex'];
            $data['usid'] = $this->sid;
            if ($userDao->add($data)) {
                $succ += 1;
            }
        }
        if($succ>0){
            $this->event->setInc('joinCount','id='.$eventId,$succ);
        }
        $this->success('成功报名'.$succ.'个成员');

    }

    //完结的活动补发积分学分
    public function bufa(){
        $map['id'] = intval($_POST['id']);
        $map['school_audit'] = 5;
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $res = $this->event->bufa($map);
        echo json_encode($res);
        exit;
    }


    //附件下载
   public function download(){
        $fid = intval($_REQUEST['fid']) > 0 ?  intval($_REQUEST['fid']) : 0;
        if($fid == 0) exit();
        if(!isset($_REQUEST['code'])) exit();
        $file_info = getAttach($fid);
        $fileName = t($_REQUEST['code']);
        if($fileName!=$file_info['savename']) exit();
        $file_info = getAttach($fid);
        $file_path = UPLOAD_PATH . '/' .$file_info['savepath'].'/' .$file_info['savename'];
        if (file_exists($file_path)) {
                include_once(SITE_PATH . '/addons/libs/Http.class.php');
                $file_info['name'] = iconv("utf-8", 'gb2312', $file_info['name']);
                Http::download($file_path, $file_info['name']);
        }
        $this->error('文件不存在');
   }

}
