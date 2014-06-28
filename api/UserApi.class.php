<?php
class UserApi extends Api{

	//按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博
	function show(){
		$data = getUserInfo($this->user_id, urldecode($this->user_name),$this->mid,true);
		return $data;
	}

	public function notificationCount() {
		if(empty($this->user_id) && isset($this->mid)){
			return service('Notify')->getCount($this->mid);
		}else{
			return service('Notify')->getCount($this->user_id);
		}

	}

	public function unsetNotificationCount()
	{
		if(empty($this->user_id) && isset($this->mid)){
			switch ($this->data['type']) { // 暂仅允许message/weibo_commnet/atMe
				case 'message':
					return (int) model('Message')->setAllIsRead($this->mid);
				case 'weibo_comment':
					return (int) model('UserCount')->setZero($this->mid, 'comment');
				case 'atMe':
					return (int) model('UserCount')->setZero($this->mid, 'atme');
				default:
					return 0;
			}
		}else{
			switch ($this->data['type']) { // 暂仅允许message/weibo_commnet/atMe
				case 'message':
					return (int) model('Message')->setAllIsRead($this->user_id);
				case 'weibo_comment':
					return (int) model('UserCount')->setZero($this->user_id, 'comment');
				case 'atMe':
					return (int) model('UserCount')->setZero($this->user_id, 'atme');
				default:
					return 0;
			}
		}
	}

	public function getNotificationList(){
		$this->data['type'] 	= $this->data['type']	? $this->data['type'] : array(1,2);
		$this->data['order']	= $this->data['order'] == 'ASC'	? '`mb`.`list_id` ASC' : '`mb`.`list_id` DESC';
		if(empty($this->user_id) && isset($this->mid)){
			return service('Notify')->getNotifityCount($this->mid, $this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page);
		}else{
			return service('Notify')->getNotifityCount($this->user_id, $this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page);
		}
	}

	public function setMessageIsRead(){
		if(empty($this->user_id) && isset($this->mid)){
			return (int)model('Message')->setMessageIsRead($this->id,$this->mid);
		}else{
			return (int)model('Message')->setMessageIsRead($this->id,$this->user_id);
		}

	}

    //通知分类获取
    public function getAnnounceCategory(){
        return M('announce_category')->field('id as cid,title as name')->order('display_order ASC, id ASC')->findAll();
    }

    //通知列表获取
    public function getAnnounceList(){
        $map['sid'] = intval($_REQUEST['school']);
        if(!$map['sid']){
            return false;
        }
        $cid = intval($_REQUEST['cid']);
        if($cid){
            $map['cid'] = $cid;
        }
          $map['isDel'] = 0;
        $offset = intval($_REQUEST['offset']);
        $limit = intval($_REQUEST['limit']);
        $list = M('announce')->field('id,title,sid as school,cid,sid1,cTime as time')
                ->where($map)->order('id DESC')->limit("$offset,$limit")->select();
        foreach ($list as $key => $value) {
            $row = $value;
            $row['time'] = date('Y-m-d H:i', $value['time']);
            //院系通知加院系
            $row['school2'] = tsGetSchoolName($row['sid1']);
            unset($row['sid1']);
            $list[$key] =  $row;
        }
        return $list;
    }

    public function getNoticeList() {
        $cid = intval($_REQUEST['cid']);
        if ($cid) {
            $map['cid'] = $cid;
        }
        $map['isDel'] = 0;
        $page = intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $count = intval($_REQUEST['count']) ? intval($_REQUEST['count']) : 10;
        $offset = ($page - 1) * $count;
        $list = M('notice')->field('id,title,front,cid,cTime as time')
                        ->where($map)->order('id DESC')->limit("$offset,$count")->select();
        return $list;
    }

      public function getNotice() {
        $map['id'] = intval($_REQUEST['id']);
        if (!$map['id']) {
            return false;
        }
        $obj = M('notice')->field('id,title,content,front,cid,cTime as time')
                        ->where($map)->find();
        return $obj;
    }


    //通知内容获取
    public function getAnnounce(){
        $map['id'] = intval($_REQUEST['id']);
        $new = intval($_REQUEST['new']);
        if(!$map['id']){
            return false;
        }
        $obj = M('announce')->field('id,title,content,sid as school,cid,sid1,cTime as time')
                ->where($map)->find();
        if($obj){
            //院系通知加院系
            $obj['school2'] = tsGetSchoolName($obj['sid1']);
            unset($obj['sid1']);
            $obj['time'] = date('Y-m-d H:i', $obj['time']);
            if(!$new)
            $obj['content'] = strip_tags(htmlspecialchars_decode($obj['content']),'<img>');
            return $obj;
        }
        return false;
    }

    //新通知的数量获取
    public function getAnnounceNew(){
        $sid = intval($_REQUEST['school']);
        if($sid){
            $map['sid'] = $sid;
        }
        $time = intval($_REQUEST['time']);
        if(!$time){
            return array('count'=>0);
        }
        $map['cTime'] = array('gt',$time);
        $map['isDel'] = 0;
        $count = M('announce')->where($map)->count();
        return array('count'=>$count);
    }

    public function userHomeInfo(){
        $announce = $this->getAnnounceNew();
        $res['announce'] = $announce['count'];
        $group = D('Member','group');
        $count = $group->where('uid='.$this->mid." AND level != 0 ")->count();
        $res['group'] = $count;
        $user = M('user')->where('uid='.$this->mid)->field('year,mobile')->find();
        $res['year'] = $user['year'];
        $res['mobile'] = $user['mobile'];
        return $res;
    }

    public function getUserSchool(){
        $sid = M('user')->getField('sid', 'uid='.$this->mid);
        $school = M('school')->where('id='.$sid)->find();
        return $school;
    }

    //检查用户是否验证、初始化
    public function checkUser(){
        $user = M('user')->where('uid='.$this->mid)->field('is_valid,is_init')->find();
        return $user;
    }
    //是否有密保邮箱
  public function passwordEmail(){
       $res['status'] = 0;
        $email = M('user')->where('uid='.$this->mid)->getField('email2');
        if($email){
            $res['email'] =$email;
            $res['status'] = 1;
            return $res;
        }
      return $res;
  }

    //初始化用户
    public function initUser(){
        $res['status'] = 0;
        $email = t($_REQUEST['email']);
        if (!isValidEmail($email)) {
            $res['msg'] = '邮箱格式不对';
            return $res;
        }
        $map['email2'] = $email;
        $hasEmail = M('user')->where($map)->field('uid')->find();
        if ($hasEmail && $hasEmail['uid'] != $this->mid) {
            $res['msg'] = '邮箱已被使用';
            return $res;
        }
        $password = $_REQUEST['password'];
        if (strlen($password) < 6 || strlen($password) > 16) {
             $res['msg'] = "密码格式有误, 合法的密码为6-15位字符";
               return $res;
        }
        $data['password'] = codePass($password);
        $data['email2'] = $email;
        $data['is_init'] = 1;
        if(M('user')->where("uid={$this->mid}")->save($data)){
            S('S_userInfo_' . $this->mid, null);
            $res['status'] = 1;
            return $res;
        }
        $res['msg'] = '操作失败，请稍后再试';
        return $res;
    }

    public function emailCode() {
         $res['status'] = 0;
        $email = t($_REQUEST['email']);
        if (!isValidEmail($email)) {
            $res['msg'] = '邮箱格式不正确';
            return $res;
        }
        $map['email2'] = $email;
        $hasEmail = M('user')->where($map)->field('uid')->find();
        if ($hasEmail && $hasEmail['uid'] != $this->mid) {
            $res['msg'] = '邮箱已被使用';
            return $res;
        }
        $send = D('UserMobile', 'home')->addRowMail($this->mid, $email);
        if ($send == -3) {
            $res['msg'] = '5分钟内只能发送一次';
            return $res;
        } elseif ($send == -4) {
            $res['msg'] = '1天内最多发送3次';
            return $res;
        } elseif ($send != 1) {
            $res['msg'] = '发送验证码失败，请稍后再试';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

        public function mobileCode() {
        $res['status'] = 0;
        $mobile = t($_REQUEST['mobile']);
        if (!isValidMobile($mobile)) {
            $res['msg'] = '手机号码格式不正确';
            return $res;
        }
        $map['mobile'] = $mobile;
        $hasMobile = M('user')->where($map)->field('uid')->find();
        if ($hasMobile && $hasMobile['uid'] != $this->mid) {
            $res['msg'] = '手机号码已被使用';
            return $res;
        }
        $send = D('UserMobile', 'home')->addRow($this->mid, $mobile);
        if ($send == -3) {
            $res['msg'] = '5分钟内只能发送一次';
            return $res;
        } elseif ($send == -4) {
            $res['msg'] = '1天内最多发送3次';
            return $res;
        } elseif ($send != 1) {
            $res['msg'] = '发送验证码短信失败，请稍后再试';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

        public function mobileBind() {
        $res['status'] = 0;
        $code = intval($_REQUEST['code']);
        $mobile = t($_REQUEST['mobile']);
        $result = D('UserMobile', 'home')->bind($this->mid, $mobile, $code);
        if (!$result) {
            $res['msg'] = '验证失败';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

    public function emailBind() {
        $res['status'] = 0;
        $code = intval($_REQUEST['code']);
        $email = t($_REQUEST['email']);
        $result = D('UserMobile', 'home')->bindEmail($this->mid, $email, $code);
        if (!$result) {
            $res['msg'] = '验证失败';
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

    public function userInfo(){
        $res = array();
        $user  = D('User', 'home')->getUserByIdentifier($this->mid);
        //$user = M('user')->field('uid, realname,sid')->where('uid='.$this->mid)->find();
        if($user){
            $res['uid'] = $this->mid;
            $res['realname'] = $user['realname'];
            $res['school'] = $user['school'];
            $res['province'] = '江苏';
            $city = getCityBySid($user['sid']);
            $res['city'] = $city?$city['city']:'';
            $res['money'] = money2xs($user['money']);
        }
        return $res;
    }

    public function useMoney(){
        $dao = Model('Money');
        $pay = $dao->moneyOut($this->mid, intval($_REQUEST['total_money']), t($_REQUEST['desc']));
        $rest = $dao->getMoneyCache($this->mid);
        $rest = $rest/100;
        if(!$pay){
            return array('status'=>0,'msg'=>'账户PU币不足,请先充值','rest'=>$rest);
        }
        return array('status'=>1,'msg'=>'消费成功','rest'=>$rest);
    }


    public function siUserInfo(){
        $res = array();
        $user  = D('User', 'home')->getUserByIdentifier($this->mid);
        if($user){
            $res['uid'] = $this->mid;
            $res['realname'] = $user['realname'];
            $res['sex'] = $user['sex'];
            $res['school'] = $user['school'];
            $city = getCityBySid($user['sid']);
            $res['city'] = $city?$city['city']:'';
        }
        return $res;
    }
}