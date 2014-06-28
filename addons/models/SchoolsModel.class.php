<?php

/**
 * 学校模型
 *
 * @author lu,dongyun <rechner00@hotmail.com>
 */
class SchoolsModel extends Model {

    protected $tableName = 'school';

    //生成分类Tree
    public function _makeTree($pid) {

        if ($pid == 0 && $cache = S('Cache_School_' . $pid)) { // pid=0 才缓存
            return $cache;
        }

        if ($c = $this->where("pid='$pid'")->order('display_order ASC, id ASC')->findAll()) {
            if ($pid == 0) {
                foreach ($c as $v) {
                    $cTree['t'] = $v['title'];
                    $cTree['a'] = $v['id'];
                    $cTree['d'] = $this->_makeTree($v['id']);
                    $cTrees[] = $cTree;
                }
            } else {
                foreach ($c as $v) {
                    $cTree['t'] = $v['title'];
                    $cTree['a'] = $v['id'];
                    $cTree['d'] = ''; //$v['id'];
                    $cTrees[] = $cTree;
                }
            }
        }
        $pid == 0 && S('Cache_School_' . $pid, $cTrees); // pid=0 才缓存
        return $cTrees;
    }

    public function makeLevelTree($pid, $level = '0') {
        if ($pid == 0 && $cache = S('Cache_School_Level_' . $pid)) { // pid=0 才缓存
            return $cache;
        }
        if ($result = $this->where("pid='$pid'")->order('display_order ASC, id ASC')->findAll()) {
            $list = array();
            foreach ($result as $key => $value) {
                $c = array();
                $id = $value['id'];
                $c['id'] = $id;
                $c['pid'] = $value['pid'];
                $c['title'] = $value['title'];
                $c['level'] = $level;
                $c['child'] = $this->makeLevelTree($id, $level + 1);
                $list[] = $c;
            }
        }
        $pid == 0 && S('Cache_School_Level_' . $pid, $list); // pid=0 才缓存
        return $list;
    }
    //显示当前id的父类
    public function makeLevelParentTree($pid) {
        if($pid == 0){
            return $this->makeLevelTree($pid);
        }
        $kid = $pid;
        $parents = array();
        while($parent = $this->where("id='$kid'")->find()){
            if($parent['pid']!=0){
                $parents[] = $parent;
            }
            $kid = $parent['pid'];
        }
        $cnt = count($parents);
        $list = null;
        if($cnt){
            foreach ($parents as $k=>$value) {
                $c = array();
                $c['id'] = $value['id'];
                $c['pid'] = $value['pid'];
                $c['title'] = $value['title'];
                $c['level'] = $cnt-$k-1;
                if($k == 0){
                    $c['child'] = $this->makeLevelTree($pid, $cnt);
                }else{
                    $c['child'] = $list;
                }
                $list[] = $c;
            }
        }else{
            return $this->makeLevelTree($pid);
        }
        //var_dump($list);echo'xxxxxxx<br/>';
        return $list;
    }

    public function makeLevel0Tree($pid=0, $level = '0') {
        if ($cache = S('Cache_School_Level0_' . $pid)) { // pid=0 才缓存
            return $cache;
        }
        if ($result = $this->where("pid='$pid'")->order('display_order ASC')->findAll()) {
            foreach ($result as $key => $value) {
                $id = $value['id'];
                $list[$id]['id'] = $id;
                $list[$id]['pid'] = $value['pid'];
                $list[$id]['display_order'] = $value['display_order'];
                $list[$id]['title'] = $value['title'];
                $list[$id]['cityId'] = $value['cityId'];
                $list[$id]['domain'] = $value['domain'];
                $list[$id]['level'] = $level;
            }
        }
        S('Cache_School_Level0_' . $pid, $list); // pid=0 才缓存
        return $list;
    }

    public function _makeTopTree() {
        $school = $this->_makeTree(0);
        foreach ($school as &$value) {
            unset($value['d']);
        }
        return $school;
    }

    //获取LI列表
    public function getCategoryList($pid = '0') {
        $list = $this->_makeLiTree($pid);
        return $list;
    }

    public function _makeLiTree($pid) {

        if ($c = $this->where("pid='$pid'")->findAll()) {

            $list .= '<ul>';
            foreach ($c as $p) {
                @extract($p);

                $ptitle = "<span id='category_" . $id . "' title='" . $title . "'><a href='javascript:void(0)' onclick=\"edit('" . $id . "')\">" . $title . "</a></span>";
                $title = '[' . $id . '] ' . $ptitle;

                $list .= '
					<li id="li_' . $id . '">
					<span style="float:right;">
						<a href="javascript:void(0)" onclick="edit(\'' . $id . '\')" style="font-size:9px">修改</a>
						<a href="javascript:void(0)" onclick="del(\'' . $id . '\')" style="font-size:9px">删除</a>
					</span> ' . $title . '
					</li>
					<hr style="height:1px;color:#ccc" />';

                $list .= $this->_makeLiTree($id);
            }
            $list .= '</ul>';
        }
        return $list;
    }

    //解析分类
    public function _digCate($array) {

        foreach ($array as $k => $v) {

            $nk = str_replace('pid', '', $k);
            if (is_numeric($nk) && !empty($v)) {
                $cates[$nk] = intval($v);
            }
        }
        $pid = is_array($cates) ? end($cates) : 0;

        unset($cates);
        return intval($pid);
    }

    //解析分类树
    public function _digCateTree($array) {
        foreach ($array as $k => $v) {
            $nk = str_replace('pid', '', $k);
            if (is_numeric($nk) && !empty($v)) {
                $cates[$nk] = intval($v);
            }
        }
        if (is_array($cates)) {
            return implode(',', $cates);
        } else {
            return intval($cates);
        }
    }

    //生成分类树
    public function _makeParentTree($id, $onlyShowPid = false) {
        $tree = $this->_makeCateTree($id);
        if ($onlyShowPid) {
            $tree = str_replace(',' . $id, '', $tree);
        }
        return $tree;
    }

    public function _makeCateTree($id) {
        //$pid	=	$this->find($id,'pid')->pid;

        $pid = $this->getField('pid', 'id=' . $id);
        if ($pid > 0) {
            $tree = $this->_makeCateTree($pid) . ',' . $id;
        } else {
            $tree = $id;
        }
        return $tree;
    }

    /**
     * 获取分组列表
     * @return unknown_type
     */
    public function __getCategory($pid = -1) {
        if ($pid != -1) {
            $categorys = $this->where("pid='$pid'")->order('display_order ASC, id ASC')->findAll();
        } else {
            if ($cache = S('Cache_School_Category')) {
                return $cache;
            }
            $categorys = $this->order('display_order ASC, id ASC')->findAll();
        }
        foreach ($categorys as $v) {
            $categorys_[$v['id']]['title'] = $v['title'];
            $categorys_[$v['id']]['pid'] = $v['pid'];
            $categorys_[$v['id']]['cityId'] = $v['cityId'];
        }
        if($pid == -1){
            S('Cache_School_Category', $categorys_);
        }
        return $categorys_;
    }

    /**
     * 发布活动，选择校区
     */
    public function getEventSelect($sid) {
        if ($sid == 0) {
            $all = $this->_makeTree($sid);
        } else {
            $parent = $this->where("id='$sid'")->find();
            if (!$parent) {
                return array();
            }
            //如果是院，返回上层学校的结果
            if ($parent['pid'] != 0) {
                return $this->getEventSelect($parent['pid']);
            }
            $all[0]['t'] = $parent['title'];
            $all[0]['a'] = $parent['id'];
            $all[0]['d'] = $this->_makeTree($sid);
        }

        foreach ($all as $value) {
            $obj['id'] = $value['a'];
            $obj['title'] = $value['t'];
            $result[] = $obj;
            foreach ($value['d'] as $v2) {
                $obj['id'] = $v2['a'];
                $obj['title'] = $value['t'] . '-' . $v2['t'];
                $result[] = $obj;
            }
        }
        return $result;
    }

    public function getChildIds($pid){
        $result = array();
        $cats = $this->__getCategory();
        if(isset($cats[$pid])){
            $result[] = $pid;
            if($cats[$pid]['pid'] == 0){
                foreach ($cats as $key=>$value) {
                    if($value['pid'] == $pid){
                        $result[] = $key;
                    }
                }
            }
        }
        return $result;
    }
}