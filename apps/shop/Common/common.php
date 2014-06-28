<?php

function orderState($id) {
    $roles = array('0'=>'未提交收货地址','1'=>'等待发货','2'=>'已发货，未确认收货','3'=>'定金已付，等待开团','4'=>'未付尾款','10'=>'交易完成','11'=>'交易取消');
    if(isset($roles[$id])){
        return $roles[$id];
    }
    return '';
}
function tgRestTime($day){
    $second = strtotime($day.'10:00:00')-time();
    return time2string($second);
}
function currentPrice($sprice,$eprice,$eprice_attended,$has_attended,$dec){
    $sprice = $sprice*100;
    $eprice = $eprice*100;
    if($has_attended<=1){
        return $sprice;
    }
    if($has_attended>=$eprice_attended){
        return $eprice;
    }
    $decNum = $has_attended-1;
    $cprice = $sprice-($dec*$decNum);
    if($cprice<$eprice){
        return $eprice;
    }
    return $cprice;
}

function getCityName($id){
   return M('citys')->getField('city', 'id='.$id);
}