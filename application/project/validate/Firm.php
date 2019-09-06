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

namespace app\project\validate;

use think\Validate;

/**
 * 企业验证器
 * @package app\system\validate
 */
class Firm extends Validate
{
    //定义验证规则
    protected $rule = [
        'firm_name|企业名称'    	=> 'require|unique:member_firm',
        'firm_manager|联系人姓名'    	=> 'require',
        'firm_tel|联系人电话'    	=> 'require|mobile',
        'firm_credit_code|社会信用代码'     => 'require',
        'firm_industry_type|所属行业'    	=> 'require',
        'firm_registered_capital|注册资本'      => 'require|float',
        'firm_legaler|法人姓名'        => 'require',
        'firm_established_time|成立日期'        => 'require',
        'firm_registered_address|注册地址'        => 'require',
        'firm_scope|经营范围'        => 'require',
        //'__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [

    ];

    // 自定义更新场景
    public function sceneAdd()
    {
        return $this->only(['firm_name','firm_manager','firm_tel','firm_credit_code','firm_industry_type','firm_registered_capital','firm_legaler','firm_established_time','firm_registered_address','firm_scope']);
    }

     // 自定义更新场景
    public function sceneEdit()
    {
        return $this->only(['firm_manager','firm_tel']);
    }

    
}
