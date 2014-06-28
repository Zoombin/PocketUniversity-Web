<?php

class HomeAction extends AdministratorAction {

    // 统计信息
    public function statistics() {
        $statistics = array();

        /*
         * 重要: 为了防止与应用别名重名，“服务器信息”、“用户信息”、“开发团队”作为key前面有空格
         */

        // 服务器信息
//		$site_version = model('Xdata')->get('siteopt:site_system_version');
//		$serverInfo['核心版本']        	= 'ThinkSNS ' . $site_version;
//        $serverInfo['服务器系统及PHP版本']	= PHP_OS.' / PHP v'.PHP_VERSION;
//        $serverInfo['服务器软件'] 			= $_SERVER['SERVER_SOFTWARE'];
//        $serverInfo['最大上传许可']     	= ( @ini_get('file_uploads') )? ini_get('upload_max_filesize') : '<font color="red">no</font>';
//
//        $mysqlinfo = M('')->query("SELECT VERSION() as version");
//        $serverInfo['MySQL版本']			= $mysqlinfo[0]['version'] ;
//        $t = M('')->query("SHOW TABLE STATUS LIKE '".C('DB_PREFIX')."%'");
//        foreach ($t as $k){
//            $dbsize += $k['Data_length'] + $k['Index_length'];
//        }
//        $serverInfo['数据库大小']			= byte_format( $dbsize );
//        $statistics[' 服务器信息'] = $serverInfo;
//        unset($serverInfo);
        // 用户信息
        $user['当前在线3分钟内'] = getOnlineUserCount();
        $user['全部用户'] = M('user')->count();
        $user['登录过的用户'] = M('login_count')->count();
        $user['客户端登录过的用户'] = M('login')->count();
        $user['验证过的用户'] = M('user')->where('`is_valid` = 1')->count();
        //$user['初始化过的用户'] = M('user')->where('`is_init` = 1')->count();
        $statistics[' 用户信息'] = $user;
        unset($user);

        // 应用统计
        $applist = array();
//        $res = model('App')->where('`statistics_entry`<>""')->field('app_name,app_alias,statistics_entry')->order('display_order ASC')->findAll();
//        foreach ($res as $v) {
//        	$d = explode('/', $v['statistics_entry']);
//        	$d[1] = empty($d[1]) ? 'index' : $d[1];
//        	$statistics[$v['app_alias']] = D($d[0], $v['app_name'])->$d[1]();
//        }
        // 开发团队
        $statistics[' 开发团队'] = array(
            '版权所有' => '<a href="http://www.zhishisoft.com" target="_blank">苏州天宫网络科技有限公司</a>',
        );

        $this->assign('statistics', $statistics);
        $this->display();
    }

    public function yy() {
        $list = M('y_tj')->order('day DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function pu() {
        $userId = intval($_POST['userId']);
        $db_prefix = C('DB_PREFIX');
        $dao = M('money');
        $dao->table("{$db_prefix}money AS a ")
            ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
            ->order('a.money DESC')
            ->field('a.uid,b.realname,b.sid,a.money');
        if($_POST['userId']){
            $dao->where('a.uid='.$userId);
        }
        $list = $dao->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function moneyin() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchMoneyIn'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchMoneyIn']);
            if(is_array($_POST))
            $_REQUEST = array_merge($_REQUEST, $_POST);
        } else {
            unset($_SESSION['admin_searchMoneyIn']);
        }
        $map = array();
        //组装搜索条件
        $fields = array('a__uid');
        $map = array();
        foreach ($fields as $v)
            if (isset($_REQUEST[$v]) && $_REQUEST[$v] != ''){
                $key=  str_replace('__', '.', $v);
                $map[$key] = t($_REQUEST[$v]);
            }
        if (isset($_POST['typeName']) && $_POST['typeName'] != ''){
            $map['typeName'] = array('like',t($_POST['typeName']).'%');
        }
        //var_dump($map);die;
        $db_prefix = C('DB_PREFIX');
        $dao = M('money_in');
        $list = $dao->table("{$db_prefix}money_in AS a ")
            ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
            ->order('a.ctime DESC')
            ->where($map)
            ->field('a.uid,a.ctime,b.realname,b.sid,a.logMoney,typeName')
            ->findPage(10);
        $this->assign($list);
        $this->display('moneyin');
    }

    public function moneyout() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchMoneyOut'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchMoneyOut']);
            if(is_array($_POST))
            $_REQUEST = array_merge($_REQUEST, $_POST);
        } else {
            unset($_SESSION['admin_searchMoneyOut']);
        }
        $map = array();
        //组装搜索条件
        $fields = array('out_uid');
        $map = array();
        foreach ($fields as $v)
            if (isset($_REQUEST[$v]) && $_REQUEST[$v] != ''){
                $key=  str_replace('__', '.', $v);
                $map[$key] = t($_REQUEST[$v]);
            }
        if (isset($_POST['typeName']) && $_POST['typeName'] != ''){
            $map['out_title'] = array('like',t($_POST['typeName']).'%');
        }
        //var_dump($map);die;
        $db_prefix = C('DB_PREFIX');
        $dao = M('money_out');
        $list = $dao->table("{$db_prefix}money_out AS a ")
            ->join("{$db_prefix}user AS b ON  a.out_uid=b.uid")
            ->order('out_ctime DESC')
            ->where($map)
            ->field('out_uid,out_ctime,b.realname,b.sid,out_money,out_title')
            ->findPage(10);
        $this->assign($list);
        $this->display('moneyout');
    }

    public function tg() {
$uids = array('1478638','1478637','1478636','1478635','1478631','1478630','1473609','1473608','1472707','1472705','1472585','1471575','1471507','1454805','1448764','1267214','1267213','1267171','264864','222527','217241','217236','206762','206758','179238','96513','71052','71051','71047','71045','33655','33654','27609','27596','1');
$realname = array('万松菊','安丽娜','朱晓峰','章润芳','沈静','胡欢欢','王素婷','朱恒','PU运营中心-Jean','PU-旅游','PU运营中心-潘芸','PU运营中心-夏萍','PU运营中心-杨虹','张晓军','王晓峰','PU运营中心-严庆','王莹','林忠','丛琪','ios测试2','PU运营中心-小爷','林一','林一','蒋慧','天宫2013','PU运营团队','PU运营中心-蒋慧','PU运营中心-雨婷','PU运营中心-本明','测试平台超管','冬天怕冷','陆冬云','徐锦盛','anglela','PU团队');
$p = intval($_GET['p']);
$p = $p?$p-1:0;
$dao = model('TgLogin');
$res = array();
for($i=0;$i<7;$i++){
    $vor = $p*7+$i;
    $day = date('Y-m-d',strtotime('-'.$vor.' day'));
    $days[] = $day;
    $logins[] = $dao->getTgLoginCache($day);
}
$i = 0;
foreach($uids as $uid){
    $res[$i][] = $uid;
    $res[$i][] = $realname[$i];
    //$res[$i][] = getrea;
    foreach($logins as $login){
        if(in_array($uid, $login)){
            $res[$i][] = '<span class="cGreen">登录<span>';
        }else{
            $res[$i][] = ' ';
        }
    }
    $i++;
}
$this->assign('days',$days);
$this->assign('list',$res);
$this->assign('p',$p+1);
$this->assign('totalRows',count($uids));
    $this->display();
    }

//	public function update()
//	{
//		$service = service('System');
//		$current_version = $service->getSystemVersion();
//		$lastest_version = $service->checkUpdate();
//
//		// 兼容ThinkSNS 2.1 Build 10992的版本号
//		foreach ($current_version as $k => $v)
//			if ($v <= 0)
//				$current_version[$k] = '10992';
//
//		// 自动升级程序仅支持ThinkSNS 2.1 Final(10920或10992)及以上版本
//		$system_version = model('Xdata')->get('siteopt:site_system_version');
//		$this->assign('system_version', ($system_version == '10920' || $system_version == '10992')
//										? 'ThinkSNS 2.1 Final Build '.$system_version
//										: $system_version);
//
//		$this->assign('is_support',     ($system_version == '10920' || $system_version == '10992' || $current_version['core'] >= 10992));
//		$this->assign('current_version', $current_version);
//		$this->assign('lastest_version', $lastest_version);
//		$this->display();
//	}
//
//	public function doUpdate()
//	{
//		$_GET['app_name'] = strtolower($_GET['app_name']);
//		$apps = model('App')->getAllApp('app_name');
//		$apps = getSubByKey($apps, 'app_name');
//		$apps[] = 'core';
//		if (!in_array($_GET['app_name'], $apps))
//			$this->error('参数错误');
//
//		$lastest_version = service('System')->checkUpdate();
//		if ($lastest_version['error'])
//			$this->error($lastest_version['error_message']);
//
//		$lastest_version = $lastest_version[$_GET['app_name']];
//		if (empty($lastest_version))
//			$this->error('应用不存在');
//		if ($lastest_version['error'])
//			$this->error($lastest_version['error_message']);
//		if (!$lastest_version['has_update'])
//			$this->error($_GET['app_name'] . '已经为最新版本');
//
//		// 升级的SQL文件 (必须)
//		// 每个版本必须附带数据升级文件, 并命名为: appname_versionNO.sql, 如: blog_14000.sql/core_14000.sql
//		// core的升级文件位于/update/目录
//		// app的升级文件位于/apps/app_name/Appinfo/目录
//		$sql_files = array();
//		foreach ($lastest_version['version_number_list'] as $version_no) {
//			if ($lastest_version['current_version_number'] >= $version_no)
//				continue ;
//
//			if ($_GET['app_name'] == 'core')
//				$path = '/update/core_' . $version_no . '.sql';
//			else
//				$path = "/apps/{$_GET['app_name']}/Appinfo/{$_GET['app_name']}_{$version_no}.sql";
//
//			if (!is_file(SITE_PATH . $path))
//				$this->error("{$path} 不存在");
//			else
//				$sql_files[] = SITE_PATH . $path;
//		}
//
//		// 升级的脚本文件 (可选)
//		$before_update_script = '';
//		$after_update_script  = '';
//		if ($_GET['app_name'] == 'core') {
//			$before_update_script = SITE_PATH . '/update/before_update_db.php';
//			$after_update_script  = SITE_PATH . '/update/after_update_db.php';
//		} else {
//			$before_update_script = SITE_PATH . "/apps/{$_GET['app_name']}/Appinfo/before_update_db.php";
//			$after_update_script  = SITE_PATH . "/apps/{$_GET['app_name']}/Appinfo/after_update_db.php";
//		}
//
//		// 执行SQL文件和脚本文件 (TODO: 数据库执行错误时的回滚)
//		if (is_file($before_update_script))
//			include_once $before_update_script;
//		foreach ($sql_files as $file) {
//			$res = M('')->executeSqlFile($file);
//			if (!empty($res))
//				$this->error("SQL错误: {$res['error_code']}");
//		}
//		if (is_file($after_update_script))
//			include_once $after_update_script;
//
//		// 升级完成, 更新版本名称和版本号
//		$dao = model('Xdata');
//		if ($_GET['app_name'] == 'core') {
//			$data['site_system_version'] 		= $lastest_version['lastest_version'];
//			$data['site_system_version_number'] = $lastest_version['lastest_version_number'];
//			$dao->lput('siteopt', $data);
//		} else {
//			$dao->put("{$_GET['app_name']}:version_number", $lastest_version['lastest_version_number'], true);
//		}
//
//		service('System')->unsetUpdateCache();
//
//		$this->success('升级成功');
//	}
}