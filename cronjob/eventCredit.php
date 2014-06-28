<?php
require ("classes/function.php");
$today = date('Y-m-d');
$res = doQuery("SELECT `value` FROM `ts_system_data` WHERE ( `list` = 'event' ) AND ( `key` = 'tjday' ) LIMIT 1");
$tjday = unserialize($res[0]['value']);
if($tjday == $today){
    die;
}
$sql = 'TRUNCATE TABLE `ts_tj_event`';
doQuery($sql);
$from = date('Y-m-d',strtotime('-30 day', strtotime($today)));
$sql = "select uid,sid,sum(credit) as note from ts_tj_eday where day>='$from' group by uid";
$list = doQuery($sql);
foreach ($list as $v) {
    $uid = $v['uid'];
    $sid = $v['sid'];
    $credit1 = $v['note'];
    $sql = "insert into ts_tj_event (`tj_uid` ,`tj_sid` ,`credit1`) VALUES ('$uid','$sid','$credit1');";
    doQuery($sql);
}
$from = date('Y-m-d',strtotime('-6 month', strtotime($today)));
$sql = "select uid,sid,sum(credit) as note from ts_tj_eday where day>='$from' group by uid;";
$list = doQuery($sql);
foreach ($list as $v) {
    $uid = $v['uid'];
    $sid = $v['sid'];
    $credit2 = $v['note'];
    $has = doQuery("select tj_uid from ts_tj_event where tj_uid=$uid limit 1");
    if($has){
        $sql = "UPDATE `ts_tj_event` SET `credit2` = '$credit2' WHERE `tj_uid` =$uid";
    }else{
        $sql = "insert into ts_tj_event (`tj_uid` ,`tj_sid` ,`credit2`) VALUES ('$uid','$sid','$credit2');";
    }
    doQuery($sql);
}
$from = date('Y-m-d',strtotime('-1 year', strtotime($today)));
$sql = "select uid,sid,sum(credit) as note from ts_tj_eday where day>='$from' group by uid;";
$list = doQuery($sql);
foreach ($list as $v) {
    $uid = $v['uid'];
    $sid = $v['sid'];
    $credit3 = $v['note'];
    $has = doQuery("select tj_uid from ts_tj_event where tj_uid=$uid limit 1");
    if($has){
        $sql = "UPDATE `ts_tj_event` SET `credit3` = '$credit3' WHERE `tj_uid` =$uid";
    }else{
        $sql = "insert into ts_tj_event (`tj_uid` ,`tj_sid` ,`credit3`) VALUES ('$uid','$sid','$credit3');";
    }
    doQuery($sql);
}
log_result(date('Y-m-d H:i:s').' tj_event');
$v = serialize($today);
doQuery("UPDATE `ts_system_data` SET `value`='$v' WHERE ( `list` = 'event' ) AND ( `key` = 'tjday' )");
$sevday=time()-3600*24*7;
doQuery("DELETE FROM `ts_user_online` where ctime<$sevday");
?>
