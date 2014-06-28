<?php
//使用V2版本的客户端,支持Oauth2.0
include_once( 'sina/saetv2.ex.class.php');
class sina{

	var $loginUrl;
	private $_sina_akey;
	private $_sina_skey;
	private $_oauth;

	public function __construct() {
		$this->_sina_akey = SINA_WB_AKEY;
		$this->_sina_skey = SINA_WB_SKEY;
		$this->_oauth = new SaeTOAuthV2( $this->_sina_akey , $this->_sina_skey );
	}

    public function getUrl($call_back = null) {
		if ( empty($this->_sina_akey) || empty($this->_sina_skey) )
			return false;
		if (is_null($call_back)) {
			$call_back = U('home/public/callback');
		}
		$this->loginUrl = $this->_oauth->getAuthorizeURL( $call_back );
		return $this->loginUrl;
	}

	//用户资料
	public function userInfo(){
		$sinauid = $this->doClient()->get_uid();
		$me = $this->doClient()->show_user_by_id($sinauid['uid']);
		$user['id']          = $me['id'];
		$user['uname']       = $me['name'];
		$user['province']    = $me['province'];
		$user['city']        = $me['city'];
		$user['location']    = $me['location'];
		$user['userface']    = str_replace(  $user['id'].'/50/' , $user['id'].'/180/' ,$me['profile_image_url'] );
		$user['sex']         = ($me['gender']=='m')?1:0;
		return $user;
	}

    private function doClient($opt){
		return new SaeTClientV2( $this->_sina_akey , $this->_sina_skey , $_SESSION['sina']['access_token']['oauth_token'] );
	}

	//验证用户
    public function checkUser(){
		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = SITE_URL;
			try {
				$token = $this->_oauth->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
				$token = null;
			}
		}else{
			return false;
		}

		if ($token) {
			setcookie( 'weibojs_'.$this->_oauth->client_id, http_build_query($token) );
			$_SESSION['sina']['access_token']['oauth_token'] = $token['access_token'];
			$_SESSION['sina']['access_token']['oauth_token_secret'] = $token['refresh_token'];
			$_SESSION['sina']['uid'] = $token['uid'];
			$_SESSION['open_platform_type'] = 'sina';
		}else{
			return false;
		}
	}

	//发布一条微博
	public function update($text,$opt){
		return $this->doClient($opt)->update($text);
	}

        public function syncWeibo($token,$uid,$type_uid,$since_id){
            $client = new SaeTClientV2( $this->_sina_akey , $this->_sina_skey , $token );
            $result = $client->user_timeline_by_id($type_uid, 1, 50, $since_id, 0, 0, 1, 0);
            if(!empty($result['statuses'])){
                //更新$since_id
                model('Xdata')->put('sync_weibo:sina',$result['statuses'][0]['idstr'],true);
                $pWeibo = D('WeiboSync','weibo');
                $from_data['url'] = 'http://weibo.com';
                $from_data['source'] = '新浪微博';
                $from_data = serialize($from_data);
                foreach ($result['statuses'] as $value) {
                    //判断是否来源新浪微博
                    if(strpos($value['source'], '新浪微博')){
                        $content = t($value['text']);
                        if(isset($value['original_pic'])){
                            $content .= $value['original_pic'];
                        }
                        $pWeibo->doSaveWeibo($uid, $content,$from_data);
                    }
                }
            }
        }

        //上传一个照片，并发布一条微博
	public function upload($text,$opt,$pic){
		return $this->doClient($opt)->upload($text,$pic);
	}

	//转发一条微博
    public function transpond($transpondId,$reId,$content='',$opt=null){

		if($reId){
			$this->doClient($opt)->send_comment($reId,$content);
		}

        if($transpondId){
            $result = $this->doClient($opt)->repost($transpondId,$content);
        }
	}

	//保存数据
	public function saveData($data){
		if(isset($data['id'])){
			return array("sinaId"=>$data['id']);
		}
		return array();
	}
}
?>