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
 * 会议室验证器
 * @package app\system\validate
 */
class Meeting extends Validate
{
    //定义验证规则
    protected $rule = [
        'meet_name|会议室名称'    	=> 'require',
        'meet_area|面积'    	=> 'require|float',
        'ban_floor|楼宇楼层'                => 'require|banFloorCheck',
        'meet_unit_price|单价'    	=> 'require|float',
        'order_start_time|可预约起始时间'      => 'require',
        'order_end_time|可预约结束时间'        => 'require',
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
 
    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['meet_name','meet_area','ban_id','floor_number','order_start_time','order_end_time','meet_unit_price','__token__']);
    }

    
}
