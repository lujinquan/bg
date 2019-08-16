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
        'rest_area|面积'    	=> 'require|float',
        'ban_id|楼宇'    	=> 'require|number',
        'floor_number|所属楼层'     => 'require',
        'order_start_time|可预约起始时间'    	=> 'require',
        'order_end_time|可预约结束时间'    	=> 'require',
        '__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [

    ];

    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['rest_name','rest_type','ban_id','floor_number','order_start_time','order_end_time','rest_area','__token__']);
    }

    
}
