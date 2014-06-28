<?php

class JpushService extends Service {

    public $objJpush;

    public function __construct() {
        $this->init();
    }

    protected function init() {
       include_once(SITE_PATH.'/addons/libs/Jpush.class.php');
        $this->objJpush =  new Jpush();
    }

      //推送消息
    public function jsend($action,$n_title,$n_content){
            switch ($action){
        	case 'send':
			$receiver_value = '';
			$sendno = 2211;
			$platform = 'android' ;
			$msg_content = json_encode(array('content_type'=>0, 'title'=>$n_title, 'message'=>$n_content));
			$res =  $this->objJpush->send($sendno, 4, $receiver_value, 2, $msg_content, $platform);
			exit();
			break;
        	case 'check':
			break;
            }

    }

    public function sendToUid($n_extras,$to,$n_content='',$n_title='新私信'){
        $toUser = M('user')->where('uid='.$to)->field('clientType')->find();
        if(!$toUser){
            return false;
        }
        $clientType = $toUser['clientType'];
        if($clientType!=1 && $clientType!=2){
            return false;
        }
        $n_content = mStr($n_content, 10, 'utf-8', '...');
        $receiver_value = $to;
        $sendno = 2211;
        $res = false;
        if ($clientType == 2) {
            $platform = 'ios';
            $msg_content = json_encode(array('n_builder_id' => 0, 'n_title' => $n_title, 'n_content' => $n_content, 'n_extras' => $n_extras));
            $obj = new Jpush();
            $res = $obj->send($sendno, 3, $receiver_value, 1, $msg_content, $platform);
        } elseif ($clientType == 1) {
            $platform = 'android';
            $msg_content = json_encode(array('content_type' => 0, 'title' => $n_title, 'message' => $n_content, 'extras' => $n_extras));
            $obj = new Jpush();
            $res = $obj->send($sendno, 3, $receiver_value, 2, $msg_content, $platform);
        }
        return $res;
    }

      //运行服务，系统服务自动运行
	public function run(){

	}

}

?>
