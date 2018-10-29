<?php

namespace common\models\attendance;

use Yii;
use \common\models\attendance\base\AttendanceRemindLog as BaseAttendanceRemindLog;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_attendance_remind_log".
 */
class AttendanceRemindLog extends BaseAttendanceRemindLog
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
