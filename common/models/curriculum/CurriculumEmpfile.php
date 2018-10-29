<?php

namespace common\models\curriculum;

use Yii;
use \common\models\curriculum\base\CurriculumEmpfile as BaseCurriculumEmpfile;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_curriculum_empfile".
 */
class CurriculumEmpfile extends BaseCurriculumEmpfile
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
}
