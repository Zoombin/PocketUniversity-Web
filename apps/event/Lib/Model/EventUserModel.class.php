<?php

/**
 * EventUserModel
 * 活动用户项
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventUserModel extends BaseModel {

    /**
     * getUserList
     * 获得用户列表
     * @param mixed $action
     * @param mixed $eventId
     * @param mixed $limit
     * @access public
     * @return void
     */
    public function getUserList($map, $order = 'isHot DESC,id DESC', $limit = 10) {
        return $this->where($map)->order($order)->findPage($limit);
    }

    public function getHandyList($mid, $map, $limit = 10, $page = 1, $order = 'ticket DESC, id DESC') {
        $offset = ($page - 1) * $limit;
        $list = $this->field('id,path,uid,realname,sex,sid,ticket')->where($map)->order($order)->limit("$offset,$limit")->select();
        foreach ($list as $key => $value) {
            $row = $value;
            $row['path'] = tsGetEventUserThumb($row['path'], 163,204,'c');
            //用户学校
            if($row['uid']){
                $row['school'] = tsGetSchoolByUid($row['uid'], ' ');
            }else{
                $row['school'] = tsGetSchoolTitle($row['sid']);
            }
            unset($row['uid']);
            //是否可己投票
            $row['canVote'] = $this->canVote($mid, $row['id']);
            $list[$key] = $row;
        }
        return $list;
    }

    public function canVote($mid,$pid){
        $player = $this->where('id = '.$pid)->find();
        if(!$player || $player['stoped']){
            return false;
        }
        $event = M('event')->where('id='.$player['eventId'])->find();
        if(!$event || !$event['isTicket']){
            return false;
        }
        //用户每天投票一次
        $map['mid'] = $mid;
        $map['cTime'] = strtotime('today');
        $map['pid'] = $pid;
        $voted = M('event_vote')->where($map)->find();
        if ($voted) {
            return false;
        }
        return true;
    }

    public function vote($mid,$pid){
        if($this->canVote($mid,$pid)){
            $this->setInc('ticket','id=' . $pid);
            $data['mid'] = $mid;
            $data['cTime'] = strtotime('today');
            $data['pid'] = $pid;
            if(D('EventVote')->add($data)){
                return true;
            }
        }
        return false;
    }

    /**
     * hasUser
     * 是否已经有了这个用户的关注
     * @param mixed $uid
     * @param mixed $id
     * @access public
     * @return void
     */
    public function hasUser($uid, $id, $status = '999') {
        $map['uid'] = $uid;
        $map['eventId'] = $id;
        if ($status != 999) {
            $map['status'] = $status;
        }
        return $this->field('status')->where($map)->find();
    }

    public function doChangeVote($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('stoped', 0);
                break;
            case "close":
                $result = $this->where($map)->setField('stoped', 1);
                break;
        }
        return $result;
    }

    public function attend($mid,$eid){
        $map['uid'] = $mid;
        $map['eventId'] = $eid;
        return $this->where($map)->setField('status', 2);
    }

}
