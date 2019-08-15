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


namespace app\space\admin;

use think\Db;
use app\system\admin\Admin;
use app\space\model\Ban as BanModel;
use app\space\model\Rest as RestModel;

class Rest extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $RestModel = new RestModel;
            $where = $RestModel->checkWhere($getData);
            $fields = 'a.rest_name,a.rest_type,a.floor_number,a.rest_volume,a.rest_unit_price,b.ban_id,b.ban_name';
            $data = [];
            $data['data'] = Db::name('space_rest')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->field($fields)->where($where)->page($page)->order('a.ctime desc')->limit($limit)->select();
            //halt($where);
            $data['count'] = Db::name('space_rest')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->where($where)->count('a.rest_id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            $RestModel = new RestModel;
            unset($data['rest_id']);
            // 入库
            if (!$RestModel->allowField(true)->create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
        }
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        return $this->fetch();
    }

    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            $RestModel = new RestModel();
            // 入库
            if (!$RestModel->allowField(true)->update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }
        $id = input('param.id/d');
        $row = BanModel::get($id);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {
        $ids = $this->request->param('id/a');        
        $res = RestModel::where([['rest_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}