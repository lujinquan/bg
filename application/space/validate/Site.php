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
 * 工位区 - 验证器
 * @package app\system\validate
 */
class Site extends Validate
{
    //定义验证规则
    protected $rule = [
        'site_name|工位区名称'    	=> 'require',
        'site_type|工位区类型'    	=> 'require',
        'site_area|工位区面积'    	=> 'require|float',
        'ban_id|所属楼宇'    	=> 'require|number',
        'floor_number|所属楼层'     => 'require',
        'sites|工位'    	=> 'require', 
        '__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [

    ];

    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['site_name','site_type','ban_id','floor_number','sites','site_area','__token__']);
    }

    
}
