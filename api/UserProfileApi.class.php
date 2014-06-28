<?php
class UserProfileApi extends Api{

	function test(){
		return 1;
	}

	function update( ) {
		$nickname = t($_REQUEST['nickname']);
		if(!$nickname){
			$data['message'] = "昵称不能为空";
			$data['boolen']  = 0;
			return $data;
		}

		if( !isLegalUsername($nickname) ){
			$data['message'] = "昵称格式有误";
			$data['boolen']  = 0;
			return $data;
		}

		if( M('user')->where("uname='{$nickname}' AND uid!={$this->mid}")->find() ){
			$data['message'] = "昵称己被使用";
			$data['boolen']  = 0;
			return $data;
		}
                if($_FILES['face']['size'] > 0) {
                         D('Avatar','home')->apiUpload($this->mid);
                         D('Avatar','home')->apiDosave($this->mid);

                }
	    //$data['province'] = intval( $_REQUEST['area_province'] );
	    $data['uname']    = $nickname;
	    //$data['city']     = intval( $_REQUEST['area_city'] );
	    //$data['location'] =  getLocation($data['province'],$data['city']);
	    $data['sex']      = intval( $_REQUEST['sex'] );
	    M('user')->where("uid={$this->mid}")->data($data)->save();

	    //修改登录用户缓存信息--名称
	    $userLoginInfo = S('S_userInfo_'.$this->mid);
	    if(!empty($userLoginInfo)) {
	    	$userLoginInfo['uname'] = $data['uname'];
			$userLoginInfo['sex'] = $data['sex'];
	    	S('S_userInfo_'.$this->mid, $userLoginInfo);
	    }

	   	$response['response'] = "资料修改成功";
		$response['boolen']  = 1;
		return $response;
	}

    public function doModifyPassword() {
        if (strlen($_REQUEST['password']) < 6 || strlen($_REQUEST['password']) > 16) {
            $this->error("密码格式有误, 合法的密码为6-16位字符");
        }
        if ($_REQUEST['password'] != $_REQUEST['repassword']) {
            $this->error("密码两次输入不一样");
        }
        if ($_REQUEST['password'] == $_REQUEST['oldpassword']) {
            $this->error("新旧密码相同");
        }
        $user = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
        if ($user['password']==md5($_REQUEST['oldpassword']) || $user['password']==  codePass($_REQUEST['oldpassword'])) {
            $dao = M('user');
            $newPass = codePass($_REQUEST['password']);
            $map['uid'] = $this->mid;
            if ($dao->where($map)->setField('password',$newPass)) {
                S('S_userInfo_' . $this->mid, null);
                $this->success("密码修改成功");
            } else {
                $this->error("密码修改失败");
            }
        } else {
            $this->error("原密码错误");
        }
    }

	private function success($message){
		header('Content-Type: application/json; charset=utf-8');
		$result['response'] = $message;
		echo json_encode($result);
		exit;
	}

	private function error($error="MobileError"){
		$this->MobileError($error);
	}

	private function MobileError($error="MobileError"){
		header('Content-Type: application/json; charset=utf-8');
		$result['message'] = $error;
		echo json_encode($result);
		exit;
	}
}