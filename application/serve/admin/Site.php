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
use app\space\model\Floor as FloorModel;
use app\space\model\SiteGroup as SiteGroupModel;
use app\project\model\Shack as ShackModel;

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
        $SiteGroupModel = new SiteGroupModel;
        $group = input('group','union');
        if ($this->request->isAjax() && $group == 'union') {
            //$data = $SiteGroupModel->getList();
            $getData = $this->request->get();

            $data = [];
            $date = '';
            $ban_name = '';
            $meet_name = '';
            $floor_number = '';
            if(isset($getData['ban_name']) && $getData['ban_name']){
                $ban_name = $getData['ban_name'];
            }
            if(isset($getData['floor_number']) && $getData['floor_number']){
                $floor_number = $getData['floor_number'];   
            }
            if(isset($getData['meet_name']) && $getData['meet_name']){
                $meet_name = $getData['meet_name'];  
            }
            if(isset($getData['date']) && $getData['date']){
                $date = $getData['date']; 
            }
            $data['data'] = $SiteGroupModel->getList($ban_name,$floor_number,$meet_name,$date,$site_group_type = 1);
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);

        }
        if ($this->request->isAjax() && $group == 'independent') {
            //$data = $SiteGroupModel->getList();
            $getData = $this->request->get();

            $data = [];
            $date = '';
            $ban_name = '';
            $meet_name = '';
            $floor_number = '';
            if(isset($getData['ban_name']) && $getData['ban_name']){
                $ban_name = $getData['ban_name'];
            }
            if(isset($getData['floor_number']) && $getData['floor_number']){
                $floor_number = $getData['floor_number'];   
            }
            if(isset($getData['meet_name']) && $getData['meet_name']){
                $meet_name = $getData['meet_name'];  
            }
            if(isset($getData['date']) && $getData['date']){
                $date = $getData['date']; 
            }
            $data['data'] = $SiteGroupModel->getList($ban_name,$floor_number,$meet_name,$date,$site_group_type = 2);
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);

        }
        
            
        
        $tabData = [];
        $tabData['menu'] = [
            [
                'title' => '联合工位区',
                'url' => '?group=union',
            ],
            [
                'title' => '独立工位区',
                'url' => '?group=independent',
            ],
            [
                'title' => '自由工位区',
                'url' => '?group=free',
            ]
        ];
        $tabData['current'] = url('?group='.$group);
        $this->assign('group',$group);
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch('index_'.$group);

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
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $SiteGroupModel = new SiteGroupModel;
            unset($data['site_id']);
            // 入库
            if (!$SiteGroupModel->allowField(true)->create($data)) {
                return $this->error('新增失败');
            }
            return $this->success('新增成功');
        }

        return $this->fetch();
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'SiteGroup.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $SiteGroupModel = new SiteGroupModel();
            // 入库
            if (!$SiteGroupModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = SiteGroupModel::get($id)->toArray();
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $row['sites'] = explode('|',$row['sites']);
        array_shift($row['sites']);
        array_pop($row['sites']);

        //获取入驻公司&个体户
        $shackArr = ShackModel::with(['member','firm'])->where([['site_group','like','%|'.$row['site_group_id'].'|%'],['shack_status','eq',1]])->field('firm_id,member_id')->select();
        $firmArr = [];
        foreach ($shackArr as $k => $v) {
            if($v['member_id']){
                $firmArr[] = [
                    'group_id' => $v['member_id'],
                    'type' => 1,
                    'group_name' => $v['member_name'],
                ];
            }
            if($v['firm_id']){
                $firmArr[] = [
                    'group_id' => $v['firm_id'],
                    'type' => 1,
                    'group_name' => $v['firm_name'],
                ];
            }
        }
        //halt($firmArr);
        $this->assign('firmArr',$firmArr);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {
        $ids = $this->request->param('id/a');        
        $res = SiteGroupModel::where([['ban_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}