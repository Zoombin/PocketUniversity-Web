<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require ("lib/function.php");
$log = date('Y-m-d H:i:s').' 支付宝';

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
    $log .= "\n认证签名成功 ";
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	//商户订单号
	$out_trade_no = $_POST['out_trade_no'];
	$total_fee = $_POST['total_fee']*100;
        $uid = substr($out_trade_no, 14);
	//交易状态
	$trade_status = $_POST['trade_status'];
    if($_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'TRADE_FINISHED') {
	$has = doQuery("select id from ts_money_trade where `out_trade_no`='".$out_trade_no."' limit 1;");
        if($has){
            echo 'success';
            exit;
        }
        //保存money_trade
        $res = doQuery("insert into ts_money_trade (`out_trade_no`, `money`, `ctime`) VALUES ('".$out_trade_no."', '".$total_fee."', '".time()."');");
        if(!$res){
            $log .= " 写入trade失败";
            log_result($log);
            echo 'fail';
            exit;
        }
        //保存money_in
        $res = doQuery("insert into ts_money_in (`uid`,`typeName`,`logMoney`,`ctime`) VALUES ('".$uid."', '支付宝', '".$total_fee."', '".time()."');");
        if(!$res){
            $log .= " 写入money_in失败";
            log_result($log);
            echo 'fail';
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
            $log .= " 即时到帐成功 ".$out_trade_no.' '.$uid. ' ' . $total_fee;
            echo 'success';
        }else{
            $log .= " 写入money失败";
            echo 'fail';
        }
    }else{
	echo 'success';		//请不要修改或删除
        exit;
    }
}else {
    //验证失败
    $log .= "\n认证签名失败 ". $alipayNotify->getDebugInfo();
    echo 'fail';

    //调试用，写文本函数记录程序运行情况是否正常
    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
}
log_result($log);
?>