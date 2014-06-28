<?php

class UserMobileModel extends Model {

    public function clean() {
        $map['status'] = array('neq', 0);
        $map['cTime'] = array('lt', strtotime('-10 minutes'));
        $this->where($map)->setField('status', 0);
    }

    public function addRow($uid, $mobile) {
        //检查5分钟发一次
        if ($this->getRestSec($uid)) {
            return -3;
        }
        //一天不能超过3次
        $map['uid'] = $uid;
        $map['cTime'] = array('gt',strtotime('-1 day'));
        $cnt = $this->where($map)->count();
        if($cnt>=3){
            return -4;
        }
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        $code = String::rand_string(4, 1);

                    //该用户以前的code统统作废
            $map = array();
            $map['status'] = array('neq', 0);
            $map['uid'] = $uid;
            $this->where($map)->setField('status', 0);

            $data['uid'] = $uid;
            $data['mobile'] = $mobile;
            $data['code'] = $code;
            $data['cTime'] = time();
            if($this->add($data)){
                closeDb();
                $msg = '亲爱的PocketUni用户，您的验证码为：'.$code.'。请您尽快完成验证。';
                $result = service('Sms')->sendsms($mobile, $msg);
                return 1;
            }

        //if($result['status']){


        //}
        return 0;
    }
    public function addRowMail($uid, $email) {
        //检查5分钟发一次
        if ($this->getRestSec($uid)) {
            return -3;
        }
        //一天不能超过3次
        $map['uid'] = $uid;
        $map['cTime'] = array('gt',strtotime('-1 day'));
        $cnt = $this->where($map)->count();
        if($cnt>=10){
            return -4;
        }
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        $code = String::rand_string(4, 1);
            //该用户以前的code统统作废
            $map = array();
            $map['status'] = array('neq', 0);
            $map['uid'] = $uid;
            $this->where($map)->setField('status', 0);

            $data['uid'] = $uid;
            $data['mobile'] = $email;
            $data['code'] = $code;
            $data['cTime'] = time();
            if($this->add($data)){
                closeDb();
                $msg = '亲爱的PocketUni用户，您的验证码为：'.$code.'。请您尽快完成验证。';
                global $ts;
                $result = service('Mail')->send_email($email, $ts['site']['site_name'] . '身份验证', $msg);
                return 1;
            }
        //if($result){

        //}
        return 0;
    }

    public function getRestSec($uid) {
        $lastTime = $this->where(array('uid' => $uid))->max('cTime');
        $rank = $lastTime + 300 - time();
        if ($rank <= 0) {
            return 0;
        }
        return $rank;
    }

    public function bind($uid,$mobile,$code){
        //$this->clean();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $obj = $this->where($map)->find();
        if($obj){
            if($obj['mobile'] == $mobile && $obj['code'] == $code){
                $this->where('id = '.$obj['id'])->setField('status', 0);
                $data['mobile'] = $mobile;
                $data['is_valid'] = 1;
                M('user')->where('uid='.$uid)->save($data);
                $userLoginInfo = S('S_userInfo_'.$uid);
                if(!empty($userLoginInfo)) {
                    $userLoginInfo['mobile'] = $mobile;
                    $userLoginInfo['is_valid'] = 1;
                    S('S_userInfo_'.$uid, $userLoginInfo);
                    if($_SESSION['userInfo']['uid'] == $uid){
                        $_SESSION['userInfo']['mobile'] = $mobile;
                        $_SESSION['userInfo']['is_valid'] = 1;
                    }
                }
                return true;
            }else{
                $this->where('id='.$obj['id'])->setField('status', $obj['status']-1);
            }
        }
        return false;
    }

    public function bindEmail($uid,$email,$code){
        //$this->clean();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $obj = $this->where($map)->find();
        if($obj){
            if($obj['mobile'] == $email && $obj['code'] == $code){
                $this->where('id = '.$obj['id'])->setField('status', 0);
                $data['email2'] = $email;
                $data['is_valid'] = 1;
                M('user')->where('uid='.$uid)->save($data);
                $userLoginInfo = S('S_userInfo_'.$uid);
                if(!empty($userLoginInfo)) {
                    $userLoginInfo['email2'] = $email;
                    $userLoginInfo['is_valid'] = 1;
                    S('S_userInfo_'.$uid, $userLoginInfo);
                    if($_SESSION['userInfo']['uid'] == $uid){
                        $_SESSION['userInfo']['email2'] = $email;
                        $_SESSION['userInfo']['is_valid'] = 1;
                    }
                }
                return true;
            }else{
                $this->where('id='.$obj['id'])->setField('status', $obj['status']-1);
            }
        }
        return false;
    }
}