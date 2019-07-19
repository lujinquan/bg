<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | Motto ：No pains, no gains !
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>
// +----------------------------------------------------------------------
namespace app\system\validate;

use think\Validate;

/**
 * 房屋验证器
 * @package app\admin\validate
 */
class SystemProject extends Validate
{
    //定义验证规则
    protected $rule = [
        'project_name|项目名称' => 'require|unique:project',
        'project_address|项目地址' => 'require',
    ];

    //定义验证提示
    protected $message = [
        
    ];

    //定义验证场景
    protected $scene = [
        //新增
        'sceneForm'  =>  ['project_name','project_address'],
         
    ];
}