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
use app\project\model\Firm as FirmModel;
use app\system\model\SystemGuard as GuardModel;
use app\project\model\Member as MemberModel;
use app\common\model\SystemAnnex as AnnexModel;

/**
 * 人员管理
 */
class Member extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $MemberModel = new MemberModel;
            $where = $MemberModel->checkWhere($getData);
            $fields = '*';
            $data = [];
            $data['data'] = $MemberModel->with('firm')->field($fields)->where($where)->page($page)->order('ctime desc')->limit($limit)->select();
            //halt($where);
            $data['count'] = $MemberModel->where($where)->count('member_id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        $group = input('group','y');
        $tabData = [];
        $tabData['menu'] = [
            [
                'title' => '正常',
                'url' => '?group=y',
            ],
            [
                'title' => '已停用',
                'url' => '?group=n',
            ]
        ];
        $tabData['current'] = url('?group='.$group);
        $this->assign('group',$group);
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch('member/index');
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            $MemberModel = new MemberModel;

            //人员管理页面中的新增员工
            if(isset($data['flag'])){
                if(isset($data['guard'])){
                    $data['guard'] = [
                        'ban' => $data['ban'],
                        'floor' => $data['floor'],
                        'guard' => $data['guard'],
                    ];
                }
                if (!$MemberModel->allowField(true)->save($data)) {
                    return $this->error('新增失败');
                }
                return $this->success('新增成功');
            }

            //入驻办理页面中的批量新增员工
            $result = [];
            $sum = count($data['member_name']);

            for ($i=0; $i < $sum; $i++) { 
                if($i == 0){
                    $result[$i] = [
                        'member_name' =>$data['member_name'][$i],
                        'member_tel' =>$data['member_tel'][$i],
                        'member_post' =>$data['member_post'][$i],
                        'member_department' =>$data['member_department'][$i],
                        'member_card' =>$data['member_card'][$i],
                        'member_type' => 1, //企业管理员
                        'project_id' => PROJECT_ID,
                        'firm_id' => $data['firm_id'],
                        'is_activate' => 1,
                    ];
                }else{
                    $result[$i] = [
                        'member_name' =>$data['member_name'][$i],
                        'member_tel' =>$data['member_tel'][$i],
                        'member_post' =>$data['member_post'][$i],
                        'member_department' =>$data['member_department'][$i],
                        'member_card' =>$data['member_card'][$i],
                        'member_type' => 2, //企业管理员
                        'project_id' => PROJECT_ID,
                        'firm_id' => $data['firm_id'],
                        'is_activate' => 1,
                    ];
                }
            }
            if (!$MemberModel->allowField(true)->saveAll($result)) {
                return $this->error('入驻失败');
            }
            return $this->success('入驻成功');
        }
        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        $this->assign('guardArr', $guardArr);

        $firmArr = FirmModel::where([['status','eq',1]])->field('firm_id,firm_name')->select();
        $this->assign('firmArr',$firmArr);

        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        return $this->fetch();
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if(isset($data['guard'])){
                $data['guard'] = [
                    'ban' => $data['ban'],
                    'floor' => $data['floor'],
                    'guard' => $data['guard'],
                ];
            }
            $MemberModel = new MemberModel;
            if (!$MemberModel->allowField(true)->update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }
        $id = input('param.id/d');
        $row = MemberModel::get($id);

        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        $this->assign('guardArr', $guardArr);

        $firmArr = FirmModel::where([['status','eq',1]])->field('firm_id,firm_name')->select();
        $this->assign('firmArr',$firmArr);

        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function stop()
    {
        $id = $this->request->param('id');        
        $res = MemberModel::where([['member_id','eq',$id]])->update(['status'=>0]);
        if($res){
            $this->success('停用成功');
        }else{
            $this->error('停用失败');
        }
    }

    public function del()
    {
        $id = $this->request->param('id');        
        $res = MemberModel::where([['member_id','eq',$id]])->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}