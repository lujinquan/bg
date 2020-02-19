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
use app\space\model\Floor as FloorModel;
use app\system\model\SystemUser as UserModel;
use app\common\model\SystemAnnex as AnnexModel;
use app\space\model\SiteGroup as SiteGroupModel;

class Floor extends Admin
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
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            unset($data['floor_id']);
            // 入库
            if (!$FloorModel->allowField(true)->create($data)) {
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
            $result = $this->validate($data, 'Floor.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel->updateAnnexEtime($data['file']);
            }else{
                $data['imgs'] = '';
            }
            
            $FloorModel = new FloorModel();
            $count = $FloorModel->where([
                ['floor_id','neq',$data['floor_id']],
                ['ban_id','eq',$data['ban_id']],
                ['floor_number','eq',$data['floor_number']]
            ])->count('floor_id');
            //halt($count);
            if($count > 0){
                return $this->error('当前楼层已被录入');
            }
            // 入库
            if (!$FloorModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = FloorModel::with('ban')->find($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {

        if ($this->request->isPost()) {
            $id = $this->request->param('id');
            $password = $this->request->param('password');
            $realPassword = UserModel::where([['id','eq',ADMIN_ID]])->value('password');
            if(!password_verify(md5($password), $realPassword)){
                $this->error('密码输入错误，请重新输入！');
            }
            $floorNumber = FloorModel::where([['floor_id','eq',$id]])->value('floor_number');   
            $sites = SiteGroupModel::where([['floor_number','like','|%'.$floorNumber.'%|']])->find(); 
            if($sites){
                $this->error('请先删除该楼宇下所有工位区');  
            }
            $res = FloorModel::where([['floor_id','eq',$id]])->update(['status'=>0]);
            if($res){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }
        $floor_id = $this->request->param('id'); 
        $this->assign('id',$floor_id);
        return $this->fetch('system@block/del_confirm');
    }

}