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

namespace app\system\admin;

use app\system\model\SystemLanguage as LanguageModel;

/**
 * 语言包管理控制器
 * @package app\system\admin
 */
class Language extends Admin
{
    // [通用添加、修改专用] 模型名称，格式：模块名/模型名
    protected $hisiModel = 'SystemLanguage';
    // [通用添加、修改专用] 验证器类，格式：app\模块\validate\验证器类名
    protected $hisiValidate = 'app\system\validate\SystemLanguage';

    /**
     * 语言包管理首页
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $data           = [];
            $data['data']   = LanguageModel::order('sort asc')->select();
            $data['code']   = 0;
            return json($data);
        }

        return $this->fetch();
    }
}
