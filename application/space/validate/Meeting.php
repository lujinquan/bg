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
 * 用户验证器
 * @package app\system\validate
 */
class Meeting extends Validate
{
    //定义验证规则
    protected $rule = [
        'meet_name|会议室名称'    	=> 'require',
        'meet_area|面积'    	=> 'require|float',
        'ban_id|楼宇'    	=> 'require|number',
        'floor_number|楼层'     => 'require',
        'meet_volume|面积'    	=> 'require|float',
        'meet_unit_price|单价'    	=> 'require|float',
        '__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [

    ];

    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['meet_name','meet_area','ban_id','floor_number','meet_volume','meet_unit_price','__token__']);
    }

    
}
