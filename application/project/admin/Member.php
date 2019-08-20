<?php

// +----------------------------------------------------------------------
// | ����ThinkPHP5����
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | �������������ѿ�Դ
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>��������QQȺ��*
// +----------------------------------------------------------------------


namespace app\project\admin;

use think\Db;
use app\system\admin\Admin;
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
            $data['data'] = $MemberModel->with('member_group')->field($fields)->where($where)->page($page)->order('ctime desc')->limit($limit)->select();
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
                'title' => '����',
                'url' => '?group=y',
            ],
            [
                'title' => '��ͣ��',
                'url' => '?group=n',
            ]
        ];
        $tabData['current'] = url('?group='.$group);
        $this->assign('group',$group);
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // ������֤
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //����
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $RestModel = new RestModel;
            unset($data['rest_id']);
            //halt($data);
            // ���
            if (!$RestModel->allowField(true)->create($data)) {
                return $this->error('����ʧ��');
            }
            return $this->success('�����ɹ�');
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
            // ������֤
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            $RestModel = new RestModel();
            // ���
            if (!$RestModel->allowField(true)->update($data)) {
                return $this->error('�༭ʧ��');
            }
            return $this->success('�༭�ɹ�');
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
            $this->success('ɾ���ɹ�');
        }else{
            $this->error('ɾ��ʧ��');
        }
    }
}