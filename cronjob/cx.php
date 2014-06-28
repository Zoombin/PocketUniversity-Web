<?php
require ("classes/function.php");

//惩罚到期
$today = date('Y-m-d');
doQuery("UPDATE `ts_event_cx` SET `absent`=0,`status`=0 WHERE eday='$today';");
//添加诚信
$webConfig = array();
function isAttend($uid){
    $has = doQuery("SELECT uid FROM `ts_event_cx` WHERE uid=$uid LIMIT 1");
    if($has){
        doQuery("UPDATE `ts_event_cx` SET `total`=`total`+1,`attend`=`attend`+1 WHERE uid=$uid;");
    }else{
        doQuery("INSERT INTO ts_event_cx (`total`,`attend`,`uid`) values ('1','1','$uid')");
    }
}

function notAttend($uid,$sid){
    $cx = doQuery("SELECT absent,status FROM `ts_event_cx` WHERE uid=$uid LIMIT 1");
    $config = getConfig($sid);
    if(!$config){
        return;
    }
    $absent = 1;
    if($cx){
        $absent = $cx[0]['absent']+1;
    }
    $status = $cx[0]['status'];
    $warn = false;
    $stop = false;
    if($status==2){
        $absent = 0;
    //禁言
    }elseif($absent>=$config['cxjy']){
        stop($uid,$config['cxjy'],$config['cxday']);
        $absent = 0;
        $stop = true;
    }elseif ($absent>=$config['cxjg'] && $status!=1) {
        warn($uid,$absent,$config['cxjy'],$config['cxday']);
        $warn = true;
    }
    $today = date('Y-m-d');
    if($cx){
        $sql = "UPDATE `ts_event_cx` SET `total`=`total`+1,`absent`=$absent";
        if($warn){
            $sql .= ",`status`=1";
        }elseif ($stop) {
            $eday = date('Y-m-d', strtotime('+'.$config['cxday'].'day'));
            $sql .= ",`status`=2,`sday`='$today',`eday`='$eday'";
        }
        $sql .= " WHERE uid=$uid;";
        doQuery($sql);
    }else{
        $st = 0;
        $sday = '0000-00-00';
        $eday = '0000-00-00';
        if($warn){
            $st = 1;
        }elseif ($stop) {
            $st = 2;
            $sday = $today;
            $eday = date('Y-m-d', strtotime('+'.$config['cxday'].'day'));
        }
        doQuery("INSERT INTO ts_event_cx (`total`,`absent`,`uid`,`status`,`sday`,`eday`) values ('1','$absent','$uid','$st','$sday','$eday')");
    }
}

function warn($uid,$absent,$times,$day){
    $now = time();
    $data = serialize(array('absent' => $absent,'times'=>$times,'day'=>$day));
    doQuery("INSERT INTO ts_notify (`from`,`receive`,`type`,`data`,`ctime`) values ('0','$uid','event_warning','$data','$now')");
}

function stop($uid,$absent,$day){
    $now = time();
    $data = serialize(array('absent' => $absent,'day'=>$day));
    doQuery("INSERT INTO ts_notify (`from`,`receive`,`type`,`data`,`ctime`) values ('0','$uid','event_stop','$data','$now')");
}

function getConfig($sid){
    if(!$sid){
        return false;
    }
    global $webConfig;
    if(!isset($webConfig[$sid])){
        $config = doQuery("SELECT cxjg,cxjy,cxday FROM `ts_school_web` WHERE sid=$sid LIMIT 1");
        $webConfig[$sid] = $config[0];
    }
    return $webConfig[$sid];
}

$sql = "SELECT * FROM `ts_event_cron` WHERE 1";
$list = doQuery($sql);
$eids = '';
if($list){
foreach ($list as $v) {
    $eventId = $v['event_id'];
    $eids .= ' '.$eventId;
    //有如果都没签到则不计算（除了发起人）
    $qds = doQuery("select count(1) as cnt from ts_event_user where status=2 and  eventId=$eventId");
    if($qds && $qds[0]['cnt']>1){
        $users = doQuery("SELECT `uid`,`status`,`usid` FROM `ts_event_user` WHERE eventId=$eventId and status!=0");
        if($users){
            foreach ($users as $user) {
                //签到的
                if($user['status'] == 2){
                    isAttend($user['uid']);
                //未签到的
                }else{
                    notAttend($user['uid'],$user['usid']);
                }
            }
        }
    }
}
doQuery('TRUNCATE TABLE `ts_event_cron`');
log_result(date('Y-m-d H:i:s').' cx:'.$eids);
}
?>
