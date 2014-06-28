<?php
//---------------------------------------------------------
//建行即时到帐支付后台回调示例，商户按照此文档进行开发即可
//---------------------------------------------------------

require ("./ResponseHandler.class.php");
require ("./function.php");
$log = date('Y-m-d H:i:s').' 建行';

/* 创建支付应答对象 */
$resHandler = new ResponseHandler();

//判断签名
if ($resHandler->isCcbSign()) {
    $log .= "\n认证签名成功 ";
    //取结果参数做业务处理
    $success = $resHandler->getParameter("SUCCESS");
    if($success!='Y'){
        $log .= "\n支付失败 ". $resHandler->getDebugInfo();
        log_result($log);
        echo "success";
        exit;
    }
                $out_trade_no = $resHandler->getParameter("ORDERID");
                //财付通订单号
                //$transaction_id = $resHandler->getParameter("transaction_id");
                //金额,以分为单位
                $total_fee = $resHandler->getParameter("PAYMENT")*100;
                //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
                //$discount = $resHandler->getParameter("discount");
                $uid = substr($out_trade_no, 14);
                if($uid != $resHandler->getParameter("REMARK1")){
                    $log .= " uid不一致 ". $resHandler->getDebugInfo();
                    log_result($log);
                    echo "fail";
                    exit;
                }
                $refUrl = getDomain($resHandler->getParameter("REFERER"));
                if($refUrl!='' && $refUrl != 'pocketuni.net' && $refUrl != 'xyhui.com'){
                    $log .= " REFERER地址不对 ". $resHandler->getDebugInfo();
                    log_result($log);
                    echo "fail";
                    exit;
                }
//                $clientIp = $resHandler->getParameter("CLIENTIP");
//                $has = doQuery("select ip from ts_login_record where `uid`='$uid' ORDER BY login_record_id DESC limit 1;");

                //------------------------------
                //处理业务开始
                //------------------------------
                //处理数据库逻辑
                //注意交易单不要重复处理
                //注意判断返回金额
                //是否已保存
                $has = doQuery("select id from ts_money_trade where `out_trade_no`='".$out_trade_no."' limit 1;");
                if($has){
                    $log .= " 重复处理";
                    log_result($log);
                    echo "success";
                    exit;
                }
                //保存money_trade
                $res = doQuery("insert into ts_money_trade (`out_trade_no`, `money`, `ctime`) VALUES ('".$out_trade_no."', '".$total_fee."', '".time()."');");
                if(!$res){
                    $log .= " 写入trade失败";
                    log_result($log);
                    echo "fail";
                    exit;
                }
                //保存money_in
                $res = doQuery("insert into ts_money_in (`uid`,`typeName`,`logMoney`,`ctime`) VALUES ('".$uid."', '中国建设银行', '".$total_fee."', '".time()."');");
                if(!$res){
                    $log .= " 写入money_in失败";
                    log_result($log);
                    echo "fail";
                    exit;
                }
                //更新钱
                $has = doQuery("select * from ts_money where `uid`='".$uid."' limit 1;");
                if($has){
                    $res = doQuery("update ts_money set money=`money`+".$total_fee." where uid=".$has[0]['uid'].";");
                }else{
                    $res = doQuery("insert into ts_money (`uid`,`money`) VALUES ('".$uid."', '".$total_fee."');");
                }
                if($res){
                    afterPay($uid, $total_fee);
                    $log .= " 即时到帐成功 ".$out_trade_no.' '.$uid. ' ' . $total_fee;
                    echo "success";
                }else{
                    $log .= " 写入money失败";
                    echo "fail";
                }
} else {
    $log .= "\n认证签名失败 ". $resHandler->getDebugInfo();
    echo "fail";
}
log_result($log);
?>