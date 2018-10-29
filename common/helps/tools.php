<?php
namespace common\helps;

/*
 * 自定义全局公共方法
 */
class tools{
    /*
     * 人事关系
     * **/
    public static function personnel(){
        $array = array(
            array('id'=>'1','name'=>'在编'),
            array('id'=>'2','name'=>'在编（劳司）'),
            array('id'=>'3','name'=>'聘用'),
            array('id'=>'4','name'=>'工勤'),
            array('id'=>'5','name'=>'临聘'),
            array('id'=>'6','name'=>'公司'),
            array('id'=>'7','name'=>'学生'),);
        return $array;
    }

    /*
     * 科室荣誉奖励种类
     * **/
    public static function rewardtype(){
        $array = array(
            array('id'=>'1','name'=>'教学'),
            array('id'=>'2','name'=>'科研'));
        return $array;
    }

    /*
     * 科室荣誉奖励类别
     * **/
    public static function rewardclass(){
        $array = array(
            array('id'=>'1','name'=>'国家级'),
            array('id'=>'2','name'=>'省部级'),
            array('id'=>'2','name'=>'院校级')
        );
        return $array;
    }


    //上传文件大小
    public static function filesize(){
        $filesize = 10*1024*1024;
        return $filesize;
    }

    //上传文件类型
    public static function filetype(){
        $filetype = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif');
        return $filetype;
    }
}