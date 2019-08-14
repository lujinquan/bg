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
use app\space\model\Park as ParkModel;

class Park extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $banModel = new BanModel;
            $where = $banModel->checkWhere($getData);
            $fields = 'ban_id,ban_name,ban_address';
            $data = [];
            $data['data'] = $banModel->field($fields)->where($where)->page($page)->order('ctime desc')->limit($limit)->select();
            //halt($where);
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
            $BanModel = new BanModel();
            unset($data['ban_id']);
            // 入库
            if (!$BanModel->allowField(true)->create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
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
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
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
        $ids = $this->request->param('id/a');        
        $res = BanModel::where([['ban_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    public function struct()
    {
        $id = input('param.id/d');
        $row = BanModel::get($id);
        if ($this->request->isAjax()) {
            $id = input('param.id/d');
            $unitID = input('param.unit_id/d',1);
            $row = BanModel::get($id);
            $houseArr = HouseModel::with(['tenant'])->where([['ban_id','eq',$id],['house_unit_id','eq',$unitID]])->field('house_floor_id,house_id,tenant_id')->order('house_floor_id asc')->select();
            $tempHouseArr = [];
            //dump($row['ban_floors']);halt($houseArr);
            
            for($j=1;$j<=$row['ban_floors'];$j++){
                foreach($houseArr as $h){
                    if($h['house_floor_id'] == $j){
                        $tempHouseArr[$j][] = [
                            'house_id' => $h['house_id'],
                            'tenant_name' => $h['tenant_name'],
                        ];
                    } 
                }
                if(!isset($tempHouseArr[$j])){
                    $tempHouseArr[$j] = [];
                }
            }
            $data = [];
            $data['data'] = $tempHouseArr;
            $data['code'] = 0;
            $data['msg'] = '获取成功';
            // halt($data);
            return json($data);
        }
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function ceshi()
    {
        return $this->fetch();
    }
}