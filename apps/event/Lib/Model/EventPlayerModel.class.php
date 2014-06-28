<?php

/**
 * EventPlayerModel
 * 活动用户项
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 ludongyun
 * @author ludongyun <rechner00@hotmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventPlayerModel extends BaseModel {

    private $_error;

    public function getError(){
        return $this->_error;
    }

    public function getHandyList($mid, $map, $limit = 10, $page = 1, $order = 'ticket DESC, id DESC') {
        $map['status'] = 1;
        $offset = ($page - 1) * $limit;
        if($map['eventId']==12239){
            $order = "RAND()";
        }
        $list = $this->field('id,path,realname,ticket,school,stoped,eventId')->where($map)->order($order)->limit("$offset,$limit")->select();
        $pids = M('event_vote')->where('eventId='.$map['eventId'].' and mid='.$mid)->field('pid')->findAll();
        $pids = getSubByKey($pids,'pid');
        foreach ($list as $key => $value) {
            $row = $value;
            $row['path'] = tsGetEventUserThumb($row['path'], 163,204,'c');
            //用户学校
            if(!$row['school']){
                $row['school'] = ' ';
            }
            $row['sex'] = '1';
            //是否可己投票
            $row['canVote'] = $this->_canVote($mid,$value,$pids);
            unset($row['stoped']);
            unset($row['eventId']);
            $list[$key] = $row;
        }
        return $list;
    }

    private function _canVote($mid,$player,$pids){
        if($player['stoped']){
            return false;
        }
        $event = M('event')->where('id='.$player['eventId'])->field('isTicket,maxVote,repeated_vote,eTime,is_school_event,school_audit')->find();
        if(!$event){
            return false;
        }
        if(!$event['isTicket']){
            return false;
        }
        if($event['eTime'] <= time() ||
                ($event['is_school_event'] && $event['school_audit'] != 2)){
            return false;
        }
        $votedCount = count($pids);
        if($event['maxVote']<$votedCount){
            return false;
        }
        if(!$event['repeated_vote']){
            $res = array_unique($pids);
            if(in_array($player['id'], $res)){
                return false;
            }
        }
        return true;
    }

    public function vote($mid,$pid,$event=false,$sid=0){
        $player = $this->where('status=1 and id = '.$pid)->field('eventId,stoped')->find();
        if(!$player || $player['stoped']){
            $this->_error = '投票已关闭';
            return false;
        }
        $eventId = $player['eventId'];
        if(!$event){
            $event = M('event')->where('id='.$eventId)->field('isTicket,maxVote,repeated_vote,allTicket,eTime')->find();
        }
        if(!$event || !$event['isTicket']){
            $this->_error = '投票已关闭';
            return false;
        }
        if($event['eTime'] <= time()){
            $this->_error = '活动已结束';
            return false;
        }
        //检查用户是否是该活动允许的学校
        if(!$sid){
            $sid = M('user')->getField('sid', 'uid=' . $mid);
        }
        $mapSchool['eventId'] =  $eventId;
        $mapSchool['sid'] = $sid;
        $eventSchool = M('event_school')->where($mapSchool)->find();
        if(!$eventSchool){
              $this->_error = '该活动不向您所在学校开放投票';
              return false;
        }

        $maxVote = $event['maxVote'];
        $votedCount = M('event_vote')->where('eventId='.$eventId.' and mid='.$mid)->count();
        if($maxVote<=$votedCount){
            $this->_error = '本活动最多可投'.$maxVote.'票，您已投满！';
            return false;
        }
        //检查重复投票
        $bandVote = $this->_getBandVote($mid,$eventId,$event['repeated_vote']);
        if(in_array($pid, $bandVote)){
            $this->_error = '不可重复投票！';
            return false;
        }
        $data['mid'] = $mid;
        $data['cTime'] = time();
        $data['pid'] = $pid;
        $data['eventId'] = $eventId;
        //是否全部投完
        $data['status'] = $event['allTicket']?0:1;
        $res = D('EventVote')->add($data);
        if($res){
            $has = $this->_hasTicket($mid, $eventId, $maxVote,$event['allTicket']);
            $resCount = $maxVote-$has;
            if($event['allTicket']){
                if($has>=$maxVote){
                    $this->_error = '投票成功！您已投满了'.$maxVote.'票!！';
                }else{
                    $this->_error = '投票成功！投满'.$maxVote.'票才生效，还差【'.$resCount.'】票!';
                }
            }else{
                $this->setInc('ticket','id=' . $pid);
                if($has>=$maxVote){
                    $this->_error = '投票成功！您已投满了'.$maxVote.'票!！';
                }else{
                    $this->_error = '投票成功！还有【'.$resCount.'】票可投!';
                }
            }
            return true;
        }
        $this->_error = '投票失败！';
        return false;
    }

    private function _hasTicket($mid,$eventId,$maxTicket,$mustAll) {
        $pidArr = M('event_vote')->where('eventId=' . $eventId . ' and mid=' . $mid)->field('pid,status')->findAll();
        $has = count($pidArr);
        if($has>=$maxTicket && $mustAll){
            foreach ($pidArr as $vote) {
                if($vote['status']==0){
                    $this->setInc('ticket', 'id='.$vote['pid']);
                }
            }
        }
        return $has;
    }

    //不可重复投票pid
    private function _getBandVote($mid,$eventId,$repeated_vote){
        $res = array();
        if($mid && !$repeated_vote){
            $pids = M('event_vote')->where('eventId='.$eventId.' and mid='.$mid)->field('pid')->findAll();
            $res = getSubByKey($pids,'pid');
            $res = array_unique($res);
        }
        return $res;
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

    public function doDelPlayer($data,$delFile='true'){
        $res = false;
        $player = $this->where($data)->findAll();
        if($this->where($data)->delete()){
            foreach($player as $v){
                if(strpos($v['path'], '/')){
                    tsDelFile($v['path']);
                }else{
                    tsDelFile('event/'.$v['path']);
                }
                $map['uid'] = $v['id'];
                D('EventImg')->doDelete($map,$delFile);
                D('EventFlash')->doDelete($map,$delFile);
                $map = array();
                $map['pid'] = $v['id'];
                D('EventVote')->doDelete($map);
            }
            $res = true;
        }
        return $res;
    }

    public function doAllowPlayer($map){
        $data['status'] = 1;
        return $this->where($map)->save($data);
    }

    //选手资料返回修改
    public function doPlayerBack($pid){
        return $this->setField('status', 2, 'id='.$pid);
    }
}
