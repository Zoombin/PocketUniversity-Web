<?php
class SitelistApi extends Api
{
	private $_allowed_fields = array('site_id', 'name', 'url', 'logo', 'description');

	private function _formatSite($site)
	{
		foreach ($site as $k => $v)
        	if (!in_array($k, $this->_allowed_fields))
        		unset($site[$k]);

    	return $site;
	}

    public function getSitelist()
    {
        $res = D('Site', 'sitelist')->getApprovedSiteList($this->since_id, $this->max_id, $this->count, $this->data['content'],'status_mtime DESC', $this->page);
        foreach ($res as &$v)
        	$v = $this->_formatSite($v);

        return $res;
    }

    public function getSiteStatus()
    {
        $site   = D('Site', 'sitelist')->getSite($this->id);
        $status = null;
        $alias  = null;
        switch ($site['status']) {
            case SiteModel::SITE_STATUS_APPLIED:
                $status = 0;
                $alias  = 'APPLIED';
                break;
            case SiteModel::SITE_STATUS_APPROVED:
                $status = 1;
                $alias  = 'APPROVED';
                break;
            case SiteModel::SITE_STATUS_DENIED:
                $status = 0;
                $alias  = 'DENIED';
                break;
            default:
                $status = 0;
                $alias  = 'NOT EXIST';
        }

        $site = $status == 1 ? $this->_formatSite($site) : '';
        return array('status' => $status,
                     'alias'  => $alias,
                     'data'   => $site);
    }

    public function authorize() {
        if ($_REQUEST['email'] && $_REQUEST['password']) {
            $username = t($_REQUEST['email']);
            if (empty($username))
		$this->MobileError('用户名不合法');
            $user = D('User', 'home')->getUserByIdentifier($username, 'email');
            if ($user['password']==md5($_REQUEST['password']) || $user['password']==  codePass($_REQUEST['password'])) {
                if ($login = M('login')->where("uid=" . $user['uid'] . " AND type='location'")->find()) {
                    $data['oauth_token'] = $login['oauth_token'];
                    $data['oauth_token_secret'] = $login['oauth_token_secret'];
                    $data['uid'] = $user['uid'];
                    if ($_REQUEST['email'] == 'wxf@mysuda.com') {
                        $data = array();
                        $data['uid'] = $user['uid'];
                        $data['oauth_token'] = getOAuthToken($user['uid']);
                        $data['oauth_token_secret'] = getOAuthTokenSecret();
                        M('login')->where('login_id='.$login['login_id'])->save($data);
                    }
                } else {
                    $data['oauth_token'] = getOAuthToken($user['uid']);
                    $data['oauth_token_secret'] = getOAuthTokenSecret();
                    $data['uid'] = $user['uid'];
                    $savedata['type'] = 'location';
                    $savedata = array_merge($savedata, $data);
                    M('login')->add($savedata);
                }
                //客户端类型写入数据库
                $client = intval($_REQUEST['client']);
                if ($client > 0) {
                    M('user')->where('uid=' . $user['uid'])->setField('clientType', $client);
                }
                $this->_recordLogin($user['uid']);
                $user = D('User', 'home')->getUserByIdentifier($user['uid'], 'uid');
                $data['uname'] = $user['uname'];
                $data['sid1'] = $user['sid1'];
                $data['sid1Name'] = tsGetSchoolName($user['sid1']);
                return $data;
            } else {
                $this->MobileError('用户名或者密码有误');
            }
        } else {
            $this->MobileError('用户名或者密码为空');
        }
    }
    //记录登录信息
    private function _recordLogin($uid){
        $data['uid'] = $uid;
        $data['ip'] = get_client_ip();
        $data['place'] = '客户端';
        $data['ctime'] = time();
        M('login_record')->add($data);
        $this->_countLogin($uid);
    }

    //统计登录人数
    private function _countLogin($uid){
        $dao = M('login_count');
        $has = $dao->where('uid='.$uid)->find();
        $data['ctime']	= time();
        if($has){
            $dao->where('uid='.$uid)->save($data);
        }else{
            $data['uid']	= $uid;
            $dao->add($data);
        }
    }

    private function MobileError($error = "MobileError") {
        header('Content-Type: application/json; charset=utf-8');
        $result['message'] = $error;
        echo json_encode($result);
        exit;
    }
    //学校列表获取
    public function getSchools(){
        return M('school')->field('id as school,title as name,email,cityId')->where('pid = 0 AND email != ""')->order('display_order ASC')->findAll();
    }
    //城市列表获取
    public function getCitys(){
        return M('citys')->order('short ASC')->findAll();
    }

    public function version() {
        $setting = model('Xdata')->lget('android');
        $version = "";
        $url = "";
        $msg = '';
        if (isset($setting['androidUrl']) && strlen(trim($setting['androidUrl'])) > 0) {
            $url = $setting['androidUrl'];
            if (isset($setting['androidVersion'])) {
                $version = $setting['androidVersion'];
            }
            if (isset($setting['info'])) {
                $msg = $setting['info'];
            }
        }
        $response['response'] = $url;
        $response['version'] = $version;
        $response['msg'] = $msg;

        return $response;
    }

//2.账号注册
//注册提交信息：选择学校、学号、站内呢称、登陆密码、确认密码 SitelistApi.class.php register(sid,number,nickname,password,repassword)
//输入:学校,学号, 昵称, 密码 (不需登录)
//返回注册结果 status： 0失败，1成功， msg：失败信息
    public function register(){
        $res['status'] = 0;
        // 是否允许注册
        $register_option = model('Xdata')->get('register:register_type');
        if ($register_option === 'closed') { // 关闭注册
            $res['msg'] = '抱歉: 本站已关闭注册';
            return $res;
        }
        if ($register_option === 'invite') { //邀请注册
            $res['msg'] = '抱歉: 本站已关闭注册';
            return $res;
        }
$res['msg'] = '抱歉: 本站已关闭注册';
return $res;
        // 参数合法性检查
        $required_field = array(
                'sid'		=> '学校',
                'number'        => '学号',
                'nickname'      => '站内呢称',
                'password'	=> '登陆密码',
                'repassword'    => '确认密码',
        );
        foreach ($required_field as $k => $v){
            if (empty($_REQUEST[$k])){
                $res['msg'] = $v . '不能为空';
                return $res;
            }
        }
        if(strpos($_REQUEST['number'],'@')){
            $res['msg'] = '输入学号而不是邮箱';
            return $res;
        }
        //
        if (!$this->isValidNickName(t($_REQUEST['nickname']))){
            $res['msg'] = '昵称不合法';
            return $res;
        }
        if (strlen($_REQUEST['password']) < 6 || strlen($_REQUEST['password']) > 16){
            $res['msg'] = '密码6-16位';
            return $res;
        }
        if ($_REQUEST['password'] != $_REQUEST['repassword']){
            $res['msg'] = '密码两次输入不一样';
            return $res;
        }

        //选择学校注册
        $map['id'] = intval($_REQUEST['sid']);
        $map['canRegister'] = 1;
        $school = M('school')->where($map)->find();
        if($school && $school['email'] != ''){
            $data['email'] = $_REQUEST['number'].$school['email'];
        }else{
            $res['msg'] = '您所在学校的账号都已导入，请用学号登录';
            return $res;
        }

        $hasEmail = M('user')->where('`email`="'.$data['email'].'"')->find();
        if ($hasEmail){
            $res['msg'] = '您的账号已注册，请用学号登录';
            return $res;
        }

        // 是否需要Email激活
        $need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));

        // 注册
        $data['sid']     = $_REQUEST['sid'];
        $data['password']  = md5($_REQUEST['password']);
        $data['uname']	   = t($_REQUEST['nickname']);
        $data['realname']   = $data['uname'];
        $data['ctime']	   = time();
        $data['is_active'] = $need_email_activate ? 0 : 1;
        $data['is_init'] = 0;

        if (!($uid = D('User', 'home')->add($data))){
            $res['msg'] = '抱歉: 注册失败，请稍后重试';
            return $res;
        }

        // 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
        $user_log = array(
                'uid'		=> $uid,
                'action'	=> 'add',
                'type'		=> '0',
                'dateline'	=> time(),
        );
        M('myop_userlog')->add($user_log);

        // 同步至UCenter
        if (UC_SYNC) {
            $uc_uid = uc_user_register($_REQUEST['nickname'],$_REQUEST['password'],$_REQUEST['email']);
            //echo uc_user_synlogin($uc_uid);
            if ($uc_uid > 0)
                    ts_add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
        }

        if ($need_email_activate == 1) { // 邮件激活
            $this->activate($uid, $_REQUEST['email'], '');
        }
        $res['status'] = 1;
        return $res;
    }

    private function isValidNickName($name) {
        //检查禁止注册的用户昵称
        $audit = model('Xdata')->lget('audit');
        if ($audit['banuid'] == 1) {
            $bannedunames = $audit['bannedunames'];
            if (!empty($bannedunames)) {
                $bannedunames = explode('|', $bannedunames);
                if (in_array($name, $bannedunames)) {
                    return false;
                }
            }
        }
        if (!isLegalUsername($name)) {
            return false;
        } else if (checkKeyWord($name)) {
            return false;
        }
        return true;
    }

    //发送激活邮件
    private function activate($uid, $email, $invite = '', $is_resend = 0) {
        //设置激活路径
        $activate_url = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));
        if ($invite) {
            $this->assign('invite', $invite);
        }
        $this->assign('url', $activate_url);

        //设置邮件模板
        $body = <<<EOD
感谢您的注册!<br>

请马上点击以下注册确认链接，激活您的帐号！<br>

<a href="$activate_url" target='_blank'>$activate_url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
        // 发送邮件
        global $ts;
        service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号", $body);
    }

    // 粉丝排行
    public function topFollowUser()
    {
            $result = D('Follow', 'weibo')->getTopFollowerUser();
            foreach ($result as $k=>$v) {
                $result[$k]['face'] = getUserFace($v['uid']);
                $user = D('User','home')->getUserByIdentifier($v['uid']);
                $result[$k]['uname'] = $user['uname'];
                $result[$k]['followers_count'] = $result[$k]['count'];
                unset($result[$k]['count']);
                $result[$k]['followed_count'] = $user['following'];
                $result[$k]['miniNum'] = $user['miniNum'];
            }
            return $result;
    }

    //返回学校院系
    public function getDepart(){
        $sid = intval($_REQUEST['sid']);
        $res = M('school')->where("pid='$sid'")->order('display_order ASC')->field('id,pid,title')->findAll();
        return $res?$res:array();
    }
}