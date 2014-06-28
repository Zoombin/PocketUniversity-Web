<?php
//---------------------------------------------------------
//财付通即时到帐支付后台回调示例，商户按照此文档进行开发即可
//---------------------------------------------------------

require ("classes/ResponseHandler.class.php");
require ("classes/RequestHandler.class.php");
require ("classes/client/ClientResponseHandler.class.php");
require ("classes/client/TenpayHttpClient.class.php");
require ("./classes/function.php");
require_once ("./tenpay_config.php");

$log = date('Y-m-d H:i:s').' 财付通';

/* 创建支付应答对象 */
$resHandler = new ResponseHandler();
$resHandler->setKey($key);

//判断签名
if ($resHandler->isTenpaySign()) {

    //通知id
    $notify_id = $resHandler->getParameter("notify_id");

    //通过通知ID查询，确保通知来至财付通
    //创建查询请求
    $queryReq = new RequestHandler();
    $queryReq->init();
    $queryReq->setKey($key);
    $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
    $queryReq->setParameter("partner", $partner);
    $queryReq->setParameter("notify_id", $notify_id);

    //通信对象
    $httpClient = new TenpayHttpClient();
    $httpClient->setTimeOut(120);
    //设置请求内容
    $httpClient->setReqContent($queryReq->getRequestURL());

    //后台调用
    if ($httpClient->call()) {
        //设置结果参数
        $queryRes = new ClientResponseHandler();
        $queryRes->setContent($httpClient->getResContent());
        $queryRes->setKey($key);

        if ($resHandler->getParameter("trade_mode") == "1") {
            //判断签名及结果（即时到帐）
            //只有签名正确,retcode为0，trade_state为0才是支付成功
            if ($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
                //取结果参数做业务处理
                $out_trade_no = $resHandler->getParameter("out_trade_no");
                //财付通订单号
                //$transaction_id = $resHandler->getParameter("transaction_id");
                //金额,以分为单位
                $total_fee = $resHandler->getParameter("total_fee");
                //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
                //$discount = $resHandler->getParameter("discount");
                $uid = $resHandler->getParameter("attach");

                //------------------------------
                //处理业务开始
                //------------------------------
                //处理数据库逻辑
                //注意交易单不要重复处理
                //注意判断返回金额
                //是否已保存
                $has = doQuery("select id from ts_money_trade where `out_trade_no`='".$out_trade_no."' limit 1;");
                if($has){
                    $log .= "\n 重复处理";
//                    log_result($log);
                    echo "success";
                    exit;
                }
                //保存money_trade
                $res = doQuery("insert into ts_money_trade (`out_trade_no`, `money`, `ctime`) VALUES ('".$out_trade_no."', '".$total_fee."', '".time()."');");
                if(!$res){
                    $log .= "\n 写入trade失败";
//                    log_result($log);
                    echo "fail";
                    exit;
                }
                //保存money_in
                $res = doQuery("insert into ts_money_in (`uid`,`typeName`,`logMoney`,`ctime`) VALUES ('".$uid."', '财付通', '".$total_fee."', '".time()."');");
                if(!$res){
                    $log .= "\n 写入money_in失败";
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
                    $log .= "\n 即时到帐成功 ".$out_trade_no.' '.$uid. ' ' . $total_fee;
                    echo "success";
                }else{
                    $log .= "\n 写入money失败";
                    echo "fail";
                }
                //------------------------------
                //处理业务完毕
                //------------------------------
            } else {
                $log .= "\n 即时到帐后台回调失败";
                //错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                //echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->                         getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
                echo "fail";
            }
        }
    } else {
        $log .= "\n call err: ".$httpClient->getResponseCode() . "," . $httpClient->getErrInfo();
        //通信失败
        echo "fail";
        //后台调用通信失败,写日志，方便定位问题
        //echo "<br>call err:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
    }
} else {
    $log .= "\n认证签名失败 ". $resHandler->getDebugInfo();
    echo "fail";
    //echo "<br/>" . "认证签名失败" . "<br/>";
    //echo $resHandler->getDebugInfo() . "<br>";
}
log_result($log);
?>