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
use app\space\model\Site as SiteModel;

class Site extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $SiteModel = new SiteModel;
            $where = $SiteModel->checkWhere($getData);
            $fields = 'a.site_id,a.site_name,a.site_type,a.site_num,a.floor_number,b.ban_name,b.ban_address';
            $data = [];
            $data['data'] = Db::name('space_site')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->field($fields)->where($where)->page($page)->order('a.ctime desc')->limit($limit)->select();
            //halt($data['data']);
            $data['count'] = $SiteModel->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->where($where)->count('site_id');
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
            $result = $this->validate($data, 'Site.add');
            if($result !== true) {
                return $this->error($result);
            }
            $SiteModel = new SiteModel;
            unset($data['site_id']);
            // 入库
            if (!$BanModel->allowField(true)->create($data)) {
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
            $result = $this->validate($data, 'Site.add');
            if($result !== true) {
                return $this->error($result);
            }
            $SiteModel = new SiteModel();
            // 入库
            if (!$SiteModel->allowField(true)->update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }
        $id = input('param.id/d');
        $row = BanModel::get($id);
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function detail()
    {
        $id = input('param.id/d');
        $row = BanModel::get($id);
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
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