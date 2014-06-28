<?php

/**
 * EventNewsModel
 * 活动的新闻模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventNewsModel extends BaseModel {

    public function doDelete($map) {
        return $this->where($map)->delete();
    }

    public function getHandyList($map = array(), $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $list = $this->field('id,title,cTime')->where($map)->order('id DESC')->limit("$offset,$limit")->select();
        foreach ($list as $key => $value) {
            $row = $value;
            $row['cTime'] = date('Y-m-d H:i', $row['cTime']);
            $list[$key] = $row;
        }
        return $list;
    }

    public function getNews($map){
        $res = $this->field('id,title,content,cTime')->where($map)->find();
        if($res){
            $res['content'] = strip_tags(htmlspecialchars_decode($res['content']),'<img>');
            return $res;
        }
        return FALSE;
    }

}
