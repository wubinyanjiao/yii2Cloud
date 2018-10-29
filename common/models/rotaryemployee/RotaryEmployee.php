<?php

namespace common\models\RotaryEmployee;

use Yii;
use \common\models\rotaryemployee\base\RotaryEmployee as BaseAttendanceRecord;
use yii\helpers\ArrayHelper;

use common\models\shift\ShiftResult;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
use common\models\shift\ShiftTypeDetail;

//ohrm_work_rotary_employee
/**
 * This is the model class for table "ohrm_attendance_record".
 */
class RotaryEmployee extends BaseAttendanceRecord
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

    /*
 *查询分组下的用户信息
 */
    public function getWorkEmployee($id){

        $info = self::find()->select([
            'hs_hr_employee.emp_number',
            'hs_hr_employee.emp_firstname',
            'hs_hr_employee.work_station as w_station',
            'hs_hr_employee.emp_retire',
            'hs_hr_employee.emp_gender',
            'hs_hr_employee.emp_marital_status',
            'hs_hr_employee.job_title_code',
            'hs_hr_employee.subunit_time',
            'hs_hr_employee.education_id',
            'hs_hr_employee.working_years',
            'hs_hr_employee.is_leader',
            'ohrm_work_rotary_employee.*',
            'ohrm_subunit.name'
        ])
            ->innerjoin('hs_hr_employee', 'hs_hr_employee.emp_number=ohrm_work_rotary_employee.emp_number')
            ->innerjoin('ohrm_subunit', 'ohrm_subunit.id=hs_hr_employee.work_station')
            ->where(['ohrm_work_rotary_employee.rotary_id'=>$id])->asArray()->all();
        return $info;
    }

    /*
     * 删除轮转规则
     * */
    public function deleteWorkConfrim($id)
    {
        self::deleteAll("rotary_id = :rotary_id",[':rotary_id'=>$id]);
    }
}
