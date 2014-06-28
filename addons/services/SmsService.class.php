<?php
/**
 * 通行证服务
 *
 * @author daniel <desheng.young@gmail.com>
 */
class SmsService extends Service
{
    public function sendsms($mobile,$msg){
        return array('status'=>'0','msg'=>'失败','body'=>'本地不可发送');
        return $this->bayou($mobile,$msg);
    }


    public function liangping($mobile,$msg){
        $name = 'tiangongwangluo';
        $pass = base64_encode('888888');
        require_once(SITE_PATH . '/addons/libs/LiangpingSmsSender.php');
        $sender=new LiangpingSmsSender();
        $result = $sender->sendsms($name,$pass,$mobile, $msg);
        return $result;
    }

    public function bayou($mobile,$msg){
        $name = '636978';
        $pass = md5("113314446");
        require_once(SITE_PATH . '/addons/libs/BayouSmsSender.php');
        $sender=new BayouSmsSender();
        $result = $sender->sendsms($name,$pass,$mobile, $msg.' 口袋校园');
        return $result;
    }

    public function run(){
            return true;
    }
}