<?php

/**
 * JfdhModel
 * 兑换历史记录
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcTypeModel extends Model {

    public function getEcType($sid) {
        $cache = S('Cache_Ec_Type_'.$sid);
        if ($cache) {
            return $cache;
        }
        $param = $this->where('sid=' . $sid)->find();
        if (!$param) {
            $param = array();
        }
        S('Cache_Ec_Type_'.$sid, $param);
        return $param;
    }

    public function editEcType($input){
        $data['sid'] = $input['sid'];
        $realname = t($input['realname']);
        $defaultName['realname'] = $realname?$realname:'选手名称';
        $school = t($input['school']);
        $defaultName['school'] = $school?$school:'选手院校';
        $content = t($input['content']);
        $defaultName['content'] = $content?$content:'简介';
        $path = t($input['path']);
        $defaultName['path'] = $path?$path:'头像+展示图片';
        $data['defaultName'] = serialize($defaultName);
        $paramCount = intval($input['paramCount']);
        for($i=0;$i<=$paramCount;$i++){
            $key = 'param_'.$i;
            $name = '';
            $row = array();
            if(isset($input[$key])){
                $name = t($input[$key]);
            }
            if($name != ''){
                $row[] = $name;
                $key = 'type_'.$i;
                $row[] = t($input[$key]);
                $key = 'wr_ok_'.$i;
                $row[] = isset($input[$key])?1:0;
                $key = 'show_ok_'.$i;
                $row[] = isset($input[$key])?1:0;
            }
            if(!empty($row)){
                $parameter[] = $row;
            }
        }
        $data['parameter'] = serialize($parameter);
        $res = M('event_parameter')->save($data);
        if(!$res){
            $res = M('event_parameter')->add($data);
        }
        if($res){
            S('Cache_Event_Param_'.$input['eventId'], null);
            return true;;
        }
        $this->error = '保存失败';
        return false;
    }

}

?>