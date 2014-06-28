<?php

class SchoolWebModel extends Model {

    public function getConfigDb($sid){
        $obj = $this->where('sid='.$sid)->find();
        if(!$obj){
            return array();
        }
        return $obj;
    }

    public function getConfigCache($sid){
        $config = S('S_config_'.$sid);
        if(empty($config)) {
            $config = $this->getConfigDb($sid);
            S('S_config_'.$sid, $config);
        }
        return $config;
    }

    public function editSchoolWeb($data){
        $config = S('S_config_'.$data['sid']);
        if(empty($config)) {
            $res = $this->add($data);
        }else{
            $res = $this->save($data);
        }
        if($res){
            S('S_config_'.$data['sid'], null);
        }
        return $res;
    }

}

?>