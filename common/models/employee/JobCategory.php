<?php

namespace common\models\employee;

use Yii;
use \common\models\employee\base\JobCategory as BaseJobCategory;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_job_category".
 */
class JobCategory extends BaseJobCategory
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
