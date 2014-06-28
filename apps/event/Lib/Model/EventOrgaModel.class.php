<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

class EventOrgaModel extends BaseModel {
    public function createOrga($map){
        if(!isset($map['cTime'])){
            $map['cTime'] = time();
        }
        return $this->add($map);
    }
}