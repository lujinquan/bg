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
       //halt(session('curr_project_row.project_name'));
         //halt($row[]);
        //halt(session('admin_user_lead'));
        if(session('curr_project_id')){
            if (cookie('hisi_iframe')) {
                $this->view->engine->layout(false);
                return $this->fetch('iframe');
            } else {

                return $this->fetch();
            }
        }else{         
            $projectModel = new Project;
            $projectArr = $projectModel->where([['status','eq',1]])->field('id,project_name,project_address')->select();
            if(ADMIN_ROLE == 1){ //如果是超管

            }
            //halt($projectArr);
            $this->assign('projectArr',$projectArr);
           return $this->fetch('lead'); 
        }
        
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
            if (!$projectModel->allowField(true)->create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
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
