<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------

namespace app\space\validate;

use think\Validate;

/**
 * 空间场地验证器
 * @package app\system\validate
 */
class Rest extends Validate
{
    //定义验证规则
    protected $rule = [
        'rest_name|场地名称'    	=> 'require',
        'rest_type|场地类型'    	=> 'require',
        'facility_ids|门禁设备'     => 'require',
        'door_ids|路径门禁'     => 'require',
        //'rest_area|面积'    	=> 'require|float',
        // 'ban_id|楼宇'    	=> 'require|number',
        // 'floor_number|所属楼层'     => 'require',
        'ban_floor|楼宇楼层'                => 'require|banFloorCheck',
        'order_start_time|可预约起始时间'      => 'require|formatStartTime',
        'order_end_time|可预约结束时间'        => 'require|formatEndTime',
        '__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [

    ];

    protected function banFloorCheck($value, $rule='', $data)
    {
        $ban_floors = explode(',',$value);
        if(in_array('', $ban_floors)){
            return '请选择正确的楼宇楼层';
        }else{
            return true; 
        }  
    }

    protected function formatStartTime($value, $rule='', $data)
    {
        $date = substr($value, 3);
        if(!in_array($date, ['00','15','30','45'])){
            return '预约起始时间格式不正确';
        }
        return true; 
    }

    protected function formatEndTime($value, $rule='', $data)
    {
        $date = substr($value, 3);
        if(!in_array($date, ['00','15','30','45'])){
            return '预约结束时间格式不正确';
        }
        if(strtotime($value) <= strtotime($data['order_start_time'])){
            return '预约结束时间必须大于开始时间';
        }
        return true; 
    }

    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['rest_name','rest_type','ban_floor','order_start_time','order_end_time','door_ids','facility_ids','__token__']);
    }

    
}
