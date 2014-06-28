<?php
$spname="苏州天宫网络科技有限公司";
$partner = "1217351001";                                  	//财付通商户号
$key = "b9b2b99dc2c40ab5cfcf268eae19f5c4";											//财付通密钥

$return_url = "http://pocketuni.net/index.php?app=home&mod=Account&act=rechargeList&r=1";//显示支付结果页面,*替换成payReturnUrl.php所在路径
$notify_url = "http://pocketuni.net/addons/libs/tenpay/payNotifyUrl.php";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
?>