<?php

class RequestHandler {

    private $pubstr = '30819d300d06092a864886f70d010101050003818b0030818702818100c042ecbf4db58a14d7539e150f8287b95d66fadf6136f49d44262d5757c25895dcfe99cf1a517a3a02f43af95b01be656ca177f2cf7e64a83e18b178abaa9f1d8f3deedfe1b0759952531c4302b6f62d564827317d13c52935b65d34b3b1f73237d3a2c82964bab0a8de157e7de6082799cb57842caac9b1de0f9a87b77748e1020111';
    private $MERCHANTID = '105320582990055';        //商户代码
    private $POSID = '081276692';            //商户柜台代码
    private $BRANCHID = '322000000';         //分行代码
    private $ORDERID = '';           //订单编号
    private $PAYMENT = '';           //订单金额
    private $CURCODE = '';           //币种
    private $TXCODE = '520100';            //交易码
    private $REMARK1 = '';           //备注1
    private $REMARK2 = '';           //备注2
    private $TYPE = '';              //接口类型
    private $GATEWAY = '';           //网关类型
    private $CLIENTIP = '';          //客户端ip地址
    private $PUB32TR2 = '';          //公钥后30位
    private $bankURL = '';           //提交url
    private $REGINFO = '';           //注册信息
    private $PROINFO = '';           //商品信息
    private $REFERER = '';            //商户域名
    private $URL = '';
    private $tmp = '';
    private $temp_New = '';
    private $temp_New1 = '';

    /**
     * 构造函数  封装参数
     * @return  void
     */
    public function __construct() {
        $this->CURCODE = '01';
        $this->bankURL = 'https://ibsbjstar.ccb.com.cn/app/ccbMain';
        $this->TYPE = 1;
        $this->PUB32TR2 = substr($this->pubstr, -30);
        $this->GATEWAY = '';
        $this->REFERER = '';
    }

    public function getVar($name) {
        return $this->$name;
    }

    public function setVar($name,$value) {
        return $this->$name = urlencode($value);
        //return $this->$name = $value;
    }

    /**
     * 生成url
     * @access  public
     * @return  url
     */
    public function getUrl() {
        $this->tmp ='MERCHANTID=' . $this->MERCHANTID . '&POSID=' . $this->POSID . '&BRANCHID=' . $this->BRANCHID . '&ORDERID=' . $this->ORDERID . '&PAYMENT=' . $this->PAYMENT . '&CURCODE=' . $this->CURCODE . '&TXCODE=' . $this->TXCODE . '&REMARK1=' . $this->REMARK1 . '&REMARK2=' . $this->REMARK2;
        $this->temp_New =$this->tmp . "&TYPE=" . $this->TYPE . "&PUB=" . $this->PUB32TR2 . "&GATEWAY=" . $this->GATEWAY . "&CLIENTIP=" . $this->CLIENTIP . "&REGINFO=" . $this->REGINFO . "&PROINFO=" . $this->PROINFO . "&REFERER=" . $this->REFERER;
        $this->temp_New1 =$this->tmp . "&TYPE=" . $this->TYPE . "&GATEWAY=" . $this->GATEWAY . "&CLIENTIP=" . $this->CLIENTIP . "&REGINFO=" . $this->REGINFO . "&PROINFO=" . $this->PROINFO . "&REFERER=" . $this->REFERER;

        $strMD5 = md5($this->temp_New);
        $this->URL .= $this->bankURL . "?" . $this->temp_New1 . "&MAC=" . $strMD5;
        return $this->URL;
    }

    /**
    *重定向到财付通支付
    */
    public function doSend() {
        header("Location:" . $this->getUrl());
        exit;
    }

    public function writeLog($order) {

        $fp = fopen('/' . $order['order_sn'] . '.txt', 'a');
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, "提交到建行支付页面时间：\r");
            fwrite($fp, local_date('Y-m-d H:i:s'));
            fwrite($fp, "\n");
            fwrite($fp, "传递url参数信息：\n");
            fwrite($fp, $this->getUrl());
            fwrite($fp, "\n记录支付前数据信息:\n");
            fwrite($fp, "订单号：" . $order['order_sn'] . "\r订单金额：" . $order['order_amount']);
            fwrite($fp, "\r\n\n\n");
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

}