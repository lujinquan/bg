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
use app\space\model\Floor as FloorModel;
use app\project\model\Shack as ShackModel;
use app\common\model\SystemAnnex as AnnexModel;
use app\space\model\SiteGroup as SiteGroupModel;

class Site extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $banArr = BanModel::where([['status','eq',1],['project_id','eq',PROJECT_ID]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
    }

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $SiteGroupModel = new SiteGroupModel;
            $where = $SiteGroupModel->checkWhere($getData);
            $fields = 'a.site_group_id,a.site_group_name,a.site_group_type,a.site_num,a.floor_number,b.ban_name,b.ban_address';
            $data = [];
            $temps = Db::name('space_site_group')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->field($fields)->where($where)->page($page)->order('a.ctime desc')->limit($limit)->select();
            foreach ($temps as $k => &$v) {
                // 统计方式【待优化】
                $v['shack_num'] = ShackModel::where([['site_group','like','%|'.$v['site_group_id'].'|%'],['shack_status','eq',1]])->count();
                if(!$v['shack_num']){
                    $v['shack_num'] = '——';
                }
                
            }
            //halt($temps);
            $data['data'] = $temps;
            $data['count'] = $SiteGroupModel->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->where($where)->count('site_group_id');
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
            $result = $this->validate($data, 'SiteGroup.add');
            if($result !== true) {
                return $this->error($result);
            }
            $ban_floors = explode(',',$data['ban_floor']);
            $data['ban_id'] = $ban_floors[0];
            $data['floor_number'] = $ban_floors[1];
            // 附件处理
            if(isset($data['file'])){
                $AnnexModel = new AnnexModel;
                $data['imgs'] = implode(',',$data['file']);  
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            // 统计工位数
            if(isset($data['sites']) && $data['sites']){
                $data['site_num'] = count($data['sites']);
            }else{
                $data['site_num'] = 0;
            }
            unset($data['site_id']);

            // 工位区入库
            $SiteGroupModel = new SiteGroupModel;
            $row = $SiteGroupModel->allowField(true)->create($data);
            if($row){
                // 如果新增的工位区为联合工位区，且添加了工位
                if($data['site_num']){
                    $sitesApplyData = [];
                    foreach($data['sites'] as $k => $v){
                        $sitesApplyData[$k]['site_group_id'] = $row['site_group_id'];
                        $sitesApplyData[$k]['site_name'] = $v;
                    }
                    $SiteModel = new SiteModel;
                    $SiteModel->insertAll($sitesApplyData);
                } 
                return $this->success('新增成功');
            }else{
                return $this->error('新增失败');
            }
            
        }
        $BanModel = new BanModel;
        $banFloors = $BanModel->banFloors();
        $this->assign('banFloors',$banFloors);
        return $this->fetch();
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'SiteGroup.edit');
            if($result !== true) {
                return $this->error($result);
            }
            $ban_floors = explode(',',$data['ban_floor']);
            $data['ban_id'] = $ban_floors[0];
            $data['floor_number'] = $ban_floors[1];
            // 附件
            if(isset($data['file'])){ 
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel->updateAnnexEtime($data['file']);
            }else{
                $data['imgs'] = '';
            }
            
            // 统计工位数
            if(!isset($data['site_num'])){
                if(isset($data['sites']) && $data['sites']){
                    $data['site_num'] = count($data['sites']);
                }else{
                    $data['site_num'] = 0;
                }
            }
            
            $SiteGroupModel = new SiteGroupModel();
            // 入库
            if (!$SiteGroupModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            // 如果新增的工位区为联合工位区，且添加了工位
            if(isset($data['sites']) && $data['sites']){
                $sitesApplyData = [];
                $SiteModel = new SiteModel;
                $SiteModel->where([['site_group_id','eq',$data['site_group_id']]])->delete();
                foreach($data['sites'] as $k => $v){
                    $sitesApplyData[$k]['site_group_id'] = $data['site_group_id'];
                    $sitesApplyData[$k]['site_name'] = $v;
                }
                $SiteModel->insertAll($sitesApplyData);
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = SiteGroupModel::get($id)->toArray();
        //halt($row);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        //$row['sites'] = SiteModel::where([['site_group_id','eq',$row['site_group_id']]])->column('site_id,site_name');
        $sites = SiteModel::where([['site_group_id','eq',$row['site_group_id']]])->field('site_id,site_name')->select()->toArray();

        //$row['sites']

        //获取入驻公司&个体户
        //$shackArr = ShackModel::with(['member','firm'])->where([['site_group','like','%|'.$row['site_group_id'].'|%'],['shack_status','eq',1]])->field('sites,firm_id,member_id')->select();
        $shackArr = ShackModel::with(['member','firm'])->where([['site_group','like','%|'.$row['site_group_id'].'|%'],['shack_status','eq',1]])->field('sites,firm_id,member_id')->select()->toArray();
        $firmArr = [];
        $shackSites = [];
        foreach ($shackArr as $k => $v) {

            if($v['member_id']){
                $group_id = $v['member_id'];
                $type = 1;
                $group_name = $v['member_name'];
                
            }
            if($v['firm_id']){
                $group_id = $v['firm_id'];
                $type = 2;
                $group_name = $v['firm_name'];
            }
            if($v['sites']){
                $siteArr =explode('|',trim($v['sites'],'|'));
                //halt($siteArr);
                foreach($siteArr as $s){
                    $shackSites[$s] = [
                        'group_id' => $group_id,
                        'type' => $type,
                        'group_name' => $group_name,
                    ];
                }
            }
        }
        //halt($shackSites);
        foreach ($sites as &$site) {
            if(isset($shackSites[$site['site_id']])){ //如果当前工位已被入驻
                $site['group_id'] = $shackSites[$site['site_id']]['group_id'];
                $site['type'] = $shackSites[$site['site_id']]['type'];
                $site['group_name'] = $shackSites[$site['site_id']]['group_name'];
            }else{
                $site['type'] = 0; //表示未被入驻
            }
        }

        //dump($sites);halt($shackArr);
        $BanModel = new BanModel;
        $banFloors = $BanModel->banFloors();
        $this->assign('banFloors',$banFloors);
        $this->assign('shackSites',$shackSites); //工位入驻数据
        $this->assign('sites',$sites); //工位数据
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {
        $ids = $this->request->param('id/a'); 
        // 删除工位区前提条件： 1、无个人或企业入驻，2、
        $res = SiteGroupModel::where([['site_group_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}