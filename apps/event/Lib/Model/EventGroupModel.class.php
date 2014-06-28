<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GroupModel
 *
 * @author Administrator
 */
class EventGroupModel extends Model {

    var $tableName = 'group';

    public function getHotList($sid,$reset = false) {
        // 1分钟锁缓存
//        if (!($cache = S('Cache_Event_Group_Hot_list')) || $reset) {
//            S('Cache_Event_Group_Hot_list_t', time()); //缓存未设置 先设置缓存设定时间
//        } else {
//            if (!($cacheSetTime = S('Cache_Event_Group_Hot_list_t')) || $cacheSetTime + 60 <= time()) {
//                S('Cache_Event_Group_Hot_list_t', time()); //缓存未设置 先设置缓存设定时间
//            } else {
//                return $cache;
//            }
//        }
        // 缓存锁结束

        $data= $this->field('id,id AS gid,name,logo,membercount,intro,ctime,cid0,cid1,vStern')
                ->where('status=1 AND is_del=0 AND disband=0  AND school='.$sid)
                ->order('membercount DESC, id DESC')
                ->limit(5)
                ->findAll();
        //var_dump($this->getLastSql());
        S('Cache_Event_Group_Hot_list', $data);
        return $data;
    }

    public function getGroupList($map = '',$mid,$order = 'id DESC') {
        $result = $this->where($map)->field('id,uid,vStern,name,logo,cid0,sid1,membercount,intro')->order($order)->findPage(10);
//追加必须的信息
        if (!empty($result['data'])) {
            foreach ($result['data'] as &$value) {
//                $value['count'] = D('Groupmember')->memberCount($value['id']);
                $value['category'] = $this->getCategoryField($value['cid0']);
                $value['schoolName'] = $this->getSchoolName($value['sid1']);
                $value['isMember'] = D('GroupMember')->isMember($mid,$value['id']);
            }
        }
        return $result;
    }

    public function getCategoryField($cid) {
        $title = M('group_category')->where('id =' . $cid)->getField('title');
        return $title;
    }

    public function getSchoolName($sid1) {
        if($sid1>0){
           $title = M('school')->where("id =$sid1")->getField('title');
        }else if($sid1=='-1'){
           $title = '校级';
        }
        return $title;
    }

    public function getNewList($sid) {
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['school'] = $sid;
        $result = $this->where($map)->field('id,name,logo,intro')->order('id DESC')->limit(6)->findAll();
        return $result;
    }

    //某人加入某部落
    public function joinGroup($mid, $gid, $level, $incMemberCount = false, $reason = '') {
        if (M('group_member')->where("uid=$mid AND gid=$gid")->find())
            exit('你已经加入过');

        $member['uid'] = $mid;
        $member['gid'] = $gid;
        $member['name'] = getUserName($mid);
        $member['level'] = $level;
        $member['ctime'] = time();
        $member['mtime'] = time();
        $member['reason'] = $reason;
        $ret = M('group_member')->add($member);

        // 不需要审批直接添加，审批就不用添加了。
        if ($incMemberCount) {
            // 成员统计
            $this->setInc('membercount', 'id=' . $gid);
        }
        if ($level == 1) {
            //修改部落活动发起权限
            $daoEventGroup = M('event_group');
            $data['gid'] = $gid;
            $data['uid'] = $mid;
            $daoEventGroup->add($data);
            M('user')->where('uid =' . $mid)->setField('can_add_event', 1);
        }

        return $ret;
    }


      public function disBandGroup($gid) {
        $result = $this->where('id=' . $gid)->setField(array('is_del', 'disband'), array(1, 1));
        if (!$result)
            return false;
        $daoEventGroup = M('event_group');
        $uids = $daoEventGroup->where('gid =' . $gid)->field('uid')->findAll();
        if (!$uids)
            return true;
        $daoUser = M('user');
        $daoEventGroup->where('gid =' . $gid)->delete();
        foreach ($uids as $v) {
            $addevent = $daoEventGroup->where('uid=' . $v['uid'])->getField('id');
            if (!$addevent) {
                $daoUser->where('uid =' . $v['uid'])->setField('can_add_event', 0);
            }
        }
        return true;
    }

    //彻底删除部落
    public function endDel($gid){
        if($this->where('id='.$gid)->delete()){
            //发起活动权限
            $daoEventGroup = M('event_group');
            $uids = $daoEventGroup->where('gid =' . $gid)->field('uid')->findAll();
            if ($uids){
                $daoUser = M('user');
                $daoEventGroup->where('gid =' . $gid)->delete();
                foreach ($uids as $v) {
                    $addevent = $daoEventGroup->where('uid=' . $v['uid'])->getField('id');
                    if (!$addevent) {
                        $daoUser->where('uid =' . $v['uid'])->setField('can_add_event', 0);
                    }
                }
            }
            //删除部落成员
            M('group_member')->where('gid =' . $gid)->delete();
            M('group_topic')->where('gid =' . $gid)->delete();
            M('group_post')->where('gid =' . $gid)->delete();
            //删除共享
            $daoAttach = M('group_attachment');
            $files = $daoAttach->where('gid='.$gid)->field('attachId')->findAll();
            if($files){
                $attIds = getSubByKey($files, attachId);
                model('Attach')->deleteAttach($attIds, true);
            }
            $daoAttach->where('gid='.$gid)->delete();
            return true;
        }
        return false;
    }

}

?>
