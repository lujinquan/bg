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

namespace app\serve\validate;

use think\Validate;

/**
 * 空间活动验证器
 * @package app\system\validate
 */
class Activity extends Validate
{
	//定义验证规则
    protected $rule = [
        'activity_title|活动名称'       => 'require|unique:serve_activity',
        'activity_type|活动内容'      => 'require',
        'file|活动封面图'      => 'require',
        'activity_start_time|活动起始时间'      => 'require',
        'activity_end_time|活动截止时间'      => 'require',
        'activity_address|活动地点'      => 'require',
        'activity_sponsor|活动主办方'      => 'require',
        'activity_participants|活动参与人员'      => 'require',
        //'__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        
    ];

     // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['activity_title', 'activity_type','activity_start_time','__token__','activity_end_time', 'activity_address','activity_sponsor','activity_participants','file']);
    }

    // 自定义更新场景
    public function sceneEdit()
    {
        return $this->only(['activity_title', 'activity_type','activity_start_time','__token__','activity_end_time', 'activity_address','activity_sponsor','activity_participants','file'])->remove('activity_title', ['unique']);
    }

}