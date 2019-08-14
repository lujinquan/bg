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
use app\common\model\SystemAnnex;
use app\space\model\Ban as BanModel;
use app\space\model\Floor as FloorModel;

class Floor extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $floorModel = new FloorModel;
            $where = $floorModel->checkWhere($getData);
            $fields = 'a.floor_id,a.floor_number,a.floor_area,b.ban_id,b.ban_name,b.ban_address';
            $data = [];
            $data['data'] = Db::name('space_floor')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->field($fields)->where($where)->page($page)->order('a.ctime desc')->limit($limit)->select();

            $data['count'] = Db::name('space_floor')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->count('a.floor_id');
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
            $result = $this->validate($data, 'Floor.add');
            if($result !== true) {
                return $this->error($result);
            }
            $FloorModel = new FloorModel();
            $findRow = $FloorModel->where([['ban_id','eq',$data['ban_id']],['floor_number','eq',$data['floor_number']],['status','eq',1]])->find();
            if ($findRow) {
                return $this->error('当前楼宇'.$data['floor_number'].'层，已存在系统中！');
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                (new \app\common\model\SystemAnnex)->updateAnnexEtime($data['file']);
            }
            unset($data['floor_id']);
            // 入库
            if (!$FloorModel->allowField(true)->create($data)) {
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
            $result = $this->validate($data, 'Floor.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                (new \app\common\model\SystemAnnex)->updateAnnexEtime($data['file']);
            }
            $FloorModel = new FloorModel();
            // 入库
            if (!$FloorModel->allowField(true)->update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }
        $id = input('param.id/d');
        $row = FloorModel::with('ban')->find($id);
        $row['imgs'] = SystemAnnex::changeFormat($row['imgs']);
        //halt($row);
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
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
        $res = FloorModel::where([['floor_id','in',$ids]])->update(['status'=>0]);
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