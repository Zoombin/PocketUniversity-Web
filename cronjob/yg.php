<?php
require ("classes/function.php");

//取消超过7天的订单
$qiDayAgo = date('Y-m-d',  strtotime('-7 day'));
doQuery("UPDATE `ts_order` SET `order_state` = '11' WHERE ( `cday` = '$qiDayAgo' ) AND ( `order_state` IN (4,0) )");

//团购开团
function cprice($sprice,$eprice,$eprice_attended,$has_attended,$dec){
    $sprice = $sprice*100;
    $eprice = $eprice*100;
    if($has_attended<=1){
        return $sprice;
    }
    if($has_attended>=$eprice_attended){
        return $eprice;
    }
    $decNum = $has_attended-1;
    $cprice = $sprice-($dec*$decNum);
    if($cprice<$eprice){
        return $eprice;
    }
    return $cprice;
}
$today = date('Y-m-d');
$time = time();
$tgList = doQuery("SELECT * FROM `ts_shop_tg` WHERE ( `codeState` = 1 ) AND ( `eday` = '$today' )");
$tgids = '';
if($tgList){
foreach ($tgList as $v) {
    $id = $v['id'];
    $tgids .= ' '.$id;
    $prodId = $v['tgprod_id'];
    $prod = doQuery("SELECT `name` FROM `ts_shop_tgprod` WHERE id=$prodId LIMIT 1");
    $name = $prod[0]['name'];
    doQuery("UPDATE `ts_shop_tgprod` SET `canActiv` = '1' WHERE `id` =$prodId;");
    $cprice = cprice($v['sprice'], $v['eprice'], $v['eprice_attended'], $v['has_attended'], $v['dec']);
    doQuery("UPDATE `ts_order` SET `order_state` = '4',`cday` = '$today' WHERE ( `product_id` = '$id' ) and (`type`=2);");
    doQuery("UPDATE `ts_shop_tg` SET `cprice` = '$cprice',`codeState` = '3' WHERE `id` =$id;");
    $uids = doQuery("SELECT order_id,uid FROM `ts_order` WHERE ( `product_id` = '$id' ) and (`type`=2)");
    if($uids){
    foreach ($uids as $va) {
        // 发送通知
        $uid = $va['uid'];
        $data = serialize(array('order_id' => $va['order_id'],'product'=>$name));
        doQuery("INSERT INTO ts_notify (`from`,`receive`,`type`,`data`,`ctime`) values ('0','$uid','shop_tgend','$data','$time')");
        //发短信
        $mobile = doQuery("SELECT `mobile` FROM `ts_user` WHERE uid=$uid LIMIT 1");
        if ($mobile[0]['mobile']) {
            $msg = '亲爱的PocketUni用户,恭喜您众志成城购买成功。商品:'
                .getShort($name,18).'请尽快付清尾款。';
            $res = sendsms($mobile[0]['mobile'], $msg);
        }
    }
    }
}
}
//云购开奖
$ygList = doQuery("SELECT id,has_attended,product_id,times FROM `ts_shop_yg` WHERE ( `codeState` = 1 ) AND ( `eday` = '$today' )");
if($tgList){
foreach ($ygList as $v) {
    $id = $v['id'];
    if($v['has_attended']==0){
        doQuery("UPDATE `ts_shop_yg` SET `eday`='0000-00-00' WHERE id=$id");
    }else{
        //计算中奖人员
        $allRNO = doQuery("SELECT * FROM `ts_shop_rno` WHERE ygid=$id");
        $winKey = rand(0, count($allRNO) - 1);
        $rnoWin = $allRNO[$winKey]['rno_id'];
        $winUid = $allRNO[$winKey]['uid'];
        doQuery("INSERT INTO `ts_order` (`uid`,`product_id`,`type`,`cday`) VALUES ('$winUid','$id',1,'$today')");
        $order_id = mysql_insert_id();
        $dateTime = date("Y-m-d H:i:s");
        doQuery("INSERT INTO `ts_order_log` (`order_id`,`oplog`,`opuser`,`optime`) VALUES ($order_id,'恭喜您一元梦想获奖，请尽快填写收货地址，以便我们为您配送！','PU系统','$dateTime')");
        doQuery("UPDATE `ts_shop_yg` SET `win`='$winUid',`codeRNO`='$rnoWin',`over_date`='$dateTime',`codeState`='3',`order_id`=$order_id WHERE id=$id");
        log_result(date('Y-m-d').' 开奖 '.$id);
        //自动开始下一期
        $pid = $v['product_id'];
        $times = $v['times'];
        $products = doQuery("SELECT need_attended,over_times,name FROM `ts_shop_product` WHERE id=$pid LIMIT 1");
        $product = $products[0];
        $time = time();
        if ($product['over_times'] == 0 || $times < $product['over_times']) {
            $need_attended = $product['need_attended'];
            $ntimes = $times + 1;
            doQuery("INSERT INTO `ts_shop_yg` (`product_id`,`need_attended`,`ctime`,`times`) VALUES ($pid,'$need_attended','$time','$ntimes')");
            log_result(date('Y-m-d').' 自动开始下一期 pid='.$pid);
        }
        // 发送通知
        $data = serialize(array('order_id' => $order_id,'product'=>$product['name']));
        doQuery("INSERT INTO ts_notify (`from`,`receive`,`type`,`data`,`ctime`) values ('0','$winUid','shop_win','$data','$time')");
        //发短信
        $mobile = doQuery("SELECT `mobile` FROM `ts_user` WHERE uid=$winUid LIMIT 1");
        if ($mobile[0]['mobile']) {
            $msg = '亲爱的PocketUni用户,恭喜您一元梦想获奖。获奖商品:'
                .getShort($product['name'],18).' 请尽快填写收货地址，以便我们为您配送！。';
            $res = sendsms($mobile[0]['mobile'], $msg);
        }
    }
}
}
log_result(date('Y-m-d H:i:s').' yg.php tgids:' .$tgids);
?>
