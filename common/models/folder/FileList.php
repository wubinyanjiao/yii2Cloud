<?php

namespace common\models\folder;

use Yii;
use \common\models\folder\base\FileList as BaseFileList;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_file_list".
 */
class FileList extends BaseFileList
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
