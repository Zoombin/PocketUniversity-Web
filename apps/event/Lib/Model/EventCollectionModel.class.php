<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

class EventCollectionModel extends BaseModel {

    //add添加或cancel取消收藏
    public function fav($uid,$eid,$type){
        $data['uid'] = $uid;
        $data['eid'] = $eid;
        if($type=='add'){
            $data['time'] = time();
            return $this->add($data);
        }
        if($type=='cancel'){
            return $this->where($data)->delete();
        }
        return false;
    }
}