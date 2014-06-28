<?php

/**
 * 在线支付服务
 *
 * @author dongyun <ludongyun@tiangongwang.com>
 */
class PayService extends Service {

    public function payUrl($order) {
        if($order['payName']=='ccb'){
            $this->ccbPay($order);
        }elseif($order['payName']=='alipay'){
            $this->alipay($order);
        }else{
            $this->tenPay($order);
        }
    }

    public function alipay($order){
        header("Content-type:text/html;charset=utf-8");
        require_once (SITE_PATH . '/addons/libs/alipay/alipay.config.php');
        require_once (SITE_PATH . '/addons/libs/alipay/lib/alipay_submit.class.php');

        $parameter = array(
		"service" => "create_direct_pay_by_user",
		"partner" => trim($alipay_config['partner']),
		"payment_type"	=> '1',
		"notify_url"	=> 'http://pocketuni.net/addons/libs/alipay/notify_url.php',
		"return_url"	=> 'http://pocketuni.net/index.php?app=home&mod=Account&act=rechargeList&r=1',
		"seller_email"	=> 'alipay@tiangongwang.com',
		"out_trade_no"	=> date('YmdHis').$order['attach'],
		"subject"	=> $order["product_name"],
		"total_fee"	=> money2xs($order["order_price"]),
		"body"	=> '',
		"show_url"	=> '',
		"anti_phishing_key"	=> '',
		"exter_invoke_ip"	=> '',
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
    }

    public function ccbPay($order){
        require_once (SITE_PATH . '/addons/libs/ccb/RequestHandler.class.php');
        $reqHandler = new RequestHandler();
        $reqHandler->setVar('ORDERID', date('YmdHis').$order['attach']);
        $reqHandler->setVar('PAYMENT', money2xs($order["order_price"]));
        //$reqHandler->setVar('PROINFO', iconv("UTF-8","gb2312",$order["product_name"]));
        $reqHandler->setVar('REMARK1', $order['attach']);
        $reqHandler->setVar('PROINFO', 'PocketUni');
        $reqHandler->setVar('REGINFO', $order["attach"]);
        $reqHandler->setVar('CLIENTIP', get_client_ip());
        //var_dump($reqHandler->getUrl());die;
        $reqHandler->doSend();
    }

    public function tenPay($order){
        require_once (SITE_PATH . '/addons/libs/tenpay/tenpay_config.php');
        require_once (SITE_PATH . '/addons/libs/tenpay/classes/RequestHandler.class.php');
        /* 获取提交的订单号 */
        $out_trade_no = date('YmdHis');
        /* 获取提交的商品名称 */
        $product_name = $order["product_name"];
        /* 获取提交的商品价格 */
        $order_price = $order["order_price"];
        /* 获取提交的备注信息 */
        //$remarkexplain = $order["remarkexplain"];
        /* 支付方式 */
        $trade_mode = 1;
        $attach = $order["attach"];
        if(isset($order["return_url"])){
            $return_url = $order["return_url"];
        }
        if(isset($order["notify_url"])){
            $notify_url = $order["notify_url"];
        }

        $strDate = date("Ymd");
        $strTime = date("His");

        /* 商品价格（包含运费），以分为单位 */
        $total_fee = $order_price;

        /* 商品名称 */
        $desc = "商品：".$product_name.",备注:".$attach;

        /* 创建支付请求对象 */
        $reqHandler = new RequestHandler();
        $reqHandler->init();
        $reqHandler->setKey($key);
        $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

//----------------------------------------
//设置支付参数
//----------------------------------------
        $reqHandler->setParameter("partner", $partner);
        $reqHandler->setParameter("out_trade_no", $out_trade_no);
        $reqHandler->setParameter("total_fee", $total_fee);  //总金额
        $reqHandler->setParameter("return_url", $return_url);
        $reqHandler->setParameter("notify_url", $notify_url);
        $reqHandler->setParameter("body", $desc);
        $reqHandler->setParameter("bank_type", "DEFAULT");     //银行类型，默认为财付通
//用户ip
        $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']); //客户端IP
        $reqHandler->setParameter("fee_type", "1");               //币种
        $reqHandler->setParameter("subject", $product_name);          //商品名称，（中介交易时必填）
//系统可选参数
        $reqHandler->setParameter("sign_type", "MD5");       //签名方式，默认为MD5，可选RSA
        $reqHandler->setParameter("service_version", "1.0");    //接口版本号
        $reqHandler->setParameter("input_charset", "utf-8");      //字符集
        $reqHandler->setParameter("sign_key_index", "1");       //密钥序号
//业务可选参数
        $reqHandler->setParameter("attach", $attach);                //附件数据，原样返回就可以了
        $reqHandler->setParameter("product_fee", "");           //商品费用
        $reqHandler->setParameter("transport_fee", "0");         //物流费用
        $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
        $reqHandler->setParameter("time_expire", "");             //订单失效时间
        $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
        $reqHandler->setParameter("goods_tag", "");               //商品标记
        $reqHandler->setParameter("trade_mode", $trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $reqHandler->setParameter("transport_desc", "");              //物流说明
        $reqHandler->setParameter("trans_type", "1");              //交易类型
        $reqHandler->setParameter("agentid", "");                  //平台ID
        $reqHandler->setParameter("agent_type", "");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $reqHandler->setParameter("seller_id", "");                //卖家的商户号

        //var_dump($reqHandler->getRequestURL());die;

        $reqHandler->doSend();
    }

    public function run() {
        return true;
    }

}