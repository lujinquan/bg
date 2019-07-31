<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 https://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------

namespace app\system\admin;

use Env;
use hisi\Dir;
use app\common\model\Cparam;
use app\common\model\Project;
use app\system\model\SystemUser;

/**
 * 后台默认首页控制器
 * @package app\system\admin
 */

class Index extends Admin
{
    /**
     * 首页
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index()
    {
        //cookie('hisi_iframe') 等于1 表示当前为iframe模式；等于0 表示当前为单页模式。
        if (cookie('hisi_iframe') && session('curr_project_id')) {
            $this->view->engine->layout(false);
            return $this->fetch('iframe');
        } else {
            
            return $this->fetch('lead');
        }     
    }

    public function lead()
    {
        return $this->fetch();
    }

    public function setPassword()
    {
        //检查是否可以进入到当前页面
        $systemUserModel = new SystemUser;
        $password = $systemUserModel->where([['id','eq',ADMIN_ID]])->value('password');
        if($password){
            return $this->error('页面不存在！');
        }

        if ($this->request->isAjax()) {
            $data = $this->request->post();

            $data['password'] = md5($data['password']);
            $data['password_confirm'] = md5($data['password_confirm']);
  
            // 验证
            $result = $this->validate($data, 'SystemUserManage.setPassword');
            if($result !== true) {
                return $this->error($result);
            }
            
            unset($data['id'], $data['password_confirm']);

            // 入库
            
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $row = $systemUserModel->where([['id','eq',ADMIN_ID]])->update($data);
            //halt($res);
            if (!$row) {
                return $this->error('设置失败');
            }
            return $this->success('设置成功','index/index',['id'=>$row['id']]);
        }
        return $this->fetch('set_password');
    }

    /**
     * 首页
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function projectLogin()
    {

        if(!session('curr_project_id')){
            if ($this->request->isAjax()) {
                $project_id = input('project_id/d');
                $projectModel = new Project;
                $projectRow = $projectModel->get($project_id);
                if($projectRow){
                    $curr_project_row = [];
                    $curr_project_row['id'] = $projectRow->id;
                    $curr_project_row['project_name'] = $projectRow->project_name;
                    $curr_project_row['project_address'] = $projectRow->project_address;
                    //halt($curr_project_row);
                    session('curr_project_id',$project_id);
                    session('curr_project_row',$curr_project_row);
                    // $result = [];
                    // $result['msg'] = '项目选择成功';
                    // $result['code'] = 1;
                    // $result['flag'] = $project_id;
                    // $result['url'] = 
                    // return jsons();
                    $this->success('项目选择成功');
                }else{
                    $this->error('项目选择失败');
                }
            }
        }
        
    }

    /**
     * 首页
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function projectAdd()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'SystemProject.sceneForm');
            if($result !== true) {
                return $this->error($result);
            }
            // 入库
            $projectModel = new Project;
            $row = $projectModel->allowField(true)->create($data);
            //halt($res);
            if (!$row) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功','',['id'=>$row['id']]);
        }
        
    }

    /**
     * 欢迎首页
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function welcome()
    {
        return $this->fetch('index');
    }

    /**
     * 清理缓存
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function clear()
    {
        $path   = Env::get('runtime_path');
        $cache  = $this->request->param('cache/d', 0);
        $log    = $this->request->param('log/d', 0);
        $temp   = $this->request->param('temp/d', 0);

        if ($cache == 1) {
            Dir::delDir($path.'cache');
        }

        if ($temp == 1) {
            Dir::delDir($path.'temp');
        }

        if ($log == 1) {
            Dir::delDir($path.'log');
        }

        return $this->success('任务执行成功');
    }
}
