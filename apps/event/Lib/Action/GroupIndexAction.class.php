<?php

/**
 * FrontAction
 * 部落页面
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class GroupIndexAction extends GroupBaseAction {

    protected $group;

    public function _initialize() {
        parent::base();
        $this->group = D('EventGroup');
          $domain = parse_url($_SERVER['SERVER_NAME']);
         $domain = substr($domain['path'], 0, strpos($domain['path'], '.'));
         $this->assign('domain',$domain);
         $this->setTitle('校园部落');
    }

    public function index() {
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['group_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['group_searchCat']['cat'] = t($_GET['cat']);
        }
        if ($_GET['cid0'] == 'all') {
            unset($_SESSION['group_searchCat']['cid0']);
        } elseif ($_GET['cid0']) {
            $_SESSION['group_searchCat']['cid0'] = intval($_GET['cid0']);
        }
        if ($_GET['year'] == 'all') {
            unset($_SESSION['group_searchCat']['year']);
        } elseif ($_GET['year']) {
            $_SESSION['group_searchCat']['year'] = t($_GET['year']);
        }
        if ($_GET['school'] == 'all') {
            unset($_SESSION['group_searchCat']['school']);

        } elseif ($_GET['school']) {
            $_SESSION['group_searchCat']['school'] = intval($_GET['school']);
        }
        if ($_GET['category'] == 'all') {
            unset($_SESSION['group_searchCat']['category']);

        } elseif ($_GET['category']) {
            $_SESSION['group_searchCat']['category'] = intval($_GET['category']);
        }
        if(isset($_POST['title'])){
            $searchTitle = t($_POST['title']);
            if (mb_strlen($searchTitle, 'utf8') < 1) {
                unset($_SESSION['group_searchCat']['title']);
            }else{
                $_SESSION['group_searchCat']['title'] = $searchTitle;
            }
        }
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['school'] = $this->sid;
        if($_SESSION['group_searchCat']['title']){
            $map['name'] = array('like', "%" . $_SESSION['group_searchCat']['title'] . "%");
            $this->assign('searchTitle', $_SESSION['group_searchCat']['title']);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_SESSION['group_searchCat']['cid0']) {
            $map['cid0'] = $_SESSION['group_searchCat']['cid0'];
        }
        if ($_SESSION['group_searchCat']['category']) {
            $map['category'] = $_SESSION['group_searchCat']['category'];
        }
        if ($_SESSION['group_searchCat']['year']) {
            $map['year'] = $_SESSION['group_searchCat']['year'];
        }
        if ($_SESSION['group_searchCat']['school']) {
            $map['sid1'] = $_SESSION['group_searchCat']['school'];
            if($map['sid1']==-1){
                  $schoolname = '校级';
            }else{
            $schoolname = tsGetSchoolName($_SESSION['group_searchCat']['school']);
            }
        }else{
               $schoolname = '全校';
        }
        switch ($_SESSION['group_searchCat']['cat']) {
            case 'manage':
                $member = D('GroupMember')->where("level IN (1,2) AND uid=" . $this->mid)->field('gid')->findAll();
                $ids = getSubByKey($member, 'gid');
                $map['id'] = array('in', $ids);
                break;
            case 'join':
                $member = D('GroupMember')->where("level=3 AND uid=" . $this->mid)->field('gid')->findAll();
                $ids = getSubByKey($member, 'gid');
                $map['id'] = array('in', $ids);
                break;
            default:
        }
        if ($init = $this->_uninit()) {
            $uninit = 'uninit';
            $this->assign('init',$init);
            $this->assign('uninit',$uninit);
        }

        $result = $this->group->getGroupList($map, $this->mid);
        $hot_group_list = $this->group->getHotList($this->sid);
        $new_group_list = $this->group->getNewList($this->sid);
        $this->assign('hot_group_list', $hot_group_list);
        $this->assign('new_group_list', $new_group_list);
        $this->assign('year',$this->_getYear());
        $this->assign('schoolname',$schoolname);
        $this->assign('catList',$this->_getCategoryList());
        $this->assign($result);
        $this->display();
    }

    private function _uninit() {
        $map['is_del'] = 0;
        $map['is_init'] = 0;
        $map['disband'] = 0;
        $map['school'] = $this->sid;
        $map['uid'] = $this->mid;
        $result = $this->group->where($map)->field("id,name")->limit(4)->findAll();
        return $result;
    }

    private function  _getYear() {
        $thisYear = date('y', time());
        $years = array();
        for ($i = 9; $i <= $thisYear; $i++) {
            $years[] = sprintf("%02d", $i);
        }
        return $years;
    }

    private function _getCategoryList() {
        $res = M('group_category')->field('id,title')->findAll();
        return $res;
        ;
    }

    public function newSubSchool() {
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('school', $school);
        $this->display();
    }

}

?>
