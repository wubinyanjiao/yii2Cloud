<?php

namespace common\models\curriculum;

use Yii;
use \common\models\curriculum\base\CurriculumFile as BaseCurriculumFile;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_curriculum_file".
 */
class CurriculumFile extends BaseCurriculumFile
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

    public function delfile($id){
        $file = new CurriculumFile();
        $file = $file::find()->where(['id'=>$id])->one();
        if($file != ''){
            $delurl = '/data/wwwroot/uploadfile/'.$file['cur_url'];
            if(file_exists($delurl)){
                unlink($delurl);
            }
        }
        $model = CurriculumFile::deleteAll(['id'=>$id]);
        return $model;
    }
}
