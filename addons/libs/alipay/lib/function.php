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

?>