<?php

class PublicAction extends Action {

    public function _initialize() {

    }

    public function extlogin(){
        $sid = intval($_REQUEST['sid']);
        if($sid!=402){
            die('学校ID错误');
        }
        $num = t($_REQUEST['num']);
        if(!$num){
            die('学号错误');
        }
        $sign = $_REQUEST['sign'];
        $trueSign = md5('zUBh39LzftHBMS9d'.$sid.$num.date('Ymd'));
        if($sign != $trueSign){
            //var_dump($trueSign);
            die('授权错误，请返回重新进入');
        }
        //登录
        $user = service('Passport')->outLogin($num, $sid);
        if(!$user){
            die(service('Passport')->getLastError());
        }
        //跳转
        $refer_url = U('home/User/index');
        if(isset($_SESSION['mid'])){
            if (!$user['is_init']) {
                $this->assign('jumpUrl', U('home/Public/userinfo'));
                $this->error('请先完善个人资料');
                exit;
            }
            if ($_SESSION['md5pass']) {
                $this->assign('jumpUrl', U('home/Account/security'));
                $this->error('系统安全策略升级，您的账号存在安全隐患，请修改密码。');
                exit;
            }
            if($user['schoolEvent']['domain'])
                $refer_url = getDomainLink($user['schoolEvent']['domain']);
        }
        redirect($refer_url);
    }

    //安卓客户端下载页面
    public function android(){
        $setting = M('system_data')->getField('value',"`list` = 'android' AND `key` = 'androidUrl'");
        if($setting){
            $url = unserialize($setting);
        }else{
            $url = 'http://pocketuni.net/pu.apk';
        }
        Header("Location: " . $url);
        die;
    }


    public function checkFlash(){
        $link = t($_REQUEST['link']);
        if($link){
            $parseLink = parse_url($link);
            if ($this->eventId != 12239 && !preg_match("/(youku.com|ku6.com|sina.com.cn|yixia.com|t.cn)$/i", $parseLink['host'], $hosts)) {
                echo 0;exit;
            }
            if ($this->eventId == 12239 && !preg_match("/(yixia.com|t.cn)$/i", $parseLink['host'], $hosts)) {
                echo 0;exit;
            }
        }
        echo 1;exit;
    }

    public function uploadImg(){
        $this->assign(model('Xdata')->lget('photo'));
        if(isset($_REQUEST['maximg'])){
            $this->assign('max_flash_upload_num',  intval($_REQUEST['maximg']));
        }
        $w = isset($_REQUEST['w'])?$_REQUEST['w']:80;
        $h = isset($_REQUEST['h'])?$_REQUEST['h']:80;
        $this->assign('w', $w);
        $this->assign('h', $h);
        $this->display();
    }
    //执行单张图片上传
    public function upload_single_pic(){
        $config = model('Xdata')->lget('photo');
        $options['max_size'] = $config['photo_max_size']*1024*1024;
        $options['allow_exts'] = $config['photo_file_ext'];
        $options['save_to_db'] = false;
        //执行上传操作
        $info = X('Xattach')->upload('public', $options);
        $w = isset($_REQUEST['w'])?$_REQUEST['w']:80;
        $h = isset($_REQUEST['h'])?$_REQUEST['h']:80;
        if ($info['status']) {
            $res['status'] = true;
            $res['type'] = 'img';
            $src = tsMakeThumbUp($info['info'][0]['savepath'].$info['info'][0]['savename'],$w,$h,'f');
            $res['src'] = $src;
            //$res['id'] = $info['info'][0]['id'];
            $res['img'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        }else{
            $res['status'] = false;
            $res['info'] = $info['info'];
        }
        echo json_encode($res);
    }
    public function uploadFile(){
        $this->assign(model('Xdata')->lget('attach'));
        if(isset($_REQUEST['maxfile'])){
            $this->assign('max_flash_upload_num',  intval($_REQUEST['maxfile']));
        }else{
            $this->assign('max_flash_upload_num',  5);
        }
        $fileId = isset($_REQUEST['fileId'])?$_REQUEST['fileId']:1;
        $this->assign('fileId', $fileId);
        $this->display();
    }
    //执行单张图片上传
    public function upload_single_file(){
        $config = model('Xdata')->lget('attach');
        $options['max_size'] = $config['attach_max_size']*1024*1024;
        $options['allow_exts'] = $config['attach_allow_extension'];
        $options['save_to_db'] = true;
        //执行上传操作
        $info = X('Xattach')->upload('public', $options);
        if ($info['status']) {
            $res['status'] = true;
            $res['type'] = 'file';
            $res['fileId'] = $_REQUEST['fileId'];
            $res['attachId'] = $info['info'][0]['id'];
            $res['src'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            $res['file'] = $info['info'][0]['name'];
        }else{
            $res['status'] = false;
            $res['info'] = $info['info'];
        }
        echo json_encode($res);
    }

    public function bmb(){
        if(!$this->mid){
            $this->error('请先登录');
        }
        if(!$this->user['can_admin']){
            $this->error('请用各校超管账号下载');
        }
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=五四杯羽毛球交流赛报名表.doc");
        echo file_get_contents(SITE_PATH . '/apps/event/Tpl/default/Public/bmb.doc');
        die;
    }

    public function cq(){
        if(!$this->mid){
            $this->error('请先登录');
        }
        if(!$this->user['can_admin']){
            $this->error('请用各校超管账号抽签');
        }
        $list = M('ymq')->where('sid='.$this->user['sid'])->find();
        if(!$list)
            $this->error('您所在学校没报名，不可抽签');
        if(isset($_GET['c']) && $list['status'] == 0){
            M('ymq')->setField('status', 1, 'sid='.$this->user['sid']);
            $list['status'] = 1;
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function cqjg(){
        $list = M('ymq')->where('status=1')->findAll();
        $this->assign('list', $list);
        $this->display();
    }

    public function adminlogin() {
        if (service('Passport')->isLoggedAdmin()) {
            redirect(U('admin/Index/index'));
        }

        $this->display();
    }

    //删除用户
    public function delete() {
        for ($i = 1; $i < 10000; $i++) {
            $res = D('User', 'home')->deleteUser($i);
        }
    }

    public function doAdminLogin() {
        // 检查验证码
        if (md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }

        // 数据检查
        if (empty($_POST['password'])) {
            $this->error(L('password_notnull'));
        }
        if (isset($_POST['email']) && !isValidEmail($_POST['email'])) {
            $this->error(L('email_format_error'));
        }

        // 检查帐号/密码
        $is_logged = false;
        if (isset($_POST['email'])) {
            $is_logged = service('Passport')->loginAdmin($_POST['email'], $_POST['password']);
        } else if ($this->mid > 0) {
            $is_logged = service('Passport')->loginAdmin($this->mid, $_POST['password']);
        } else {
            $this->error(L('parameter_error'));
        }

        // 提示消息不显示头部
        $this->assign('isAdmin', '1');

        if ($is_logged) {
            redirect(U('admin/Index/index'));
        } else {
            $this->assign('jumpUrl', U('home/Public/adminlogin'));
            $this->error(L('login_error'));
        }
    }

    public function login() {
        if (service('Passport')->isLogged())
            U('home', '', true);

        unset($_SESSION['sina'], $_SESSION['key'], $_SESSION['douban'], $_SESSION['qq'], $_SESSION['open_platform_type']);

        //验证码
        $opt_verify = $this->_isVerifyOn('login');
        if ($opt_verify) {
            $this->assign('login_verify_on', $opt_verify);
        }

        $data['email'] = t($_REQUEST['email']);
        $data['uid'] = t($_REQUEST['uid']);
        $uids = array();


        // 正在热议 1小时缓存
        $data['hot_topic'] = D('Topic', 'weibo')->getHot();

        // 人气推荐
        $data['hot_user'] = D('Follow', 'weibo')->getTopFollowerUser(0, 6);
        $uids = array_merge($uids, getSubByKey($data['hot_user'], 'uid'));

        // 正在发生 (原创的文字微博)
        $data['lastest_weibo'] = D('Operate', 'weibo')->getLastWeibo();
        $uids = array_merge($uids, getSubByKey($data['lastest_weibo'], 'uid'));
        $this->assign('since_id', empty($data['lastest_weibo']) ? 0 : $data['lastest_weibo'][0]['weibo_id'] );

        // 原创的图片微博
        $data['pic_weibo'] = S('S_login_pic_weibo');
        if (empty($data['pic_weibo'])) {
            $map['transpond_id'] = 0;
            $map['type'] = 1;
            $data['pic_weibo'] = D('Operate', 'weibo')->where($map)->order('weibo_id DESC')->limit(3)->findAll();
            S('S_login_pic_weibo', $data['pic_weibo'], 3600);
        }
        $uids = array_merge($uids, getSubByKey($data['pic_weibo'], 'uid'));
        foreach ($data['pic_weibo'] as $k => $v) {
            $imageData = unserialize($v['type_data']);
            if (isset($imageData[0])) {
                $data['pic_weibo'][$k]['type_data'] = $imageData[0];
            } else {
                $data['pic_weibo'][$k]['type_data'] = $imageData;
            }
        }

        D('User', 'home')->setUserObjectCache(array_flip(array_flip($uids)));
        // 第三方平台

        $this->assign($data);
        $this->assign('regInfo', model('Xdata')->lget('register'));
        $this->setTitle(L('login'));
        //公告列表
        $map['isDel'] = 0;
        $noticeList = M('notice')->where($map)->field('id,title,front')->order('id DESC')->limit(4)->findAll();
        $this->assign('noticeList', $noticeList);
        $this->assign('adhome', getAd(0,0, 0, 5));
        $this->assign('adleft', getAd(0,0, 1, 1));
        $this->display('login1305');
    }

    function displayAddons() {
        $result = array();
        $param['res'] = &$result;
        $param['type'] = $_REQUEST['type'];
        Addons::addonsHook($_GET['addon'], $_GET['hook'], $param);
        isset($result['url']) && $this->assign("jumpUrl", $result['url']);
        isset($result['title']) && $this->setTitle($result['title']);
        isset($result['jumpUrl']) && $this->assign('jumpUrl', $result['jumpUrl']);
        if (isset($result['status']) && !$result['status']) {
            $this->error($result['info']);
        }
        if (isset($result['status']) && $result['status']) {
            $this->success($result['info']);
        }
    }

    //第三方登录页面显示
    function tryOtherLogin() {
        if (!in_array($_GET['type'], array('sina', 'douban', 'qq'))) {
            $this->error(L('parameter_error'));
        }
        include_once(SITE_PATH . "/addons/plugins/Login/lib/{$_GET['type']}.class.php");
        $platform = new $_GET['type']();
        redirect($platform->getUrl());
    }

    // 腾讯回调地址
    public function qqcallback() {
        include_once( SITE_PATH . '/addons/plugins/Login/lib/qq.class.php' );
        $qq = new qq();
        $qq->checkUser();
        redirect(U('home/Public/otherlogin'));
    }

    //外站帐号登录
    public function otherlogin() {
        if (!in_array($_SESSION['open_platform_type'], array('sina', 'douban', 'qq'))) {
            $this->error(L('not_authorised'));
        }

        $type = $_SESSION['open_platform_type'];
        include_once( SITE_PATH . "/addons/plugins/Login/lib/{$type}.class.php" );
        $platform = new $type();
        $userinfo = $platform->userInfo();
        // 检查是否成功获取用户信息
        if (empty($userinfo['id']) || empty($userinfo['uname'])) {
            $this->assign('jumpUrl', SITE_URL);
            $this->error(L('user_information_filed'));
        }
        if ($info = M('login')->where("`type_uid`='" . $userinfo['id'] . "' AND type='{$type}'")->find()) {
            $user = M('user')->where("uid=" . $info['uid'])->find();
            if (empty($user)) {
                // 未在本站找到用户信息, 删除用户站外信息,让用户重新登录
                M('login')->where("type_uid=" . $userinfo['id'] . " AND type='{$type}'")->delete();
            } else {
                if ($info['oauth_token'] == '') {
                    $syncdata['login_id'] = $info['login_id'];
                    $syncdata['oauth_token'] = $_SESSION[$type]['access_token']['oauth_token'];
                    $syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
                    M('login')->save($syncdata);
                }

                service('Passport')->registerLogin($user);

                redirect(U('home/User/index'));
            }
        }
        $this->assign('user', $userinfo);
        $this->assign('type', $type);
        $this->setTitle(L('third_party_account_login'));
        $this->display();
    }

    // 激活外站登录
    public function initotherlogin() {
        if (!in_array($_POST['type'], array('douban', 'sina', 'qq'))) {
            $this->error(L('parameter_error'));
        }


        if (!isLegalUsername(t($_POST['uname']))) {
            $this->error(L('nickname_format_error'));
        }

        $haveName = M('User')->where("`uname`='" . t($_POST['uname']) . "'")->find();
        if (is_array($haveName) && sizeof($haveName) > 0) {
            $this->error(L('nickname_used'));
        }

        $type = $_POST['type'];
        include_once( SITE_PATH . "/addons/plugins/Login/lib/{$type}.class.php" );
        $platform = new $type();
        $userinfo = $platform->userInfo();

        // 检查是否成功获取用户信息
        if (empty($userinfo['id']) || empty($userinfo['uname'])) {
            $this->assign('jumpUrl', SITE_URL);
            $this->error(L('create_user_information_failed'));
        }

        // 检查是否已加入本站
        $map['type_uid'] = $userinfo['id'];
        $map['type'] = $type;
        if (($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid=' . $local_uid)->find())) {
            $this->assign('jumpUrl', SITE_URL);
            $this->success(L('you_joined'));
        }
        // 初使化用户信息, 激活帐号
        $data['uname'] = t($_POST['uname']) ? t($_POST['uname']) : $userinfo['uname'];
        //$data['province'] = intval($userinfo['province']);
        //$data['city'] = intval($userinfo['city']);
        //$data['location'] = $userinfo['location'];
        $data['sex'] = intval($userinfo['sex']);
        $data['is_active'] = 1;
        $data['is_init'] = 1;
        $data['ctime'] = time();
        $data['is_synchronizing'] = ($type == 'sina') ? '1' : '0'; // 是否同步新浪微博. 目前仅能同步新浪微博

        if ($id = M('user')->add($data)) {
            // 记录至同步登录表
            $syncdata['uid'] = $id;
            $syncdata['type_uid'] = $userinfo['id'];
            $syncdata['type'] = $type;
            $syncdata['oauth_token'] = $_SESSION[$type]['access_token']['oauth_token'];
            $syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
            M('login')->add($syncdata);

            // 转换头像
            if ($_POST['type'] != 'qq') { // 暂且不转换QQ头像: QQ头像的转换很慢, 且会拖慢apache
                D('Avatar')->saveAvatar($id, $userinfo['userface']);
            }

            // 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
            $userlog = array(
                'uid' => $id,
                'action' => 'add',
                'type' => '0',
                'dateline' => time(),
            );
            M('myop_userlog')->add($userlog);

            service('Passport')->loginLocal($id);

            $this->registerRelation($id);

            redirect(U('home/Public/followuser'));
        } else {
            $this->error('account_sync_error');
        }
    }

    public function bindaccount() {
        if (!in_array($_POST['type'], array('douban', 'sina', 'qq'))) {
            $this->error(L('parameter_error'));
        }

        $psd = ($_POST['passwd']) ? $_POST['passwd'] : true;
        $type = $_POST['type'];

        if ($user = service('Passport')->getLocalUser($_POST['email'], $psd)) {
            include_once( SITE_PATH . "/addons/plugins/Login/lib/{$type}.class.php" );
            $platform = new $type();
            $userinfo = $platform->userInfo();

            // 检查是否成功获取用户信息
            if (empty($userinfo['id']) || empty($userinfo['uname'])) {
                $this->assign('jumpUrl', SITE_URL);
                $this->error(L('user_information_filed'));
            }

            // 检查是否已加入本站
            $map['type_uid'] = $userinfo['id'];
            $map['type'] = $type;
            if (($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid=' . $local_uid)->find())) {
                $this->assign('jumpUrl', SITE_URL);
                $this->success(L('you_joined'));
            }

            $syncdata['uid'] = $user['uid'];
            $syncdata['type_uid'] = $userinfo['id'];
            $syncdata['type'] = $type;
            if (M('login')->add($syncdata)) {
                service('Passport')->registerLogin($user);

                $this->assign('jumpUrl', U('home/User/index'));
                $this->success(L('bind_success'));
            } else {
                $this->assign('jumpUrl', SITE_URL);
                $this->error(L('bind_error'));
            }
        } else {
            $this->error(L('wrong_account'));
        }
    }

    //
    public function callback() {
        include_once( SITE_PATH . '/addons/plugins/Login/lib/sina.class.php' );
        $sina = new sina();
        $sina->checkUser();
        redirect(U('home/public/otherlogin'));
    }

    public function doubanCallback() {
        if (!isset($_GET['oauth_token'])) {
            $this->error('Error: No oauth_token detected.');
            exit;
        }
        require_once SITE_PATH . '/addons/plugins/Login/lib/douban.class.php';
        $douban = new douban();
        if ($douban->checkUser($_GET['oauth_token'])) {
            redirect(U('home/Public/otherlogin'));
        } else {
            $this->assign('jumpUrl', SITE_URL);
            $this->error(L('checking_failed'));
        }
    }

    public function doLogin() {
        // 检查验证码
        $opt_verify = $this->_isVerifyOn('login');
        if ($opt_verify && md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }
        //选择学校登录
        $username = '';
        if(!$_POST['sid']){
            $this->error('请选择学校');
        }
        if(!$_POST['number']){
            $this->error('请输入学号');
        }
        if(!$_POST['password']){
            $this->error('请输入登录密码');
        }
        $map['id'] = intval($_POST['sid']);
        $suffix = M('school')->where($map)->find();
        if(!$suffix){
            $this->error('该学校尚未开通');
        }
        $username = t($_POST['number']) . $suffix['email'];
//        if (isset($_POST['sid'][0]) && isset($_POST['number'][0])) {
//            $map['id'] = intval($_POST['sid']);
//            $suffix = M('school')->where($map)->find();
//            if ($suffix && $suffix['email'] != '') {
//                $username = t($_POST['number']) . $suffix['email'];
//            }
//        } else {
//            $username = $_POST['email'];
//        }
        //
        $password = $_POST['password'];

        if (!$password) {
            $this->error(L('please_input_password'));
        }
        $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));

        //检查是否激活
        if (!$result && service('Passport')->getLastError() == '用户未激活') {
            $this->assign('jumpUrl', U('home/public/login'));
            $this->error('该用户尚未激活，请更换帐号或激活帐号！');
            exit;
        }

        if ($result) {
//            if (UC_SYNC && $result['reg_from_ucenter']) {
//                //从UCenter导入ThinkSNS，跳转至帐号修改页
//                $refer_url = U('home/Public/userinfo');
//            } elseif ($_SESSION['refer_url'] != '') {
//                //跳转至登录前输入的url
//                $refer_url = $_SESSION['refer_url'];
//                unset($_SESSION['refer_url']);
//            } else {
//                $refer_url = U('home/User/index');
//            }
            $refer_url = U('home/User/index');
            if(isset($_SESSION['mid'])){
                $user = D('User', 'home')->getUserByIdentifier($_SESSION['mid'], 'uid');
                if (!$user['is_valid']) {
                    $this->assign('jumpUrl', U('home/Public/userinfo0'));
                    $this->error('请先身份验证');
                    exit;
		}
                if (!$user['is_init']) {
                    $this->assign('jumpUrl', U('home/Public/userinfo'));
                    $this->error('请先完善个人资料');
                    exit;
		}
                if ($_SESSION['md5pass']) {
                    $this->assign('jumpUrl', U('home/Account/security'));
                    $this->error('系统安全策略升级，您的账号存在安全隐患，请修改密码。');
                    exit;
		}
                if($user['schoolEvent']['domain'])
                    $refer_url = getDomainLink($user['schoolEvent']['domain']);
            }
            redirect($refer_url);
        } else {
            $this->error(L('login_error'));
        }
    }

    public function doAjaxLogin() {
        // 检查验证码
        $opt_verify = $this->_isVerifyOn('login');
        if ($opt_verify && md5($_POST['verify']) != $_SESSION['verify']) {
            $return['message'] = L('error_security_code');
            $return['status'] = 0;
            exit(json_encode($return));
        }

        //选择学校登录
        $username = '';
        if (isset($_POST['sid']) && isset($_POST['number'][0])) {
            $map['id'] = intval($_POST['sid']);
            $suffix = M('school')->where($map)->find();
            if ($suffix && $suffix['email'] != '') {
                $username = $_POST['number'] . $suffix['email'];
            }
        }
        $password = $_POST['password'];

        if (!$password) {
            $return['message'] = L('password_notnull');
            $return['status'] = 0;
            exit(json_encode($return));
        }

        $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']) === 1);

        //检查是否激活
        if (!$result && service('Passport')->getLastError() == '用户未激活') {
            $return['message'] = L('account_inactive');
            $return['status'] = 0;
            exit(json_encode($return));
        }
        if (!$result) {
            $return['message'] = L('account_login_failed');
            $return['status'] = 0;
            exit(json_encode($return));
        }
        if ($_SESSION['md5pass']) {
            $return['status'] = 2;
            exit(json_encode($return));
        }
        $return['message'] = L('login_success');
        $return['status'] = 1;

        exit(json_encode($return));
    }

    public function logout() {
//        service('Passport')->logoutLocal();
//        $this->assign('jumpUrl', U('home/index'));
//        $this->success(L('exit_success') . ( (UC_SYNC) ? uc_user_synlogout() : '' ));

        service('Passport')->logoutLocal();
        $domain = parse_url($_SERVER['SERVER_NAME']);
        $hostNeedle = get_host_needle();
        if(!isHostNeedle()){
            $url = "http://" . $hostNeedle . "/index.php?app=home&mod=Public&act=logout2&back=" . $hostNeedle;
        }else{
            $schoolDomain = getDomainLink($this->user['schoolEvent']['domain']);
            if ('http://' . $domain != $schoolDomain) {
                $url = $schoolDomain . "/index.php?app=home&mod=Public&act=logout2&back=" . $hostNeedle;
            } else {
                $url = U('home/Public/login');
            }
        }
        redirect($url);
    }

    public function logout2() {
        service('Passport')->logoutLocal();
        Header("Location: http://" . $_GET['back']);
    }

    public function logoutAdmin() {
        // 成功消息不显示头部
        $this->assign('isAdmin', '1');

        service('Passport')->logoutLocal();
        redirect(U('home/Public/adminlogin'));
    }

    public function logoutPu() {
        // 成功消息不显示头部
        $this->assign('isAdmin', '1');

        service('Passport')->logoutPu();
        redirect(U('home/Public/login'));
    }

    private function __getInviteInfo($invite_code) {
        $res = null;
        $invite_option = model('Invite')->getSet();
        switch (strtolower($invite_option['invite_set'])) {
            case 'close':
                $res = null;
                break;
            case 'common':
                $res = D('User', 'home')->getUserByIdentifier($invite_code, 'uid');
                break;
            case 'invitecode':
                $res = model('Invite')->checkInviteCode($invite_code);
                if ($res['is_used'])
                    $res = null;
                break;
        }

        return $res;
    }

    public function isRegisterOpen() {
        return strtolower(model('Xdata')->get('register:register_type')) == 'open';
    }

    public function isRegisterAvailable() {
        echo $this->isRegisterOpen() ? '1' : '0';
    }

    public function register() {
        //验证码
        $opt_verify = $this->_isVerifyOn('register');
        if ($opt_verify)
            $this->assign('register_verify_on', 1);

        // 邀请码
        $invite_code = h($_REQUEST['invite']);
        $invite_info = null;

        // 是否开放注册
        $register_option = model('Xdata')->get('register:register_type');
        if ($register_option == 'closed') { // 关闭注册
            $this->error(L('reg_close'));
        } else if ($register_option == 'invite') { // 邀请注册
            // 邀请方式
            $invite_option = model('Invite')->getSet();
            if ($invite_option['invite_set'] == 'close') { // 关闭邀请
                $this->error(L('reg_invite_close'));
            } else { // 普通邀请 OR 使用邀请码
                if (!$invite_code)
                    $this->error(L('reg_invite_warming'));
                else if (!($invite_info = $this->__getInviteInfo($invite_code)))
                    $this->error(L('reg_invite_code_error'));
            }
        } else { // 公开注册
            if (!($invite_info = $this->__getInviteInfo($invite_code)))
                unset($invite_code, $invite_info);
        }

        $this->assign('invite_info', $invite_info);
        $this->assign('invite_code', $invite_code);
        $this->setTitle(L('reg'));
        $this->display();
    }

    public function doRegister() {
die;
        // 验证码
        $verify_option = $this->_isVerifyOn('register');
        if ($verify_option && md5($_POST['verify']) != $_SESSION['verify'])
            $this->error(L('error_security_code'));

        // 邀请码
        $invite_code = h($_REQUEST['invite_code']);
        $invite_info = null;


        // 是否允许注册
        $register_option = model('Xdata')->get('register:register_type');
        if ($register_option === 'closed') { // 关闭注册
            $this->error(L('reg_close'));
        } else if ($register_option === 'invite') { //邀请注册
            // 邀请方式
            $invite_option = model('Invite')->getSet();
            if ($invite_option['invite_set'] == 'close') { // 关闭邀请
                $this->error(L('reg_invite_close'));
            } else { // 普通邀请 OR 使用邀请码
                if (!$invite_code)
                    $this->error(L('reg_invite_warming'));
                else if (!($invite_info = $this->__getInviteInfo($invite_code)))
                    $this->error(L('reg_invite_code_error'));
            }
        } else { // 公开注册
            if (!($invite_info = $this->__getInviteInfo($invite_code)))
                unset($invite_code, $invite_info);
        }
        if (empty($_POST['sid'])) {
            $this->error('请选择学校');
        }
        if (empty($_POST['number'])) {
            $this->error('请输入学号');
        }
        if (strpos($_POST['number'], '@')) {
            $this->error('输入学号而不是邮箱');
        }
        //选择学校注册
        $map['id'] = intval($_POST['sid']);
        $map['canRegister'] = 1;
        $suffix = M('school')->where($map)->find();
        if ($suffix && $suffix['email'] != '') {
            $_POST['email'] = $_POST['number'] . $suffix['email'];
        } else {
            $this->error('此学校不公开注册，账号已完成批量导入，请用学号登录');
        }

        //
        // 参数合法性检查
        $required_field = array(
            'email' => 'Email',
            'nickname' => L('username'),
            'password' => L('password'),
            'repassword' => L('retype_password'),
        );
        foreach ($required_field as $k => $v)
            if (empty($_POST[$k]))
                $this->error($v . L('not_null'));

        if (!$this->isValidEmail($_POST['email']))
            $this->error(L('email_format_error_retype'));
        if (!$this->isValidNickName($_POST['nickname']))
            $this->error(L('username_format_error'));
        if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'])
            $this->error(L('password_rule'));
        if (!$this->isEmailAvailable($_POST['email']))
            $this->error(L('email_used_retype'));



        // 是否需要Email激活
        $need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));

        // 注册
        $data['sid'] = $_POST['sid'];
        $data['email'] = $_POST['email'];
        $data['password'] = codePass($_POST['password']);
        $data['uname'] = t($_POST['nickname']);
        $data['realname'] = $data['uname'];
        $data['ctime'] = time();
        $data['is_active'] = $need_email_activate ? 0 : 1;

        if (!($uid = D('User', 'home')->add($data)))
            $this->error(L('reg_filed_retry'));

        // 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
        $user_log = array(
            'uid' => $uid,
            'action' => 'add',
            'type' => '0',
            'dateline' => time(),
        );
        M('myop_userlog')->add($user_log);

        // 将邀请码设置已用
        model('Invite')->setInviteCodeUsed($invite_code);

        // 同步至UCenter
        if (UC_SYNC) {

            $uc_uid = uc_user_register($_POST['nickname'], $_POST['password'], $_POST['email']);
            //echo uc_user_synlogin($uc_uid);
            if ($uc_uid > 0)
                ts_add_ucenter_user_ref($uid, $uc_uid, $data['uname']);
        }

        if ($need_email_activate == 1) { // 邮件激活
            $this->activate($uid, $_POST['email'], $invite_code);
        } else {
            // 置为已登录, 供完善个人资料时使用
            service('Passport')->loginLocal($uid);

            // 缓存邀请信息, 供完善个人资料后使用
            //$_SESSION["invite_info_{$uid}"] = $invite_info;
            //if (!is_numeric(stripos($_POST['HTTP_REFERER'], dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']))) && $register_option != 'invite') {
            //注册完毕，跳回注册页之前
            //redirect($_POST['HTTP_REFERER']);
            //} else {
            //注册完毕，跳转至帐号修改页
            redirect(U('home/Public/userinfo'));
            //}
        }
    }


    public function a($uid, $email, $invite = '', $is_resend = 0) {
        //设置激活路径
        $activate_url = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));

        //设置邮件模板
        $body = <<<EOD
感谢您的注册!<br>

请马上点击以下注册确认链接，激活您的帐号！<br>

<a href="$activate_url" target='_blank'>$activate_url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

默认密码为：123456,激活后请第一时间修改。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
        // 发送邮件
        global $ts;
        service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号", $body);
    }

    public function mobileCode() {
        $mobile = t($_REQUEST['mobile']);
        if (!isValidMobile($mobile)) {
            echo -2;
            return;
        }
        $map['mobile'] = $mobile;
        $hasMobile = M('user')->where($map)->field('uid')->find();
        if($hasMobile && $hasMobile['uid']!=$this->mid){
            echo -5; exit;
        }
        $res = D('UserMobile','home')->addRow($this->mid,$mobile);
        echo $res; exit;
    }

    public function emailCode() {
        $email = t($_POST['email']);
        if (!isValidEmail($email)) {
            echo -2;
            return;
        }
        $map['email2'] = $email;
        $hasMobile = M('user')->where($map)->field('uid')->find();
        if($hasMobile && $hasMobile['uid']!=$this->mid){
            echo -5; exit;
        }
        $res = D('UserMobile','home')->addRowMail($this->mid,$email);
        echo $res; exit;
    }

    public function mobileBind() {
        $code = intval($_POST['code']);
        $mobile = t($_POST['mobile']);
        $res = D('UserMobile','home')->bind($this->mid,$mobile,$code);
        if($res){
            echo 1; exit;
        }
        echo 0; exit;
    }

    public function emailBind() {
        $code = intval($_POST['code']);
        $email = t($_POST['email']);
        $res = D('UserMobile','home')->bindEmail($this->mid,$email,$code);
        if($res){
            echo 1; exit;
        }
        echo 0; exit;
    }
    //身份验证
    public function userinfo0() {
        if (!$this->mid)
            redirect(U('home/Public/login'));
        // 已初始化的用户, 不允许在此修改资料
        if ($this->user['is_valid'] && $this->user['is_init']){
            //redirect(U('home/User/index'));
        }
        $this->assign('restSec',D('UserMobile','home')->getRestSec($this->mid));
        $this->setTitle('身份验证');
        $this->display();

    }

    // 完善个人资料
    public function userinfo() {
        if (!$this->mid)
            redirect(U('home/Public/login'));

        // 已初始化的用户, 不允许在此修改资料
        //global $ts;
        //if (!$this->user['is_valid'])
            //redirect(U('home/Public/userinfo0'));
        if ($this->user['is_init'])
            redirect(U('home/User/index'));
        if ($_POST) {
            if(md5($_POST['password']) == $this->user['password'] || codePass($_POST['password']) == $this->user['password']){
                $this->error('密码必须改掉，不可跟原始密码一样！');
            }
            //jun  邮箱验证
            $data['email2'] = t($_POST['email2']);
            $this->check_email( $data['email2']);
            $email=$data['email2'];
            //$this->check_email($email);
            $res=M('user')->where("`email2`= '$email'")->field('uid')->find();
            if($res && $res['uid'] != $this->mid){
                $this->error('该邮箱已被使用');
            }
            //jun 密码判断
            if (strlen($_REQUEST['password']) < 6 || strlen($_REQUEST['password']) > 16) {
                $this->error("密码格式有误, 合法的密码为6-16位字符");
            }
            if ($_REQUEST['password'] != $_REQUEST['repassword']) {
                $this->error("密码两次输入不一样");
            }

           $data['password'] = codePass($_POST['password']);
            //------------------
            //if (!$user_info['uname']) {
                if (!$this->isValidNickName($_POST['nickname']))
                    $this->error(L('nickname_format_error_or_used'));
                else
                    $data['uname'] = t($_POST['nickname']);
            //}
            $mobile = t($_POST['mobile']);
            if($mobile){
                if(strlen($mobile)!=11){
                    $this->error("手机号码格式不对");
                }
                $hasMobile = M('user')->where("mobile='$mobile'")->field('uid')->find();
                if($hasMobile && $hasMobile['uid']!=$this->mid){
                    $this->error('该手机号码已被使用');
                }
            }
            $data['mobile'] = $mobile;
            $data['sex'] = intval($_POST['sex']);
            $data['is_init'] = 1;
            M('user')->where("uid={$this->mid}")->data($data)->save();

            // 关联操作
            $this->registerRelation($this->mid);
            unset($_SESSION["invite_info_{$this->mid}"]);
            $_SESSION['md5pass'] = false;
            S('S_userInfo_' . $this->mid, null);

            redirect(U('home/User/index'));
        } else {
            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            $this->assign('nickname', $user_info['uname']);
            //$this->assign('year', $user_info['year']);
            //$this->assign('major', $user_info['major']);
            $sid = $user_info['sid'];
            if ($sid && $user_info['sid1']) {
                $this->assign('sids', $user_info['sid1']);
                $this->assign('sName', tsGetSchoolName($user_info['sid1']));
            }
            $this->assign('editSid', $sid);
            $this->setTitle(L('complete_information'));
            $this->display();
        }
    }

    //关注推荐用户
    public function followuser() {
        if ($_POST) {
            if ($_POST['followuid']) {
                foreach ($_POST['followuid'] as $value) {
                    D('Follow', 'weibo')->dofollow($this->mid, $value, 0);
                }
            }
            if ($_POST['doajax']) {
                echo '1';
            } else {
                redirect(getUrlDomain());
            }
        } else {
            $users = D('Follow', 'weibo')->getTopFollowerUser($this->mid, 12);
            $user_model = D('User', 'home');
            $user_model->setUserObjectCache(getSubByKey($users, 'uid'));
            foreach ($users as $k => $v) {
                $user = $user_model->getUserByIdentifier($v['uid'], 'uid');
                $users[$k]['uname'] = $user['uname'];
                //$users[$k]['location'] = $user['location'];
            }

            $this->assign('users', $users);
            $this->setTitle(L('recommend_user'));
            $this->display();
        }
    }

    //使用邀请码注册
    public function inviteRegister() {
        if (!$invite = service('Validation')->getValidation()) {
            $this->error(L('reg_invite_code_error'));
        }

        if ("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] != $invite['target_url']) {
            $this->error(L('url_error'));
        }
        $this->assign('invite', $invite);

        $invite['data'] = unserialize($invite['data']);
        $map['tpl_record_id'] = $invite['data']['tpl_record_id'];
        $tpl_record = model('Template')->getTemplateRecordByMap($map, '', 1);
        $tpl_record = $tpl_record['data'][0]['data'];
        $this->assign('template', $tpl_record);

        //邀请人的好友
        $friend = model('Friend')->getFriendList($invite['from_uid'], null, 9);
        $this->assign($friend);

        $this->display('invite');
    }

    public function resendEmail() {
        $invite = service('Validation')->getValidation();
        $this->activate(intval($_GET['uid']), $_GET['email'], $invite, 1);
    }

    //发送激活邮件
    public function activate($uid, $email, $invite = '', $is_resend = 0) {
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
        $email_sent = service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号", $body);

        // 渲染输出
        if ($email_sent) {
            $email_info = explode("@", $email);
            switch ($email_info[1]) {
                case "qq.com" : $email_url = "mail.qq.com";
                    break;
                case "163.com" : $email_url = "mail.163.com";
                    break;
                case "126.com" : $email_url = "mail.126.com";
                    break;
                case "gmail.com" : $email_url = "mail.google.com";
                    break;
                default : $email_url = "mail." . $email_info[1];
            }

            $this->assign("uid", $uid);
            $this->assign('email', $email);
            $this->assign('is_resend', $is_resend);
            $this->assign("email_url", $email_url);
            $this->display('activate');
        } else {
            $this->assign('jumpUrl', U('home/Index/index'));
            $this->error(L('email_send_error_retry'));
        }
    }

    public function doActivate() {
        $invite = service('Validation')->getValidation();
        if (!$invite) {
            $this->assign('jumpUrl', U('home/Public/register'));
            $this->error(L('activation_code_error_retry'));
        }
        $uid = $invite['from_uid'];

        $user = M('user')->where("`uid`=$uid")->find();
        if ($user['is_active'] == 1) {
            $this->assign('jumpUrl', U('home/Public/login'));
            $this->success(L('account_activity'));
            exit;
        } else if ($user['is_active'] == 0) {
            //激活帐户
            $res = M('user')->where("`uid`=$uid")->setField('is_active', 1);
            if (!$res)
                $this->error(L('activation_failed'));

            service('Passport')->registerLogin($user);

            //关联操作
            $this->registerRelation($user['uid'], $invite);

            service('Validation')->unsetValidation();

            $this->assign('jumpUrl', U('home/Account/index'));
            $this->success(L("activation_success"));
        } else {
            $this->assign('jumpUrl', U('home/Public/register'));
            $this->error(L('activation_code_error_retry'));
        }
    }

    public function sendPassword() {
        //$this->_checkEmailTime();
        $this->display();
    }

    private function _checkEmailTime(){
        if(isset($_SESSION['forgetPass'])){
            if($_SESSION['forgetPassTime']+300 > time()){
                $this->error('邮件已发送，5分钟内不可重复发送！');
            }
        }
    }

    public function doSendPassword() {
        $this->_checkEmailTime();
        $_POST["email"] = t($_POST["email"]);
        if (!$this->isValidEmail($_POST['email']))
            $this->error(L('email_format_error'));

        $user = M("user")->where('`email2`="' . $_POST['email'] . '"')->find();

        if (!$user) {
            $this->error(L("email_not_reg"));
        } else {
            $_SESSION['forgetPass'] = $_POST['email'];
            $_SESSION['forgetPassTime'] = time();
            $code = base64_encode($user["uid"] . "." . md5($user["uid"] . '+' . $user["password"]));
            $url = U('home/Public/resetPassword', array('code' => $code));
            $body = <<<EOD
<strong>{$user["uname"]}，你好: </strong><br/>

您只需通过点击下面的链接重置您的密码: <br/>

<a href="$url">$url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;

            global $ts;
            $email_sent = service('Mail')->send_email($user['email2'], L('reset') . "{$ts['site']['site_name']}" . L('password'), $body);

            if ($email_sent) {
                $this->assign('jumpUrl', SITE_URL);
                $this->success(L('send_you_mailbox') . $email . L('notice_accept'));
            } else {
                $this->error(L('email_send_error_retry'));
            }
        }
    }

    public function doSendPassword2() {
        $sid = intval($_POST['sid']);
        if($sid<1){
            $this->error('请选择学校');
        }
        $number = t($_POST['number']);
        if(!$number){
            $this->error('请输入学号');
        }
        $email2 = t($_POST['email2']);
        if(!$email2){
            $this->error('请输入密保邮箱');
        }
        $suffix = M('school')->getField('email', 'id='.$sid);
        if(!$suffix){
            $this->error('学校尚未开通');
        }
        $map['email'] = $number . $suffix;
        $user = M('user')->where($map)->field('uid,password,email2,mobile')->find();
        if(!$user){
            $this->error('账号不存在');
        }
        if($user['email2'] != $email2){
            $this->error('密保邮箱错误');
        }
        $mobile = t($_POST['mobile']);
        if($user['mobile'] && $user['mobile'] != $mobile){
            $this->error('绑定的手机错误');
        }
        $code = base64_encode($user["uid"] . "." . md5($user["uid"] . '+' . $user["password"]));
        $url = U('home/Public/resetPassword', array('code' => $code));
        redirect($url);die;
    }

    public function resetPassword() {
        $code = explode('.', base64_decode($_GET['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();

        if ($code[1] == md5($code[0] . '+' . $user["password"])) {
            $this->assign('email', $user["email"]);
            $this->assign('code', $_GET['code']);
            $this->display();
        } else {
            $this->error(L("link_error"));
        }
    }

    public function doResetPassword() {
        if ($_POST["password"] != $_POST["repassword"]) {
            $this->error(L("password_same_rule"));
        }
        if (strlen($_REQUEST['password']) < 6 || strlen($_REQUEST['password']) > 16) {
            $this->error("密码格式有误, 合法的密码为6-16位字符");
        }

        $code = explode('.', base64_decode($_POST['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();

        if ($code[1] == md5($code[0] . '+' . $user["password"])) {
            $data['password'] = codePass($_POST['password']);
            if($data['password'] == $user["password"]){
                $res = true;
            }else{
                $res = M('user')->where('uid='.$code[0])->save($data);
            }
            //去掉用户缓存信息
            if ($res) {
                S('S_userInfo_' . $code[0], null);
                $this->assign('jumpUrl', U('home/Public/login'));
                $this->success(L('save_success'));
            } else {
                $this->error(L('save_error_retry'));
            }
        } else {
            $this->error(L("safety_code_error"));
        }
    }

    public function doModifyEmail() {
        if (!$validation = service('Validation')->getValidation()) {
            $this->error(L('error_security_code'));
        }
        if ("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] != $validation['target_url']) {
            $this->error(L('url_error'));
        }

        $validation['data'] = unserialize($validation['data']);
        $map['uid'] = $validation['from_uid'];
        $map['email'] = $validation['data']['oldemail'];
        if (M('user')->where($map)->setField('email', $validation['data']['email'])) {
            service('Validation')->unsetValidation();
            service('Passport')->logoutLocal();
            $this->assign('jumpUrl', SITE_URL);
            $this->success(L('activate_new_email_success'));
        } else {
            $this->error(L('activate_new_email_failed'));
        }
    }

    //检查Email地址是否合法
    public function isValidEmail($email) {
        if (UC_SYNC) {
            $res = uc_user_checkemail($email);
            if ($res == -4) {
                return false;
            } else {
                return true;
            }
        } else {
            return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
        }
    }

    //检查Email是否可用
    public function isEmailAvailable($email = null) {
        $return_type = empty($email) ? 'ajax' : 'return';
        $email = empty($email) ? $_POST['email'] : $email;

        $res = M('user')->where('`email`="' . $email . '"')->find();
        if (UC_SYNC) {
            $uc_res = uc_user_checkemail($email);
            if ($uc_res == -5 || $uc_res == -6) {
                $res = true;
            }
        }

        if (!$res) {
            if ($return_type === 'ajax')
                echo 'success';
            else
                return true;
        }else {
            if ($return_type === 'ajax')
                echo L('email_used');
            else
                return false;
        }
    }

    //检查昵称是否符合规则，且是否为唯一

    public function isValidNickName($name) {
        $return_type = empty($name) ? 'ajax' : 'return';
        $name = empty($name) ? t($_POST['nickname']) : $name;

        //检查禁止注册的用户昵称
        $audit = model('Xdata')->lget('audit');
        if ($audit['banuid'] == 1) {
            $bannedunames = $audit['bannedunames'];
            if (!empty($bannedunames)) {
                $bannedunames = explode('|', $bannedunames);
                if (in_array($name, $bannedunames)) {
                    if ($return_type === 'ajax') {
                        echo '这个昵称进制注册';
                        return;
                    } else {
                        $this->error('这个昵称进制注册');
                    }
                }
            }
        }

        if (UC_SYNC) {
            $uc_res = uc_user_checkname($name);
            if ($uc_res == -1 || !isLegalUsername($name)) {
                if ($return_type === 'ajax') {
                    echo L('username_rule');
                    return;
                }
                else
                    return false;
            }
        } else if (!isLegalUsername($name)) {
            if ($return_type === 'ajax') {
                echo L('username_rule');
                return;
            }
            else
                return false;
        } else if (checkKeyWord($name)) {
            if ($return_type === 'ajax') {
                echo '昵称含有敏感词';
                return;
            } else {
                $this->error('昵称含有敏感词');
            }
        }

        if ($this->mid) {
            $res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
            $nickname = M('user')->getField('uname', "uid={$this->mid}");
            if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $nickname != $name) {
                $res = 1;
            }
        } else {
            $res = M('user')->where("uname='{$name}'")->count();
            if (UC_SYNC && ($uc_res == -2 || $uc_res == -3)) {
                $res = 1;
            }
        }

        if (!$res) {
            if ($return_type === 'ajax')
                echo 'success';
            else
                return true;
        }else {
            if ($return_type === 'ajax')
                echo L('username_used');
            else
                return false;
        }
    }

    //检查是否真实姓名。支持ajax和return
    public function isValidRealName($name = null, $opt_register = null) {
        $return_type = empty($name) ? 'ajax' : 'return';
        $name = empty($name) ? t($_POST['uname']) : $name;
        $opt_register = empty($opt_register) ? model('Xdata')->lget('register') : $opt_register;

        if ($opt_register['register_realname_check'] == 1) {
            $lastname = explode(',', $opt_register['register_lastname']);
            $res = in_array(substr($name, 0, 3), $lastname) || in_array(substr($name, 0, 6), $lastname);
        } else {
            $res = true;
        }

        if ($res) {
            if ($return_type === 'ajax')
                echo 'success';
            else
                return true;
        }else {
            if ($return_type === 'ajax')
                echo 'fail';
            else
                return false;
        }
    }

    // 注册的关联操作
    public function registerRelation($uid, $invite_info = null) {
        if (($uid = intval($uid)) <= 0)
            return;

        $dao = D('Follow', 'weibo');

        // 使用邀请码时, 建立与邀请人的关系
        if ($invite_info['uid']) {
            // 互相关注
            D('Follow', 'weibo')->dofollow($uid, $invite_info['uid']);
            D('Follow', 'weibo')->dofollow($invite_info['uid'], $uid);

            // 添加邀请记录
            model('InviteRecord')->addRecord($invite_info['uid'], $uid);

            //邀请人积分操作
            X('Credit')->setUserCredit($invite_info['uid'], 'invite_friend');
        }

        // 默认关注的好友
        $auto_freind = model('Xdata')->lget('register');
        $auto_freind['register_auto_friend'] = explode(',', $auto_freind['register_auto_friend']);
        foreach ($auto_freind['register_auto_friend'] as $v) {
            if (($v = intval($v)) <= 0)
                continue;
            $dao->dofollow($v, $uid);
            $dao->dofollow($uid, $v);
        }

        // 开通个人空间
        $data['uid'] = $uid;
        model('Space')->add($data);

        //注册成功 初始积分
        X('Credit')->setUserCredit($uid, 'init_default');
    }

    public function verify() {
        require_once(SITE_PATH . '/addons/libs/Image.class.php');
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        Image::buildImageVerify();
    }

    //上传图片
    public function uploadpic() {
        if ($_FILES['pic']) {
            //执行上传操作
            $savePath = $this->getSaveTempPath();
            $filename = md5(time() . 'teste') . '.' . substr($_FILES['pic']['name'], strpos($_FILES['pic']['name'], '.') + 1);
            if (@copy($_FILES['pic']['tmp_name'], $savePath . '/' . $filename) || @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath . '/' . $filename)) {
                $result['boolen'] = 1;
                $result['type_data'] = 'temp/' . $filename;
                $result['picurl'] = __UPLOAD__ . '/temp/' . $filename;
            } else {
                $result['boolen'] = 0;
                $result['message'] = L('upload_filed');
            }
        } else {
            $result['boolen'] = 0;
            $result['message'] = L('upload_filed');
        }

        exit(json_encode($result));
    }

    //上传临时文件
    public function getSaveTempPath() {
        $savePath = SITE_PATH . '/data/uploads/temp';
        if (!file_exists($savePath))
            mk_dir($savePath);
        return $savePath;
    }

    // 地区管理
    public function getArea() {
        echo json_encode(model('Area')->getAreaTree());
    }

    /**  文章  * */
    public function document() {
        $list = array();
        $detail = array();
        $res = M('document')->where('`is_active`=1')->order('`display_order` ASC,`document_id` ASC')->findAll();

        // 获取content为url且在页脚显示的文章
        global $ts;
        $ids_has_url = array();
        foreach ($ts['footer_document'] as $v)
            if (!empty($v['url']))
                $ids_has_url[] = $v['document_id'];

        $_GET['id'] = intval($_GET['id']);

        foreach ($res as $v) {
            // 不显示content为url且在页脚显示的文章
            if (in_array($v['document_id'], $ids_has_url))
                continue;

            $list[] = array('document_id' => $v['document_id'], 'title' => $v['title']);

            // 当指定ID，且该ID存在，且该文章的内容不是url时，显示指定的文章。否则显示第一篇
            if ($v['document_id'] == $_GET['id'] || empty($detail)) {
                $v['content'] = htmlspecialchars_decode($v['content']);
                $detail = $v;
            }
        }
        unset($res);

        $this->assign('detail', $detail);
        $this->assign('list', $list);
        $this->display();
    }

    public function toWap() {
        $_SESSION['wap_to_normal'] = '0';
        cookie('wap_to_normal', '0', 3600 * 24 * 365);
        U('wap', '', true);
    }

    public function toW3g() {
        $_SESSION['wap_to_normal'] = '0';
        cookie('wap_to_normal', '0', 3600 * 24 * 365);
        U('w3g', '', true);
    }

    public function fetchNew() {
        $map['weibo_id'] = array('gt', intval($_POST['since_id']));
        $map['transpond_id'] = 0;
        $map['type'] = 0;
        $data = D('Operate', 'weibo')->doSearchTopic('`weibo_id` > ' . intval($_POST['since_id']) . ' AND transpond_id =0 AND `type` = 0', 'weibo_id DESC', 0, false);
        $res = $data['data'][0];
        if ($res) {
            $res['uname'] = getUserSpace($res['uid'], '', '_blank', '{uname}');
            $res['user_pic'] = getUserSpace($res['uid'], '', '_blank', '{uavatar=m}');
            $res['friendly_date'] = friendlyDate($res['ctime']);
            $res['content'] = format($res['content'], true);
            echo json_encode($res);
        } else {
            echo 0;
        }
    }

    public function error404() {
        $this->display('404');
    }

    private function _isVerifyOn($type = 'login') {
        // 检查验证码
        if ($type != 'login' && $type != 'register')
            return false;
        $opt_verify = $GLOBALS['ts']['site']['site_verify'];
        return in_array($type, $opt_verify);
    }

    //获取开发平台应用列表接口
    public function getDevelopList() {
        $pageId = intval($_REQUEST['p']);
        $list = D('Develop', 'develop')->getListDevelop();

        foreach ($list['data'] as $key => $value) {
            switch ($value['type']) {
                case 1:
                    $list['data'][$key]['type'] = '风格模板';
                    break;
                case 2:
                    $list['data'][$key]['type'] = '插件';
                    break;
                case 3:
                    $list['data'][$key]['type'] = '应用';
                    break;
            }
        }

        $html = '<div class="opentitlenav">';
        $html .= '<p class="appmz">共有<b>' . $list['count'] . '</b>个应用</p>';
        $html .= '<p class="applx">类型</p>';
        $html .= '<p class="appcs">下载次数</p>';
        $html .= '<p class="appkf">开发者</p>';
        $html .= '</div>';

        $html .= '<ul>';

        foreach ($list['data'] as $value) {
            $html .= '<li>';
            $html .= '<p class="pic"><a href="#"><img src="' . $value['logo'] . '" style="width:64px; height:64px;" /></a></p>';
            $html .= '<p class="name"><b><a href="content.php?id=' . $value['develop_id'] . '">' . $value['title'] . '</a></b><em>' . getShort(strip_tags($value['explain']), 16) . '</em></p>';
            $html .= '<p class="sort">' . $value['type'] . '</p>';
            $html .= '<p class="down">' . $value['download_count'] . '</p>';
            $html .= '<p class="oper"><a href="' . U('home/Space/index', array('uid' => $value['uid'])) . '">' . getUserName($value['uid']) . '</a></p>';
            $attachUrl = U('home/Public/downloadWithDevelop', array('id' => $value['develop_id']));
            $html .= '<p class="caoz"><em><a href="' . $attachUrl . '"></a></em></p>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        //设置列表分页
        if ($list['totalPages'] > 1) {
            $pageHtml = '';
            if ($pageId != 1) {
                $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . ($pageId - 1) . ')">上一页</a>';
            }

            if ($list['nowPage'] <= 3) {
                for ($i = 1; $i <= 5; $i++) {
                    if ($i == $list['nowPage']) {
                        $pageHtml .= '<span class="current">' . $i . '</span>';
                    } else {
                        $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . $i . ')">' . $i . '</a>';
                    }
                }
                $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . $list['totalPages'] . ')">...' . $list['totalPages'] . '</a>';
            } else {
                $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(1)">1...</a>';

                if (($list['totalPages'] - $list['nowPage']) <= 3) {
                    for ($i = $list['totalPages'] - 4; $i <= $list['totalPages']; $i++) {
                        if ($i == $list['nowPage']) {
                            $pageHtml .= '<span class="current">' . $i . '</span>';
                        } else {
                            $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . $i . ')">' . $i . '</a>';
                        }
                    }
                } else {
                    $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . ($list['nowPage'] - 2) . ')">' . ($list['nowPage'] - 2) . '</a>';
                    $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . ($list['nowPage'] - 1) . ')">' . ($list['nowPage'] - 1) . '</a>';
                    $pageHtml .= '<span class="current">' . $list['nowPage'] . '</span>';
                    $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . ($list['nowPage'] + 1) . ')">' . ($list['nowPage'] + 1) . '</a>';
                    $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . ($list['nowPage'] + 2) . ')">' . ($list['nowPage'] + 2) . '</a>';

                    $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . $list['totalPages'] . ')">...' . $list['totalPages'] . '</a>';
                }
            }

            if ($pageId != $list['totalPages']) {
                $pageHtml .= '<a href="javascript:void(0)" onclick="pageShow(' . ($pageId + 1) . ')">下一页</a>';
            }

            $html .= '<div class="page">' . $pageHtml . '</div>';
        }

        $data = json_encode(array('html' => $html));
        echo $_GET['callback'] . "(" . $data . ")";
        exit();
    }

    //获取开发平台应用详细
    public function getDevelopDetail() {
        $developId = intval($_REQUEST['id']);
        $data = D('Develop', 'develop')->getDetailDevelop($developId);

        foreach ($data as $key => $value) {
            switch ($data['type']) {
                case 1:
                    $data['type'] = '风格模板';
                    break;
                case 2:
                    $data['type'] = '插件';
                    break;
                case 3:
                    $data['type'] = '应用';
                    break;
            }
        }

        $html = '<dl>';
        $html .= '<dt><img src="' . $data['logo'] . '" style="width:64px; height:64px;" /></dt>';
        $html .= '<dd class="la">';
        $html .= '<p class="mingz">' . $data['title'] . '</p>';
        $html .= '<p class="fenl">类型：' . $data['type'] . '</p>';
        $html .= '<p class="zuoz">开发者：<a href="' . U('home/Space/index', array('uid' => $data['uid'])) . '">' . getUserName($data['uid']) . '</a></p>';
        $html .= '</dd>';
        $html .= '<dd class="lb">';
        $attachUrl = U('home/Public/downloadWithDevelop', array('id' => $data['develop_id']));
        $html .= '<p class="caoz"><em><a href="' . $attachUrl . '"></a></em></p>';
        $html .= '</dd>';
        $html .= '</dl>';
        $html .= '<div class="contentapptxt">';
        $html .= '<h5>简介</h5>';
        $html .= '<p>' . $data['explain'] . '</p>';
        $html .= '</div>';

        $data = json_encode(array('html' => $html));
        echo $_GET['callback'] . "(" . $data . ")";
        exit();
    }

    //官网应用平台下载链接
    public function downloadWithDevelop() {
        try {
            $model = D('Develop', 'develop');
            $id = intval($_GET['id']);
            $data = D('Develop', 'develop')->getDetailDevelop($id);
            $attach = $data['file'];
            if (!$attach) {
                $this->error(L('attach_noexist'));
            }
            if ($data['status'] != DevelopModel::STATUS_PASS)
                $this->error('该扩展尚未通过审核，不允许下载');

            //下载函数
            require_cache('./addons/libs/Http.class.php');
            $file_path = $attach['url'];
            if (file_exists($file_path)) {
                //计算数
                $model->download($id);
                $filename = iconv("utf-8", 'gb2312', $attach['name']);
                Http::download($file_path, $filename);
            } else {
                $this->error(L('attach_noexist'));
            }
        } catch (ThinkException $e) {
            $this->error($e->getMessage());
        }
    }

    public function school() {
        //已选地区
        $selectedArea = explode(',', $_GET['selected']);
        if (!empty($selectedArea[0])) {
            $this->assign('selectedarea', $_GET['selected']);
        }
        $pNetwork = model('Schools');
        $list = $pNetwork->makeLevel0Tree(0);
        //var_dump($list);die;
        $this->assign('list', json_encode($list));
        $this->display();
    }

    public function multiSchool() {
        $selectedArea = explode(',', $_GET['selected']);
        if (!empty($selectedArea[0])) {
            $this->assign('selectedarea', $_GET['selected']);
        }
        $pNetwork = model('Schools');
        $list = $pNetwork->makeLevelParentTree(intval($_GET['sid']));
        $this->assign('list', json_encode($list));
        $this->display();
    }

    public function getSubSchool() {
        $pid = $_POST['pid'];
        $map['pid'] = intval($pid);
        $sub = M('school')->where($map)->findAll();
        if(!$sub){
            $sub = array();
        }
        echo(json_encode($sub));
        exit;
    }

    public function newSchool(){
        $schools = model('Schools')->_makeTree(0);
        $this->assign('school', $schools);
        $this->display();
    }

    public function citys() {
        $citys = model('Citys')->getAllCitys();
        $first = $citys[0]['id'];
        $allSchools = model('Schools')->makeLevel0Tree();
        $host = get_host_needle();
        $schools = array();
        foreach($allSchools as $school){
            if($school['cityId'] == $first){
                $school['domain'] = 'http://'.$school['domain'].'.'.$host;
                $schools[] = $school;
            }
        }
        $this->assign('citys', $citys);
        $this->assign('first',$first);
        $this->assign('schools', $schools);
        $this->display();
    }
    public function ajaxCitySchool(){
        $cityId = intval($_POST['cityId']);
        $allSchools = model('Schools')->makeLevel0Tree();
        $host = get_host_needle();
        $schools = array();
        foreach($allSchools as $school){
            if($school['cityId'] == $cityId){
                $school['domain'] = 'http://'.$school['domain'].'.'.$host;
                $schools[] = $school;
            }
        }
        if ($schools) {
            exit(json_encode($schools));
        } else {
            exit(json_encode(array()));
        }
    }
    //切换学校官网
    public function changeSchoolDomain() {
        $citys = model('Citys')->getAllCitys();
        $first = $citys[0]['id'];
        $allSchools = model('Schools')->makeLevel0Tree();
        $host = get_host_needle();
        $schools = array();
        foreach($allSchools as $school){
            if($school['cityId'] == $first){
                $school['domain'] = 'http://'.$school['domain'].'.'.$host;
                $schools[] = $school;
            }
        }
//
//        $map['cityId'] = $citys[0]['id'];
//        $map['pid'] = 0;
//        $map['eTime'] = array('neq',0);
//        $schools = M('school')->where($map)->field('title,id,domain')->findAll();
//        $host = get_host_needle();
//        foreach($schools as &$school){
//            $school['domain'] = 'http://'.$school['domain'].'.'.$host;
//        }
        $this->assign('citys', $citys);
        $this->assign('first',$first);
        $this->assign('schools', $schools);
        $this->display();
    }
    public function ajaxSchoolDomain(){
        $cityId = intval($_POST['cityId']);
        $allSchools = model('Schools')->makeLevel0Tree();
        $host = get_host_needle();
        $schools = array();
        foreach($allSchools as $school){
            if($school['cityId'] == $cityId){
                $school['domain'] = 'http://'.$school['domain'].'.'.$host;
                $schools[] = $school;
            }
        }
//        $map['cityId'] = $cityId;
//        $map['pid'] = 0;
//        $map['eTime'] = array('neq',0);
//        $schools = M('school')->where($map)->field('title,id,domain')->findAll();
//        $host = get_host_needle();
//        foreach($schools as &$school){
//            $school['domain'] = 'http://'.$school['domain'].'.'.$host;
//        }
        if ($schools) {
            exit(json_encode($schools));
        } else {
            exit(json_encode(array()));
        }
    }

    public function newPreSchool() {
        $schools = model('Schools')->_makeTree(0);
        $this->assign('school', $schools);
        $this->display();
    }

    public function ajaxNewSchool() {
        $sid = intval($_POST['sid']);
        $schools = model('Schools')->_makeTree(0);
        foreach ($schools as $key => $value) {
            if ($value['a'] == $sid) {
                if(isset($schools[$key]['d'])){
                    exit(json_encode($schools[$key]['d']));
                }else{
                    exit(json_encode(array()));
                }
            }
        }
      exit(json_encode(array()));
    }
    private function check_email($email) {
           if (!$email) {
                $this->error('请填写邮箱号');
            }
       $pattern = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if(!preg_match($pattern,$email)){
              $this->error('邮箱格式不正确');
        }
    }
    public function checkMail(){
        $email=t($_POST['email']);
        $this->check_email($email);
        $res=M('user')->where("`email2`= '$email'")->field('uid')->find();
        if($res && $res['uid'] != $this->mid){
            $this->error('该邮箱已被使用');
        }else{
            $this->success('ok');
        }
    }

    public function pulogin() {
        if (service('Passport')->isLoggedPu()) {
            redirect(U('home/Puback/index'));
        }

        $this->display();
    }

    public function doPuLogin() {
        // 检查验证码
        if (md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }

        // 数据检查
        if (empty($_POST['password'])) {
            $this->error(L('password_notnull'));
        }
        if (isset($_POST['email']) && !isValidEmail($_POST['email'])) {
            $this->error(L('email_format_error'));
        }

        // 检查帐号/密码
        $is_logged = false;
        if (isset($_POST['email'])) {
            $is_logged = service('Passport')->loginPu($_POST['email'], $_POST['password']);
        } else if ($this->mid > 0) {
            $is_logged = service('Passport')->loginPu($this->mid, $_POST['password']);
        } else {
            $this->error(L('parameter_error'));
        }

        // 提示消息不显示头部
        $this->assign('isAdmin', '1');

        if ($is_logged) {
            redirect(U('home/Puback/index'));
        } else {
            $this->assign('jumpUrl', U('home/Public/pulogin'));
            $this->error('登录失败或无权查看');
        }
    }

                //推送消息
    public function push(){
        include_once(SITE_PATH.'/addons/libs/Jpush.class.php');
			$n_title   =  'sssss';
			$n_content = 'sadasdfff是';
			$receiver_value = '';
			$sendno = 2211;
			$platform = 'android,ios' ;
			$msg_content = json_encode(array('n_builder_id'=>0, 'n_title'=>$n_title, 'n_content'=>$n_content));
			$obj = new Jpush();
			$res = $obj->send($sendno, 4, $receiver_value, 1, $msg_content, $platform);


    }

    public function jpush(){
          service('Jpush')->jsend('send', 'sadssssd','活sssss动统计');
    }

     //附件下载
   public function download(){
        $fid = intval($_REQUEST['fid']) > 0 ?  intval($_REQUEST['fid']) : 0;
        if($fid == 0) exit();
        if(!isset($_REQUEST['code'])) exit();
        $file_info = getAttach($fid);
        $fileName = t($_REQUEST['code']);
        if($fileName!=$file_info['savename']) exit();
        $file_path = UPLOAD_PATH . '/' .$file_info['savepath'].'/' .$file_info['savename'];
        if (file_exists($file_path)) {
                include_once(SITE_PATH . '/addons/libs/Http.class.php');
                $file_info['name'] = iconv("utf-8", 'gb2312', $file_info['name']);
                Http::download($file_path, $file_info['name']);
        }
        $this->error('文件不存在');
   }
}
