<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 https://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------

namespace app\system\validate;

use think\Validate;

/**
 * 用户验证器
 * @package app\system\validate
 */
class SystemUserManage extends Validate
{
    //定义验证规则
    protected $rule = [
    	'username|账户名称' => 'require|unique:system_user',
        'nick|昵称'       => 'require|unique:system_user',
        //'role_id|角色'    => 'requireWith:role_id|notIn:0,1',
        'post|职务'       => 'require',
        //'pro_ids|授权项目' => 'require',
        'password|密码'   => 'require|confirm',
        //'mobile|手机号'   => 'requireWith:mobile|regex:^1\d{10}',
        
        //'__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        // 'username.require' => '请输入账户名称',
        // 'role_id.require'  => '请选择角色分组',
        // 'role_id.notIn'    => '禁止设置为超级管理员',
        // 'email.require'    => '邮箱不能为空',
        // 'email.email'      => '邮箱格式不正确',
        // 'email.unique'     => '该邮箱已存在',
        //'password.require' => '密码为空',
        //'password.length'  => '密码设置无效',
        //'mobile.regex'     => '手机号不正确',
    ];

    // //定义验证场景
    // protected $scene = [
    //     //新增
    //     'sceneAdd'  =>  ['username','nick','role_id'],
    //     //编辑
    //     'sceneEdit'  =>  ['nick','role_id'],
    //     //设置密码
    //     'sceneSetPassword'  =>  ['password'],
         
    // ];

    //添加
    public function sceneAdd()
    {
        return $this->only(['username', 'nick','post']);
        //return $this->only(['username', 'nick', 'role_id','post'])->remove('post',['require']);
    }

    // 编辑
    public function sceneEdit()
    {
        return $this->only(['post']);
    }

    // 自定义更新个人信息
    public function sceneSetPassword()
    {
        return $this->only(['password']);
    }

    
}

