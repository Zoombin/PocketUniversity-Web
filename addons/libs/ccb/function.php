<?php

define('SITE_PATH', '/data/sites/2012.xyhui.com');
define('API_ROOT',SITE_PATH.'/apps/myop/api');

// 请注意服务器是否开通fopen配置
function  log_result($word) {
    $fp = fopen("/data/plog.txt","a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,$word."\n\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

function getDb() {
    static $_db = '';
    if (empty($_db) ) {
        require_once API_ROOT . '/lib/ez_sql_core.php';
        require_once API_ROOT . '/lib/ez_sql_mysql.php';
        include SITE_PATH . '/config.inc.php';
        $_config = include SITE_PATH . '/config.inc.php';
        $_db = new ezSQL_mysql($_config['DB_USER'], $_config['DB_PWD'], $_config['DB_NAME'], $_config['DB_HOST']);
    }
    return $_db;
}

function doQuery($sql = '') {
	if (empty($sql) )
		return false;
	$_db = getDb();

	//当INSERT/DELETE/UPDATE/REPLACE时调用ez_sql的query函数，否则调用get_results函数
    if ( preg_match("/^(insert|delete|update|replace)\s+/i", $sql) ) {
    	$res = $_db->query($sql);
	} else {
    	$res = $_db->get_results($sql, ARRAY_A);
    }
    return $res;
}

function getDomain($host){
    $host = strtolower($host);
    if (strpos($host, '/') !== false) {
        $parse = @parse_url($host);
        $host = $parse['host'];
    }
    $topleveldomaindb = array('com', 'edu', 'gov', 'int', 'mil', 'net', 'org', 'biz', 'info', 'pro', 'name', 'museum', 'coop', 'aero', 'xxx', 'idv', 'mobi', 'cc', 'me');
    $str = '';
    foreach ($topleveldomaindb as $v) {
        $str.=($str ? '|' : '') . $v;
    }
    $matchstr = "[^\.]+\.(?:(" . $str . ")|\w{2}|((" . $str . ")\.\w{2}))$";
    if (preg_match("/" . $matchstr . "/ies", $host, $matchs)) {
        $domain = $matchs['0'];
    } else {
        $domain = $host;
    }
    return $domain;
}

function giftPu($uid,$money,$title){
    $has = doQuery("select * from ts_money where `uid`='".$uid."' limit 1;");
    if($has){
        $res = doQuery("update ts_money set money=`money`+".$money." where uid=".$uid.";");
    }else{
        $res = doQuery("insert into ts_money (`uid`,`money`) VALUES ('".$uid."', '".$money."');");
    }
    if($res){
        doQuery("insert into ts_money_in (`uid`,`typeName`,`logMoney`,`ctime`) VALUES ('".$uid."', '".$title."', '".$money."', '".time()."');");
        return true;
    }
    return false;
}

function afterPay($uid,$pay){
    //充值十元（含）以上送2个PU币
    if($pay>=1000){
        //一个帐户送币上限12元（3月1日至5月15日）
        $start = strtotime('2014-02-13');
        $end = strtotime('2014-05-15');
        $now = time();
        if($now>=$start && $now<=$end){
            $typeName = '建行充值回馈';
            $hasGift = doQuery("select count(1) as count from ts_money_ccb where `uid`='".$uid."'");
            if(!$hasGift || $hasGift[0]['count']<6){
                $res = giftPu($uid,200,$typeName);
                if($res){
                    $pay2 = sprintf('%.2f', $pay/100);
                    doQuery("insert into ts_money_ccb (`uid`,`pay`,`gift`,`ctime`) VALUES ('".$uid."', '".$pay2."', '2.00', '".time()."');");
                }
            }
        }
    }
}
?>