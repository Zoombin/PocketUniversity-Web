<?php

class CourseApi extends Api {

    public function test() {
        return 2232;
    }

    public function courseList() {
        $map['isDel'] = 0;
        $map['status'] = 2;
        $user = M('user')->where('uid =' . $this->mid)->getField('sid');
        $map['sid'] = $user['sid'];
        if ($_REQUEST['action'] == 'join') {
            $map_join['uid'] = $this->mid;
            $courseIds = M('course_user')->field('courseId')->where($map_join)->findAll();
            foreach ($courseIds as $v) {
                $in_arr[] = $v['courseId'];
            }
            $map['id'] = array('in', $in_arr);
        } else {
            $map['limitCount'] = array('gt', 0);
            $map['deadline'] = array('gt', time());
        }
        $cid = intval($_REQUEST['cid']);
        if ($cid) {
            $map['typeId'] = $cid;
        }
        $keyword = t($_REQUEST['keyword']);
        if ($keyword) {
            $map['title'] = array('like', "%" . $keyword . "%");
        }
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = D('Course', 'event')->apiCourseList($map, $count, $page);
        if($list){
            return $list;
        }else{
            return array();
        }
    }

    public function catList() {
        $result = M('course_type')->field('id,name')->order('id')->findAll();
        return $result;
    }
    public function course(){
        $id = intval($_REQUEST['id']);
        $res = D('Course','event')->apiCourse($id,$this->mid);
       return $res;
    }

    public function joinCourse() {
        $id = intval($_REQUEST['id']);
        $uid = $this->mid;
        $user = M('user')->where('uid =' . $uid)->field('realname,sex,email,sid,mobile')->find();
        $data['id'] = $id;
        $data['uid'] = $this->mid;
        $data['tel'] = $user['mobile'];
        $data['realname'] = $user['realname'];
        $data['sex'] = $user['sex'];
        $data['studentId'] = getUserEmailNum($user['email']);
        $data['sid'] = $user['sid'];
        $res = D('Course', 'event')->apiAddUser($data);
        return $res;
    }
}
