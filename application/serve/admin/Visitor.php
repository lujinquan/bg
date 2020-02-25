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
use app\serve\model\Visitor as VisitorModel;
use app\project\model\Member as MemberModel;
use app\common\model\SystemAnnex as AnnexModel;

class Visitor extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
    }

    public function index()
    { 
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $VisitorModel = new VisitorModel;
            $where = $VisitorModel->checkWhere($getData);
            //halt($getData);
            $data = [];
            $data['data'] = $VisitorModel->with('member')->where($where)->page($page)->order('visitor_id desc')->limit($limit)->select()->toArray();
            //halt($data['data']);
            $data['count'] = $VisitorModel->where($where)->count();
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);

        }
    	return $this->fetch();
    }

    public function add()
    { 
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Visitor.add');
            if($result !== true) {
                return $this->error($result);
            }
            $VisitorModel = new VisitorModel;
            //$data['visitor_cuid'] = ADMIN_ID;
            $data['project_id'] = PROJECT_ID;
            // 入库
            if (!$VisitorModel->allowField(true)->create($data)) {
                return $this->error('新增失败');
            }
            return $this->success('新增成功');
        }
        $MemberModel = new MemberModel;
        $members = MemberModel::where([['project_id','eq',PROJECT_ID],['member_status','eq',1]])->field('member_id,member_name')->select()->toArray();
        //halt($members);
        $this->assign('members',$members);
        return $this->fetch();
    }

    /**
     * 功能描述：签到
     * =====================================
     * @author  Lucas 
     * email:   598936602@qq.com 
     * Website  address:  www.mylucas.com.cn
     * =====================================
     * 创建时间: 2020-02-24 12:01:15
     * @example 
     * @link    文档参考地址：
     * @return  返回值  
     * @version 版本  1.0
     */
    public function sign()
    { 
        if ($this->request->isGet()) {
            $id = input('id');
            $row = VisitorModel::get($id);
            if($row->sign_time){
                return $this->error('已签到，请勿重复签到！');
            }
            $row->sign_time = time();
            if (!$row->save()) {
                return $this->error('签到失败');
            }
            return $this->success('签到成功');
        }
    }

}