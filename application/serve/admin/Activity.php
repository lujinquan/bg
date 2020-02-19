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
use app\serve\model\Activity as ActivityModel;

class Activity extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        // $banArr = BanModel::where([['status','eq',1],['project_id','eq',PROJECT_ID]])->field('ban_id,ban_name')->select();
        // $this->assign('banArr',$banArr);
    }

    public function index()
    { 
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $ActivityModel = new ActivityModel;
            $where = $ActivityModel->checkWhere($getData);
            $data = [];
            $data['data'] = $ActivityModel->where($where)->page($page)->order('activity_id desc')->limit($limit)->select()->toArray();
            //halt($data['data']);
            $data['count'] = $ActivityModel->where($where)->count();
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
            $result = $this->validate($data, 'Activity.add');
            if($result !== true) {
                return $this->error($result);
            }
            $ActivityModel = new ActivityModel;
            $data['activity_cuid'] = ADMIN_ID;
            $data['project_id'] = PROJECT_ID;
            //$data['ctime'] = time();
            $data['activity_content'] = htmlspecialchars($data['activity_content']);
            if(isset($data['file'])){ //附件
                $data['activity_imgs'] = $data['file'];
                // $AnnexModel = new AnnexModel;
                // $AnnexModel->updateAnnexEtime($data['file']);
            }
            // 入库
            if (!$ActivityModel->allowField(true)->create($data)) {
                return $this->error('发布失败');
            }
            return $this->success('发布成功');
        } 
        return $this->fetch();
    }

    /**
     * 修改活动
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function edit()
    {
        $ActivityModel = new ActivityModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Activity.edit');
            if($result !== true) {
                return $this->error($result);
            }
            // if(!isset($data['is_show'])){
            //     $data['is_show'] = 0;
            // }
            if(isset($data['file'])){ //附件
                $data['activity_imgs'] = $data['file'];
                // $AnnexModel = new AnnexModel;
                // $AnnexModel->updateAnnexEtime($data['file']);
            }
            // 入库
            if (!$ActivityModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = $ActivityModel->find($id);
        $row['activity_imgs'] = AnnexModel::changeFormat($row['activity_imgs']);
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

   

    /**
     * 活动详情
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function detail()
    {
        $ActivityModel = new ActivityModel;
        $id = input('param.id/d');
        $row = $ActivityModel->find($id);
        $row['activity_imgs'] = AnnexModel::changeFormat($row['activity_imgs']);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 删除活动
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function del()
    {
        $ids = $this->request->param('id/a'); 
        $ActivityModel = new ActivityModel;       
        $res = $ActivityModel->where([['activity_id','in',$ids]])->update(['activity_dtime'=>time()]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


}