<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * fun : 通用控制器类
 */

require_once APPPATH . '/libraries/comm/smsapi.php';
require_once APPPATH . '/libraries/comm/captcha.php';
require_once APPPATH . '/libraries/comm/validator.php';

class MY_Controller extends CI_Controller {

    /**
     * 构造函数
     *
     * @access public
     */
    public function __construct(){
        parent::__construct();
        // $this->unable();
        $this->base_web_url = (ENVIRONMENT === "development") ? "http://trade.test.com/" : "http://trade.coincoming.com/";
        $this->load->service('user/user_service');
        $this->oSession = $this->get_session();
        $this->oValidator = new Validator();
        $this->conf_errcode = array(
            "PARAM_ERR"                     => 0x20000000,      // 配置服务,参数错误
            "IMG_CODE_PARAM_ERR"            => 0x20000001,      // 图片验证码参数不合法
            "IMG_CODE_CHECK_ERR"            => 0x20000002,      // 图片验证码校验失败
            "GET_IMG_CODE_FROM_REDIS_ERR"   => 0x20000003,      // 图片验证码失效
            "IDAKER_TYPE_ID_ERR"            => 0x20000004,      // idmaker中type id 错误
            "HOST_ERR"                      => 0x20000005,      // 非法host
            "HAVE_NO_MK"                    => 0x20000006,      // 请求非法,不含mk
            "FILE_TYPE_ERR"                 => 0x20000007,      // 文件类型非法
            "FILE_SIZE_ERR"                 => 0x20000008,      // 文件大小非法
            "FILE_UPLOAD_ERR"               => 0x20000009,      // 文件上传失败
            "ACT_LOTTERY_ERR"               => 0x2000000a,      // 抽奖失败
            "ACT_HAVE_NO_POWER_TO_LOTTERY"  => 0x2000000b,      // 用户抽奖机会已经用完
            "CHECK_EMAIL_CODE_ERR"          => 0x2000000c,      // 邮箱验证码校验失败
            "GET_EMAIL_CODE_ERR"            => 0x2000000d,      // 从缓存中获取邮箱验证码失败
            "SEMD_MAIL_ERR"                 => 0x2000000e,      // 邮件发送失败
        );
        $this->conf_redis = array(
            'IMG_VERIFY_CODE'     => "img_code_",   // mid_session_key_  mid为XXX的session key
            'EMAIL_CODE'          => "email_code_", // email_code_uin      用户信息
        );
    }

    /**
     * 直出页面初始化
     *
     * @access protected
     */
    protected function init_page(){
        $this->create_mk();
    }

    /**
     * api接口初始化
     *
     * @access protected
     */
    protected function init_api(){
        if(ENVIRONMENT !== "development"){
            // 不在测试环境下,直接报错404
            $ip = getRealIpAddr();
            $ip = isset($ip) ? $ip : 0;
            cilog('debug',"ip:{$ip}");
            if(!$this->check_host()){
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "referer为空";
                cilog('error',$ip.' 访问api,非法的refer：'.$referer);
                // render_json($this->conf_errcode['HOST_ERR']);
                $this->go_err();
            };

            if(!$this->oSession->mk){
                cilog('error','请求环境错误,该api中没有mk '.$ip);
                // render_json($this->conf_errcode['HAVE_NO_MK']);
                $this->go_err();
            }
        }else{
            cilog('debug',"当前处于测试环境下!接口可异步请求! [fun:init_api]");
        }
    }

    /**
     * 初始化log
     *
     * @access protected
     */
    protected function init_log()
    {
        $con = $this->router->fetch_class();
        $func = $this->router->fetch_method();
        cilog('debug',"\n ======== [fun:{$func}] [class:{$con}] ========");
    }

    /**
     * 登陆校验
     *
     * @access protected
     *
     */
    protected function check_login(){
        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            render_json($flag);
        }
    }

    protected function check_admin_log(){
        if(ENVIRONMENT !== "development"){
            $flag = $this->user_service->check_online_($this->oSession);
            if ($flag != 0){
                // 页面未登录,停止后面的操作
                render_json($flag);
            }
        }else{
            cilog('debug',"当前处于测试环境下!无需校验后台登陆态! [fun:check_admin_log]");
        }
    }


    // 错误页面
    public function go_err()
    {
        show_404();
    }

    // 跳转首页
    public function go_loginout()
    {
        header("Location: {$this->base_web_url}user/pLogin");
        exit();
    }


    /**
     * @fun   初始化定时任务脚本
     */
    protected function init_cron($key,$log_filename,$fun_title)
    {
        $cron_key = $this->config->item('cron_key');
        $key = isset($key) ? $key : "";
        cilog('error',"\n ==== {$fun_title} ====",$log_filename);
        if($key !== $cron_key){
            cilog('error',"定时任务启动密钥错误 [key:{$key}]",$log_filename);
            exit();
        }
    }

    /**
     * 创建/续期 mk
     *
     * @access protected
     */
    protected function create_mk()
    {
        $mk = $this->oSession->mk;
        if ($mk===''){
            $mk = create_guid("mechine_key");
        }
        $d = time2timestamp()."akslk76761";
        set_cookie('mk',$mk,3600);
        set_cookie('mechine',"BC14B6749862A3E2DB5E6CBFE287557A",3600);
        set_cookie('key',time2timestamp(),3600);
        set_cookie('sukey',$d,3600);
        set_cookie('id',10986,3600);
        $this->oSession->mk = $mk;
    }

    protected function check_env()
    {
        $mk = $this->oSession->mk;
        if ($mk===''){
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 从客户端获取 login session
     *
     * @return stdClass
     * @access protected
     */
    protected function get_session()
    {
        $oSession = new stdClass();
        $uin = get_cookie('uin');
        $skey = get_cookie('skey');
        $mk = get_cookie('mk');
        $oSession->uin = strFilter($uin);
        $oSession->skey = strFilter($skey);
        $oSession->mk = strFilter($mk);
        return $oSession;
    }
    /**
     * 从客户端获取admin_login session
     *
     */
    protected  function get_session_(){
        $oSession = new stdClass();
        $uin = get_cookie('uin_');
        $skey = get_cookie('skey_');
        $mk = get_cookie('mk');
        $oSession->uin = strFilter($uin);
        $oSession->skey = strFilter($skey);
        $oSession->mk = strFilter($mk);
        return $oSession;
    }

    /**
     * 生成图片验证码
     *
     * @access public
     */
    public function get_verify_img()
    {
        $this->init_log();
        $this->create_mk();
        $oCaptcha = new Captcha();
        $mk = $this->oSession->mk;
        $key = $this->conf_redis['IMG_VERIFY_CODE'].$mk;
        $oCaptcha->doimg();
        $value = $oCaptcha->getCode();
        $this->cache->redis->save($key,$value,60*10);
        cilog('error',"img_code写入成功 mk:{$mk} img_code:{$value}");
    }

    /**
     * 验证图片验证码
     *
     * @param $img_code
     * @return int
     * @access public
     */
    protected function check_img_code($img_code)
    {
        if(!$this->oValidator->isImgCode($img_code)){
            cilog('error',"图片验证码,参数错误! img_code:{$img_code}");
            return $this->conf_errcode['IMG_CODE_PARAM_ERR'];
        }

        $mk = $this->oSession->mk;
        $key = $this->conf_redis['IMG_VERIFY_CODE'].$mk;
        $code_from_redis = $this->cache->redis->get($key);

        if (!$code_from_redis){
            cilog('error',"从缓存中获取图片验证失败! img_code:{$img_code}");
            return $this->conf_errcode['GET_IMG_CODE_FROM_REDIS_ERR'];
        }

        if ($code_from_redis !== $img_code){
            cilog('error',"图片验证码,校验失败! img_code:{$img_code}");
            return $this->conf_errcode['IMG_CODE_CHECK_ERR'];
        }

        $this->cache->redis->delete($key);
        cilog('debug',"图片验证码校验成功!");
        return 0;
    }

    /**
     * 校验邮箱验证码
     */
    protected function check_email_code($email_code)
    {
        if(!$this->oValidator->isImgCode($email_code)){
            cilog('error',"邮箱验证码,参数错误! email_code:{$email_code}");
            return $this->conf_errcode['CHECK_EMAIL_CODE_ERR'];
        }

        $oSession = $this->get_session();
        $key = $this->conf_redis['EMAIL_CODE'].$oSession->uin;
        $code_from_redis = $this->cache->redis->get($key);

        if (!$code_from_redis){
            cilog('error',"从缓存中获取邮箱验证码失败! email_code:{$email_code}");
            return $this->conf_errcode['GET_EMAIL_CODE_ERR'];
        }

        if ($code_from_redis !== $email_code){
            cilog('error',"邮箱验证码,校验失败! email_code:{$email_code}");
            return $this->conf_errcode['CHECK_EMAIL_CODE_ERR'];
        }

        $this->cache->redis->delete($key);
        cilog('debug',"邮箱验证码校验成功!");
        return 0;
    }

    /**
     * 检查来源域名HTTP_REFERER，判断是否可以设置允许第三方跨域ajax请求头部
     *
     * @access public
     */
    public function check_host()
    {
        $whiteHosts = $this->config->item('whitehost');
        if (isset($_SERVER['HTTP_REFERER'])) {
            try {
                $refer = $_SERVER['HTTP_REFERER'];
                for ($i = 0; $i < count($whiteHosts); $i++) {
                    $whiteHosts[$i] = urldecode($whiteHosts[$i]);
                    if ($refer && (strpos($refer, $whiteHosts[$i]) !== false)) {
                        header("P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR");
                        header("Access-Control-Allow-Origin: " . $whiteHosts[$i]);
                        header("Access-Control-Allow-Credentials: true");
                        return true;
                    }
                }
            } catch (Exception $e) {

            }
        }

        // 检查备份域名的有效性
        return false;
    }

    /**
     * GET 请求
     *
     * @access public
     * @param string $url 请求地址
     * @return string 请求结果
     * @access public
     */
    public function http_get($url)
    {
        $oCurl = curl_init();
        if (stripos($url, "http://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);

        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * POST 请求
     *
     * @param string $url 请求地址
     * @param array $param post参数
     * @return string 请求结果
     * @access public
     */
    public function http_post($url, $param)
    {
        $oCurl = curl_init();
        if (stripos($url, "http://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * 访问ip 黑名单
     */
    public function unable()
    {
        $ip = getRealIpAddr();
        $ip = isset($ip) ? $ip : FALSE;
        $ip_black = array();

        if(in_array($ip,$ip_black)){
            cilog('error',"ip为黑名单用户,禁止访问! [ip:{$ip}]");
            $this->go_err();
            exit();
        }
    }

    /**
     * 获取db连接
     */
    protected function get_db_conn($database=NULL)
    {
        $database = isset($database) ? $database : 'trade_user';
        return $this->load->database($database,TRUE);
    }
}