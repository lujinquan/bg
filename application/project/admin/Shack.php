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


namespace app\project\admin;

use think\Db;
use app\system\admin\Admin;
use app\project\model\Shack as ShackModel;
use app\system\model\SystemGuard as GuardModel;
use app\common\model\SystemAnnex as AnnexModel;

class Shack extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $ShackModel = new ShackModel;
            $where = $ShackModel->checkWhere($getData);
            $fields = '*';
            $data = [];
            $data['data'] = $ShackModel->with(['member_firm','SystemUser'])->field($fields)->where($where)->page($page)->order('ctime desc')->limit($limit)->select();
            //halt($data['data']);
            $data['count'] = $ShackModel->where($where)->count('id');
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
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $RestModel = new RestModel;
            unset($data['rest_id']);
            //halt($data);
            // 入库
            if (!$RestModel->allowField(true)->create($data)) {
                return $this->error('新增失败');
            }
            return $this->success('新增成功');
        }
        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        $this->assign('guardArr', $guardArr);
        return $this->fetch();
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
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
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = RestModel::get($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        //halt($row);
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