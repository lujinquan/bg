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

use app\common\controller\Common;
use app\system\model\SystemUser as UserModel;
use think\captcha\Captcha;
use SendMessage\ServerCodeAPI;

/**
 * 后台公共控制器
 * @package app\system\admin
 */
class Publics extends Common
{
    /**
     * 登录页面
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index()
    {
        $model = new UserModel;
        $loginError = (int)session('admin_login_error');
        
        if ($this->request->isPost()) {
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $password   = $this->request->post('password/s');
            switch ($type) {
                case 1: //用户名密码登录
                    if (!$model->login($username, $password)) {
                        $loginError = ($loginError+1);
                        session('admin_login_error', $loginError);
                        $data['token'] = $this->request->token();
                        //$data['captcha'] = $loginError >= 3 ? captcha_src() : '';
                        return $this->error($model->getError(), url('index'), $data);
                    }
                    //halt(1);
                    session('admin_login_error', 0);          
                    return $this->success('登录成功，页面跳转中...', url('index/index'));
                    break;

                case 2: //手机短信登录
                    $data       = [];
                    $code   = $this->request->post('code/d');

                    $row = $model->where([['username','eq',$username],['status','eq',1],['role_id','in',[1,2]]])->find();
                    if(!$row){
                        return $this->error('用户名不存在或被禁用');
                    }

                    $auth = new ServerCodeAPI();
                    
                        $res = $auth->CheckSmsYzm($username, $code);
                        $res = json_decode($res);
                        // 验证短信码是否正确
                        if($res->code == '200'){ 
                            if (!$model->loginPhone($username)) {
                                $loginError = ($loginError+1);
                                session('admin_login_error', $loginError);
                                return $this->error($model->getError(), url('index'), $data);
                            }
                            session('admin_login_error', 0);
                            $password = $model->where([['username','eq',$username]])->value('password');
                            if(!$password){
                                return $this->success('登录成功，页面跳转中...', url('index/setPassword'));
                            }
                            return $this->success('登录成功，页面跳转中...', url('index/index'));
                        } else if($res->code == '413'){
                            return $this->error('验证失败');
                        } else {
                            return $this->error('请重新获取');
                        }

                    break;

                case 3: //验证
                    $row = $model->where([['username','eq',$username],['status','eq',1]])->find();
                    if(!$row){
                        return $this->error('用户名不存在或被禁用');
                    }
                    //验证通过即发送短信
                    $auth = new ServerCodeAPI();
                    if(!isset($data['code'])){
                        $res = $auth->SendSmsCode($username);
                        $res = json_decode($res);
                        if($res->code == '416'){
                            $this->error('验证次数过多，请更换登录方式');
                        }
                    }
                    return $this->success('用户名验证成功');
                    break;
                default:
                    # code...
                    break;
            }

            
        }

        if ($model->isLogin()) {
            $this->redirect(url('index/index', '', true, true));
        }

        $this->view->engine->layout(false);
        
        $this->assign('loginError', $loginError);

        return $this->fetch();
    }

    public function firstLogin()
    {
        return $this->fetch();
    }

    public function forget()
    {
        return $this->fetch();
    }

    /**
     * 登录页面
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index_old()
    {
        $model = new UserModel;
        $loginError = (int)session('admin_login_error');
        
        if ($this->request->isPost()) {
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $password   = $this->request->post('password/s');

            //$captchaObj = new Captcha();
            // $username   = $this->request->post('username/s');
            // $password   = $this->request->post('password/s');
            //$captcha    = $this->request->post('captcha/s');
            $data       = [];

            /* if ($loginError >= 3) {

                if (empty($captcha)) {
                    return $this->error('请输入验证码');
                }

                if (!captcha_check($captcha)) {
                    return $this->error('验证码错误');
                }
            } */
            
            if (!$model->login($username, $password)) {

                $loginError = ($loginError+1);
                session('admin_login_error', $loginError);

                $data['token'] = $this->request->token();
                //$data['captcha'] = $loginError >= 3 ? captcha_src() : '';

                return $this->error($model->getError(), url('index'), $data);

            }

            session('admin_login_error', 0);
            
            return $this->success('登录成功，页面跳转中...', url('index/index'));

        }

        if ($model->isLogin()) {
            $this->redirect(url('index/index', '', true, true));
        }

        $this->view->engine->layout(false);
        
        $this->assign('loginError', $loginError);

        return $this->fetch();
    }

    /**
     * 退出登录
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function logout(){
        model('SystemUser')->logout();
        $this->redirect(ROOT_DIR);
    }


    /**
     * 图标选择
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function icon() {
        return $this->fetch();
    }

    /**
     * 解锁屏幕
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function unlocked()
    {
        $_pwd = $this->request->post('password/s');
        $model = model('SystemUser');
        $login = $model->isLogin();
        
        if (!$login) {
            return $this->error('登录信息失效，请重新登录！');
        }

        $password = $model->where('id', $login['uid'])->value('password');
        if (!$password) {
            return $this->error('登录异常，请重新登录！');
        }

        if (!password_verify($_pwd, $password)) {
            return $this->error('密码错误，请重新输入！');
        }

        return $this->success('解锁成功');
    }

}
