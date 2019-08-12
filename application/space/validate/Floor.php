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
class Floor extends Validate
{
    //定义验证规则
    protected $rule = [
        'floor_number|楼层'     => 'require|number',
        'floor_area|面积'    	=> 'require',
        '__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [

    ];

    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['floor_number', 'floor_area','__token__']);
    }

    
}
