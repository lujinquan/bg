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
use app\serve\model\Notice as NoticeModel;

class Notice extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 公告列表
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $SystemNotice = new NoticeModel;
            $where = $SystemNotice->checkWhere($getData);
            $data = [];
            $data['data'] = $SystemNotice->where($where)->page($page)->order('sort asc')->limit($limit)->select()->toArray();
            //halt($data['data']);
            $data['count'] = $SystemNotice->where($where)->count();
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);

        }
        return $this->fetch();
    }

    /**
     * 新增公告
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Notice');
            if($result !== true) {
                return $this->error($result);
            }
            $systemNotice = new NoticeModel;
            $data['cuid'] = ADMIN_ID;
            $data['project_id'] = PROJECT_ID;
            //$data['ctime'] = time();
            $data['content'] = htmlspecialchars($data['content']);
            // 入库
            if (!$systemNotice->allowField(true)->create($data)) {
                return $this->error('发布失败');
            }
            return $this->success('发布成功');
        }
        return $this->fetch();
    }

    /**
     * 修改公告
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function edit()
    {
        $systemNotice = new NoticeModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Notice');
            if($result !== true) {
                return $this->error($result);
            }
            if(!isset($data['is_show'])){
                $data['is_show'] = 0;
            }
            // 入库
            if (!$systemNotice->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = $systemNotice->find($id);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 公告状态
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function is_show()
    {
        $systemNotice = new NoticeModel;
        $id = input('param.id/d');
        $is_show = input('param.is_show/s');
        // $row = $systemNotice->get($id);
        // if($row){
        $systemNotice->where([['id','eq',$id]])->update(['etime'=>time(),'is_show'=>$is_show]);
        //}
        //halt($row);
        //$systemusers = session('systemusers');
        //halt($systemusers[1]);
        // 更新已读记录 
        //$systemNotice->updateReads($id);
        return $this->success('设置成功');
    }

    /**
     * 公告详情
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function detail()
    {
        $systemNotice = new NoticeModel;
        $id = input('param.id/d');
        $row = $systemNotice->find($id);
        //halt($row);
        //$systemusers = session('systemusers');
        //halt($systemusers[1]);
        // 更新已读记录 
        $systemNotice->updateReads($id);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 删除公告
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function del()
    {
        $ids = $this->request->param('id/a'); 
        $systemNotice = new NoticeModel;       
        $res = $systemNotice->where([['id','in',$ids]])->update(['dtime'=>time()]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}