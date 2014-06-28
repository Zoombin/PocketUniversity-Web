<?php
require ("classes/function.php");

$now = time();
//推荐活动过期
doQuery("UPDATE `ts_event` SET `isTop`=0 WHERE isTop=1 and eTime<='$now';");
?>
