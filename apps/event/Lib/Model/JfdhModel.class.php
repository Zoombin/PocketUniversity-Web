<?php

/**
 * JfdhModel
 * 兑换历史记录
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class JfdhModel extends Model {

    public function isCodeUesed($code) {
        $map['code'] = $code;
        $map['isGet'] = 0;
        return $this->where($map)->find();
    }

    public function doAdd($data) {
        $data['cTime'] = time();
        $id = $this->add($data);
        if ($id) {
            //更新物品数量
            M('jf')->setDec('number', array('id' => $data['jfid']), $data['number']);
            //更新用户积分信息
            $uid = $data['uid'];
            $sumCost = $data['number'] * $data['cost'];
            $map['uid'] = $uid;
            M('user')->setInc('school_event_score_used', $map, $sumCost);
            $userLoginInfo = S('S_userInfo_' . $uid);
            if (!empty($userLoginInfo)) {
                $user = M('user')->field('school_event_score_used')->where('uid = ' . $uid)->find();
                $userLoginInfo['school_event_score_used'] = $user['school_event_score_used'];
                S('S_userInfo_' . $uid, $userLoginInfo);
                if ($_SESSION['userInfo']['uid'] == $uid) {
                    $_SESSION['userInfo'] = $userLoginInfo;
                }
            }
        }
        return $id;
    }

    public function getList($map = '', $field = '*', $order = 'id DESC', $limit = 10) {
        $res = $this->where($map)->field($field)->order($order)->findPage($limit);
        $jfids = getSubByKey($res['data'], 'jfid');
        $map1['id'] = array('in', $jfids);
        $tempJf = M('jf')->where($map1)->findAll();
        //转换成array($id => $jf)的格式
        $jf = array();
        foreach ($tempJf as $v) {
            $jf[$v['id']] = $v;
        }
        unset($tempJf);
        foreach ($res['data'] as $k => $v) {
            $res['data'][$k]['jf'] = $jf[$v['jfid']];
        }
        return $res;
    }

    public function linqu($map){
        $obj = $this->where($map)->find();
        if($obj){
            $data['isGet'] = 1;
            $data['uTime'] = time();
            if($this->where($map)->save($data)){
                return true;
            }
        }
        return false;
    }
}

?>