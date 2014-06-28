<?php
class SurveyModel extends BaseModel
{
    public function _initialize(){
        parent::_initialize();
    }

    public function addUserVote($userData, $opts){
        $this->startTrans();
        //survey_user 投票用户
        $suserId = M('survey_user')->add($userData);
        if(!$suserId){
            $this->rollback();
            return false;
        }
        //增加问卷被投次数
        $this->setInc('vote_num', 'id='.$userData['survey_id']);
        //增加选项被投次数
        $map['id'] = array('in', $opts);
        if(!D('SurveyOpt', 'event')->setInc('num',$map)){
            $this->rollback();
            return false;
        }
        //用户所投选项
        $dao = M('survey_user_opt');
        $data['suser_id'] = $suserId;
        foreach ($opts as $value) {
            $data['opt_id'] = $value;
            if(!$dao->add($data)){
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }
}
?>