<?php
/**
 * @name UserModel
 * @desc user 数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class UserModel
{
    public $errno;
    public $errmsg;
    private $pwdSalt = 'yaf';
    private $db;//数据库连接句柄

    public function __construct()
    {
        //初始化数据库连接
        //采用PDO方式
        $dsn = "mysql:host=127.0.0.1;dbname=yafapi;";
        $dbUser = 'root';
        $dbPwd = 'vagrant';
        $this->db = new PDO($dsn, $dbUser, $dbPwd);
    }
    
    public function selectSample()
    {
        return 'Hello World!';
    }

    public function insertSample($arrInfo)
    {
        return true;
    }

    /**
     * 用户注册操作
     * @param $uname
     * @param $pwd
     * @return bool
     */
    public function register($uname, $pwd)
    {
        //首先根据要注册用户名查找是否已存在同名用户
        $query = $this->db->prepare("select count(id) as users from `user` where uname =':uname'");
        $query->execute([':uname' => $uname]);
        $unameExists = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($unameExists[0]['users'] > 0) {
            $this->errno = '1005';
            $this->errmsg = '用户名已存在';
            return false;
        }
        $password = '';
        if (strlen($pwd) < 6) {
            $this->errno = '1006';
            $this->errmsg = '密码至少6位';
            return false;
        }
        $password = $this->generatePassword($pwd);
        $userIns = $this->db->prepare("INSERT INTO `user`(`uname`, `pwd`, `reg_time`) VALUES(:uname, :pwd, :reg_time)");
        $insCount = $userIns->execute([
            ':uname' => $uname,
            ':pwd' => $password,
            ':reg_time' => date("Y-m-d H:i:s", time()),
        ]);
        if (!$insCount) {
            $this->errno = '1007';
            $this->errmsg = '注册失败';
            return false;
        }
    }

    /**
     * 生成加密密码
     * @param $pwd
     * @return string
     */
    private function generatePassword($pwd)
    {
        $password = md5($this->pwdSalt .'-'. $pwd);
        return $password;
    }

    /**
     * 用户登录验证
     * @param $uname
     * @param $pwd
     */
    public function login($uname, $pwd):int
    {
        //根据用户名查找用户信息
        $queryUser = $this->db->prepare("SELECT `id`,`pwd` from `user` where uname = :uname");
        $queryUser->execute([':uname' => trim($uname)]);
        $res = $queryUser->fetchAll();
        if (!$res || count($res) < 1) {
            $this->errno = '1016';
            $this->errmsg = '用户不存在';
            return false;
        }
        $userInfo = $res[0];
        if ($userInfo['pwd'] !== $this->generatePassword(trim($pwd))) {
            $this->errno = '1017';
            $this->errmsg = '密码错误';
            return false;
        }
        return intval($userInfo['id']);
    }

}
