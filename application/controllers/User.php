<?php
/**
 * @name UserController
 * @author root
 * @desc 用户控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class UserController extends Yaf_Controller_Abstract
{
	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/yafapi/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction($name = "Luochen")
    {
        /*//1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");

        //2. fetch model
        $model = new SampleModel();

        //3. assign
        $this->getView()->assign("content", $model->selectSample());
        $this->getView()->assign("name", $name);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return true;*/
        return $this->loginAction();
    }

    /**
     * 用户登录操作
     */
    public function loginAction()
    {
        //判断是否包含登录标识
        $submit = $this->getRequest()->getQuery('submit', '0');
        if ($submit != '1') {
            echo json_encode([
                'errno' => 1012,
                'errmsg' => '请通过正确渠道登录',
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }
        //获取登录的用户名和密码
        $uname = $this->getRequest()->getPost('uname', false);
        $pwd = $this->getRequest()->getPost('pwd', false);
        //检测参数是否为空
        if (!$uname || !$pwd) {
            echo json_encode([
                'errno' => 1002,
                'errmsg' => '用户名和密码都是必须的',
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }
        //调起登录方法
        $model = new UserModel();
        $uid = $model->login($uname, $pwd);
        if ($uid != false) {
            //登录成功
            //设置 session
            session_start();
            $_SESSION['user_token'] = md5('salt' . $_SERVER['REQUEST_TIME'] .$uid);
            $_SESSION['user_token_time'] = $_SERVER['REQUEST_TIME'];
            $_SESSION['uid'] = $uid;

            echo json_encode([
                'errno' => 0,
                'errmsg' => '登录成功',
                'data' => [
                    'uname' => $uname,
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg,
            ], JSON_UNESCAPED_UNICODE);
        }
        return false;
    }

    /**
     * 用户注册操作
     */
    public function registerAction()
    {
        //获取要注册的用户名和密码
        $uname = $this->getRequest()->getPost('uname', false);
        $pwd = $this->getRequest()->getPost('pwd', false);
        //检测参数是否为空
        if (!$uname || !$pwd) {
            echo json_encode([
                'errno' => 1002,
                'errmsg' => '用户名和密码都是必须的',
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }
        $model = new UserModel();
        //调用用户注册方法
        if ($model->register(trim($uname), trim($pwd))) {
            echo json_encode([
                'errno' => 0,
                'errmsg' => '',
                'data' => [
                    'uname' => $uname,
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg,
            ], JSON_UNESCAPED_UNICODE);
        }
        return true;
    }

}
