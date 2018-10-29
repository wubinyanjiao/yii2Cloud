<?php

namespace common\models\overtime;

use Yii;
use \common\models\overtime\base\AttendanceRecord as BaseAttendanceRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_attendance_record".
 */
class AttendanceRecord extends BaseAttendanceRecord
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
