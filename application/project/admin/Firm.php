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
use app\common\model\SystemAnnex as AnnexModel;
use app\project\model\Firm as FirmModel;
use app\project\model\Member as MemberModel;
use app\project\model\Shack as ShackModel;
use app\space\model\SiteGroup as SiteGroupModel;
use app\system\model\SystemGuard as GuardModel;
use app\system\model\SystemUser as UserModel;

class Firm extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $ShackModel = new ShackModel;
            $where = $ShackModel->checkWhere($getData);
            $fields = 'a.firm_id,a.member_id,b.firm_name,b.firm_coupon_num,c.member_name,from_unixtime(a.shack_start_time,\'%Y-%m-%d\') shack_start_time,from_unixtime(a.shack_end_time,\'%Y-%m-%d\') shack_end_time,a.shack_status,a.id';
            $data = [];
            $temps = Db::name('project_shack')->alias('a')->join('member_firm b','a.firm_id = b.firm_id','left')->join('member c','a.member_id = c.member_id','left')->where($where)->field($fields)->page($page)->order('a.ctime desc')->limit($limit)->select();
            //halt($temps);
            $curTime = strtotime(date('Y-m-d'));
            foreach ($temps as $k => &$v) {
                if($v['member_name']){
                    $v['group_name'] = $v['member_name'];
                }
                if($v['firm_name']){
                    $v['group_name'] = $v['firm_name'];
                }
                $diffTime = strtotime($v['shack_end_time']) - $curTime;

                //入驻人数
                
                //状态
                if($diffTime > 3600*24){
                    $v['firm_type'] = 1; //已入驻
                }elseif($diffTime > 0){
                    $v['firm_type'] = 2; //即将到期
                }else{
                    $v['firm_type'] = 3; //已到期
                }
            }
            $data['data']  = array_slice($temps, ($page- 1) * $limit, $limit);
            //halt($data['data']);
            $data['count'] = Db::name('project_shack')->alias('a')->join('member_firm b','a.firm_id = b.firm_id','left')->join('member c','a.member_id = c.member_id','left')->where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        $group = input('group','y');
        $tabData = [];
        $tabData['menu'] = [
            [
                'title' => '当前入驻',
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
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if($data['firm_id']){ //二次录入
                $result = $this->validate($data, 'Firm.edit');
            }else{ //初次录入
                $result = $this->validate($data, 'Firm.add');
            }
            // 数据验证
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $FirmModel = new FirmModel;

            if($data['firm_id']){ //如果存在即是编辑
                if (!$FirmModel->allowField(true)->update($data)) {
                    return $this->error('录入失败');
                }
                return $this->success('录入成功');
            }else{ //如果不存在即是新增 
                // 入库
                $res = $FirmModel->allowField(true)->create($data);
                //halt($res);
                if (!$res) {
                    return $this->error('录入失败');
                }
                return $this->success('录入成功','',['firm_id'=>$res['firm_id']]);
            }    
        }
        return $this->fetch();
    }

    /**
     * 企业编辑
     * @return [type] [description]
     */
    public function edit()
    {
        
        if ($this->request->isPost()) {
            $data = $this->request->post();

            /*企业编辑实际上是入驻的个人或者企业的编辑*/

            if(isset($data['member_id'])){ //如果入驻的单位是个体户
                //数据验证【待补充】
                
                //更新个人基础信息member
                $MemberModel = new MemberModel;
                $memberUpdateData = [
                    'member_id' => $data['member_id'],
                    'member_name' => $data['member_name'], //更新个人户姓名
                    'member_tel' => $data['member_tel'], //更新个体户电话
                    'member_remark' => $data['member_remark'], //更新个体户备注信息
                ];
                if (!$MemberModel->allowField(true)->update($memberUpdateData)) {
                    return $this->error('修改失败');
                }
                
                //更新入驻信息shack
                if(isset($data['file']) && $data['file']){
                    $shackUpdateData = [
                        'id' => $data['id'],
                        'imgs' => implode(',',$data['file']), //更新入驻合同附件
                    ];
                    $ShackModel = new ShackModel;
                    if (!$ShackModel->allowField(true)->update($shackUpdateData)) {
                        return $this->error('修改失败');
                    }
                }
                return $this->success('修改成功');
            }

            if(isset($data['firm_id'])){ //如果入驻的单位是企业
                //数据验证【待补充】
                
                //更新企业基础信息firm
                $FirmModel = new FirmModel;
                $firmUpdateData = [
                    'firm_id' => $data['firm_id'],
                    'firm_tel' => $data['firm_tel'], //更新联系人电话
                    'firm_manager' => $data['firm_manager'], //更新联系人姓名
                    'firm_industry_type' => $data['firm_industry_type'], //更新企业所属行业
                    'firm_remark' => $data['firm_remark'], //更新企业备注信息
                ];
                if (!$FirmModel->allowField(true)->update($firmUpdateData)) {
                    return $this->error('修改失败');
                }

                //更新入驻信息shack
                if(isset($data['file']) && $data['file']){
                    $shackUpdateData = [
                        'id' => $data['id'],
                        'imgs' => implode(',',$data['file']), //更新入驻合同附件
                    ];
                    $ShackModel = new ShackModel;
                    if (!$ShackModel->allowField(true)->update($shackUpdateData)) {
                        return $this->error('修改失败');
                    }
                }
                return $this->success('修改成功');
            }

           
        }

        $id = input('param.id/d');
        $row = ShackModel::with(['firm','member'])->find($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 企业详情
     * @return [type] [description]
     */
    public function detail()
    {
        $id = input('param.id/d');
        $row = ShackModel::with(['firm','member'])->find($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        if($row['firm_id']){
            $row['firm_imgs'] = AnnexModel::changeFormat($row['firm_imgs']);
        }
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 企业管理
     * @return [type] [description]
     */
    public function manage()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            // $result = $this->validate($data, 'Firm.add');
            // if($result !== true) {
            //     return $this->error($result);
            // }
            $FirmModel = new FirmModel;
            // 入库
            if (!$FirmModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = ShackModel::with(['firm','member'])->find($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $this->assign('data_info',$row);

        // 获取工位区
        $siteGroupModel = new SiteGroupModel;
        $sites = $siteGroupModel->where([['status','eq',1]])->field('site_group_id,site_group_type,site_group_name')->select();
        $siteArr = [];
        $siteArr[1] = [];
        $siteArr[2] = [];
        $siteArr[3] = [];
        foreach ($sites as $k => $v) {
            $siteArr[$v['site_group_type']][] = [
                'value' => $v['site_group_id'],
                'title' => $v['site_group_name'],
            ];
        }
        $this->assign('siteArr',$siteArr);

        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        $this->assign('guardArr', $guardArr);

        
        return $this->fetch();
    }

    /**
     * 抵用券发放
     * @return [type] [description]
     */
    public function coupon()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();

            /*企业编辑实际上是入驻的个人或者企业的编辑*/
            //$find = ShackModel::get($data['id']);
            if(isset($data['member_id'])){ //如果入驻的单位是个体户
                $MemberModel = new MemberModel;
                if (!$MemberModel->allowField(true)->update($data)) {
                    return $this->error('发券失败');
                }
                return $this->success('发券成功'); 
            }
            if(isset($data['firm_id'])){ //如果入驻的单位是企业
                $FirmModel = new FirmModel;
                if (!$FirmModel->allowField(true)->update($data)) {
                    return $this->error('发券失败');
                }
                return $this->success('发券成功');
            }
    
        }
        $id = input('param.firm_id/d');
        $row = ShackModel::with(['firm','member'])->find($id);
        //$row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 企业停用
     * @return [type] [description]
     */
    public function stop()
    {
        // $ids = $this->request->param('id/a');        
        // $res = FirmModel::where([['firm_id','in',$ids]])->update(['status'=>0]);
        // if($res){
        //     $this->success('停用成功');
        // }else{
        //     $this->error('删除失败');
        // }
        if ($this->request->isPost()) {
            $id = $this->request->param('id');
            $password = $this->request->param('password');
            $realPassword = UserModel::where([['id','eq',ADMIN_ID]])->value('password');
            //halt(ADMIN_ID);
            //dump($password);halt($realPassword);
            if(!password_verify(md5($password), $realPassword)){
                $this->error('密码效验失败');
            }
            $row = ShackModel::get($id);  
            if($row['member_id']){
                $res = MemberModel::where([['firm_id','eq',$row['member_id']]])->update(['status'=>0]);
            }
            if($row['firm_id']){
                $res = FirmModel::where([['firm_id','eq',$row['firm_id']]])->update(['status'=>0]);
                MemberModel::where([['firm_id','eq',$row['firm_id']]])->update(['status'=>0]);
            }
            
            if($res){
                $this->success('停用成功');
            }else{
                $this->error('停用失败');
            }
        }
        $id = $this->request->param('id'); 
        $this->assign('id',$id);
        return $this->fetch();
    }


    public function del()
    {
        $ids = $this->request->param('id/a');        
        $res = FirmModel::where([['firm_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}