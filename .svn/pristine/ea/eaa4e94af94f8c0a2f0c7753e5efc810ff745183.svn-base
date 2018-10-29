<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\Skill as BaseSkill;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_skill".
 */
class Skill extends BaseSkill
{

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                # custom validation rules
            ]
        );
    }



    public function getSkillList($work_station){
        $skillList = self::find()->where('work_station = :work_station', [':work_station' => $work_station])->orderBy('id desc')->asArray()->all();
        return $skillList;
    }

    public function getSkillListPage($work_station,$page=null,$pageSize=null){
        $query = self::find()->where('work_station = :work_station', [':work_station' => $work_station]);
        $count=$query->count();
        $data['totalCount']=$count;
        $data['pageSize']=$pageSize;
        $data['current_page']=$page;
        $startrow = ($page-1)*$pageSize;
        $data['data']=$query->offset($startrow)->limit($pageSize)->asArray()->all();
        return $data;
    }

    public function add($data){
        if ($this->load($data) && $this->save()) {
            return true;
        }
        return false;
    }
}
