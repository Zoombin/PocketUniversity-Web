<?php

/**
 * 摇一摇
 *
 * @author lu,dongyun <rechner00@hotmail.com>
 */
class LyModel extends Model {

    protected $tableName = 'ly_day';
    private $isClose = 0; //是否关闭
    private $maxLucky = 5; //最多奖品
    private $lucky = 1000; //中奖率

    public function canCj($uid){
        if($this->isClose){
            return false;
        }
        if($this->luckyCount() >= $this->maxLucky){
            return false;
        }
        if($this->todayDone($uid)){
            return false;
        }
        return true;
    }

    //中奖人数
    public function luckyCount(){
        return M('ly_lucky')->count();
    }

    //今日已抽奖次数
    public function todayDone($uid){
        $map['uid'] = $uid;
        $map['day'] = date('Y-m-d');
        return $this->where($map)->count();
    }

    public function cj($uid){
        if(!$this->canCj($uid)){
            return false;
        }
        $res = false;
        if($this->add(array('uid'=>$uid,'day'=>date('Y-m-d')))){
            if($this->zj()){
                $zjData = array('uid'=>$uid,'ctime'=>time());
                if(M('ly_lucky')->add($zjData)){
                    $res = true;
                }
            }
        }
        return $res;
    }

    private function zj(){
        $rand = mt_rand(1, $this->lucky);
        if($rand == 1){
            return true;
        }
        return false;
    }
}