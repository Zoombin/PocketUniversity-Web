<?php

class YyApi extends Api {

    public function __construct() {
        parent::__construct();
    }

    public function test() {
        var_dump($this->mid);
        return 2;
    }

    //取得当前摇摇次数
    //返回 数组 rest剩余次数，times当前次数，cost费用
    public function status() {
        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
//        if($user_sid!=473){
//            return array('rest'=>0,'times'=>51,'cost'=>999);;
//        }
        return Model('Yuser')->getStatus($this->mid);
    }

    //摇-摇
    //返回 数组 status状态，msg信息，data数据（type 1空2钱3人）
    public function yy() {
        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
//        if($user_sid!=473){
//            return array('status'=>0,'msg'=>'维护中，稍后再试！');
//        }
        return Model('Yuser')->yy($this->mid);
    }


}
