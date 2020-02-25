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
 * 入驻验证器
 * @package app\system\validate
 */
class Shack extends Validate
{
    //定义验证规则
    protected $rule = [
        'member_card|身份证号'    	=> 'idCard',
        'shack_start_time|入驻开始时间'    	=> 'require',
        'shack_end_time|入驻结束时间'    	=> 'require|shackTimeCheck',
        'guard|门禁'    	=> 'require',
        //'__token__'      	  	=> 'require|token',
    ];

    //定义验证提示
    protected $message = [
        // 'firm_name.unique' => '企业名称已存在，请点击效验按钮',
        // 'firm_registered_capital.gt' => '请输入正确注册资本金额',
    ];

    protected function shackTimeCheck($value, $rule='', $data)
    {
        if(strtotime($value) <= strtotime($data['shack_start_time'])){
            return '入驻结束时间必须大于开始时间';
        }
        return true; 
    }

    // 公司入驻
    public function sceneAddfirm()
    {
        //return $this->only(['firm_name','firm_manager','firm_tel','firm_registered_capital','firm_legaler','firm_established_time','firm_registered_address']);
    }

    // 个人入驻
    public function sceneAddpersion()
    {
        return $this->only(['member_card']);
    }

    // 自由工位入驻
    public function sceneAddsitegroup()
    {
        return $this->only(['member_card','shack_start_time','shack_end_time','guard']);
    }

    // 其他场地入驻
    public function sceneAddrest()
    {
        //return $this->only(['firm_manager','firm_tel']);
    }
    
}
