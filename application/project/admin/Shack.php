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
use app\space\model\Ban as BanModel;
use app\space\model\SiteGroup as SiteGroupModel;
use app\project\model\Shack as ShackModel;
use app\project\model\Firm as FirmModel;
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
            $data['data'] = $ShackModel->with(['firm','SystemUser'])->field($fields)->where($where)->page($page)->order('ctime desc')->limit($limit)->select();
            //halt($data['data']);
            $data['count'] = $ShackModel->where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        return $this->fetch();
    }

    public function getFirms()
    {
        $keywords = input('param.keywords/s');
        $where = [];
        $where[] = ['status','eq',1];
        // 查询公司名称
        if($keywords){
            $where[] = ['firm_name','like','%'.$keywords.'%'];
        }
        $data = [];
        $data['data'] = FirmModel::where($where)->field('firm_id as userCode,firm_name as userName,firm_name as deptName')->select();
        $data['count'] = FirmModel::where($where)->count();
        return json($data);

    }

    public function getFirmDetail()
    {
        $firm_name = input('param.firm_name');
        $where = [];
        $where[] = ['firm_name','eq',$firm_name];
        $row = FirmModel::where($where)->find();
        if($row){
            $data = [];
            $data['data'] = $row;
            $data['code'] = 1;
            return json($data);
        }else{
            return $this->error('系统中无该公司数据');
        }
    }

    // public function ceshi()
    // {
    //     $data = [
    //         "code"=> 0,
    //         "msg"=> "",
    //         "count"=> 100,
    //         "data"=> [
    //             [
    //                 "userName"=> "测试用户1",
    //                 "userCode"=> "170001",
    //                 "deptName"=> "技术部门1"
    //             ], [
    //                 "userName"=> "测试用户2",
    //                 "userCode"=> "170002",
    //                 "deptName"=> "行政部门1"
    //             ], [
    //                 "userName"=> "测试用户3",
    //                 "userCode"=> "170003",
    //                 "deptName"=> "测试部门2"
    //             ], [
    //                 "userName"=> "测试用户4",
    //                 "userCode"=> "170004",
    //                 "deptName"=> "测试部门2"
    //             ], [
    //                 "userName"=> "测试用户5",
    //                 "userCode"=> "170005",
    //                 "deptName"=> "测试部门3"
    //             ]
    //         ]
    //     ];
    //     return json($data);
                        
        
    // }

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
        
        // 获取工位区
        $siteGroupModel = new SiteGroupModel;
        $sites = $siteGroupModel->where([['status','eq',1]])->field('site_group_id,site_group_type,site_group_name')->select();
        $siteArr = [];
        $siteArr[1] = [];
        $siteArr[2] = [];
        foreach ($sites as $k => $v) {
            $siteArr[$v['site_group_type']][] = [
                'value' => $v['site_group_id'],
                'title' => $v['site_group_name'],
            ];
        }
        $this->assign('siteArr',$siteArr);
        // 获取楼宇
        $banArr = BanModel::where([['status','eq',1],['project_id','eq',PROJECT_ID]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        $this->assign('guardArr', $guardArr);

        $type = input('param.type/d');
        switch ($type) {
            case 1: // 企业入驻
                $html = 'addfirm';
                break;
            case 2: // 个人入驻
                $html = 'addpersion';
                break;
            case 3: // 自由工位区入驻
                $html = 'addsitegroup';
                break;
            case 4: //其他场地入驻
                $html = 'addrest';
                break;

            default: //企业入驻
                $html = 'addfirm';
                break;
        }
        return $this->fetch($html);
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