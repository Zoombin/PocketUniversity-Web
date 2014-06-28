<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

/**
 * EventModel
 * 活动主数据库模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventModel extends BaseModel {

    var $mid;

    //首页置顶推荐活动
    public function getSchoolIndex($map,$limit=4){
        $map['isDel'] = 0;
        $map['status'] = 1;
        return $this->where($map)->field('id,title,sid,typeId,coverId,sTime,eTime,uid')
                ->order('id DESC')->limit($limit)->findAll();
    }
    //首页全省活动
    public function getProv($sid,$limit=4){
        if($sid!=505){
            $eventIds = D('EventSchool')->getEventIds($sid);
            $map['id'] = array('in', $eventIds);
        }
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['is_prov_event'] = 1;
        $map['eTime'] = array('gt',time());
        return $this->where($map)->field('id,title,typeId,coverId,sTime,eTime,uid')
                ->order('id DESC')->limit($limit)->findAll();
    }

    public function getSlide($sid){
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['is_school_event'] = $sid;
        $map['isHot'] = 1;
        //$map['logoId'] = array('neq', 0);
        $slide = $this->where($map)->order('id DESC')->field('id,title,logoId,typeId,default_banner')->limit(5)->findAll();
        return $slide;
    }

    public function getHomeList($map = '', $mid = 0, $limit = 3, $order = 'id DESC') {
        $this->mid = $mid;
        $result = $this->where($map)->order($order)->limit($limit)->findAll();
        if (!empty($result)) {
            $user = self::factoryModel('user');
            foreach ($result as &$value) {
                $value['cover'] = tsGetCover($value['coverId']);
                $value['canJoin'] = true;
                if ($mid > 0) {
                    $userDao = self::factoryModel('user');
                    if ($hasUser = $userDao->hasUser($this->mid, $value['id'])) {
                        $value['canJoin'] = false;
                    }
                }
            }
        }
        return $result;
    }

    public function handyGetEventList($mid, $html = 1, $map = array(), $limit=10, $page=1, $order = 'isTop DESC, id DESC'){
        $this->mid = $mid;
        $sql = $this->field('id,title,coverId,sTime,eTime,uid,sid,typeId as cid,joinCount,note,noteUser,deadline,
            school_audit,limitCount,isTop,need_tel,is_school_event')
                        ->where($map)->order($order);
        if($html){
            $list = $sql->findPage($limit);
            $data = $list['data'];
        }else{
            $offset = ($page - 1) * $limit;
            $data = $sql->limit("$offset,$limit")->select();
        }
        $cate = D('EventType','event')->getType();
        //收藏的eids
        $colleted = M('event_collection')->where('uid='.$mid)->findAll();
        $colIds = getSubByKey($colleted, 'eid');
        foreach ($data as $key => $value) {
            $row = $value;
            $row['cover'] = tsGetCover($row['coverId']);
            unset($row['coverId']);
            $row['sTime'] = date('Y-m-d H:i', $row['sTime']);
            $row['eTime'] = date('Y-m-d H:i', $row['eTime']);
            $row['uname'] = getUserName($row['uid']);
            $row['sName'] = tsGetSchoolTitle($row['is_school_event']);
            $row['cName'] = $cate[$row['cid']];
            //是否己结束
            $row['isFinish'] = 0;
            if($row['school_audit'] == 5 || $row['deadline'] <= time()){
                $row['isFinish'] = 1;
            }
            unset($row['school_audit']);
            unset($row['deadline']);
            //是否己参与
            $row['hasJoin'] = 0;
            if (D('EventUser','event')->hasUser($this->mid, $row['id'])) {
                $row['hasJoin'] = 1;
            }
            //是否可报名
            $row['canJoin'] = 0;
            if(!$row['isFinish'] && !$row['hasJoin'] && $row['limitCount'] > 0){
                $row['canJoin'] = 1;
            }
            unset($row['limitCount']);
            //是否己参与评分
            $row['hasNoted'] = intval(D('EventNote','event')->hasNoted($row['id'], $this->mid));
            $data[$key] = $row;
            //是否已收藏
            $row['hasFav'] = false;
            if($colIds && in_array($row['id'], $colIds)){
                $row['hasFav'] = true;
            }
        }
        if($html){
            $list['data'] = $data;
        }else{
            $list = $data;
        }
        return $list;
    }

    public function getEvent($mid, $map){
        $this->mid = $mid;
        if(!$map['id']){
            return false;
        }
        $map['isDel'] = 0;
        $row = $this->where($map)->field('id,adminCode,uid,title,coverId,logoId,sTime,eTime,uid,sid,typeId as cid,default_banner,is_prov_event,
            address,cost,costExplain,limitCount,contact,joinCount,note,noteUser,deadline,school_audit,is_school_event,need_tel,description')
                ->find();
        if($row){
            //$row['description'] = '活动简介';
            //是否己结束
            $row['isFinish'] = 0;
            if($row['school_audit'] == 5 || $row['deadline'] <= time()){
                $row['isFinish'] = 1;
            }
            unset($row['school_audit']);
            //是否显示签到
            $canAttend = $this->canAttend($mid,$row);
            $row['showAttend'] = $canAttend['status'];
            //unset($row['is_school_event']);
            //经费
            $row['cost'] = tsGetCost($row['cost']);
            $row['cover'] = tsGetCover($row['coverId']);
            unset($row['coverId']);
            $row['banner'] = tsGetLogo($row['logoId'],'m'.$row['cid'],$row['default_banner'],480,270,'f');
            unset($row['logoId']);
            $row['isStart'] = $row['sTime'] < time() ? 0 : 1;
            $row['sTime'] = date('Y-m-d H:i', $row['sTime']);
            $row['eTime'] = date('Y-m-d H:i', $row['eTime']);
            $row['deadline'] = date('Y-m-d H:i', $row['deadline']);
            $row['uname'] = getUserName($row['uid']);
            $row['uface'] = getUserFace($row['uid']);
            $row['sName'] = tsGetSchoolTitle($row['is_school_event']);
            $cate = D('EventType','event')->getType();
            $row['cName'] = $cate[$row['cid']];
            $orga = M('event_orga')->where('eventId = '.$map['id'])->find();
            $row['orga'] = '';
            if($orga){
                $orga['content'] = str_replace('&lt;br&gt;', ' ', $orga['content']);
                $row['orga'] = strip_tags(htmlspecialchars_decode($orga['content']),'<img>');
            }
            //是否己参与
            $row['hasJoin'] = 0;
            if (D('EventUser','event')->hasUser($this->mid, $row['id'])) {
                $row['hasJoin'] = 1;
            }
            //是否可报名
            $row['canJoin'] = 0;
            if(!$row['isFinish'] && !$row['hasJoin'] && $row['limitCount'] > 0){
                $row['canJoin'] = 1;
            }
            unset($row['is_prov_event']);
            unset($row['limitCount']);
            //是否己参与评分
            $row['hasNoted'] = intval(D('EventNote','event')->hasNoted($row['id'], $this->mid));
            //是否有新闻
            $row['news'] = D('EventNews')->where('eventId = '.$map['id'])->order('id DESC')->count();
            //是否有花絮
            $row['photo'] = D('EventImg')->where(array('eventId' => $map['id'], 'uid'=>0))->order('id DESC')->count();
            //是否已收藏
            $colleted = M('event_collection')->where('uid='.$mid)->findAll();
            $colIds = getSubByKey($colleted, 'eid');
            $row['hasFav'] = false;
            if($colIds && in_array($row['id'], $colIds)){
                $row['hasFav'] = true;
            }
            if($mid == $row['uid']&&empty($row['adminCode'])){
                $row['adminCode'] = $this->getAdminCode($row['id']);
            }
            return $row;
        }
        return false;
    }

    public function canAttend($mid, $event, $checkTime=true){
        $res = array('status'=>0);
        if(!$event['is_school_event']){
            $res['msg'] = '该活动无需签到';
            return $res;
        }
        if($checkTime){
            $now = time();
            //开始前1小时签到
            $startTime = $event['sTime']-3600;
            if($now > $event['eTime'] || $now < $startTime){
                $res['msg'] = '签到失败，签到时间活动前一小时至活动结束';
                return $res;
            }
        }
        $daoUser = D('EventUser','event');
        $user = $daoUser->hasUser($mid,$event['id']);
        if(!$user){
            $res['msg'] = '签到失败，您尚未报名';
            return $res;
        }
        if($user['status'] == 0){
            $res['msg'] = '签到失败，您的报名尚未通过审核';
            return $res;
        }
        if($user['status'] == 2){
            $res['msg'] = '不可重复签到';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

    //扫描活动码签到，方式2
    public function userAttend($mid,$code){
        $res = array('status'=>0);
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['attendCode'] = $code;
        $event = $this->where($map)->field('id,sTime,eTime,is_school_event')->find();
        if(!$event){
            $res['msg'] = '签到失败，无效二维码';
            return $res;
        }
        $canAttend = $this->canAttend($mid,$event);
        if($canAttend['status'] == 0){
            return $canAttend;
        }
        $daoUser = D('EventUser','event');
        if(!$daoUser->attend($mid,$event['id'])){
            $res['msg'] = '签到失败';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }
    public function isAttend($mid,$code) {
        $res = array('status' => 0);
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['adminCode'] = strtoupper($code);
        $event = $this->where($map)->field('id,sTime,eTime,is_school_event')->find();
        if (!$event) {
            $res['msg'] = '活动码错误，或活动已结束';
            return $res;
        }
        $canAttend = $this->canAttend($mid,$event);
        if ($canAttend['status'] == 0) {
            return $canAttend;
        }
        $res['status'] = 1;
        return $res;
    }

    //管理员后台帮忙签到
    public function adminUserAttend($mid, $eventId) {
              $res = array('status'=>0);
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['id'] = $eventId;
        $event = $this->where($map)->field('id,sTime,eTime,is_school_event')->find();
        if (!$event) {
            $res['msg'] = '签到失败';
            return $res;
        }
        $canAttend = $this->canAttend($mid,$event,false);
        if ($canAttend['status'] == 0) {
          return $canAttend;
        }
        $daoUser = D('EventUser', 'event');
        if (!$daoUser->attend($mid, $event['id'])) {
            $res['msg'] = '签到失败';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }


    //个人二维码签到，方式1
    public function adminAttend($mid,$code,$uid){
        $res = array('status'=>0);
        $map['isDel'] = 0;
        $map['status'] = 1;
        //$map['uid'] = $mid;
        $map['adminCode'] = strtoupper($code);
        $event = $this->where($map)->field('id,uid,sTime,eTime,is_school_event,codelimit')->find();
        if(!$event){
            $res['msg'] = '无效活动码或您无权签到';
            return $res;
        }
        //活动签到码授权的人数判断
        $daoCode = M('event_code');
        $where['adminCode'] = $map['adminCode'];
        $where['uid'] = $mid;
        $result = $daoCode->where($where)->find();
        if(!$result){
           $codelimit = $event['codelimit'] ? $event['codelimit'] : 5;
           $code = $daoCode->where("`adminCode`='$code'")->count();
           if($code>=$codelimit){
                 $res['msg'] = '授权人数已满,您无权签到';
                  return $res;
           }else{
               $daoCode->add($where);
           }
        }

        $canAttend = $this->canAttend($uid,$event);
        if($canAttend['status'] == 0){
            return $canAttend;
        }
        $daoUser = D('EventUser','event');
        if(!$daoUser->attend($uid,$event['id'])){
            $res['msg'] = '签到失败';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

    public function getQrCode($eid){
        $event = $this->where('id='.$eid)->field('id,attendCode')->find();
        if(!$event){
            return '';
        }elseif($event['attendCode']){
            return $event['attendCode'];
        }else{
            require_once(SITE_PATH . '/addons/libs/String.class.php');
            $randval = String::rand_string(5);
            $code = $randval.$eid;
            $this->setField('attendCode', $code, 'id='.$eid);
            return $code;
        }
    }

    public function getAdminCode($eid){
        $event = $this->where('id='.$eid)->field('id,adminCode')->find();
        if(!$event){
            return '';
        }elseif($event['adminCode']){
            return $event['adminCode'];
        }else{
            require_once(SITE_PATH . '/addons/libs/String.class.php');
            $randval = String::rand_string(2,5);
            $code = $randval.$eid;
            $this->setField('adminCode', $code, 'id='.$eid);
            return $code;
        }
    }

    public function getEventList($map = '', $mid, $order = 'isTop DESC, id DESC') {
        $this->mid = $mid;
        $result = $this->where($map)->order($order)->findPage(getConfig('limitpage'));
        //追加必须的信息
        if (!empty($result['data'])) {
            $colleted = M('event_collection')->where('uid='.$mid)->findAll();
            $colIds = getSubByKey($colleted, 'eid');
            $user = self::factoryModel('user');
            foreach ($result['data'] as &$value) {
                $value = $this->_appendContent($value);
                $value['cover'] = getCover($value['coverId']);
                //计算待审核人数
                if ($this->mid == $value['uid']) {
                    $value['verifyCount'] = $user->where("status = 0  AND eventId =" . $value['id'])->count();
                }
                //是否已评分
                $value['hasNoted'] = D('EventNote')->hasNoted($value['id'], $mid);
                //检查是否已收藏
                $value['hasColleted'] = false;
                if($colIds && in_array($value['id'], $colIds)){
                    $value['hasColleted'] = true;
                }
            }
        }
        return $result;
    }

    /**
     * 追加和反解析数据
     * @param mixed $data
     * @access public
     * @return void
     */
    private function _appendContent($data) {
        $type = self::factoryModel('type');
        $data['type'] = $type->getTypeName($data['typeId']);

        //反解析时间
        $data['time'] = date('Y-m-d H:i', $data['sTime']) . " 至 " . date('Y-m-d H:i', $data['eTime']);
        $data['dl'] = date('Y-m-d H:i', $data['deadline']);

        //追加权限
        $data += $this->checkMember($data['uid'], $this->mid);

        //追加是否已参加的判定
        $userDao = self::factoryModel('user');
        if ($result = $userDao->hasUser($this->mid, $data['id'])) {
            $data['canJoin'] = false;
            $data['hasMember'] = $result['status'];
            return $data;
        }

        return $data;
    }

    /**
     * checkRoll
     * 检查权限
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function checkMember($eventAdmin, $mid) {
        $result = array(
            'admin' => false,
            'canJoin' => true,
            'hasMember' => false,
        );
        if ($mid == $eventAdmin) {
            $result['admin'] = true;
            return $result;
        }

        return $result;
    }

    /**
     * doAddEvent
     * 添加活动
     * @param mixed $map
     * @param mixed $feed
     * @access public
     * @return void
     */
    public function doAddEvent($eventMap, $cover,$attach) {
        $eventMap['cTime'] = isset($eventMap['cTime']) ? $eventMap['cTime'] : time();
        if ($cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    $eventMap['coverId'] = $value['id'];
                } elseif ($value['key'] == 'logo') {
                    $eventMap['logoId'] = $value['id'];
                }elseif ($value['key'] == 'attach') {
                    $eventMap['attachId'] = $value['id'];
                }
            }
        }
        //活动签到权限人数
        $codelimit = M('event_add')->getField('codelimit', 'uid='.$eventMap['uid']);
        if(!$codelimit){
            $codelimit = 5;
        }
        $eventMap['codelimit'] = $codelimit;
        //$eventMap['coverId'] = $cover['status'] ? $cover['info'][0]['id'] : 0;
        //$eventMap['limitCount'] = 0 == intval($eventMap['limitCount']) ? 999999999 : $eventMap['limitCount'];

        $addId = $this->add($eventMap);
        //组织单位
        $orgaMap['eventId'] = $addId;
        D('EventOrga')->createOrga($orgaMap);

        return $addId;
    }


        /**
     * doAddEvent
     * 客户端添加活动
     * @param mixed $map
     * @param mixed $feed
     * @access public
     * @return void
     */
    public function apiDoAddEvent($eventMap,$cover) {
        $eventMap['cTime'] = isset($eventMap['cTime']) ? $eventMap['cTime'] : time();
        if ($cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    $eventMap['coverId'] = $value['id'];
                }
            }
        }
        //$eventMap['limitCount'] = 0 == intval($eventMap['limitCount']) ? 999999999 : $eventMap['limitCount'];
        $addId = $this->add($eventMap);
        //组织单位
        $orgaMap['eventId'] = $addId;
        D('EventOrga', 'event')->createOrga($orgaMap);
        return $addId;
    }

//根据存储路径，获取图片真实URL
    function get_photo_url($savepath) {
        return './data/uploads/' . $savepath;
    }

    public function doEditEvent($eventMap, $cover, $obj) {
        $eventMap['rTime'] = isset($eventMap['rTime']) ? $eventMap['rTime'] : time();
        if ($cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    //删除旧的
                    model('Attach')->deleteAttach($obj['coverId'], true, true);
                    $eventMap['coverId'] = $value['id'];
                } elseif ($value['key'] == 'logo') {
                    model('Attach')->deleteAttach($obj['logoId'], true, true);
                    $eventMap['logoId'] = $value['id'];
                } elseif ($value['key'] == 'attach') {
                    model('Attach')->deleteAttach($obj['attachId'], true, true);
                    $eventMap['attachId'] = $value['id'];
                }
            }
        }
        //$eventMap['limitCount'] = 0 == intval($eventMap['limitCount']) ? 999999999 : $eventMap['limitCount'];
        $addId = $this->where('id =' . $obj['id'])->save($eventMap);

        return $addId;
    }

    /**
     * factoryModel
     * 工厂方法
     * @param mixed $name
     * @static
     * @access private
     * @return void
     */
    public static function factoryModel($name) {
        return D("Event" . ucfirst($name), 'event');
    }

    /**
     * doAddUser
     * 添加用户行为
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doAddUser($data) {
        $result = array('status' => 0);

        //诚信系统禁止中
        $stoped = M('event_cx')->field('eday')->where('uid='.$data['uid'].' and status=2')->find();
        if($stoped){
            $result['info'] = '您被诚信系统惩罚中，'.$stoped['eday'].' 前不可参加活动';
            return $result;
        }

        //检查用户是否是该活动允许的学校
        $mapSchool['eventId'] = $data['id'];
        $mapSchool['sid'] = $data['usid'];
        $eventSchool = M('event_school')->where($mapSchool)->find();
        if(!$eventSchool){
            $result['info'] = '该活动不向您所在学校开放报名';
            return $result;
        }
        //检查这个id是否存在
        if (false == $event = $this->where('id =' . $data['id'])->field('need_tel,allow,limitCount')->find()) {
            $result['info'] = '活动不存在';
            return $result;
        }
//        if($event['is_prov_event']){
//            $result['info'] = '该活动不可报名';
//            return $result;
//        }
        if($event['need_tel'] && empty($data['tel'])){
            $result['info'] = '联系电话不能为空';
            return $result;
        }

        //检查是否已经加入
        $userDao = self::factoryModel('user');
        if ($userDao->hasUser($data['uid'], $data['id'])) {
            $result['info'] = '已经报名该活动';
            return $result;
        }

        if (!$event['allow'] && $event['limitCount'] < 1) {
            $result['info'] = '人数已满！添加失败';
            return $result;
        }

        $map = $data;
        $map['eventId'] = $data['id'];
        unset($map['id']);
        $map['cTime'] = time();
        $map['status'] = $event['allow'] ? 0 : 1;
        $res = $userDao->add($map);
        if ($res) {
            $result['info'] = '报名成功，请等待审核';
            if ($map['status']) {
                $result['info'] = '报名成功';
                $this->setInc('joinCount', 'id=' . $map['eventId']);
                $this->setDec('limitCount', 'id=' . $map['eventId']);
                X('Credit')->setUserCredit($map['uid'], 'join_event');
//                if($event['is_school_event']){
//                    $this->getScore($res, $data['uid'], $event['is_school_event'], $event['score']);
//                }
            }
            $result['status'] = 1;
        } else {
            $result['info'] = '报名失败';
        }
        return $result;
    }

    /**
     * doArgeeUser
     * 同意申请
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doArgeeUser($data) {
        $userDao = self::factoryModel('user');
        $data['status'] = 0;
        if ($userDao->where($data)->setField('status', 1)) {
            $this->setInc('joinCount', 'id=' . $data['eventId']);
            $this->setDec('limitCount', 'id=' . $data['eventId']);
            $user = $userDao->where('id='.$data['id'])->field('uid')->find();
            X('Credit')->setUserCredit($user['uid'], 'join_event');
            return true;
        }
        return false;
    }

    /**
     * doDelUser
     * 删除用户
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doDelUser($data,$adminDo=false) {
//        $data['status'] = 0;
//        $userDao = self::factoryModel('user');
//        return $userDao->where($data)->delete();

        $res['status'] = 0;
        $userDao = self::factoryModel('user');
        $user = $userDao->where($data)->field('id, uid, status,eventId,fTime,score,credit')->find();
        if(!$user){
            $res['msg'] = '用户不存在';
            return $res;
        }
        if($user['status']==2){
            $res['msg'] = '用户已签到，不可删除';
            return $res;
        }
        if($user['status']==1 && !$adminDo){
            $event = $this->where('id='.$user['eventId'])->field('deadline')->find();
            if(time()>$event['deadline']){
                $res['msg'] = '报名已截止，取消参加请联系发起人操作';
                return $res;
            }
        }
        if ($userDao->where('id = '.$user['id'])->delete()) {
            //记录数相应减1
            $deleteMap['id'] = $user['eventId'];
            if ($user['status']) {
                $this->setInc('limitCount', $deleteMap);
                $this->setDec("joinCount", $deleteMap);
                X('Credit')->setUserCredit($user['uid'], 'cancel_join_event');
//                $event = $this->where('id='.$user['eventId'])->find();
//                if($event['is_school_event'] && $user['fTime']){
//                    $this->decScore($user['id'], $user['uid'], $event['is_school_event'], $user['score'], $user['credit']);
//                }
            }
            $res['status'] = 1;
            $res['msg'] = '取消成功';
            return $res;
        }
        $res['msg'] = '取消失败';
        return $res;
    }

    public function doEditData($time, $id) {
        //检查安全性，防止非管理员访问
        $uid = $this->where('id=' . $id)->getField('uid');
        if ($uid != $this->mid) {
            return -1;
        }

        if ($this->where('id=' . $id)->setField('deadline', $time)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * getList
     * 供后台管理获取列表的方法
     * @param mixed $order
     * @param mixed $limit
     * @access public
     * @return void
     */
    public function getList($map, $order, $limit) {
        $result = $this->where($map)->order($order)->findPage($limit);
        //将属性追加
        foreach ($result['data'] as $k=>&$value) {
            $value = $this->_appendContent($value);
            $result['data'][$k]['attach'] =getAttach($value['attachId']);
        }
        return $result;
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @param mixed $eventId
     * @access public
     * @return void
     */
    public function doDeleteEvent($eventId) {
        //TODO 检查是否是管理员

        if (empty($eventId)) {
            return false;
        }
        //取出选项ID
        $optsIds = $this->field('uid,title')->where($eventId)->findAll();
        foreach ($optsIds as &$v) {
            //积分
            X('Credit')->setUserCredit($v['uid'], 'delete_event');
            //$v = $v['optsId'];
            // 发送通知
            $notify_dao = service('Notify');
            $notify_data = array('title' => $v ['title']);
            $notify_dao->sendIn($v ['uid'], 'event_del', $notify_data);
        }
        //$opts_map['id'] = array('in', $optsIds);
        //删除活动
        $this->where($eventId)->setField('isDel', 1);
        $news['eventId'] = $eventId['id'];
        D('EventNews')->doDelete($news);
        return true;
        /*
          if ($this->where($eventId)->delete()) {
          //删除选项
          self::factoryModel('opts')->where($opts_map)->delete();
          //删除成员
          $user_map['eventId'] = $eventId['id'];
          self::factoryModel('user')->where($user_map)->delete();
          return true;
          }
         */

        return false;
    }

    /**
     * doIsHot
     * 设置推荐
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsHot($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "recommend":   //推荐
                $result = $this->where($map)->setField('isHot', 1);
                break;
            case "cancel":   //取消推荐
                $result = $this->where($map)->setField('isHot', 0);
                break;
        }
        return $result;
    }

    /**
     * 设置投票
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsTicket($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('isTicket', 1);
                break;
            case "close":
                $result = $this->where($map)->setField('isTicket', 0);
                break;
        }
        return $result;
    }
    public function doRepeatedVote($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('repeated_vote', 1);
                break;
            case "close":
                $result = $this->where($map)->setField('repeated_vote', 0);
                break;
        }
        return $result;
    }
    public function doPlayerUpload($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('player_upload', 1);
                break;
            case "close":
                $result = $this->where($map)->setField('player_upload', 0);
                break;
        }
        return $result;
    }

    /**
     * 设置置顶
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsTop($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "top":   //置顶
                $result = $this->where($map)->setField('isTop', 1);
                break;
            case "cancel":   //取消置顶
                $result = $this->where($map)->setField('isTop', 0);
                break;
        }
        return $result;
    }

    /**
     * doIsActiv
     * 重新激活
     * @param Intger $id
     * @access public
     * @return void
     */
    public function doIsActiv($id) {
        $result = false;
        if ($id > 0) {
            $map['id'] = $id;
            $data['deadline'] = strtotime('+1 day');
            $result = $this->where($map)->save($data);
        }
        return $result;
    }

    /**
     * getHotList
     * 推荐列表
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function getHotList($map=array()) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['isHot'] = 1;
        $result = $this->where($map)->order('isTop DESC, id DESC')->limit(5)->findAll();
        return $result;
    }

    /**
     * hasMember
     * 判断是否是有这个成员
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function hasMember($uid, $eventId) {
        $user = self::factoryModel('user');
        if ($result = $user->where('uid=' . $uid . ' AND eventId=' . $eventId)->field('action,status')->find()) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 审核通过
     * @param array $ids
     * @return boolean
     */
    public function doAudit($ids) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField('status', 1); // 通过审核
        if ($res) {
            // 发送通知
            $events = $this->where($map)->findAll();
            $notify_dao = service('Notify');
            foreach ($events as $v) {
                $notify_data = array('title' => $v ['title'], 'eventId' => $v ['id']);
                $notify_dao->sendIn($v ['uid'], 'event_audit', $notify_data);
            }
        }
        return $res;
    }
    //jun  完结驳回
     public function doFinishBack($ids,$reason) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField('school_audit', 4);
        if ($res) {
            // 发送通知
            $events = $this->where($map)->field('id,title,uid')->findAll();
            $notify_dao = service('Notify');
            foreach ($events as $v) {
                $notify_data['eventId'] = $v['id'];
                $notify_data['title'] = $v['title'];
                $notify_data['reason'] = $reason;
                $notify_dao->sendIn($v ['uid'], 'event_finishback', $notify_data);
            }
        }
        return $res;
    }
    /**
     * 校方审核通过
     * @param array $ids
     * @return boolean
     */
    public function doSchoolAudit($id,$uid,$sid,$credit,$score,$codelimit,$isAdmin) {
        $map['id'] = $id;
        $user = D('User', 'home')->getUserByIdentifier($uid);
        $res = false;
        if($user['sid'] != $sid && !$isAdmin){
            return false;
        }
        $data['credit'] = intval($credit);
        $data['score'] = intval($score);
        $data['codelimit'] = intval($codelimit);
        $data['school_audit'] = 1;
        $isAudit2 = $user['can_event2'] || $isAdmin;
        if($isAudit2){
            $data['school_audit'] = 2;
            $data['audit_uid2'] = $uid;
        }
        $res = $this->where($map)->save($data);
        if($res && $isAudit2){
            $res = $this->doAudit($id);
        }
        if($res){
            //终审通过
            $event = $this->where("id=".$id)->field('uid,title')->find();
            if($isAudit2){
                //将发起人默认参加 和签到
                $autor = M('user')->where('uid='.$event['uid'])->field('realname,mobile,sex,sid')->find();
                $euData['eventId'] = $map['id'];
                $euData['uid'] = $event['uid'];
                $euData['realname'] = $autor['realname'];
                $euData['sex'] =  $autor['sex'];
                $euData['tel'] =  $autor['mobile'];
                $euData['usid'] =  $autor['sid'];
                $euData['cTime'] =  time();
                $euData['status']=2;
                M('event_user')->add($euData);
                $this->setInc('joinCount', 'id=' . $id);
                $this->setDec('limitCount', 'id=' . $id);
                X('Credit')->setUserCredit($event['uid'], 'join_event');
            }else{
                //发短信给终审人
                $endUser= M('user')->where("can_event2=1 AND sid=".$sid)->field('uid,mobile')->findAll();
                $arr=array();
                $daoUserPrivacy = M('user_privacy');
                foreach($endUser as $v){
                    if($v['mobile']){
                        $isSend = $daoUserPrivacy->where("`key`='active' AND uid=".$v['uid'])->field('value')->find();
                        if($isSend['value']!=1){
                            $arr[] = $v['mobile'];
                        }
                    }
                }
//                $mobile=implode(',', $arr);
//                if($mobile){
//                    $msg = '亲爱的PocketUni用户,有新的活动"'. $event['title'].'"等待您的终极审核。';
//                    service('Sms')->sendsms($mobile, $msg);
//                }
            }
        }
        return $res;
    }

    /**
     * 完结活动
     * @param array $id
     * @return boolean
     */
    public function doFinish($id,$code,$giveScore=true) {
        $map['id'] = array('IN', $id);
        $code = intval($code);
        $res = $this->where($map)->setField('school_audit', $code);
        if(!$res){
            return false;
        }
        if($code!=5){
            return $res;
        }
        M('event_cron')->add(array('event_id'=>$id));
        $event = $this->where($map)->field('title,is_school_event,score,credit')->find();
        $map = array();
        $map['eventId'] = $id;
        $map['status'] = 2;
        $eventUser = D('EventUser')->where($map)->field('id,uid,usid')->findAll();
        $uids = getSubByKey( $eventUser, 'uid' );
        //完结不发放积分 私信通知
        if($code==5 && !$giveScore){
            $notify_dao = service('Notify');
            $notify_data['title'] = $event['title'];
            $notify_dao->sendIn($uids, 'event_finish_error', $notify_data);
            return true;
        }
        $map['fTime'] = 0;
        //发放积分
        if($res && $event){
            if(!empty($uids)){
                $map = array();
                $map['uid'] = array('IN', $uids);
                $map['sid'] = $event['is_school_event'];
                //发放积分
                if($event['score']>0){
                    M('user')->setInc('school_event_score',$map,$event['score']);
                }
                if($event['credit']>0){
                    M('user')->setInc('school_event_credit',$map,$event['credit']);
                }
                //用户设为已发放
                $map = array();
                foreach ($eventUser as $v) {
                    $map['id'] = $v['id'];
                    $data['credit'] = $event['credit'];
                    $data['score'] = $event['score'];
                    $data['fTime'] = time();
                    D('EventUser')->where($map)->save($data);
                    if($event['credit']>0){
                        $this->upEday($v['uid'], $v['usid'], $event['credit']);
                    }
                }
                //用户进行积分操作后，登录用户的缓存将修改
                foreach($uids as $uid){
                    $userLoginInfo = S('S_userInfo_'.$uid);
                    if(!empty($userLoginInfo)) {
                        $userLoginInfo['school_event_credit'] = $userLoginInfo['school_event_credit']+$event['credit'];
                        $userLoginInfo['school_event_score'] = $userLoginInfo['school_event_score']+$event['score'];
                        S('S_userInfo_'.$uid, $userLoginInfo);
                        if($_SESSION['userInfo']['uid'] == $uid){
                            $_SESSION['userInfo'] = $userLoginInfo;
                        }
                    }
                }
            }
        }

        return $res;
    }
    //补发积分
    public function bufa($where) {
        $result['status'] = 0;
        $event = $this->where($where)->field('id,is_school_event,score,credit')->find();
        if(!$event){
            $result['info'] = '活动不存在或您没有权限！';
            return $result;
        }
        $map = array();
        $map['eventId'] = $event['id'];
        $map['status'] = 2;
        $map['fTime'] = 0;
        $map['usid'] = $event['is_school_event'];
        $eventUser = D('EventUser')->where($map)->field('id,uid,usid')->findAll();
        $uids = getSubByKey( $eventUser, 'uid' );
        if(!empty($uids)){
            $map = array();
            $map['uid'] = array('IN', $uids);
            //发放积分
            if($event['score']>0){
                M('user')->setInc('school_event_score',$map,$event['score']);
            }
            if($event['credit']>0){
                M('user')->setInc('school_event_credit',$map,$event['credit']);
            }
            //用户设为已发放
            $map = array();
            foreach ($eventUser as $v) {
                $map['id'] = $v['id'];
                $data['credit'] = $event['credit'];
                $data['score'] = $event['score'];
                $data['fTime'] = time();
                D('EventUser')->where($map)->save($data);
                if($event['credit']>0){
                    $this->upEday($v['uid'], $v['usid'], $event['credit']);
                }
            }
            //用户进行积分操作后，登录用户的缓存将修改
            foreach($uids as $uid){
                $userLoginInfo = S('S_userInfo_'.$uid);
                if(!empty($userLoginInfo)) {
                    $userLoginInfo['school_event_credit'] = $userLoginInfo['school_event_credit']+$event['credit'];
                    $userLoginInfo['school_event_score'] = $userLoginInfo['school_event_score']+$event['score'];
                    S('S_userInfo_'.$uid, $userLoginInfo);
                    if($_SESSION['userInfo']['uid'] == $uid){
                        $_SESSION['userInfo'] = $userLoginInfo;
                    }
                }
            }
        }
        $result['status'] = 1;
        $result['info'] = '成功补发'.count($uids).'个签到成员积分学分';

        return $result;
    }

    //活动统计日结
    public function upEday($uid,$sid,$credit){
        $today = date('Y-m-d');
        $map['uid'] = $uid;
        $map['day'] = $today;
        $res = M('tj_eday')->field('id,credit')->where($map)->find();
        $data['uid'] = $uid;
        $data['sid'] = $sid;
        $data['credit'] = $credit;
        $data['day'] = $today;
        if($res){
            $data['credit'] += $res['credit'];
            return M('tj_eday')->where('id='.$res['id'])->save($data);
        }else{
            return M('tj_eday')->add($data);
        }
    }
    //发放积分
    /*
    public function getScore($eventUserId,$uid,$sid,$score){
        if ($uid>0) {
            $map = array();
            $map['uid'] = $uid;
            $map['sid'] = $sid;
            //发放积分
            $fafang = M('user')->setInc('school_event_score', $map, $score);
            //用户设为已发放
            $map = array();
            $map['id'] = $eventUserId;
            D('EventUser')->where($map)->setField('fTime', time());
            //用户进行积分操作后，登录用户的缓存将修改
            if($fafang){
                $userLoginInfo = S('S_userInfo_' . $uid);
                if (!empty($userLoginInfo)) {
                    $userLoginInfo['school_event_score'] = $userLoginInfo['school_event_score'] + $score;
                    S('S_userInfo_' . $uid, $userLoginInfo);
                    if ($_SESSION['userInfo']['uid'] == $uid) {
                        $_SESSION['userInfo'] = $userLoginInfo;
                    }
                }
            }
        }
    }
*/
    //删除积分
    public function decScore($eventUserId,$uid,$sid,$score){
        if ($uid>0) {
            $map = array();
            $map['uid'] = $uid;
            $map['sid'] = $sid;
            //发放积分
            $fafang = M('user')->setDec('school_event_score', $map, $score);
            $fafang = M('user')->setDec('school_event_credit', $map, $credit);
            //用户进行积分操作后，登录用户的缓存将修改
            if($fafang){
                $userLoginInfo = S('S_userInfo_' . $uid);
                if (!empty($userLoginInfo)) {
                    $userLoginInfo['school_event_score'] = $userLoginInfo['school_event_score'] - $score;
                    $userLoginInfo['school_event_credit'] = $userLoginInfo['school_event_credit'] - $credit;
                    S('S_userInfo_' . $uid, $userLoginInfo);
                    if ($_SESSION['userInfo']['uid'] == $uid) {
                        $_SESSION['userInfo'] = $userLoginInfo;
                    }
                }
            }
        }
    }


    /**
     * 驳回
     * @param array $ids
     * @return boolean
     */
    public function doDismissed($ids,$reason,$del) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField('school_audit', 6);
        if($del){
            $this->where($map)->setField('isDel', 1);
        }
        if ($res) {
            // 发送通知
            $events = $this->where($map)->field('id,title,uid')->findAll();
            $notify_dao = service('Notify');
            foreach ($events as $v) {
                $url = $v['title'];
                if(!$del){
                    $link = U('event/Author/index', array('id' => $v['id']));
                    $url = '<a href="'.$link.'">'.$v['title'].'</a>';
                }
                $notify_data['title'] = $url;
                $notify_data['reason'] = $reason;
                $notify_dao->sendIn($v ['uid'], 'event_delaudit', $notify_data);
            }
        }
        return $res;
    }

    /**
     * 驳回
     * @param array $ids
     * @return boolean
     */
    public function doDismissed0($ids) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField('status', 2);
        if ($res) {
            // 发送通知
            $events = $this->where($map)->findAll();
            $notify_dao = service('Notify');
            foreach ($events as $v) {
                $notify_data = array('title' => $v ['title']);
                $notify_data['reason'] = '';
                $notify_dao->sendIn($v ['uid'], 'event_delaudit', $notify_data);
            }
        }
        return $res;
    }

    /**
     * 评分
     */
    public function doAddNote($eventId, $uid, $note) {
        $noteArr = array(1, 2, 3, 4, 5);
        if (!in_array($note, $noteArr)) {
            return false;
        }
        $daoNote = D('EventNote','event');
        //是否已评分
        if ($daoNote->hasNoted($eventId, $uid)) {
            return false;
        }
        $map['id'] = $eventId;
        $event = $this->where($map)->find();
        //活动是否存在
        if (!$event) {
            return false;
        }
        if (!$daoNote->addNote($eventId, $uid, $note)) {
            return false;
        }
        $avg = $daoNote->getAvg($eventId);
        $data['note'] = $avg;
        $data['noteUser'] = $daoNote->getNoteCount($eventId);;
        if ($this->where($map)->save($data)) {
            $data['note'] = sprintf('%0.1f', $avg);
            return $data;
        }
        return false;
    }

}

?>