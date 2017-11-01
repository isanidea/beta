<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class user  用户模块
 */

// require_once APPPATH . '/libraries/comm/captcha.php';
class User extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user',TRUE);
    }

    // 登陆页面
    public function pLogin()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('user/login');
    }

    // 注册页面
    public function pRegister()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('user/register');
    }
    //第三方注册页面
    public function pRegisterPlatform(){
        $this->init_log();
        $this->init_page();
        $this->load->view('user/pRegisterPlatform');

    }
    //twitter登录请求地址
    public function twitter(){
        header('location:	https://api.twitter.com/oauth/authorize?');
        exit;
    }
    //teitter 回调地址处理
    public function twitter_callback(){

    }


    // 个人中心 - 资产中心
    public function pBalance()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_balance');
        }
    }

    // 个人中心-成交查询
    public function pOrder()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_orders');
        }
    }

    // 个人中心-比特币交易管理
    public function pAd()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/use_ad');
        }
    }

    // 个人中心-安全中心
    public function pSecurity()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_security');
        }
    }

    // 个人中心-实名认证
    public function pProfile()
    {
        $this->init_log();
        $this->init_page();
        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_profile');
        }
    }

    // 个人中心-充币
    public function pCoinIn()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_coin_in');
        }
    }

    // 个人中心-提币
    public function pCoinOut()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_coin_out');
        }
    }

    // 个人中心-ico
    public function pIco()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('user/user_ico');
        }
    }

    // 用户协议
    public function pTerms()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('terms');
    }

    // 忘记密码页面
    public function pForget()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('user/forget');
    }

    // 重置密码页面
    public function pReset()
    {
        $this->init_log();
        $this->init_page();

        $token = get_post_value('token');
        $email = get_post_value('email');

        if(!$this->oValidator->isEmail($email)){
            cilog('error',"邮箱格式不对 [email:{$email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($token)){
            cilog('error',"重置密钥格式不对,跳转错误页面 [token:{$token}]");
            // render_json($this->conf_errcode['PARAM_ERR']);
            $this->go_err();
        }

        $key = $this->user_service->user_redis_key['RESET_KEY_PW'].$email;
        $value = $this->cache->redis->get($key);
        if(!$value){
            cilog('error',"重置密钥格式不对,获取不到redis key,跳转错误页面 [token:{$token}]");
            // render_json($this->conf_errcode['PARAM_ERR']);
            $this->go_err();
        }
        $this->load->view('user/reset');
    }

    /**
     * 重置密码接口
     *
     * pw      新密码
     * token   密钥
     * email   邮箱账号
     * code    图片验证码
     */
    public function reset_pw()
    {
        $this->init_log();
        $this->init_api();

        $str_pw = get_post_value('pw');
        $token = get_post_value('token');
        $email = get_post_value('email');
        $img_code = get_post_value('code');

        if(!$this->oValidator->isImgCode($img_code)){
            cilog('error',"图片验证码格式不对 [email:{$img_code}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($str_pw)){
            cilog('error',"登陆密码格式不对 [pw:{$str_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isEmail($email)){
            cilog('error',"邮箱格式不对 [email:{$email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($token)){
            cilog('error',"重置密钥格式不对 [token:{$token}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 验证图片验证码
        $flag = $this->check_img_code($img_code);
        if ($flag != 0){
            render_json($flag);
        }

        $key = $this->user_service->user_redis_key['RESET_KEY_PW'].$email;
        $value = $this->cache->redis->get($key);
        $sum_value_from_redis = md5($key . $token);
        $this->cache->redis->delete($key);
        if((!$value) OR ($value !== $sum_value_from_redis)){
            cilog('error',"重置密钥格式不对,跳转报错错误码 [token:{$token}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $salt_key = create_guid('pw_salt_key');

        // 获取用户信息
        $arr_email_user = $this->user_service->get_uin_by_email($this->conn,$email);
        if (!is_array($arr_email_user)){
            render_json($arr_email_user,'');
        }

        $uin = $arr_email_user['f_uin'];

        $flag = $this->user_service->update_user_info(
            $this->conn,
            $uin,
            $arr_userinfo_update = array(
                'f_password' => $this->user_service->get_last_pw($salt_key,$str_pw),
                'f_key' => $salt_key,
            )
        );

        if($flag !== 0){
            render_json($flag);
        }else{
            render_json(0);
        }
    }

    /**
     * 发送重置密码的邮件
     *
     * email    用户邮件名
     * code     邮箱验证码
     */
    public function send_reset_msg()
    {
        $this->init_log();
        $this->init_api();

        $str_email = get_post_value('email');
        $email_code = get_post_value('code');

        if(!$this->oValidator->isEmail($str_email)){
            cilog('error',"邮箱格式不对 [email:{$str_email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isImgCode($email_code)){
            cilog('error',"图片验证码格式不对 [email:{$email_code}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 获取用户信息
        $arr_email_user = $this->user_service->get_uin_by_email($this->conn,$str_email);
        if (!is_array($arr_email_user)){
            render_json($arr_email_user,'');
        }

        // 校验用户状态
        $flag = $this->user_service->chenck_user_state($arr_email_user);
        if($flag !== 0) {
            render_json($flag);
        }

        // 验证图片验证码
        $flag = $this->check_img_code($email_code);
        if ($flag != 0){
            render_json($flag);
        }
        // $uin = $arr_email_user['f_uin'];

        // 生成重置密码的链接
        $key = $this->user_service->user_redis_key['RESET_KEY_PW'].$str_email;
        $reset_token = create_guid("reset_password");
        $reset_token_in_redis = md5($key . $reset_token);  // 校验重置页面的登陆状态

        $this->cache->redis->save($key,$reset_token_in_redis,30*60);
        cilog('debug',"写入重置密码密钥信息到redis成功! [key:{$key}] [value:{$reset_token_in_redis}] [token:{$reset_token}]");
        $url = $this->base_web_url."user/pReset?token=".$reset_token."&email=".$str_email;

        $message = "Click for reset your password!"."<a href='{$url}'> begin reset! </a> <br>The link is valid for 30 minutes!!";
        $subject = "Reset password email";
        $arr_to = array(
            $str_email,
        );
        $flag = send_mail_v($subject,$message,$arr_to);
        if ($flag){
            cilog('debug',"邮件发送成功! [sub:{$subject}] [message:{$message}] [to:{$str_email}]");
            render_json(0);
        }else{
            render_json($this->conf_errcode['SEMD_MAIL_ERR']);
        }
    }


    /**
     * @fun    用户登陆
     * @param  email      邮箱
     * @param  password   密码
     * @param  img_code   验证码
     */
    public function login_with_pw()
    {
        $this->init_log();
        $this->init_api();

        $str_email = get_post_value('email');
        $str_password = get_post_value('password');
        $str_img_code = get_post_value('img_code');

        cilog('debug',"param [email:{$str_email}] [pw:{$str_password}] [img_code:{$str_img_code}]");

        if(!$this->oValidator->isEmail($str_email)){
            cilog('error',"邮箱格式不对 [email:{$str_email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($str_password)){
            cilog('error',"密码格式不对 [email:{$str_password}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isImgCode($str_img_code)){
            cilog('error',"验证码格式不对 [email:{$str_img_code}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 验证图片验证码
        $flag = $this->check_img_code($str_img_code);
        if ($flag != 0){
            render_json($flag,"");
        }

        // 获取用户信息并校验密码
        $arr_email_user = $this->user_service->get_uin_by_email($this->conn,$str_email);
        if (!is_array($arr_email_user)){
            render_json($arr_email_user,'');
        }

        // 未激活用户不允许登陆
        if ($arr_email_user['f_state'] == $this->user_service->user_state['CAN_NOT_CHECK_EMAIL']){
            cilog('error',"用户未激活,不允许登陆! [uin:{$arr_email_user['f_uin']}] [state:{$arr_email_user['f_state']}]");
            render_json($this->user_service->user_errcode['USER_NOT_ACTIVE']);
        }

        $flag = $this->user_service->check_pw($arr_email_user['f_key'],$arr_email_user['f_password'],$str_password);
        if($flag != 0 ){
            render_json($flag,'');
        }

        $userinfo = $this->user_service->get_user_info($this->conn,$arr_email_user['f_uin']);
        $this->user_service->check_ip($this->conn,$userinfo);

        // 写login session
        $uin = $arr_email_user['f_uin'];
        $skey = create_guid('session_key');
        $this->user_service->set_session($uin,$skey);
        $this->oSession = $this->get_session();
        cilog('debug',"用户登陆成功 [email:{$str_email}]");
        render_json(0,'');
    }


    /**
     * @fun    注册一个用户
     * @param  email      邮箱
     * @param  password   密码
     * @param  img_code   验证码
     * @param  invite_email   邀请人邮箱
     */
    public function register()
    {
        $this->init_log();
        $this->init_api();

        $str_email = get_post_value('email');
        $str_password = get_post_value('password');
        $str_img_code = get_post_value('img_code');
        $invite_user_mail = get_post_value('invite_email');

        cilog('debug',"param [email:{$str_email}] [pw:{$str_password}] [img_code:{$str_img_code}]");

        // 验证图片验证码
        $flag = $this->check_img_code($str_img_code);
        if ($flag != 0){
            render_json($flag);
        }

        if(!$this->oValidator->isEmail($str_email)){
            cilog('error',"邮箱格式不对 [email:{$str_email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($str_password)){
            cilog('error',"密码格式不对 [email:{$str_password}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('user/user_service');
        $errcode = $this->user_service->user_errcode['USER_INVITER_NOT_EXIST'];

        if($invite_user_mail !== ''){
            if(!$this->oValidator->isEmail($invite_user_mail)){
                cilog('error',"邀请人邮箱格式不对 [email:{$invite_user_mail}]");
                render_json($errcode);
            }

            $rsp = $this->user_service->get_uin_by_email($this->conn,$invite_user_mail);
            if(!is_array($rsp)){
                cilog('error',"用户邀请人邮箱不合法!");
                render_json($errcode);
            }
        }

        $flag = $this->user_service->add_user($this->conn,$str_email,$str_password,$invite_user_mail);
        if ($flag !== 0) {
            render_json($flag);
        }

        render_json(0,"");
    }


    /**
     * 获取用户详情
     */
    public function get_user_info()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $this->load->service('user/user_service');
        $arr_userinfo = $this->user_service->get_user_info($this->conn,$uin);

        if(is_array($arr_userinfo)){
            $userinfo=$this->user_service->export_user($arr_userinfo);
            render_json(0,"",$userinfo);
        }else{
            render_json($arr_userinfo);
        }
    }

    /**
     * @fun     修改密码
     * @param   pre_pw  原密码
     * @param   type    1 登录密码 2 交易密码
     * @param   pw      新密码
     * @param   code    邮箱验证码
     */
    public function update_pw()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $pre_pw = get_post_value('pre_pw');  // 原密码
        $type = get_post_valueI('type');
        $password = get_post_value('pw');    // 新密码
        $email_code = get_post_value('code');

        if ($type === 1){
            $key = 'f_password';
        }elseif ($type === 2){
            $key = 'f_deal_pw';
        }else{
            cilog('error',"类型参数不对 [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($password)){
            cilog('error',"密码格式不对 [pw:{$password}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 校验邮箱验证码
        $flag = $this->check_email_code($email_code);
        if($flag !== 0){
            render_json($flag);
        }

        // 获取用户信息
        $arr_userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($arr_userinfo)){
            render_json($arr_userinfo);
        }

        // 校验用户状态
        $flag = $this->user_service->chenck_user_state($arr_userinfo);
        if($flag !== 0) {
            render_json($flag);
        }

        // 检验用户密码是否正确
        $flag = $this->user_service->check_pw($arr_userinfo['f_key'],$arr_userinfo[$key],$pre_pw);
        if($flag !== 0) {
            render_json($flag);
        }

        // 获取用户最终密码
        $last_pw = $this->user_service->get_last_pw($arr_userinfo['f_key'],$password);
        $arr_userinfo_update[$key] = $last_pw;
        $flag = $this->user_service->update_user_info($this->conn,$uin,$arr_userinfo_update);
        if($flag !== 0) {
            render_json($flag);
        }
        render_json(0);
    }

    /**
     * 忘记用户交易密码
     *
     * loginpw       用户登陆密码
     * dealpw        新的交易密码
     * code          邮箱验证码
     */
    public function forget_deal_pw()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $login_pw = get_post_value('loginpw');
        $deal_pw = get_post_value('dealpw');
        $email_code = get_post_value('code');

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        if(!$this->oValidator->isPw($login_pw)){
            cilog('error',"登陆密码格式不对 [pw:{$login_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($deal_pw)){
            cilog('error',"交易密码格式不对 [pw:{$deal_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 校验邮箱验证码
        $flag = $this->check_email_code($email_code);
        if($flag !== 0){
            render_json($flag);
        }

        // 获取用户信息
        $arr_userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($arr_userinfo)){
            render_json($arr_userinfo);
        }

        // 校验用户状态
        $flag = $this->user_service->chenck_user_state($arr_userinfo);
        if($flag !== 0) {
            render_json($flag);
        }

        // 校验登陆密码
        $flag = $this->user_service->check_pw($arr_userinfo['f_key'],$arr_userinfo['f_password'],$login_pw);
        if($flag !== 0) {
            render_json($flag);
        }

        // 修改交易密码
        $last_pw = $this->user_service->get_last_pw($arr_userinfo['f_key'],$deal_pw);
        $arr_userinfo_update['f_deal_pw'] = $last_pw;
        $flag = $this->user_service->update_user_info($this->conn,$uin,$arr_userinfo_update);
        if($flag !== 0) {
            render_json($flag);
        }
        render_json(0);
    }


    /**
     * @fun     修改用户信息
     */
    public function update_userinfo()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $state = get_post_valueI('state'); // 用户需要验证的状态

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        // 基本参数校验
        if(($state === 0) OR ($state >= $this->user_service->user_state['CAN_NOT_CHECK_EMAIL'])){
            cilog('error',"state参数错误 [state:{$state}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 获取用户信息
        $userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($userinfo)){
            render_json($userinfo);
        }

        // 校验用户状态
        $flag = $this->user_service->chenck_user_state($userinfo);
        if($flag !== 0) {
            render_json($flag);
        }

        if($userinfo['f_state'] < $state){
            cilog('error',"用户状态不合法 db_state{$userinfo['f_state']} state_client:{$state}");
            render_json($this->user_service->user_errcode['USER_ERR_STATE']);
        }

        if ($state === $this->user_service->user_state['CHECK_THREE'])
        {
            // 3级验证
            $truename = get_post_value('truename'); // 真实姓名
            $idcard = get_post_value('idcard');     // 身份证号码
            $arr_pic = get_post_value('pic');       // 数组

            if(!$this->oValidator->isTruename($truename)){
                cilog('error',"用户姓名格式不对 [truename:{$truename}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

//            if(!$this->oValidator->isIdcard($idcard)){
//                cilog('error',"身份证号码格式不对 [idcard:{$idcard}]");
//                render_json($this->conf_errcode['PARAM_ERR']);
//            }

            if(strlen($idcard) < 6){
                cilog('error',"身份证号码格式不对 [idcard:{$idcard}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            if(!is_array($arr_pic)){
                cilog('error',"身份证图片格式不对 [idcard:{$arr_pic}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            $arr_pic_data = array(
                'front' => $arr_pic['front'],
                'pic_with_hand' => $arr_pic['pic_with_hand'],
            );

            if(strlen($arr_pic['front']) != 32){
                cilog('error',"身份证图片格式不对 [front:{$arr_pic['front']}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            if(strlen($arr_pic['pic_with_hand']) != 32){
                cilog('error',"身份证图片格式不对 [pic_with_hand:{$arr_pic['pic_with_hand']}]");
                cilog('error',"身份证图片格式不对 [pic_with_hand:{$arr_pic['pic_with_hand']}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            $arr_userinfo_update = array(
                'f_truename' => $truename,
                'f_idcard' => $idcard,
                'f_pic_id' => serialize($arr_pic_data),
                'f_state' => ($userinfo['f_state'] > $state) ? $userinfo['f_state'] : $this->user_service->user_state['SUBMIT_IDCARD'],
            );
        }
        elseif ($state === $this->user_service->user_state['CHECK_TWO'])
        {
            // 2级验证
            $country = get_post_value('country');  // 国家
            $prov = get_post_value('prov');        // 省
            $city = get_post_value('city');        // 市
            $dist = get_post_value('dist');        // 区
            $address = get_post_value('address');  // 详细地址

            if(($country === '') OR ($prov === '') OR ($city === '') OR ($dist === '') OR ($address === '')){
                cilog('error',"数据格式不对 [country:{$country}] [prov:{$prov}] [city:{$city}] [dist:{$dist}] [address:{$address}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            $arr_userinfo_update = array(
                'f_country' => $country,
                'f_prov' => $prov,
                'f_city' => $city,
                'f_dist' => $dist,
                'f_addr_info' => $address,
                'f_state' => ($userinfo['f_state'] > $state) ? $userinfo['f_state'] : $this->user_service->user_state['CHECK_THREE'],
            );
        }
        elseif ($state === $this->user_service->user_state['CHECK_ONE'])
        {
            // 1级验证
            $deal_pw = get_post_value('pw');   // 交易密码
            $phone = get_post_value('phone');  // 用户手机号码
            $email_code = get_post_value('email_code');  // 邮件验证码

            if(!$this->oValidator->isPw($deal_pw)){
                cilog('error',"交易密码格式不对 [pw:{$deal_pw}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            // 验证邮件验证码
            $flag = $this->check_email_code($email_code);
            if($flag !== 0){
                render_json($flag);
            }

            // 验证手机号码
//            if(!$this->oValidator->isMobile($phone)){
//                cilog('error',"手机号码格式不对 [phone:{$phone}]");
//                render_json($this->conf_errcode['PARAM_ERR']);
//            }
            if(strlen($phone) < 1){
                cilog('error',"手机号码格式不对 [phone:{$phone}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }

            $arr_userinfo_update = array(
                'f_deal_pw' => $this->user_service->get_last_pw($userinfo['f_key'],$deal_pw),
                'f_phone' => $phone,
                'f_state' => ($userinfo['f_state'] > $state) ? $userinfo['f_state'] : $this->user_service->user_state['CHECK_TWO'],
            );
        }
        else
        {
            cilog('error',"state参数错误 [state:{$userinfo['f_state']}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $flag = $this->user_service->update_user_info($this->conn,$uin,$arr_userinfo_update);
        if($flag !== 0){
            render_json($flag);
        }else{
            render_json(0);
        }
    }

    /**
     * @fun     发送邮箱验证码
     */
    public function send_email_code()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $arr_userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($arr_userinfo)){
            render_json($arr_userinfo);
        }

        $code = rand(1000,9999);
        $object = 'Email verification code';
        $message = 'Your email verification code is:'.$code.' The verification code is valid for 30 minutes.';
        $arr_to = array($arr_userinfo['f_email']);
        $flag = send_mail_v($object,$message,$arr_to,$arr_cc=NULL,$arr_filename=NULL);
        if ($flag){
            cilog('error','邮件发送成功!');
            $key = $this->conf_redis['EMAIL_CODE'].$uin;
            $this->cache->redis->save($key,$code,60*30);
            cilog('error',"email_code写入成功 uin:{$uin} email_code:{$code}");
            render_json(0);
        }
        render_json($this->conf_errcode['SEMD_MAIL_ERR']);
    }

    /**
     * @fun     发送激活链接
     * @param   email   邮箱地址
     */
    public function send_active_email()
    {
        $this->init_log();
        $this->init_api();

        $str_email = get_post_value('email');

        if(!$this->oValidator->isEmail($str_email)){
            cilog('error',"邮箱格式不对 [email:{$str_email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 获取用户的uin
        $arr_email_user = $this->user_service->get_uin_by_email($this->conn,$str_email);
        if (!is_array($arr_email_user)){
            render_json($arr_email_user);
        }

        $token = create_guid('active_key');
        $key = $this->user_service->user_redis_key['REG_CODE'].$token;
        $value = $arr_email_user['f_uin'];
        $_timeout = $this->user_service->user_redis_key['TIMEOUT'];
        $this->cache->redis->save($key,$value,$_timeout);

        // redis 中记录用户激活密钥
        $reg_key = $this->user_service->user_redis_key['REG_KEY_CODE'].$arr_email_user['f_uin'];
        $reg_value = $this->cache->redis->get($reg_key);
        if($reg_value){
            // 找到用户曾经激活的密钥,删除改激活码
            $key_reg = $this->user_service->user_redis_key['REG_CODE'].$reg_value;
            $this->cache->redis->delete($key_reg);
        }

        $message = $this->user_service->get_active_url($str_email,$token);
        $arr_to = array($str_email);
        $flag = send_mail_v('Account activation email',$message,$arr_to,$arr_cc=NULL,$arr_filename=NULL);
        if ($flag){
            cilog('error','邮件发送成功!');
            render_json(0);
        }
        render_json($this->conf_errcode['SEMD_MAIL_ERR']);
    }

    /**
     * @fun      激活用户
     * @param    email   需要激活的邮箱地址
     * @param    token   激活密钥
     */
    public function active()
    {
        $this->init_log();
        $token = get_post_value('token');

        $key = $this->user_service->user_redis_key['REG_CODE'].$token;
        $value = $this->cache->redis->get($key);

        // 获取激活码
        if(!$value){
            cilog('error',"该激活码为空! [key:{$key}] [value:{$value}]");
            echo 'Activation failed, please try again later！';
            exit();
        }

        $uin = $value;
        // 通过激活码换取用户uin
        if($uin < 10000){
            cilog('error',"uin参数不合法! [uin:{$uin}]");
            echo 'Activation failed, please try again later！';
            exit();
        }

        // 获取用户信息
        $arr_userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($arr_userinfo)){
            cilog('error',"获取用户信息失败! [userinfo:{$arr_userinfo}]");
            echo 'Activation failed, please try again later！';
            exit();
        }
        $this->cache->redis->delete($key);

        // 判断用户状态
        if ((int)$arr_userinfo['f_state'] !== $this->user_service->user_state['CAN_NOT_CHECK_EMAIL']){
            cilog('error',"用户激活状态不符合! [state:{$arr_userinfo['f_state']}]");
            echo 'Activation failed, please try again later！';
            exit();
        }

        // 更新用户状态
        $this->conn->trans_start();
        $attributes = array(
            'f_state' => $this->user_service->user_state['CHECK_ONE'],
            'f_modify_time' => timestamp2time(),
        );
        $where = array('f_uin' => $uin);
        $this->load->model("user/Model_t_uin");
        $tablename = $this->Model_t_uin->get_tablename();
        $this->Model_t_uin->update_all(
            $this->conn,
            $tablename,
            $attributes,
            $where
        );

        $this->conn->trans_complete();
        if ($this->conn->trans_status() === FALSE)
        {
            // $conn->trans_rollback();
            cilog('error',"用户激活失败!");
            echo "Activation failed, please try again later！";
            exit();
        }
        else
        {
            // $conn->trans_commit();
            cilog('debug',"更新用户数据成功!");
            $key = $this->user_service->user_redis_key['USER_INFO_PRE'].$uin;
            $this->cache->redis->delete($key);
            $this->load->view('user/reg_scu');
        }
    }

    /**
     * @fun      注销登录
     */
    public function loginout()
    {
        $this->init_log();
        $this->init_api();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }

        $oSession = $this->get_session();
        $this->user_service->loginout($oSession);
    }

    /**
     * @fun      常驻 发邮件
     *
     * state     0 默认 1 失败 2 成功 3 待发送
     *
     * 定时任务,每分钟跑一次
     */
    public function send_email($key)
    {
        $log_filename = "cron_user_";
        $this->init_cron($key,$log_filename,"开始发送邮件");

        $this->load->model("conf/Model_t_email_log");
        $tablename = $this->Model_t_email_log->_tableName;
        $email_list = $this->Model_t_email_log->find_all(
            $this->conn,
            NULL,
            $tablename,
            array('f_state' => 3),
            $limit = 5,
            1,
            'f_create_time asc'
        );

        if (!is_array($email_list) OR (count($email_list) === 0)){
            cilog('error',"获取邮件队列为空,退出",$log_filename);
            exit();
        }

        foreach ($email_list as $row){
            $flag = send_mail($row['f_to_email'],$row['f_subject'],$row['f_message']);
            if($flag !== 0){
                cilog('error','发送邮件失败! email:'.$row['f_to_email'].' errmsg:'.$flag,$log_filename);
                $this->Model_t_email_log->update_all(
                    $this->conn,
                    $tablename,
                    array('f_state' => 1),
                    array('f_id' => $row['f_id'])
                );
                continue;
            }

            cilog('error','发送邮件成功! email:'.$row['f_to_email'],$log_filename);
            $this->Model_t_email_log->update_all(
                $this->conn,
                $tablename,
                array('f_state' => 2),
                array('f_id' => $row['f_id'])
            );
        }
        exit();
    }


    /**
     * 第三方平台登录
     *
     */

    public function login_with_platform(){
        $this->init_log();
        $this->init_api();

        $plat_id = get_post_valueI('plat_id');
        $access_token = get_post_value('access_token');
        $plat_user_id = get_post_valueI('plat_user_id');
        if(in_array($plat_id,array(1,2))){
            cilog('error',"plat_id [plat_id:{$plat_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);;
        }

        if($access_token === ''){
            cilog('error',"access_token参数不能为空 [access_token:{$access_token}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($plat_user_id === 0){
            cilog('error',"plat_user_id [plat_id:{$plat_user_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);;
        }

        $this->load->service('user/user_service');

        $token_check = $this->user_service->check_plat_user_id($access_token,$plat_id);
        if($token_check != 0) {
            render_json($this->user_errcode['PLAT_NOT_USER'],"平台没有此账号");
        }

        $res = $this->user_service->user_login_with_flat($this->conn,$plat_user_id,$plat_id);

        if($res == $this->user_errcode['PLAT_NOT_USER']){
            render_json($this->user_service->user_errcode['PLAT_NOT_USER']);
        }
        if($res == $this->user_errcode['USER_NOT_LOCAL']){
            render_json($this->user_service->user_errcode['PLAT_NOT_USER']);
        }

        // 未激活用户不允许登陆
        if ($res['f_state'] == $this->user_service->user_state['CAN_NOT_CHECK_EMAIL']){
            cilog('error',"用户未激活,不允许登陆! [uin:{$res['uin']}] [state:{$res['f_state']}]");
            render_json($this->user_service->user_errcode['USER_NOT_ACTIVE'],'未激活',$res['f_email']);
        }
        $uin = $res['f_uin'];
        $skey = create_guid('session_key');
        $this->user_service->set_session($uin,$skey);
        $this->oSession = $this->get_session();
        cilog('debug',"用户登陆成功 [plat_user_id:{$plat_user_id}]");
        render_json(0,'');

    }


    /**
     * 第三方注册
     */
    public function register_with_platform(){

        $this->init_log();
        $this->init_api();

        $plat_id = get_post_valueI('plat_id');
        $plat_user_id = get_post_valueI('plat_user_id');
        $access_token = get_post_value('access_token');
        $email = get_post_value('email');
        $pwd = get_post_value('password');
        $invite_email = get_post_value('invite_email');
        $img_code = get_post_value('img_code');

        cilog('debug',"param [email:{$email}] [pwd:{$pwd}] [img_code:{$img_code}] [plat_id:{$plat_id}]
        [plat_user_id :{$plat_user_id}]");

        // 验证图片验证码
        $flag = $this->check_img_code($img_code);
        if ($flag != 0){
            render_json($flag);
        }

        if(!$this->oValidator->isEmail($email)){
            cilog('error',"邮箱格式不对 [email:{$email}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($pwd)){
            cilog('error',"密码格式不对 [pwd:{$pwd}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('user/user_service');
        $errcode = $this->user_service->user_errcode['USER_INVITER_NOT_EXIST'];

        if($invite_email !== ''){
            if(!$this->oValidator->isEmail($invite_email)){
                cilog('error',"邀请人邮箱格式不对 [email:{$invite_email}]");
                render_json($errcode);
            }
            $rsp = $this->user_service->get_uin_by_email($this->conn,$invite_email);
            if(!is_array($rsp)){
                cilog('error',"用户邀请人邮箱不合法!");
                render_json($errcode);
            }
        }

        if(in_array($plat_id,array(1,2))){
            cilog('error',"plat_id [plat_id:{$plat_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);;
        }

        if($access_token === ''){
            cilog('error',"access_token参数不能为空 [access_token:{$access_token}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($plat_user_id === 0){
            cilog('error',"plat_user_id [plat_user_id:{$plat_user_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);;
        }

        $this->load->service('user/user_service');
        $token_check = $this->user_service->check_plat_user_id($access_token,$plat_id);
        if($token_check != 0) {
           render_json($this->user_errcode['PLAT_NOT_USER']);
        }

        $flag = $this->user_service->register_with_plat($this->conn,$plat_user_id,$plat_id,$email,$pwd,$invite_email);
        if ($flag == $this->user_service->user_errcode['USER_LOCAL_EXIST']) {
            render_json($this->user_service->user_errcode['USER_LOCAL_EXIST'],"此用户本地已经注册过了");
        }
        if ($flag == $this->user_service->user_errcode['USER_EMAIL_EXIST']) {
            render_json($this->user_service->user_errcode['USER_EMAIL_EXIST'],"用户邮箱已经注册过了");
        }
        if ($flag == $this->user_service->user_errcode['USER_GET_IDMAKER_ERR']) {
            render_json($this->user_service->user_errcode['USER_GET_IDMAKER_ERR'],"用户id生成失败");
        }
        if ($flag == $this->user_service->user_errcode['USER_ADD_ERR']) {
            render_json($this->user_service->user_errcode['USER_ADD_ERR'],"用户添加失败");
        }
        render_json(0,"用户添加成功",'');

    }















}
