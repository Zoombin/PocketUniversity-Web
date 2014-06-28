<?php

/**
 * 学校模型
 *
 * @author lu,dongyun <rechner00@hotmail.com>
 */
class CitysModel extends Model {

    //生成分类Tree
    public function getAllCitys(){
        if ($cache = S('Cache_Citys')) { // pid=0 才缓存
            return $cache;
        }
        $citys = M('citys')->order('short ASC')->findAll();
        S('Cache_Citys', $citys);
        return $citys;
    }

    public function addCity($city,$short){
        $data['city'] = $city;
        $data['short'] = $short;
        M('citys')->add($data);
        S('Cache_Citys', null);
    }
}