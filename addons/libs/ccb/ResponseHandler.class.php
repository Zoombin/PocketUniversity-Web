<?php

class ResponseHandler {

    /** 应答的参数 */
    var $parameters;

    /** debug信息 */
    var $debugInfo;

    public function __construct() {
        $this->ResponseHandler();
    }

    public function ResponseHandler() {
        $this->parameters = array();
        $this->debugInfo = "";

        /* GET */
        foreach ($_GET as $k => $v) {
            $this->setParameter($k, $v);
        }
        /* POST */
        foreach ($_POST as $k => $v) {
            $this->setParameter($k, $v);
        }
    }

    /**
     * 获取参数值
     */
    public function getParameter($parameter) {
        return $this->parameters[$parameter];
    }

    /**
     * 设置参数值
     */
    public function setParameter($parameter, $parameterValue) {
        $this->parameters[$parameter] = $parameterValue;
    }

    /**
     * 获取所有请求的参数
     * @return array
     */
    public function getAllParameters() {
        return $this->parameters;
    }

    /**
     * 是否财付通签名,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     * true:是
     * false:否
     */
    public function isCcbSign() {
        $backUrl = $_SERVER['QUERY_STRING'];
        return $this->_isCcbSign($backUrl . "\n");
    }

    private function _isCcbSign($send_data) {
        $this->_setDebugInfo($send_data);
        $address = '127.0.0.1';
        $service_port = 35432;
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            return false;
        }
        $result = socket_connect($socket, $address, $service_port);
        if ($result < 0) {
            return false;
        }
        //发送命令
        $in = $send_data;
        socket_write($socket, $in, strlen($in));
        $out = socket_read($socket, 2048);
        socket_close($socket);
        if(substr($out, 0, 2) == 'Y|'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取debug信息
     */
    public function getDebugInfo() {
        return $this->debugInfo;
    }

    /**
     * 设置debug信息
     */
    public function _setDebugInfo($debugInfo) {
        $this->debugInfo = $debugInfo;
    }

}

?>