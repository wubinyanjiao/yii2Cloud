<?php

namespace common\models\user;

use common\models\attachment\Attachment;
use common\models\employee\Contacts;
use common\models\employee\Employee;
use common\models\employee\Record;
use common\models\emptitle\EmpTitle;
use common\models\emptitle\Type;
use common\models\subunit\Subunit;
use common\models\system\WeixinMember;
use common\models\teach\Teach;
use Yii;
use \common\models\user\base\User as BaseUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_user".
 */
class User extends BaseUser
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
 * 员工列表
 * **/
    public function userlist($data,$isleader,$workstation,$role){
        $emp_firstname = isset($data['emp_firstname']) ? $data['emp_firstname'] :'';
        $emp_marital_status = isset($data['emp_marital_status']) ? $data['emp_marital_status'] :'';
        $emp_gender = isset($data['emp_gender']) ? $data['emp_gender'] :'';
        $job_title_code = isset($data['job_title_code']) ? $data['job_title_code'] :'';
        $eeo_cat_code = isset($data['eeo_cat_code']) ? $data['eeo_cat_code'] :'';
        $work_station = isset($data['work_station']) ? $data['work_station'] :'';
        $start_age = isset($data['start_age']) ? $data['start_age'] :'';
        if($start_age != ''){
            $start_age=strtotime($start_age);
            $start_age=date('Y-m-d',$start_age);
        }
        $end_age = isset($data['end_age']) ? $data['end_age'] :'';
        if($end_age != ''){
            $end_age=strtotime($end_age);
            $end_age=date('Y-m-d',$end_age);
        }
        $contract_status = isset($data['contract_status']) ? $data['contract_status'] :'';
        $status = isset($data['status']) ? $data['status'] :'';
        $start_retire = isset($data['start_retire']) ? $data['start_retire'] :'';
        if($start_retire != ''){
            $start_retire=strtotime($start_retire);
            $start_retire=date('Y-m-d',$start_retire);
        }
        $end_retire = isset($data['end_retire']) ? $data['end_retire'] :'';
        if($end_retire != ''){
            $end_retire=strtotime($end_retire);
            $end_retire=date('Y-m-d',$end_retire);
        }
        $page = isset($data['page']) ? $data['page'] :1;

        $where = '1 = 1 ';
        if($status == 0 || $status == ''){
            $where .=" and termination_id is null";
        }else{
            $where .=" and termination_id > 0";
        }
        if($isleader == true && $role != 1){
            $where .= " and work_station = '$workstation' ";
        }
        if($emp_firstname != '' ){
            $where .= " and emp_firstname like '%$emp_firstname%' ";
        }
        if($emp_marital_status != '' && $emp_marital_status != -1){
            $where .=" and emp_marital_status = '$emp_marital_status'";
        }
        if($emp_gender != '' && $emp_gender != -1){
            $where .= " and emp_gender = '$emp_gender'";
        }
        if($job_title_code != '' && $job_title_code != -1){
            $where .= " and job_title_code = '$job_title_code'";
        }
        if($eeo_cat_code != '' && $eeo_cat_code != -1){
            $where .= " and eeo_cat_code = '$eeo_cat_code'";
        }
        if($work_station != '' && $work_station != -1){
            $where .= " and work_station = '$work_station'";
        }
        if($contract_status != '' && $contract_status != -1){
            $where .= " and contract_status = '$contract_status'";
        }

            $where .= " and deleted = '0'";

        if($start_age != ''){
            $where .= " and emp_birthday > '$start_age'";
        }
        if($end_age != ''){
            $where .= " and emp_birthday < '$end_age'";
        }
        if($start_retire != ''){
            $where .= " and emp_retire > '$start_retire'";
        }
        if($end_retire != ''){
            $where .= " and emp_retire < '$end_retire'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;
        $query =(new \yii\db\Query())
            ->select(['a.emp_number','a.employee_id','a.emp_firstname','b.job_title','d.name as category','a.emp_marital_status','a.emp_gender','a.emp_birthday','a.contract_status'
                ,'c.name','a.subunit_time','c.leader_id','f.deleted','user_name','a.termination_id'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_job_title b','a.job_title_code = b.id') //职称
            ->leftJoin('orangehrm_mysql.ohrm_subunit c','a.work_station = c.id')        //小组
            ->leftJoin('orangehrm_mysql.ohrm_job_category d','a.eeo_cat_code = d.id')        //岗位
            ->leftJoin('orangehrm_mysql.ohrm_user f','a.emp_number = f.emp_number')        //员工状态
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where)
            ->all();
        $employee = new Employee();
        $array  = Personnel::find()->asArray()->all();

        foreach ($query as $k => $v){
            $age = $this->getage($v['emp_birthday']);
            $query[$k]['age'] = $age;
            $name = $employee::find()->asArray()->select(['emp_firstname'])->where(['emp_number'=>$v['leader_id']])->one();
            $query[$k]['leader'] = $name['emp_firstname'];


            $query[$k]['id'] = (($page-1)*$pagesize)+$k+1;
            if($v['contract_status'] == 3 || $v['contract_status'] == 4){
                $query[$k]['contract_status'] = '聘用';
            }else{
                $arr = Personnel::find()->where(['id'=>$v['contract_status']])->one();
                $query[$k]['contract_status'] = $arr['name'];
            }

            if($v['termination_id'] == ''){
                $query[$k]['status'] = '正常';
            }else{
                $reason = Reason::find()->where(['id'=>$v['termination_id']])->one();
                $query[$k]['status'] = $reason['name'];
            }


        }
        $count = (new \yii\db\Query())
            ->select(['a.emp_number','a.employee_id','a.emp_firstname','b.job_title','d.name as category','a.emp_marital_status','a.emp_gender','a.emp_birthday','a.contract_status'
                ,'c.name','a.subunit_time','c.leader_id','f.deleted'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_job_title b','a.job_title_code = b.id') //职称
            ->leftJoin('orangehrm_mysql.ohrm_subunit c','a.work_station = c.id')        //小组
            ->leftJoin('orangehrm_mysql.ohrm_job_category d','a.eeo_cat_code = d.id')        //岗位
            ->leftJoin('orangehrm_mysql.ohrm_user f','a.emp_number = f.emp_number')        //员工状态
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where)
            ->count();


        $model['count'] =  (int)$count;
        $model['pagesize'] = (int)$pagesize;
        $model['result'] = $query;
        return $model;
    }


    /*
     * 查询员工头像
     * **/
    public function selpicture($emp_number){
        $picture = new Picture();
        $query = $picture::find()->select('epic_picture_url')->where(['emp_number'=>$emp_number])->one();
        $query['epic_picture_url'] = trim($query['epic_picture_url'],"/");
        return $query;

    }

    /*
     * 计算年龄
     * **/
    public function getage($emp_birthday){
        if($emp_birthday != ''){

            list($year,$month,$day) = explode("-",$emp_birthday);
            $year_diff = date("Y") - $year;
            $month_diff = date("m") - $month;
            $day_diff  = date("d") - $day;
            if ($day_diff < 0 || $month_diff < 0)
                $year_diff--;
            return $year_diff;
        }else{
            return 0;
        }
    }


    /*
     * 学历
     * **/
    public function education(){
        $where = "a.contract_status in (1,3,4)";
        $query =(new \yii\db\Query())
            ->select(['count(*)','b.name'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_education b','a.education_id = b.id')
            ->groupBy('education_id')
            ->where($where)
            ->all();

        foreach ($query as $k =>$v){
            if($v['name'] == ''){
                $v['name'] = "其他";
            }
            $v['num'] = $v['count(*)'];
            unset($v['count(*)']);
            $query[$k] = $v;

        }

        return $query;
    }


    /*
     *职称
     * **/
    public function title(){
        $where = "a.contract_status in (1,3,4)";
        $query =(new \yii\db\Query())
            ->select(['count(*)','b.job_title'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_job_title b','a.job_title_code = b.id')
            ->groupBy('job_title_code')
            ->where($where)
            ->all();
        foreach ($query as $k =>$v){
            if($v['job_title'] == ''){
                $v['job_title'] = "其他";
            }
            $v['num'] = $v['count(*)'];
            unset($v['count(*)']);
            $query[$k] = $v;

        }

        return $query;
    }





    /*
     * 查询员工基本信息
     * **/
    public function selbasic($emp_number){
        $query = (new \yii\db\Query())
            ->select(['a.emp_number','a.emp_firstname','a.emp_gender','a.emp_other_id','a.joined_date','a.nation_code','a.emp_work_email',
                'a.emp_marital_status','a.minzu_code','a.emp_politics','a.emp_birthday','a.emp_retire','a.emp_mobile','a.emp_work_telephone',
                'a.emp_street2','b.eec_name','b.eec_mobile_no','c.open_id'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.hs_hr_emp_emergency_contacts b','a.emp_number = b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user c','a.emp_number = c.emp_number')
            ->where(['a.emp_number'=>$emp_number])
            ->one();

        $city= Jiguan::find()->select(['province','city','county'])->asArray()->where(['emp_number'=>$emp_number])->one();

        $query['home'] = array($city['province'],$city['city'],$city['county']);
        if($query['open_id'] == ''){
            $query['open_id'] = false;
        }else{
            $query['open_id'] = true;
        }
        return $query;
    }

    /*
     * 修改员工基本信息
     * **/
    public function upbasic($data){
        $emp_number = $data['emp_number'];
        $emp_firstname = isset($data['emp_firstname']) ? $data['emp_firstname'] :'';
        $emp_gender = isset($data['emp_gender']) ? $data['emp_gender'] :'';
        $emp_other_id = isset($data['emp_other_id']) ? $data['emp_other_id'] :'';
        $joined_date = isset($data['joined_date']) ? $data['joined_date'] :'';
        if($joined_date != ''){
            $joined_date=strtotime($joined_date);
            $joined_date=date('Y-m-d',$joined_date);
        }
        $nation_code = isset($data['nation_code']) ? $data['nation_code'] :'';
        $emp_marital_status = isset($data['emp_marital_status']) ? $data['emp_marital_status'] :'';
        $emp_work_email = isset($data['emp_work_email']) ? $data['emp_work_email'] :'';
        $minzu_code = isset($data['minzu_code']) ? $data['minzu_code'] :'';
        $emp_politics = isset($data['emp_politics']) ? $data['emp_politics'] :'';
        $emp_birthday = isset($data['emp_birthday']) ? $data['emp_birthday'] :'';
        if($emp_birthday != ''){
            $emp_birthday=strtotime($emp_birthday);
            $emp_birthday=date('Y-m-d',$emp_birthday);
        }
        $emp_retire = isset($data['emp_retire']) ? $data['emp_retire'] :'';
        if($emp_retire != ''){
            $emp_retire=strtotime($emp_retire);
            $emp_retire=date('Y-m-d',$emp_retire);
        }
        $emp_mobile = isset($data['emp_mobile']) ? $data['emp_mobile'] :'';
        $emp_work_telephone= isset($data['emp_work_telephone']) ? $data['emp_work_telephone'] :'';
        $emp_street2 = isset($data['emp_street2']) ? $data['emp_street2'] : '';


        $province = isset($data['home'][0]) ? $data['home'][0] :'';
        $city = isset($data['home'][1]) ? $data['home'][1] :'';
        $county = isset($data['home'][2]) ? $data['home'][2] :'';

        $eec_name = isset($data['eec_name']) ? $data['eec_name'] :'';
        $eec_mobile_no = isset($data['eec_mobile_no']) ? $data['eec_mobile_no'] :'';

        $employee = new Employee();
        $employee = $employee::find()->where(['emp_number'=>$emp_number])->one();
        $employee->emp_firstname = $emp_firstname;
        $employee->emp_gender = $emp_gender;
        $employee->emp_work_email = $emp_work_email;
        $employee->emp_other_id = $emp_other_id;
        $employee->joined_date = $joined_date;
        $employee->nation_code = $nation_code;
        $employee->emp_marital_status = $emp_marital_status;
        $employee->minzu_code = $minzu_code;
        $employee->emp_politics = $emp_politics;
        $employee->emp_birthday = $emp_birthday;
        $employee->emp_retire = $emp_retire;
        $employee->emp_mobile = $emp_mobile;
        $employee->emp_work_telephone = $emp_work_telephone;
        $employee->emp_street2 = $emp_street2;
        $info = $employee->save();

        if($info){
            $contatus = new Contacts();
            $con = $contatus::find()->where(['emp_number'=>$emp_number])->one();
            if($con == ''){
                $contatus->emp_number = $emp_number;
                $contatus->eec_name = $eec_name;
                $contatus->eec_mobile_no =$eec_mobile_no;
                $query = $contatus->save();
                if($query){
                    $jiguan = new Jiguan();
                    $ji = $jiguan::find()->where(['emp_number'=>$emp_number])->one();
                    if($ji == ''){
                        $jiguan->emp_number = $emp_number;
                        $jiguan->province = $province;
                        $jiguan->city = $city;
                        $jiguan->county = $county;
                        if($jiguan->save()){
                            return true;
                        }
                    }else{
                        $ji->province = $province;
                        $ji->city = $city;
                        $ji->county = $county;
                        if($ji->save()){
                            return true;
                        }
                    }
                }else{
                    return false;
                }
            }else{
                $con->eec_name = $eec_name;
                $con->eec_mobile_no =$eec_mobile_no;
                $query = $con->save();
                if($query){
                    $jiguan = new Jiguan();
                    $ji = $jiguan::find()->where(['emp_number'=>$emp_number])->one();
                    if($ji == ''){
                        $jiguan->emp_number = $emp_number;
                        $jiguan->province = $province;
                        $jiguan->city = $city;
                        $jiguan->county = $county;
                        if($jiguan->save()){
                            return true;
                        }
                    }else{
                        $ji->province = $province;
                        $ji->city = $city;
                        $ji->county = $county;
                        if($ji->save()){
                            return true;
                        }
                    }
                }else{
                    return false;
                }
            }


        }else{
            return false;
        }
    }



    /*
     * 查询员工岗位信息
     * **/
    public function selpost($emp_number){

        $query = (new \yii\db\Query())
            ->select(['emp_number','incourtyard_date','probation_date','formal_date','eeo_cat_code','workload_ranking','is_scheduling',
                'eeo_cat_describe','work_station','subunit_time','contract_status','attime_education','attime_graduation','attime_graduation_school',
                'attime_studymajor','now_education','now_graduationtime','now_academic_degree','now_academic_degreetime','now_graduation_school','is_rotation'])
            ->from('orangehrm_mysql.hs_hr_employee')
            ->where(['emp_number'=>$emp_number])
            ->one();

        /*$employee = new Employee();
        $query = $employee::find()->select(['emp_number','incourtyard_date','probation_date','formal_date','eeo_cat_code','workload_ranking','is_scheduling',
            'eeo_cat_describe','work_station','subunit_time','contract_status','attime_education','attime_graduation','attime_graduation_school',
            'attime_studymajor','now_education','now_graduationtime','now_academic_degree','now_academic_degreetime','now_graduation_school'])
            ->asArray()->where(['emp_number'=>$emp_number])->one();*/
        if($query['work_station']!= ''){
            $leader = (new yii\db\Query())
                ->select('emp_firstname')
                ->from('orangehrm_mysql.hs_hr_employee a')
                ->leftJoin('orangehrm_mysql.ohrm_subunit b','a.emp_number = b.leader_id')
                ->where(['b.id'=>$query['work_station']])
                ->one();
            $query['leader'] = $leader['emp_firstname'];
        }else{
            $query['leader'] = '';
        }
        return (['result'=>$query,"code"=>'200',"message"=>'查找成功',"isSuccess"=>true]);

    }

    /*
     * 员工头像上传
     * **/
    public function uploadportrait($emp_number,$size,$img_info,$name,$filename){
        $picture = new Picture();
        $pic = $picture::find()->where(['emp_number'=>$emp_number])->one();

        if($pic){
            unlink($pic['epic_picture_url']);
            $pic->epic_picture_url = $filename;
            $pic->epic_filename = $name;
            $pic->epic_type = $img_info['mime'];
            $pic->epic_file_size = $size;
            $pic->epic_file_width = $img_info[0];
            $pic->epic_file_height = $img_info[1];
            $query = $pic->save();
            if($query){
                return (['result'=>$query,"code"=>'200',"message"=>'添加成功',"isSuccess"=>true]);
            }else{
                return (['result'=>$query,"code"=>'403',"message"=>'添加失败',"isSuccess"=>false]);
            }
        }else{
            $picture->emp_number = $emp_number;
            $picture->epic_picture_url = $filename;
            $picture->epic_filename = $name;
            $picture->epic_type = $img_info['mime'];
            $picture->epic_file_size = $size;
            $picture->epic_file_width = $img_info[0];
            $picture->epic_file_height = $img_info[1];
            $query = $picture->save();
            if($query){
                return (['result'=>$query,"code"=>'200',"message"=>'添加成功',"isSuccess"=>true]);
            }else{
                return (['result'=>$query,"code"=>'403',"message"=>'添加失败',"isSuccess"=>false]);
            }
        }
    }


    /*
     * 添加附件
     * **/
    public function addfile($emp_number,$size,$name,$details,$screen,$file_name,$type,$filename){
        $attachment = new Attachment();

        $id = $attachment::find()->select('eattach_id')->orderBy('eattach_id desc')->one();
        $id = $id['eattach_id'] + 1;
        $attachment->emp_number = $emp_number;
        $attachment->eattach_id = $id;
        $attachment->eattach_desc = $details;
        $attachment->eattach_filename = $file_name;
        $attachment->eattach_size = $size;
        $attachment->eattach_attachment_url = $filename;
        $attachment->eattach_type = $type;
        $attachment->screen = $screen;
        $attachment->attached_by_name = $name;
        $attachment->attached_time =  date('Y-m-d H:i:s',time());

        $query = $attachment->save();
        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'添加成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'添加失败',"isSuccess"=>false]);
        }

    }

    /*
     * 变组详情
     * **/
    public function selrecored($emp_number){

        $query = (new \yii\db\Query())
            ->select(['b.emp_firstname','c.name as orange_department','a.new_department','a.time_in','a.time_out','a.total_month','a.tmpclassname'])
            ->from('orangehrm_mysql.ohrm_work_rotary_record a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number=b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_subunit c','a.orange_department=c.id')
            ->orderBy('a.time_in')
            ->where(['b.emp_number'=>$emp_number,'a.is_rotate'=>0])
            ->all();
        $subunit = new Subunit();
        foreach ($query as $k => $v){
            if($v['orange_department'] == '药学' || $v['orange_department'] == '护理'){
                $query[$k]['orange_department'] = '静配中心';
            }

            $sql = $subunit::find()->select(['name'])->where(['id'=>$v['new_department']])->one();
            $query[$k]['new_department'] = $sql['name'];
            if($v['new_department'] == ''){
                $query[$k]['new_department'] = $v['tmpclassname'];
            }
            if($v['new_department'] == 16 || $v['new_department'] == 17){
                $query[$k]['new_department'] = '静配中心';
            }

            if($v['time_out'] == '至今'){
                $time = date('Y-m-d',time());
                $time_out = $this->getMonthNum($v['time_in'],$time,'-');
                $query[$k]['total_month'] = $time_out;
            }
        }
        return $query;
    }
    /*
     *修改员工岗位信息
     * **/
    function getMonthNum( $date1, $date2, $tags='-' ){
        $date1 = explode($tags,$date1);
        $date2 = explode($tags,$date2);
        return abs($date2[0] - $date1[0]) * 12 + ($date2[1] - $date1[1]);
    }
    public function uppost($data,$role){
        $emp_number = $data['emp_number'];
        $employee = new Employee();
        $emp = $employee::find()->where(['emp_number'=>$emp_number])->one();


       if($emp['work_station'] != (int)$data['work_station']){


            if($role != 1){
                return 2;
            }
           /* $week = date('w');
            if($week != 1){

                return 3;
            }*/
            //计算相差几个月
            $subunit_time = date('Y-m-d ',time());
            if($emp['subunit_time'] == ''){
                $emp['subunit_time'] = $subunit_time;
            }

            $mytime= date("Y-m-d", strtotime("-1 day"));
            $total_month = $this->getMonthNum($emp['subunit_time'],$subunit_time,'-');
            $arr = Record::find()->where(['emp_number'=>$emp_number,'time_out'=>'至今'])->one();
            if($arr != ''){
                $arr->time_out = $mytime;
                $arr->total_month = $total_month;
                $arr->save();
            }




            $subunit = Subunit::find()->where(['id'=>$data['work_station']])->one();
            $subname = $subunit['name'];

            $user = User::find()->where(['emp_number'=>$emp_number])->one();
            $user_name = $user['user_name'];

            $record = new Record();
            $record->emp_number = $emp_number;
            $record->orange_department = $arr['new_department'];
            $record->new_department = (int)$data['work_station'];
            $record->time_in = date('Y-m-d');
            $record->time_out = '至今';
            $record->total_month = 0;
            $record->tmpclassname = $subname;
            $record->tmpgongzihao = $user_name;
            $record->create_at = $subunit_time;
            $record->save();


        }else{
            $subunit_time = $emp['subunit_time'];
        }

        $incourtyard_date = isset($data['incourtyard_date'])?$data['incourtyard_date']:'';
        if($incourtyard_date != ''){
            $incourtyard_date=strtotime($incourtyard_date);
            $incourtyard_date=date('Y-m-d',$incourtyard_date);
        }
        $probation_date = isset($data['probation_date'])?$data['probation_date']:'';
        if($probation_date != ''){
            $probation_date=strtotime($probation_date);
            $probation_date=date('Y-m-d',$probation_date);
        }
        $formal_date = isset($data['formal_date'])?$data['formal_date']:'';
        if($formal_date != ''){
            $formal_date=strtotime($formal_date);
            $formal_date=date('Y-m-d',$formal_date);
        }

        $emp->incourtyard_date = $incourtyard_date;
        $emp->probation_date = $probation_date;
        $emp->formal_date = $formal_date;
        $emp->eeo_cat_code = isset($data['eeo_cat_code'])?$data['eeo_cat_code']:'';
        $emp->is_scheduling = isset($data['is_scheduling'])?$data['is_scheduling']:'';
        $emp->eeo_cat_describe = isset($data['eeo_cat_describe'])?$data['eeo_cat_describe']:'';
        $emp->work_station = isset($data['work_station'])?$data['work_station']:'';
        $emp->subunit_time = $subunit_time;
        $emp->contract_status = isset($data['contract_status'])?$data['contract_status']:'';
        $emp->is_rotation = isset($data['is_rotation'])?$data['is_rotation']:'';
        $query = $emp->save();
        return $query;

    }


    /*
     * 资质查询
     * **/
    public function selaptitude($emp_number){
        $query = (new \yii\db\Query())
            ->select(['emp_number','education_id','job_title_code','working_years','emp_status','mutual_exclusion','faculty_code',
                'licenses_id','special_personnel','job_title_time','keyan_title_code','keyan_title_time','teacher_title_code','teacher_title_time','tutor',])
            ->from('orangehrm_mysql.hs_hr_employee')
            ->where(['emp_number'=>$emp_number])
            ->one();

       return $query;
    }


    /*
     * 资质修改
     * **/
    public function upaptitude($data){
        $emp_number = $data['emp_number'];
        $job_title_time = isset($data['job_title_time'])?$data['job_title_time']:'';
        if($job_title_time != ''){
            $job_title_time=strtotime($job_title_time);
            $job_title_time=date('Y-m-d',$job_title_time);
        }
        $keyan_title_code = isset($data['keyan_title_code'])?$data['keyan_title_code']:'';
        $keyan_title_time = isset($data['keyan_title_time'])?$data['keyan_title_time']:'';
        if($keyan_title_time != ''){
            $keyan_title_time=strtotime($keyan_title_time);
            $keyan_title_time=date('Y-m-d',$keyan_title_time);
        }
        $teacher_title_code = isset($data['teacher_title_code'])?$data['teacher_title_code']:'';
        $teacher_title_time = isset($data['teacher_title_time'])?$data['teacher_title_time']:'';
        if($teacher_title_time != ''){
            $teacher_title_time=strtotime($teacher_title_time);
            $teacher_title_time=date('Y-m-d',$teacher_title_time);
        }
        $tutor = isset($data['tutor'])?$data['tutor']:'';

        $employee = new Employee();
        $emp = $employee::find()->where(['emp_number'=>$emp_number])->one();
        $emp->faculty_code = isset($data['faculty_code'])?$data['faculty_code']:'';
        //$emp->education_id = isset($data['education_id'])?$data['education_id']:'';
        $emp->job_title_code = isset($data['job_title_code'])?$data['job_title_code']:'';
        $emp->working_years = isset($data['working_years'])?$data['working_years']:'';
        $emp->emp_status = isset($data['emp_status'])?$data['emp_status']:'';
        $emp->mutual_exclusion = isset($data['mutual_exclusion'])?$data['mutual_exclusion']:'';
        $emp->licenses_id = isset($data['licenses_id'])?$data['licenses_id']:'';
        $emp->special_personnel = isset($data['special_personnel'])?$data['special_personnel']:'';
        $emp->job_title_time = $job_title_time;
        $emp->keyan_title_code = $keyan_title_code;
        $emp->keyan_title_time = $keyan_title_time;
        $emp->teacher_title_code = $teacher_title_code;
        $emp->teacher_title_time = $teacher_title_time;
        $emp->tutor = $tutor;
        $query = $emp->save();
        return $query;
    }

    /*
     * 员工识别号查询
     * **/
    public function selpassword($emp_number){
        $user = new User();
        $query = $user::find()->select(['emp_number','user_name'])->where(['emp_number'=>$emp_number])->one();
        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'查询成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'查询失败',"isSuccess"=>false]);
        }
    }

    /*
     * 修改员工密码和识别号
     * **/
    public function uppassword($data){
        $emp_number = $data['emp_number'];
        $user = new User();
        $user = $user::find()->where(['emp_number'=>$emp_number])->one();
        $user->user_name = $data['user_name'];
        if($data['password'] != ''){
            $user->user_password = md5($data['password']);
        }
        $query = $user->save();
        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'修改成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'修改失败',"isSuccess"=>false]);
        }
    }

    public function subunit($customerId){
        $query = Subunit::find()->select(['id','name'])->where(['customer_id'=>$customerId])->all();
        return $query;
    }


    /*
     * 员工附件列表
     * **/
    public function filelist($emp_number,$screen){

        $attachment = new Attachment();
        if($screen == 'personal'){
            $where = "emp_number = '$emp_number' and screen in ('$screen','idcard')";
        }else if($screen == 'aptitude'){
            $where = "emp_number = '$emp_number' and screen in ('$screen','record','field')";
        }else{
            $where = "emp_number = '$emp_number' and screen = '$screen'";
        }

        $query = $attachment::find()->where($where)->all();
        foreach ($query as $k => $v){
            $query[$k]['eattach_size'] = $this->formatBytes($v['eattach_size']);
        }
        return $query;
    }

    /*
     * 附件大小
     * **/
    public function formatBytes($eattach_size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $eattach_size >= 1024 && $i < 4; $i++) $eattach_size /= 1024;
        return round($eattach_size, 2).$units[$i];
    }


    /*
     * 员工附件详情
     * **/
    public function selectfile($eattach_id){
        $attachment = new Attachment();
        $query = $attachment::find()->where(['eattach_id'=>$eattach_id])->one();
        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'查询成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'查询失败',"isSuccess"=>false]);
        }
    }

    /*
     * 员工附件修改
     * **/
    public function upfile($size,$name,$details,$file_name,$type,$filename,$eattach_id){
        $attachment = new Attachment();
        $att = $attachment::find()->where(['eattach_id'=>$eattach_id])->one();
        unlink($att['eattach_attachment_url']);
        $att->eattach_desc = $details;
        $att->eattach_filename = $file_name;
        $att->eattach_size = $size;
        $att->eattach_attachment_url = $filename;
        $att->eattach_type = $type;
        $att->attached_by_name = $name;
        $att->attached_time = date('Y-m-d H:i:s',time());
        $query = $att->save();
        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'修改成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'修改失败',"isSuccess"=>false]);
        }
    }

    /*
     * 根据组查找组长
     * **/
    public function selleadder($id){
        $query = (new \yii\db\Query())
            ->select('b.emp_firstname')
            ->from('orangehrm_mysql.ohrm_subunit a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.leader_id=b.emp_number')
            ->where(['a.id'=>$id])
            ->one();
        return $query;
    }

    public function deluser($emp_number){
        $user = new User();
        $query = $user::updateAll(['deleted'=>1],['emp_number'=>$emp_number]);
        $query = Employee::updateAll(['termination_id'=>7],['emp_number'=>$emp_number]);
        return $query;
    }

    /**
     * **根据id 查询用户信息
     * @param  [type]  $id      用户表ID  
     * @return [type]          
     */
    public function getSystemUsersById($id){

        $user = User::find()->where(['id'=>$id])->one();

        return $user;

    }
    /**
     * **根据id 查询用户信息
     * @param  [type]  $id      用户表ID  
     * @return [type]          
     */
    public function getSystemUsersByEmpNumber($empNumber){

        $user = User::find()->where(['emp_number'=>$empNumber])->one();

        return $user;

    }
    /**
     * **根据id 查询用户信息
     * @param  [type]  $id      用户表ID  
     * @return [type]          
     */
    public function getSystemUsersByUserName($userName){

        $user = User::find()->where(['user_name'=>$userName])->one();

        return $user;

    }

    public function searchSystemUsersById($id){
        $q = User::find();
        $q->from('ohrm_user u');

        $q->joinWith('employee e');

        $q->andWhere(['u.id'=> $id]);

        return $q->asArray()->one();
    }

    public function deleteOpenUnbind($id){
        $query = new WeixinMember();
        $recod = $query->deleteAll('userid =:id ',array(':id'=>$id));
        return $recod;
    }

    /**
     * **根据查询条件查询用户信息
     * @param  [type]  $seach      用户表ID  
     * @return [type]          
     */
    public function getSystemUsersBySearch($search){

        $query = User::find();


        if(!empty($search['customer_id'])){
            //$query->andWhere(['customer_id'=>$search['customer_id']]);
            $query->andWhere('customer_id = :customer_id',[':customer_id' => $search['customer_id']]);
        }
        if(!empty($search['open_id'])){
            //$query->andWhere(['open_id'=>$search['open_id']]);
            $query->andWhere('open_id = :open_id',[':open_id' => $search['open_id']]);
        }

        $user = $query->one();
        return $user;

    }



    public function getcity(){
        $arr = City::find()->asArray()->all();
        $query = $this->GetTree($arr,1);
        return $query;
    }

    /*
   * 递归查找
   * **/
    public function getTree($arr, $pId)
    {
        $tree = array();
        foreach($arr as $k => $v)
        {
            if($v['fu_id'] == $pId)
            {        //父亲找到儿子
                $v['value']= $v['id'];
                $v['label']= $v['name'];
                unset($v['id']);
                unset($v['name']);
                $v['children'] = $this->getTree($arr, $v['value']);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }

    /*
     * 根据组id 查找组员
     * **/
    public function getByEmployee($role_id,$emp_gender=0){
        if($role_id == '' || $role_id == 0){
            $where = '';
        }else{
            $where = " work_station = $role_id";
        }
        if($emp_gender){
            $where .=" and emp_gender = $emp_gender";
        }
        $query = Employee::find()->select(['emp_number','emp_firstname'])->where($where)->all();
        return $query;
    }


    /**
     * 轮转信息进表
     * @param  [type]  $emp_number      员工id
     * @param  [type]  $work_station      组id
     * @return [type]
     */
    public function putinSubunit($data,$time=null){
        if($time != null){
            Record::deleteAll(['in','time_in',$time]);
        }
        foreach ($data as $k => $v){
            $user = User::find()->select(['user_name'])->where(['emp_number'=>$v['emp_number']])->one();
            $subunit = Subunit::find()->select(['name'])->where(['id'=>$v['work_station']])->one();
            $old_subunit = Record::find()->select(['orange_department'])->where(['emp_number'=>$v['emp_number']])->andWhere(['time_out'=>'至今'])->one();
            $record = new  Record();
            $record->emp_number = $v['emp_number'];
            $record->orange_department = $old_subunit['orange_department'];
            $record->new_department = $v['work_station'];
            $record->time_in = $v['time_in'];
            $record->tmpclassname = $subunit['name'];
            $record->tmpgongzihao = $user['user_name'];
            $record->is_rotate = 1;
            $info = $record->save();
            if($info == false){
                return $info;
            }
        }
        return true;
    }


    /*
     * 查找最底层的组
     * **/
    public function getSmallSubunit($customer_id){
        $where = " id != 1 and customer_id = '$customer_id'";
        $subunit = Subunit::find()->asArray()->where($where)->all();
        $data = array();
        foreach ($subunit as $k => $v){
            $sub = Subunit::find()->where(['unit_id'=>$v['id'],'customer_id'=>$customer_id])->one();
            if($sub == ''){
                $data[$k] = $v;
            }
        }

        return $data;
    }


    /**
     * 微信员工基本信息查询
     * @param  [type]  $emp_number      员工id
     * @return [type]
     */
    public function WeChatEmployee($emp_number){
        $query = (new \yii\db\Query())
            ->select(['a.emp_number','a.emp_firstname','a.emp_mobile','a.emp_work_telephone','a.work_station','a.subunit_time','b.eec_name','b.eec_mobile_no'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.hs_hr_emp_emergency_contacts b','a.emp_number = b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user c','a.emp_number = c.emp_number')
            ->where(['a.emp_number'=>$emp_number])
            ->one();
        $subunit = Subunit::find()->where(['id'=>$query['work_station']])->one();
        $record = Record::find()->where(['emp_number'=>$emp_number,'is_rotate'=>1])->one();
        //return $record;
        if(empty($record)){
            $soon_subunit = '';
            $soon_time = '';
        }else{
            if($record['new_department'] == 16 || $record['new_department'] == 17){
                $soon_subunit = '静配中心';
            }else{
                $soonsubunit = Subunit::find()->where(['id'=>$record['new_department']])->one();
                $soon_subunit = $soonsubunit['name'];
            }
            $soon_time = $record['time_in'];
        }
        $query['subunit_name'] = $subunit['name'];
        $query['soon_subunit'] = $soon_subunit;
        $query['soon_time'] = $soon_time;
        return $query;
    }


    /**
     * 微信员工基本信息修改
     * @param  [type]
     * @return [type]
     */
    public function UpdateWeChatEmployee($emp_number,$data){
        //$emp_number = isset($data['emp_number']) ? $data['emp_number'] :'';
        $emp_firstname = isset($data['emp_firstname']) ? $data['emp_firstname'] :'';
        $emp_mobile = isset($data['emp_mobile']) ? $data['emp_mobile'] :'';
        $emp_work_telephone = isset($data['emp_work_telephone']) ? $data['emp_work_telephone'] :'';
        $eec_name = isset($data['eec_name']) ? $data['eec_name'] :'';
        $eec_mobile_no = isset($data['eec_mobile_no']) ? $data['eec_mobile_no'] :'';

        $employee = new Employee();
        $employee = $employee::find()->where(['emp_number'=>$emp_number])->one();
        $employee->emp_firstname = $emp_firstname;
        $employee->emp_mobile = $emp_mobile;
        $employee->emp_work_telephone = $emp_work_telephone;
        $info = $employee->save();

        if($info){
            $contatus = new Contacts();
            $con = $contatus::find()->where(['emp_number'=>$emp_number])->one();
            if($con == ''){
                $contatus->emp_number = $emp_number;
                $contatus->eec_name = $eec_name;
                $contatus->eec_mobile_no =$eec_mobile_no;
                $query = $contatus->save();
                return $query;
            }else{
                $con->eec_name = $eec_name;
                $con->eec_mobile_no =$eec_mobile_no;
                $query = $con->save();
                return $query;
            }
        }else{
            return false;
        }
    }

    public function getEmployeeByRoleId($roleId = null,$workStation = null){
        $q = User::find();
        $q->joinWith('employee');

        if($roleId){
            $q->andWhere(['in','ohrm_user.user_role_id',$roleId]);
        }
        if($workStation){
            $q->andWhere('hs_hr_employee.work_station = :workStation',[':workStation'=>$workStation]);
        }


        $list = $q->all();

        return $list;
    }




    /**
     * 轮转员工基本信息查询
     * @param  [type]  $workStation 组id     $time 时间
     * @return [type]
     */
    public function RotationEmployee($workStation,$time){

        foreach ($workStation as $key=>$value){

            $data = array();
            $info = array();
            $where = "new_department = '$value' and time_in < '$time'";
            $record = Record::find()->where($where)->orderBy('emp_number')->all();
            if(!empty($record)){
                foreach ($record as $k => $v){
                    if($v['time_out'] == '至今'){
                        $temp_date = '2035-12-30';
                        $v['time_out'] = date('Y-m-d',strtotime($temp_date));

                    }
                    if($time < date('Y-m-d',strtotime($v['time_out']))){
                        $info[] = $v;
                    }
                }
            }

            if(empty($info)){
                return '没有符合的数据';
            }
            foreach ($info as $k => $v){
                if($v['total_month'] == 0){
                    $time = date('Y-m-d',time());
                    $time_out = $this->getMonthNum($v['time_in'],$time,'-');
                    $v['total_month'] = $time_out;
                }
                $user = Employee::find()->select(['emp_number','emp_firstname','emp_gender','emp_birthday','mutual_exclusion','is_leader','is_rotation'])->where(['emp_number'=>$v['emp_number']])->one();
                $title = EmpTitle::find()->where(['emp_number'=>$v['emp_number']])->orderBy('time desc')->one();
                $teach = Teach::find()->where(['emp_number'=>$v['emp_number']])->orderBy('end_time desc')->one();
                $lunzhuan = Record::find()->select(['sum(total_month) as total_month'])->where(['emp_number'=>$v['emp_number'],'new_department'=>$value])->all();
                $data[$k]['emp_number'] = $user['emp_number'];
                $data[$k]['emp_firstname'] = $user['emp_firstname'];
                $data[$k]['emp_gender'] = $user['emp_gender'];
                $data[$k]['work_station'] = $value;
                $data[$k]['emp_birthday'] = $user['emp_birthday'];
                $data[$k]['mutual_exclusion'] = $user['mutual_exclusion'];
                $data[$k]['is_leader'] = $user['is_leader'];
                $data[$k]['is_rotation'] = $user['is_rotation'];
                $data[$k]['title_id'] = $title['class_id'];
                $data[$k]['title_time'] = $title['time'];
                $data[$k]['record_id'] = $teach['record_id'];
                $data[$k]['work_time'] = $v['total_month'];
                $data[$k]['count_work_time'] = $lunzhuan[0]['total_month'];
            }
            $arr[] = $data;
        }
        return $arr;
    }



    /**
     * 定时任务：轮转组数据更新
     * @param  [type]
     * @return [type]
     */
    public function RotationUpdate(){
        $time = date('Y-m-d',time());
        $data = Record::find()->asArray()->where(['is_rotate'=>1,'time_in'=>$time])->all();
       foreach ($data as $k => $v){
           $tr = Yii::$app->db->beginTransaction();
           try{
               $record = Record::find()->where(['emp_number'=>$v['emp_number'],'is_rotate'=>0,'time_out'=>'至今'])->one();
               $total_month = $this->getMonthNum($record['time_in'],$time,'-');
               $record->time_out = date("Y-m-d", strtotime("-1 day"));
               $record->total_month = $total_month;
               $record->save();

               $record_new = Record::find()->where(['id'=>$v['id']])->one();

               $employee = Employee::find()->where(['emp_number'=>$v['emp_number']])->one();
               $employee->work_station = $record_new['new_department'];
               $employee->subunit_time = $record_new['time_in'];
               $employee->save();

               $record_new->time_out= '至今';
               $record_new->is_rotate = 0;
               $record_new->save();

           }catch (Exception $e) {

               $tr->rollBack();
               return false;
           }
       }

       return true;


    }



    //获取用户ip
    public function getIp()
    {

        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"]))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else
        {
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);

        return $cip;
    }


    /*
     * 获取某个时间某个组将轮转人员
     * 时间 time
     * 组id work_station
     * **/
    public function getRotationEmpnumber($time,$work_station){
        $in_subunit = Record::find()->select(['emp_number'])->where(['is_rotate'=>1,'time_in'=>$time,'new_department'=>$work_station])->all();
        $out_subunit = Record::find()->select(['emp_number'])->where(['is_rotate'=>1,'time_in'=>$time,'orange_department'=>$work_station])->all();

        $in_subunit = array_column($in_subunit,'emp_number');
        //$in_subunit = implode(",", $in_subunit);

        $out_subunit = array_column($out_subunit,'emp_number');
        //$out_subunit = implode(",", $out_subunit);

        $data['in_subunit'] = $in_subunit;
        $data['out_subunit'] = $out_subunit;
        return $data;
    }



    /**
     * 未来某个时间段组的人员信息
     * @param  [type]  $workStation 组id     $time 时间
     * @return [type]
     */
    public function FutureEmployee($workStation,$time){
            $info = array();
            $data = array();
            $record = Employee::find()->select(['emp_number'])->where(['work_station'=>$workStation,'termination_id'=>null])->asArray()->all();
            $in = Record::find()->where(['time_in'=>$time,'new_department'=>$workStation,'is_rotate'=>1])->asArray()->all();
            $out = Record::find()->where(['time_in'=>$time,'orange_department'=>$workStation,'is_rotate'=>1])->asArray()->all();
            if(!empty($in)){
                foreach ($in as $k => $v){
                    $emp_data = Employee::find()->select(['emp_number'])->where(['emp_number'=>$v['emp_number']])->asArray()->one();
                    $record[] = $emp_data;
                }
            }
            if(!empty($out)){
                foreach ($out as $k => $v){
                   foreach ($record as $key =>$value){
                       if($value['emp_number'] == $v['emp_number']){
                            unset($record[$key]);
                       }
                   }
                }
            }


            foreach ($record as $k => $v){
                $user = Employee::find()->select(['emp_number','emp_firstname','joined_date','emp_birthday','special_personnel','eeo_cat_code','emp_gender','mutual_exclusion','work_station','is_leader'])->where(['emp_number'=>$v['emp_number']])->asArray()->one();
                $teach = Teach::find()->where(['emp_number'=>$v['emp_number']])->orderBy('end_time desc')->asArray()->one();
                $data[$k] = $user;
                $data[$k]['education_id'] = $teach['record_id'];;
            }

        $data = array_values($data);
        return $data;
    }


}
