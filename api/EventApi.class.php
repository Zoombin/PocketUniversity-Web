<?php

class EventApi extends Api {

    private $daoEvent;
    private $sjVote;

    public function __construct() {
        parent::__construct();
        $this->daoEvent = D('Event', 'event');
        $this->sjVote = false;
    }

    public function test() {
        var_dump($this->mid);
        return 2;
    }

    private function _getMap() {
        $map['isDel'] = 0;
        $map['status'] = 1;
        //$map['is_school_event'] = array('not in', array('473'));
        //$map['show_in_xyh'] = 1;
        return $map;
    }

    public function eventList() {
        $map = $this->_getMap();
        $sid = intval($_REQUEST['school']);
        if ($sid) {
            $eventIds = D('EventSchool', 'event')->getEventIds($sid);
            //去掉全省已结束的活动
            $finishIds = $this->daoEvent->where('is_prov_event=1 and eTime<' . time())->field('id')->findAll();
            $finishIds = getSubByKey($finishIds, 'id');
            $eventIds = array_diff($eventIds, $finishIds);
            $map['id'] = array('in', $eventIds);
            $map['school_audit'] = array('neq', 5);
        } else {
            $map['show_in_xyh'] = 1;
        }
        $sid = intval($_REQUEST['sid']);
        if ($sid) {
            $map['sid'] = $sid;
        }
        $orgId = intval($_REQUEST['orgId']);
        if ($orgId) {
            $map['sid'] = $orgId;
        }
        $keyword = t($_REQUEST['keyword']);
        if ($keyword) {
            $map['title'] = array('like', "%" . $keyword . "%");
        }
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = $this->daoEvent->handyGetEventList($this->mid, 0, $map, $count, $page);
        if ($list) {
            return $list;
        } else {
            return array();
        }
    }

    public function catList() {
        $list = D('EventType', 'event')->order('id')->field('id,name as title')->findAll();
        return $list;
    }

    public function getOrgList() {
        $sid = intval($_REQUEST['sid']);
        if ($sid) {
            $zhuzhi = D('SchoolOrga', 'event')->getAll($sid, 1);
            $orglist['zhuzhi'] = $zhuzhi ? $zhuzhi : array();
            $bumen = D('SchoolOrga', 'event')->getAll($sid, 2);
            $orglist['bumen'] = $bumen ? $bumen : array();
            $orglist['school'] = M('school')->where("pid='$sid'")->order('display_order ASC')->field('id,pid,title')->findAll();
            return $orglist;
        } else {
            return array();
        }
    }

    public function myEventList() {
        $map = $this->_getMap();
        $action = t($_REQUEST['action']);
        if ($action == 'launch') {
            $map['status'] = array('in', '0,1');
            $map['uid'] = $this->mid;
        } elseif ($action == 'join') {
            $map_join['status'] = 1;
            $map_join['uid'] = $this->mid;
            $eventIds = D('EventUser', 'event')->field('eventId')->where($map_join)->findAll();
            foreach ($eventIds as $v) {
                $in_arr[] = $v['eventId'];
            }
            $map['id'] = array('in', $in_arr);
        } elseif ($action == 'fav') {
            $map['status'] = 1;
            $result = M('event_collection')->field('eid')->where("uid=$this->mid")->select();
            $eids = getSubByKey($result, eid);
            $map['id'] = array('in', $eids);
        } else {
            return array();
        }
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = $this->daoEvent->handyGetEventList($this->mid, 0, $map, $count, $page);
        if ($list) {
            return $list;
        } else {
            return array();
        }
    }

    public function event() {
        $id = intval($_REQUEST['id']);
        $map = $this->_getMap();
        $map['id'] = $id;
       $school_audit= $this->daoEvent->getField('school_audit','id='.$id);
       if($school_audit<2){
            $res['status'] = 0;
            $res['msg'] = '该活动未通过审核';
           return $res;
       }
        $list = $this->daoEvent->getEvent($this->mid, $map);
        return $list;
    }

    public function newsList() {
        $id = intval($_REQUEST['id']);
        $map['eventId'] = $id;
        $map['isDel'] = 0;
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = D('EventNews', 'event')->getHandyList($map, $count, $page);
        return $list;
    }

    public function news() {
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['isDel'] = 0;
        $list = D('EventNews', 'event')->getNews($map);
        return $list;
    }

    public function playerList() {
        $id = intval($_REQUEST['id']);
        if ($id == C('SJ_GROUP')) {
            return $this->playerList1(3);
        }
        if ($id == C('SJ_PERSON')) {
            return $this->playerList1(5);
        }
        if ($id == C('SJ_FS')) {
            return $this->playerList1(9);
        }
        $keyword = t($_REQUEST['key']);
        $map['eventId'] = $id;
        if (strlen($keyword) > 0) {
            $map['realname'] = array('like', "%{$keyword}%");
        }
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = D('EventPlayer', 'event')->getHandyList($this->mid, $map, $count, $page);
        return $list;
    }

    //十佳
    public function playerList1($type) {
        $map['type'] = $type;
        $map['status'] = 5;

        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $keyword = t($_REQUEST['key']);
        if (strlen($keyword) > 0) {
            $map['title'] = array('like', "%{$keyword}%");
        }
        $offset = ($page - 1) * $count;
        $dao = M('sj');
        $list = $dao->where($map)->field('id,sid,title as realname,ticket')->order('ticket DESC, id DESC')->limit("$offset,$count")->select();
        $sjIds = getSubByKey($list, 'id');
        $map = array();
        $map['sjid'] = array('in', $sjIds);
        $map['status'] = 1;
        $imgArr = M('sj_img')->where($map)->field('sjid,attachId')->findAll();
        $imgs = orderArray($imgArr, 'sjid');
        foreach ($list as $key => $value) {
            $row = $value;
            $row['path'] = getImgAttach($imgs[$value['id']]['attachId'], 163, 204, 'c');
            //用户学校
            $row['school'] = tsGetSchoolName($value['sid']);
            unset($row['sid']);
            $row['sex'] = '1';
            //是否可己投票
            $restCount = $this->_canVote1(intval($_REQUEST['id']), $value['id']);
            $row['canVote'] = false;
            if ($restCount) {
                $row['canVote'] = true;
            }
            $list[$key] = $row;
        }
        return $list;
    }

    public function photoList() {
        $id = intval($_REQUEST['id']);
        $map['eventId'] = $id;
        $map['uid'] = 0;
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = D('EventImg', 'event')->getHandyHxList($map, $count, $page);
        return $list;
    }

    public function vote() {
        $pid = intval($_REQUEST['pid']);
//        if(isset($_REQUEST['eid'])){
//            $eid = intval($_REQUEST['eid']);
//            if($eid == C('SJ_GROUP') || $eid == C('SJ_PERSON') || $eid == C('SJ_FS')){
//                return $this->vote1($eid, $pid);
//            }
//        }
//        if($pid<3000){
//            $sj = M('sj')->where('id='.$pid)->field('status,type')->find();
//            if($sj && $sj['status'] == 5){
//                if($sj['type'] == 3){
//                    return $this->vote1(C('SJ_GROUP'), $pid);
//                }
//                if($sj['type'] == 5){
//                    return $this->vote1(C('SJ_PERSON'), $pid);
//                }
//                if($sj['type'] == 9){
//                    return $this->vote1(C('SJ_FS'), $pid);
//                }
//            }
//        }
        return D('EventPlayer', 'event')->vote($this->mid, $pid);
    }

    private function vote1($eid, $pid) {
        $restCount = $this->_canVote1($eid, $pid);
        if (!$restCount) {
            return false;
        }
        $map['mid'] = $this->mid;
        $map['eventId'] = $eid;
        $map['pid'] = $pid;
        $map['cTime'] = time();
        $dao = M('sj_vote');
        $vid = $dao->add($map);
        if ($vid) {
            if ($restCount == 1) {
                $this->_incSjTicket($eid);
                //$this->success('投票成功！您已投满了10票!');
            }
            return true;
        }
        return false;
    }

    //投满10票生效
    private function _incSjTicket($eid) {
        $sjIdArr = M('sj_vote')->where('eventId=' . $eid . ' and mid=' . $this->mid)->field('pid')->findAll();
        $sjIds = getSubByKey($sjIdArr, 'pid');
        $map['id'] = array('in', $sjIds);
        return M('sj')->where($map)->setInc('ticket');
    }

    //是否可以投票
    private function _canVote1($eid, $pid) {
        if (!$this->sjVote) {
            return false;
            //$this->error('投票失败，投票尚未开始或已关闭');
        }
        //是否已验证
        $userValid = M('user')->getField('is_valid', 'uid=' . $this->mid);
        if (!$userValid) {
            return false;
        }
        $map['mid'] = $this->mid;
        $map['eventId'] = $eid;
        $dao = M('sj_vote');
        $count = $dao->where($map)->count();
        if ($count >= 10) {
            return false;
            //$this->error('您已投满了10票! 每人每评选最多投10票');
        }
        $map['pid'] = $pid;
        $ticketed = $dao->where($map)->count();
        if ($ticketed) {
            return false;
            //$this->error('您已给他投过票了!');
        }
        return 10 - $count;
    }

    public function note() {
        $id = intval($_REQUEST['id']);
        $note = intval($_REQUEST['note']);
        if ($this->daoEvent->doAddNote($id, $this->mid, $note)) {
            return true;
        }
        return false;
    }

    public function join() {
        $data['id'] = intval($_REQUEST['id']);
        if ($data['id'] == C('SJ_GROUP') || $data['id'] == C('SJ_PERSON') || $data['id'] == C('SJ_FS')) {
            return false;
        }
        $data['uid'] = $this->mid;
        $user = M('user')->field('realname,sex,sid,mobile')->where('uid=' . $this->mid)->find();
        //$data['sex'] = intval($_REQUEST['sex']);
        if (!$user) {
            return false;
        }
        $data['realname'] = $user['realname'];
        $data['sex'] = $user['sex'];
        $need_tel = $this->daoEvent->getField('need_tel', 'id=' . $data['id']);
        if ($need_tel) {
            if (!$user['mobile']) {
                return false;
            }
            $data['tel'] = $user['mobile'];
        }
        $data['usid'] = $user['sid'];

        $result = $this->daoEvent->doAddUser($data);
        if ($result['status'] == 0) {
            return false;
        }
        return true;
    }

    public function recommEventList() {
        //$count = intval($_REQUEST['count'])?5:intval($_REQUEST['count']);
        $count = 5;
        $map = $this->_getMap();
        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $map['is_school_event'] = $user_sid;
//        $eventIds = D('EventSchool', 'event')->getEventIds($user_sid);
//        if(!$eventIds){
//            return array();
//        }
//        $map['id'] = array('in', $eventIds);
        //$map['show_in_xyh'] = 1;
        //$map['isHot'] = 1;
        //$map['logoId'] = array('neq', 0);
        $list = $this->daoEvent->field('id,title,logoId,typeId,default_banner')->where($map)->order('isTop DESC, id DESC')->limit($count)->findAll();
        foreach ($list as $key => $value) {
            $row = $value;
            $row['banner'] = tsGetLogo($row['logoId'], 'm' . $row['typeId'], $row['default_banner'], 480, 270, 'f');
//            $row['banner'] = SITE_URL .'/apps/event/Tpl/default/Public/images/default_banner.jpg';
//            $cover = D('Attach')->field('savepath,savename')->find($row['logoId']);
//            if($cover){
//                $row['banner'] = SITE_URL .'/data/uploads/'.$cover['savepath'].$cover['savename'];
//            }
            unset($row['logoId']);
            unset($row['typeId']);
            unset($row['default_banner']);
            $list[$key] = $row;
        }
        if ($list) {
            return $list;
        } else {
            return array();
        }
    }

    //用户签到
    public function userAttend() {
        $code = t($_REQUEST['code']);
        $res = $this->daoEvent->userAttend($this->mid, $code);
        return $res;
    }

    public function adminAttend() {
        $code = t($_REQUEST['code']);
        $uid = t($_REQUEST['uid']);
        $res = $this->daoEvent->adminAttend($this->mid, $code, $uid);
        return $res;
    }

    public function isChecked() {
        $code = t($_REQUEST['code']);
        $uid = $_REQUEST['uid'];
        $res = $this->daoEvent->isAttend($uid, $code);
        return $res;
    }

    //收藏
    public function fav() {
        $eid = t($_REQUEST['id']);
        $act = t($_REQUEST['type']);
        $res = D('EventCollection', 'event')->fav($this->mid, $eid, $act);
        return (boolean) $res;
    }

    //抽签接口
    public function lucky() {
        $eid = intval($_REQUEST['eventId']);
        $daouser = M('event_user');
        $result = $daouser->where(' lot=0 AND status=2 AND eventId=' . $eid)->field('id')->findAll();
        if (!$result) {
            $res['status'] = 0;
             return $res;
        }
        $ids = getSubByKey($result, 'id');
        $key = array_rand($ids);
        $res = $daouser->where('id=' . $ids[$key])->field('id,realname')->find();
        $res['status'] = 1;
        return $res;
    }

    //抽签确认接口
    public function confirm() {
        $res['status'] = 0;
        $eid = intval($_REQUEST['eid']);
        $daouser = M('event_user');
        $result = $daouser->where('id=' . $eid)->setField('lot', 1);
        if (!$result) {
            $res['msg'] = '过滤失败';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

    //发起活动
    public function doAddEvent() {
        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $res['status'] = 0;
        //判断发起活动权限
        $canEvent = M('user')->getField('can_add_event', 'uid=' . $this->mid);
        if (!$canEvent) {
            $res['msg'] = '您没有权限发布活动';
            return $res;
        }
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
            if (empty($_REQUEST[$k])){
               $res['msg'] = $v . '不可为空';
            return $res;
            }
        }
        $title = t($_REQUEST['title']);
        if (mb_strlen($title, 'UTF8') > 30) {
            $res['msg'] = '活动名称最大30个字';
            return $res;
        }
        $textarea = t($_REQUEST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 250) {
            $res['msg'] = '活动简介1到250字';
            return $res;
        }
        //发起部落活动判断
        if (isset($_REQUEST['gid']) && !$_REQUEST['gid']) {
            $res['msg'] = '归属部落不能为空';
            return $res;
        }
        $map['sid'] = intval($_REQUEST['sid']);
        if($map['sid']==0){
            $res['msg'] = '归属组织不能为空';
            return $res;
        }
        $map['audit_uid'] = intval($_REQUEST['audit_uid']);
        if($map['audit_uid']==0){
            $res['msg'] = '审核人不能为空';
            return $res;
        }

        if (isset($_REQUEST['gid']) && $_REQUEST['gid']) {
            $result = M('event_group')->where('gid=' . $_REQUEST['gid'] . ' AND uid =' . $this->mid)->find();
            if ($result) {
                $map['gid'] = $_REQUEST['gid'];
            } else {
                $res['msg'] = '您无法发起该部落活动';
                return $res;
            }
        }
        $map['deadline'] = $this->_paramDate($_REQUEST['deadline']);
        $map['sTime'] = $this->_paramDate($_REQUEST['sTime']);
        $map['eTime'] = $this->_paramDate($_REQUEST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $res['msg'] = '结束时间不得早于开始时间';
            return $res;
        }

        if ($map['deadline'] > $map['eTime']) {
            $res['msg'] = '报名截止时间不能晚于结束时间';
            return $res;
        }
        if (empty($_FILES['cover']['name'])) {
              $res['msg'] = '请上传活动图标';
                return $res;
        }

          //得到上传的图片
        if(!isImg($_FILES['cover']['tmp_name'])){
               $res['msg'] = '活动图标文件格式不对';
              return $res;
        }
        $images = X('Xattach')->upload('event' );
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
             $res['msg'] = $images['info'];
                return $res;
        }
        $map['uid'] = $this->mid;
        $map['title'] = $title;
        $map['address'] = t($_REQUEST['address']);
        $map['limitCount'] = intval(t($_REQUEST['limitCount']));
        $map['typeId'] = intval($_REQUEST['typeId']);
        $map['allow'] = isset($_REQUEST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_REQUEST['need_tel']) ? 1 : 0;
        $map['show_in_xyh'] = 1;
        if ($user_sid == 473) {
            $map['show_in_xyh'] = 0;
        }

        $map['is_school_event'] = $user_sid;

        $map['isTicket'] = isset($_REQUEST['isTicket']) ? 1 : 0;
        $map['description'] = $textarea;
        //发布审核
        $map['status'] = 0;
        $map['logoId'] = 62323;
        //发起活动授权人数
       $codelimit = M('event_add')->getField('codelimit','uid='.$this->mid);
       if($codelimit){
          $map['codelimit'] =$codelimit;
       }else{
            $map['codelimit'] = 5;
       }
        if ($addId = $this->daoEvent->apiDoAddEvent($map,$images)) {
            X('Credit')->setUserCredit($this->mid, 'add_event');
            //显示于学校
            D('EventSchool','event')->addBySids($addId, $user_sid);
            //发短信给初审人
            $isSend = M('user_privacy')->where("`key`='active' AND uid=" . $map['audit_uid'])->field('value')->find();
            if ($isSend['value'] == 1) {
                $active = M('user')->where('uid=' . $map['audit_uid'])->field('mobile,sid')->find();
                if ($active['sid'] == $user_sid && $active['mobile']) {
                    $msg = '亲爱的PocketUni用户,有新的活动"' . $map['title'] . '"等待您的审核。http://pocketuni.net';
                    service('Sms')->sendsms($active['mobile'], $msg);
                }
            }
             $res['status'] = 1;
             $res['msg'] = '创建成功，请等待审核';
              return $res;
        } else {
               $res['msg'] = '添加失败';
               return $res;
        }
    }

    private function _paramDate($date) {
        $date_list = explode(' ', $date);
        list( $year, $month, $day ) = explode('-', $date_list[0]);
        list( $hour, $minute, $second ) = explode(':', $date_list[1]);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }


      public function addEvent() {
           $res['status'] = 0;
            $canEvent = M('user')->getField('can_add_event', 'uid=' . $this->mid);
        if (!$canEvent) {
            $res['msg'] = '您没有权限发布活动';
            return $res;;
        }
         $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
//        //审核人
//        $map['can_event'] = 1;
//        $map['sid'] = $user_sid;
//        $audit = M('user')->where($map)->field('uid,uname,event_role_info')->findAll();
//        if (!$audit) {
//             $res['msg'] = '暂无审核人员，请耐心等待！';
//            return $res;;
//        }

        //部落活动
        $group = M('event_group')->where('uid=' . $this->mid)->findAll();
      if ($group) {
            $daogroup = M('group');
            foreach ($group as $k => $v) {
                $group[$k]['title'] = $daogroup->getField('name', 'id = ' . $v['gid']);
            }
             $res['group'] = $group;
        }
        $school = model('Schools')->makeLevel0Tree($user_sid);
            $i=0;
        foreach($school as $v){
            $schools[$i] = $v;
            unset($schools[$i]['domain']);
            unset($schools[$i]['level']);
            unset($schools[$i]['display_order']);
            unset($schools[$i]['cityId']);
            $i++;
        }
        $res['schoolOrga'] =D('SchoolOrga','event')->getAll($user_sid);
        $res['school'] = $schools;
         $res['status'] = 1;
        return  $res;
    }

       public function csOrga() {
        $cs = intval($_REQUEST['sid']);
        if ($cs != 0) {
            $uidArr = M('event_csorga')->where('orga='.$cs)->field('uid')->findAll();
            if($uidArr){
                $map['uid'] = array('in',getSubByKey($uidArr,'uid'));
                $audit = M('user')->where($map)->field('uid,realname,event_role_info')->findAll();
            }
        }
        return  $audit;
    }
    //活动抽签重置
       public function eventReset() {
        $res['status'] = 0;
        $eventId = intval($_REQUEST['id']);
        $map['eventId'] = $eventId;
        $result = M('event_user')->where($map)->setField('lot', 0);
        if ($result){
            $res['msg'] = '重置成功';
            $res['status'] = 1;
        }else{
             $res['msg'] = '重置失败';
        }
        return $res;
    }
    
    //取消参加的活动
    public function cancelEvent() {
        $data['eventId'] = intval($_REQUEST['id']);
        $data['uid'] = $this->mid;
        $res = $this->daoEvent->doDelUser($data);
        return $res;
    }

}
