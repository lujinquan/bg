<?php

// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>
// +----------------------------------------------------------------------
// | Motto: There is only one kind of failure in the world is to give up .
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
            $data['activity_content'] = htmlspecialchars($data['activity_content']);
            if(isset($data['file'])){ //附件
                $data['activity_imgs'] = $data['file'];
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
    * 2020-02-19 19:55:15
    * ============================================================================
    * xxxx管理系统V1.0 
    * 版权所有 2006-2017 xxx有限公司，并保留所有权利
    * 官方网址: http://www.xxx.com
    * ============================================================================
    * @author: Lucas 
    * @createTime: $1
    * $Last update: 2017-3-14
   */
  
   /**
    * $2
    * =====================================
    * @author  Lucas 
    * email:   598936602@qq.com 
    * Website  address:  www.mylucas.com.cn
    * =====================================
    * 创建时间: $1
    * @return  返回值  $3
    * @version 版本  1.0
    */

   /**
    * 
    * =====================================
    * @author  Lucas 
    * email:   598936602@qq.com 
    * Website  address:  www.mylucas.com.cn
    * =====================================
    * 创建时间: 2020-02-19 20:25:13
    * @return  返回值  
    * @version 版本  1.0
    */
   
   /**
    * 
    * =====================================
    * @author  Lucas 
    * email:   598936602@qq.com 
    * Website  address:  www.mylucas.com.cn
    * =====================================
    * 创建时间: 2020-02-19 20:29:59 <-- 这里输入 ctrl + shift + . 自动生成当前时间戳
    * @return  返回值  
    * @version 版本  1.0
    */
   

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
     * 删除活动【假删】
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