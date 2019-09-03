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

use app\system\admin\Admin;
use app\space\model\Ban as BanModel;
use app\space\model\Floor as FloorModel;
use app\system\model\SystemUser as UserModel;

class Ban extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $banModel = new BanModel;
            $where = $banModel->checkWhere($getData);
            $fields = 'ban_id,ban_name,ban_area,ban_address';
            $data = [];
            $temp = $banModel->field($fields)->where($where)->page($page)->order('ctime desc')->limit($limit)->select();
            foreach($temp as &$t){
                $f = FloorModel::where([['ban_id','eq',$t['ban_id']]])->field('group_concat(floor_number) floor_numbers,sum(floor_area) floor_areas')->find();
                
                $t['floor_numbers'] = $f['floor_numbers'];
                $t['floor_areas'] = $f['floor_areas'];
                //$t['floor_numbers'] = $t->floor();
            }
            //halt($temp);
            $data['data'] = $temp;
            $data['count'] = $banModel->where($where)->count('ban_id');
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
            $result = $this->validate($data, 'Ban.add');
            if($result !== true) {
                return $this->error($result);
            }
            $data['project_id'] = PROJECT_ID;
            $BanModel = new BanModel();
            unset($data['ban_id']);
            // 入库
            if (!$BanModel->allowField(true)->create($data)) {
                return $this->error('新增失败');
            }
            return $this->success('新增成功');
        }
        return $this->fetch();
    }

    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Ban.add');
            if($result !== true) {
                return $this->error($result);
            }
            $BanModel = new BanModel();
            // 入库
            if (!$BanModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = BanModel::get($id);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function detail()
    {
        $id = input('param.id/d');
        $row = BanModel::get($id);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id');
            $password = $this->request->param('password');
            $realPassword = UserModel::where([['id','eq',ADMIN_ID]])->value('password');
            if(!password_verify($password, $realPassword)){
                $this->error('密码效验失败');
            }   
            $floors = FloorModel::where([['ban_id','eq',$id]])->count('floor_id'); 
            if($floors){
                $this->error('请先删除该楼宇下所有楼层');   
            }
            $res = BanModel::where([['ban_id','eq',$id]])->update(['status'=>0]);
            if($res){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }
        $ban_id = $this->request->param('id'); 
        $this->assign('ban_id',$ban_id);
        return $this->fetch();
    }
    
}