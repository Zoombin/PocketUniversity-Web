<?php
//define('SITE_PATH', '/data/sites/2012.xyhui.com');
define('SITE_PATH', 'E:\xampp\htdocs\2013xyhui\web');
define('API_ROOT',SITE_PATH.'/apps/myop/api');
date_default_timezone_set('PRC');
set_time_limit(0);
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

// 请注意服务器是否开通fopen配置
function  log_result($word) {
    $fp = fopen(SITE_PATH."/data/cron_log.txt","a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,$word."\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

function sendsms($mobile,$msg){
    $name = '636978';
    $pass = md5("113314446");
    require_once(SITE_PATH . '/addons/libs/BayouSmsSender.php');
    $sender=new BayouSmsSender();
    $result = $sender->sendsms($name,$pass,$mobile, $msg.' 口袋校园');
    return $result;
}
function getShort($str, $length = 40, $ext = '') {
	$str	=	htmlspecialchars($str);
	$str	=	strip_tags($str);
	$str	=	htmlspecialchars_decode($str);
	$strlenth	=	0;
	$output		=	'';
	preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
	foreach($match[0] as $v){
		preg_match("/[\xe0-\xef][\x80-\xbf]{2}/",$v, $matchs);
		if(!empty($matchs[0])){
			$strlenth	+=	1;
		}elseif(is_numeric($v)){
			$strlenth	+=	0.5;    // 字符字节长度比例 汉字为1
		}else{
			$strlenth	+=	0.5;    // 字符字节长度比例 汉字为1
		}

		if ($strlenth > $length) {
			$output .= $ext;
			break;
		}

		$output	.=	$v;
	}
	return $output;
}

?>
