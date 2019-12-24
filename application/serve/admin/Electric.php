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


namespace app\serve\admin;

use think\Db;
use app\system\admin\Admin;
use app\common\model\SystemAnnex as AnnexModel;
use app\space\model\Ban as BanModel;
use app\space\model\Meeting as MeetingModel;

class Electric extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        // $banArr = BanModel::where([['status','eq',1],['project_id','eq',PROJECT_ID]])->field('ban_id,ban_name')->select();
        // $this->assign('banArr',$banArr);
    }

    public function index()
    { 
    	return $this->fetch();
    }
}