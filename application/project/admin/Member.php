<?php

// +----------------------------------------------------------------------
// | »ùÓÚThinkPHP5¿ª·¢
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | »ù´¡¿ò¼ÜÓÀ¾ÃÃâ·Ñ¿ªÔ´
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>£¬¿ª·¢ÕßQQÈº£º*
// +----------------------------------------------------------------------


namespace app\project\admin;

use think\Db;
use app\system\admin\Admin;
use app\space\model\Ban as BanModel;
use app\project\model\Member as MemberModel;
use app\common\model\SystemAnnex as AnnexModel;


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
            
            // $result = $this->validate($data, 'Member.add');
            // if($result !== true) {
            //     return $this->error($result);
            // }
            // if(isset($data['file'])){ 
            //     $data['imgs'] = implode(',',$data['file']);
            //     $AnnexModel = new AnnexModel;
            //     $AnnexModel->updateAnnexEtime($data['file']);
            // }
            $MemberModel = new MemberModel;
            $result = [];
            $sum = count($data['member_name']);
            //dump($sum);halt($data);
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
            //halt($result);
            
            if (!$MemberModel->allowField(true)->saveAll($result)) {
                return $this->error('新增失败');
            }
            return $this->success('新增成功');
        }
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        return $this->fetch();
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // Êý¾ÝÑéÖ¤
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            $RestModel = new RestModel();
            // Èë¿â
            if (!$RestModel->allowField(true)->update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }
        $id = input('param.id/d');
        $row = MemberModel::get($id);
        //$row['imgs'] = AnnexModel::changeFormat($row['imgs']);
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