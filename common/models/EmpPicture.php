<?php

namespace common\models;

use Yii;
use \common\models\base\EmpPicture as BaseEmpPicture;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_picture".
 */
class EmpPicture extends BaseEmpPicture
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

    public function getEmpPictureByEmpNumber($empNumber){
        $query = EmpPicture::find();
        $query->where('emp_number = :empNumber',[':empNumber' => $empNumber]);
        $list  = $query->one();
        return $list;
    }
}
