<?php

/**
 * 摇一摇
 *
 * @author lu,dongyun <rechner00@hotmail.com>
 */
class YuserModel extends Model {

    protected $tableName = 'y_user';
    private $maxTimes = 50; //每天最多摇的次数
    private $maxMoney = 100000; //免费每天摇出pu币上限（分）

    public function getStatus($uid){
        $res = $this->where('y_uid='.$uid)->find();
        if(!$res){
            return array('rest'=>50,'times'=>1,'cost'=>0);
        }
        if($res['day'] != date('Y-m-d')){
            return array('rest'=>50,'times'=>1,'cost'=>0);
        }
        if($res['y_times']>$this->maxTimes){
            return array('rest'=>0,'times'=>51,'cost'=>999);
        }
        $data['rest'] = $this->maxTimes-$res['y_times']+1;
        $data['times'] = $res['y_times']+0;
        $data['cost'] = yCost($res['y_times']);
        return $data;

    }

    public function addTimes($uid,$times,$cost,$data){
        $today = date('Y-m-d');
        $res = $this->where('y_uid='.$uid)->find();
        if(!$res){
            $this->add(array('y_uid'=>$uid,'y_times'=>2,'day'=>$today));
        }elseif($res['day'] != $today){
            $this->save(array('y_uid'=>$uid,'y_times'=>2,'day'=>$today));
        }else{
            $this->setInc('y_times', "y_uid=$uid");
        }
        //统计
        $this->_addTj($cost, $data);

        //log
        if($data['type']==2){
            $save['lucky_uid'] = $uid;
            $save['y_times'] = $times;
            $save['type'] = $data['type'];
            $save['product'] = $data['win'];
            $save['day'] = $today;
            M('y_lucky')->add($save);
        }
    }

    public function yy($uid){
        $status = $this->getStatus($uid);
        if($status['rest']<=0){
            return array('status'=>0,'msg'=>'今天已满'.$this->maxTimes.'次，请明天再摇');
        }
        //付钱
        $pay = true;
        if($status['cost']>0){
            $pay = Model('Money')->moneyOut($uid, $status['cost'], '摇一摇');
        }
        $currentPu = $status['cost']/100;
        if(!$pay){
            return array('status'=>0,'msg'=>'账户PU币不足'.$currentPu.'，请先充值');
        }
        $data['status'] = 1;
        $data['data'] = $this->lucky($uid,$status['times']);
        //加次数
        $this->addTimes($uid,$status['times'],$status['cost'],$data['data']);
        $data['rsp'] = $this->getStatus($uid);
        return $data;
    }

    public function lucky($uid,$times){
        $chance = yChance($times);
        $rand = rand(1, 100);
        if($rand<=$chance[0]){
            //return array('type'=>1);
            return $this->winUser($times);
        }
        if($rand<=($chance[0]+$chance[1])){
            $moneyOut = $this->_getFreeMoneyOut();
            $money = $moneyOut['money'];
            if(($this->maxMoney-$money<50)){
                return $this->winUser($times);
            }
            return $this->winPu($uid,$times);
        }
        return $this->winUser($times);
    }

    private function _getFreeMoneyOut(){
        $moneyOut = S('S_free_money_out');
        $day = date('Y-m-d');
        if(empty($moneyOut) || $moneyOut['day']!=$day){
            $moneyOut = array();
            $moneyOut['day'] = $day;
            $moneyOut['money'] = intval(M('y_tj')->getField('free_moneyOut',"day='$day'"));
            S('S_free_money_out', $moneyOut);
        }
        return $moneyOut;
    }

    public function winPu($uid,$times){
        $chance = puChance($times);
        $winArr = puTile($times);
        $rand = rand(1, 100);
        $sumChance = 0;
        $win = 0;
        foreach ($chance as $k => $v) {
            $sumChance += $chance[$k];
            if($rand<=$sumChance){
                $win = $winArr[$k];
                break;
            }
        }
        //充值记录
        if($win>0){
            Model('Money')->moneyIn($uid, $win, '摇一摇');
        }
        $user['win'] = $win;
        $user['type'] = 2;
        return $user;
    }

    public function winUser($times){
        $dao = M('login_record');
        $maxCid = $dao->max('login_record_id');
        $notfound = true;
        while($notfound){
            $cid = rand(1, $maxCid);
            $uid = $dao->getField('uid', 'login_record_id='.$cid);
            if($uid){
                $notfound = false;
            }
        }
        $user = M('user')->field('uid as win,realname,sid as school,sex')->where('uid='.$uid)->find();
        $user['school'] = tsGetSchoolName($user['school']);
        $user['sex'] = ($user['sex']==0)?'女':'男';
        $user['type'] = 3;
        return $user;
    }

    private function _addTj($cost,$tjData){
        $dao = M('y_tj');
        $out = 0;
        if($tjData['type']==2){
            $out = $tjData['win'];
            if($cost==0){
                $moneyOut = $this->_getFreeMoneyOut();
                $moneyOut['money'] += $out;
                S('S_free_money_out', $moneyOut);
            }
        }
        $data['day'] = date('Y-m-d');
        $data['times'] = array('exp','times+1');
        if($cost>0){
            $data['moneyIn'] = array('exp','moneyIn+'.$cost);
        }
        if($out>0){
            $data['moneyOut'] = array('exp','moneyOut+'.$out);
        }
        switch ($cost) {
            case 0:
                $data['free_times'] = array('exp','free_times+1');
                $data['free_moneyOut'] = array('exp','free_moneyOut+'.$out);
                break;
            case 100:
                $data['one_times'] = array('exp','one_times+1');
                $data['one_moneyOut'] = array('exp','one_moneyOut+'.$out);
                break;
            case 200:
                $data['two_times'] = array('exp','two_times+1');
                $data['two_moneyOut'] = array('exp','two_moneyOut+'.$out);
                break;
            case 500:
                $data['five_times'] = array('exp','five_times+1');
                $data['five_moneyOut'] = array('exp','five_moneyOut+'.$out);
                break;
            default:
                break;
        }
        $res = $dao->save($data);
        if(!$res){
            $data['times'] = 1;
            $data['moneyIn'] = $cost;
            $data['moneyOut'] = $out;
            switch ($cost) {
                case 0:
                    $data['free_times'] = 1;
                    $data['free_moneyOut'] = $out;
                    break;
                case 100:
                    $data['one_times'] = 1;
                    $data['one_moneyOut'] = $out;
                    break;
                case 200:
                    $data['two_times'] = 1;
                    $data['two_moneyOut'] = $out;
                    break;
                case 500:
                    $data['five_times'] = 1;
                    $data['five_moneyOut'] = $out;
                    break;
                default:
                    break;
            }
            $dao->add($data);
        }
    }

    public function test(){
        $total = $_GET['num'];
        $money = $_GET['m'];
        if($money==0){
            $times = 1;
        }elseif($money==1){
            $times = 20;
        }elseif($money==2){
            $times = 40;
        }elseif($money==5){
            $times = 50;
        }else{
            die('钱0,1,2,5块');
        }
        $puChange = yChance($times);
        $winNum = $total*$puChange[1]/100;
        $puChance = puChance($times);
        $puTile = puTile($times);
        $out = 0;
        foreach ($puChance as $k => $v) {
            $out += $winNum*$v*$puTile[$k];
        }
        $out = $out/10000;
        $in = $total*$money;
        $yy = $in-$out;
        $res = $total.'人 支出：'.$out.' 进账：'.$in.' 盈余：'.$yy;
        echo $res;
    }
}