<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * user  用户服务  0x2001
 */


class User_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->user_base_web_url = (ENVIRONMENT === "development") ? "http://trade.test.com/" : "http://trade.coincoming.com/";
        $this->load->model("user/Model_t_uin");
        $this->load->model("user/Model_t_admin");
        $this->user_base_key = "qwe2017&*%$#ajlnun";      // 服务基础密钥
        $this->user_errcode = array(
            "USER_PARAM_ERR"           => 0x20010000,      // 用户接口,参数错误
            "USER_ADD_ERR"             => 0x20010001,      // 用户接口,添加用户失败
            "USER_EMAIL_EXIST"         => 0x20010002,      // 用户接口,该邮箱已经注册过
            "USER_GET_IDMAKER_ERR"     => 0x20010003,      // 用户接口,获取uin id失败
            "USER_GET_USERINFO_ERR"    => 0x20010004,      // 用户接口,获取用户信息失败
            "USER_UIN_NOT_EXIST"       => 0x20010005,      // 用户接口,参数错误,uin不存在
            "USER_NOT_ACTIVE"          => 0x20010006,      // 用户接口,用户未激活
            "USER_NOT_ALLOW"           => 0x20010007,      // 用户接口,用户删除或者禁用
            "USER_ERR_STATE"           => 0x20010008,      // 用户接口,用户状态异常
            "USER_UPDATE_USERINFO_ERR" => 0x20010009,      // 用户接口,更新用户数据失败
            "USER_EMAIL_NOT_ONLY"      => 0x2001000a,      // 用户接口,邮箱不唯一
            "USER_EMAIL_NOT_EXIST"     => 0x2001000b,      // 用户接口,邮箱不存在
            "USER_PW_CHECK_ERR"        => 0x2001000c,      // 用户接口,密码校验失败
            "USER_GET_USERINFO_LIST_ERR"=> 0x2001000d,     // 用户接口,获取用户信息列表失败
            "USER_ADMIN_NOT_EXSIT"      => 0x2001000e,      // 用户接口,管理员账号不存在
            "USER_ADMIN_STATE_ERR"      => 0x2001000f,      // 用户接口,管理员用户状态错误
            "USER_ADMIN_PW_CHECK_ERR"   => 0x20010011,      // 用户接口,管理员密码校验失败
            "USER_ADMIN_CREAT_ERR"      => 0x20010012,      //用户接口，管理员创建失败
            "USER_INVITER_NOT_EXIST"    => 0x20010013,      //用户接口，邀请人不存在
            "USER_NOT_LOGIN"           => 0x20019999,      // 用户接口,用户未登录
            "PLAT_NOT_USER"            => 0x20010014,      //用户接口，平台没有此用户
            "USER_NOT_LOCAL"            => 0x20010015,     //用户接口，本地没有此用户
            "PLAT_USER_BIND_ERR"        => 0x20010016,     //用户接口，平台和本地绑定失败
            "USER_LOCAL_EXIST"            => 0x20010017,     //用户接口，本地有此用户
        );
        $this->user_redis_key = array(
            'TIMEOUT'             => 3600,
            'SESSION_KEY_PRE'     => "uin_session_key_",   // uin_session_key_  uin为XXX的session key
            'SESSION_ADMIN_KEY'   => "admin_session_key_", // admin_session_key_  admin_id为xxx的session key
            'USER_INFO_PRE'       => "userinfo_",          // userinfo_uin      用户信息
            'EMAIL_CODE'          => "email_code_",        // email_code_uin    用户信息
            'REG_CODE'            => "reg_code_",          // reg_code_         用户信息
            'REG_KEY_CODE'        => "reg_key_code_",      // reg_key_code_     注册激活的key
            'RESET_KEY_PW'        => "reset_pw_",          // reset_pw_         重置密码的key
        );
        $this->user_state = array(
            "BASE"                   => 0,       // 初始值
            "CHECK_ONE"              => 1,       // 待完成1级验证
            "CHECK_TWO"              => 2,       // 待完成2级验证
            "CHECK_THREE"            => 3,       // 待完成3级验证
            "SUBMIT_IDCARD"          => 4,       // 用户提交身份信息,待验证
            "USER_REGISTER_SCU"      => 5,       // 用户注册成功,并完成所有验证
            "CAN_NOT_CHECK_EMAIL"    => 6,       // 注册成功,邮箱未验证
            "CAN_NOT_ALLOW"          => 100,     // 用户删除或者禁用
        );

        $this->admin_user_rule = array(
            "ADMIN_LOGIN"          => 1,             //登录权限
            "ADMIN_UPDATE_PWD"     => 2,             //修改密码
        );

        $this->facebook = array(
            "APP_ID" => "289014404949541",
            "APP_SECRET" =>"a9290d57820637061190e81b247fd650",
        );
    }

    // 校验登陆态
    public function check_online($oSession)
    {
        $uin = $oSession->uin;
        $skey = $oSession->skey;
        $key = $this->user_redis_key['SESSION_KEY_PRE'].$uin;
        $skey_from_redis = $this->cache->redis->get($key);
        if($skey_from_redis === $skey){
            // 登陆态续期
            $this->set_session($uin,$skey);
            return 0;
        }else{
            return $this->user_errcode['USER_NOT_LOGIN'];
        }
    }
    //检验后台登录态
    public function check_online_($oSession){
        $uin = $oSession->uin;
        $skey = $oSession->skey;
        $key = $this->user_redis_key['SESSION_ADMIN_KEY'].$uin;
        $skey_from_redis = $this->cache->redis->get($key);
        if($skey_from_redis === $skey){
            // 登陆态续期
            $this->set_session_($uin,$skey);
            return 0;
        }else{
            return $this->user_errcode['USER_NOT_LOGIN'];
        }
    }

    // 退出登陆
    public function loginout($oSession)
    {
        $uin = $oSession->uin;
        $key = $this->user_redis_key['SESSION_KEY_PRE'].$uin;
        $this->cache->redis->delete($key);
        delete_cookie('uin');
        delete_cookie('skey');
        header("Location: {$this->user_base_web_url}");
    }

    // 写入登陆态
    public function set_session($uin,$skey)
    {
        $key = $this->user_redis_key['SESSION_KEY_PRE'].$uin;
        $_timeout = $this->user_redis_key['TIMEOUT'];
        // 写入 uin,skey 到cookie
        set_cookie("uin",$uin,$_timeout);
        set_cookie("skey",$skey,$_timeout);

        // 写入 uin,skey 到redis
        $this->cache->redis->save($key,$skey,$_timeout);
    }

    //后台写入登录态
    public function set_session_($uin,$skey){
        $key = $this->user_redis_key['SESSION_ADMIN_KEY'].$uin;
        $_timeout = $this->user_redis_key['TIMEOUT'];
        // 写入 uin,skey 到cookie
        set_cookie("uin",$uin,$_timeout);
        set_cookie("skey",$skey,$_timeout);

        // 写入 uin,skey 到redis
        $this->cache->redis->save($key,$skey,$_timeout);
    }


    // 参数过滤,用户对外展示数据汇总
    public function export_user($aUserinfo)
    {
        return array(
            // 'intUin' => $aUserinfo['f_uin'],
            'strEmail' => $aUserinfo['f_email'],
            'strPhone' => $aUserinfo['f_phone'], //substr_replace($aUserinfo['f_phone'],"*******",0,7),
            'strCountry' => $aUserinfo['f_country'],
            'strProv' => $aUserinfo['f_prov'],
            'strCity' => $aUserinfo['f_city'],
            'strDist' => $aUserinfo['f_dist'],
            'intJoinNum' => $aUserinfo['f_can_join'],
            'strAddrinfo' => $aUserinfo['f_addr_info'], //(strlen($aUserinfo['f_addr_info']) >= 3) ? substr_replace($aUserinfo['f_addr_info'],"****",3) : "***",
            'strTruename' => substr_replace($aUserinfo['f_truename'],"****",3),
            'intState' => $aUserinfo['f_state'],
            'intIdcard' => substr_replace($aUserinfo['f_idcard'],"*******",4,10),
        );
    }

    // 获取最终的加密的密码串
    public function get_last_pw($key,$pw)
    {
        $base_key = $this->user_base_key;
        return md5($base_key.$pw.$key);
    }

    // 验证密码
    public function check_pw($key,$pw_db,$pw_client)
    {
        $last_pw = $this->get_last_pw($key,$pw_client);
        if ($last_pw === $pw_db){
            return 0;
        }else{
            cilog('error',"密码校验失败 [key:{$key}] [pw_db:{$pw_db}] [pw_client:{$pw_client}] [last_pw:{$last_pw}]");
            return $this->user_errcode['USER_PW_CHECK_ERR'];
        }
    }

    // 校验用户状态是否合法
    public function chenck_user_state($aUserinfo)
    {
        // 校验当前用户状态
        if (($aUserinfo['f_state'] <= 0) OR ($aUserinfo['f_state'] >= $this->user_state['CAN_NOT_CHECK_EMAIL'])){
            // 该用户未激活 已删除或者未激活用户只有浏览权限
            cilog('error',"该用户未激活 [uin:{$aUserinfo['f_uin']}] [state:{$aUserinfo['f_state']}]");
            return $this->user_errcode['USER_NOT_ACTIVE'];
        }
        return 0;
    }

    // 生成激活链接
    public function get_active_url($email,$token)
    {
        $url = $this->user_base_web_url.'user/active?token='.$token;
        return 'Dear'.$email. '：<br/>'
                        .'<p style=\'text-indent:2em\'>Thank you for signing up for a new account. Please click the link below to activate your account.
。</p>'
                        .'<p style=\'text-indent:2em\'><a href=\''.$url.'\' target=\'_blank\'> click activate account </a></p>'
                        .'<p style=\'text-indent:2em\'>If the above link cannot be clicked, copy it to your browser\'s address bar and enter it. The link is valid for 1 hour!
!</p>'
                .'<p>thanks!</p>'."<p>Coincoming Customer Support</p>";
    }


    /**
     * @fun    添加用户
     * @param  $conn
     * @param  $str_email
     * @param  $str_password
     * @return int
     */
    public function add_user($conn,$str_email,$str_password,$invite_user_mail)
    {
        // 1 判断当前邮箱是否存在
        $tablename = $this->Model_t_uin->get_tablename();
        $where = array('f_email' => $str_email);
        $count = $this->Model_t_uin->count($conn,$tablename,$where);
        if($count != 0)
        {
            cilog('error',"email:{$str_email} 已经存在!");
            return $this->user_errcode['USER_EMAIL_EXIST'];
            exit();
        }

        // 2 获取uin
        $this->load->model("conf/Model_t_idmaker");
        $uin = $this->Model_t_idmaker->get_id_from_idmaker($conn,'UIN_ID');
        if (!$uin){
            return $this->user_errcode['USER_GET_IDMAKER_ERR'];
        }

        $pw_key = create_guid("uin_key");
        $last_pw = $this->get_last_pw($pw_key,$str_password);
        cilog('error','生成的最终的密码:'.$last_pw);

        $userinfo = array(
            'f_uin' => $uin,
            'f_email' => $str_email,
            'f_password' => $last_pw,
            'f_key' => $pw_key,
            'f_state' => $this->user_state['CAN_NOT_CHECK_EMAIL'],
            'f_invite_email' => $invite_user_mail,
            'f_ip' => getRealIpAddr(),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );

        $conn->trans_start();
        // 插入数据用户表数据
        $this->Model_t_uin->save($conn,$tablename,$userinfo);
        cilog('debug',"写入用户表成功! [uin:{$uin}]");

        // 添加用户资产信息
        $this->load->model("coin/Model_t_coin");
        $tablename = $this->Model_t_coin->get_tablename();
        $coin_list = $this->Model_t_coin->find_all($conn,'f_coin_id,f_abbreviation',$tablename,array(), 100, 1, 'f_coin_id asc');

        $this->load->model("finance/Model_t_finance_info");
        $tablename = $this->Model_t_finance_info->get_tablename();
        foreach ($coin_list as $row){
            $finance_info = array(
                'f_uin' => $uin,
                'f_coin_id' => $row['f_coin_id'],
                'f_coin_abbr' => $row['f_abbreviation'],
                'f_total_vol' => 0,
                'f_can_use_vol' => 0,
                'f_freeze_vol' => 0,
                'f_create_time' => timestamp2time(),
                'f_modify_time' => timestamp2time(),
            );
            $this->Model_t_finance_info->save($conn,$tablename,$finance_info);
            cilog('debug',"写入用户初始资产信息成功! [uin:{$uin}] [coin_id:{$row['f_coin_id']}]");
        }
        $conn->trans_complete();

        if ($conn->trans_status() === FALSE)
        {
            // $conn->trans_rollback();
            cilog('error',"添加用户数据失败,开始回滚数据!");
            return $this->user_errcode['USER_ADD_ERR'];
        }
        else
        {
            // $conn->trans_commit();
            cilog('debug',"添加用户数据成功!");
            return 0;
        }
    }

    /**
     * @fun    获取用户详情
     * @param  $conn
     * @param  $uin
     * @return mixed
     */
    public function get_user_info($conn,$uin)
    {
        cilog('debug',"获取用户信息成功! [uin:{$uin}]");
        $key = $this->user_redis_key['USER_INFO_PRE'].$uin;
        $str_userinfo = $this->cache->redis->get($key);
        if(!$str_userinfo){
            // redis 中没有用户信息,从db获取
            $tablename = $this->Model_t_uin->get_tablename();
            $select = "*";
            $where = array('f_uin'=>$uin);
            $sort = NULL;
            $arr_userinfo = $this->Model_t_uin->find_by_attributes($conn,$select,$tablename,$where, $sort);
            if(!$arr_userinfo){
                cilog('error',"获取用户信息失败! uin:{$uin}");
                return $this->user_errcode['USER_GET_USERINFO_ERR'];
            }
            $str_userinfo = serialize($arr_userinfo);
            $this->cache->redis->save($key,$str_userinfo,$this->user_redis_key['TIMEOUT']);
        }
        return unserialize($str_userinfo);
    }

    /**
     * @fun    更新用户信息
     * @param  $conn
     * @param  $arr_userinfo_update
     * @return int
     */
    public function update_user_info($conn,$uin,$arr_userinfo_update)
    {
        $userinfo = $this->get_user_info($conn,$uin);
        if (!$userinfo){
            // 用户uin不存在
            return $this->user_errcode['USER_UIN_NOT_EXIST'];
        }

        $update_data = array(
            'f_modify_time' => timestamp2time(),
        );

        // 校验当前用户状态
        $flag = $this->chenck_user_state($userinfo);
        if($flag != 0){
            return $flag;
        }

        foreach ($arr_userinfo_update as $key => $value){
            $update_data[$key] = $value;
        }

        // 开始db操作,更新数据
        $conn->trans_start();
        $tablename = $this->Model_t_uin->get_tablename();
        $where = array('f_uin'=>$uin);
        $this->Model_t_uin->update_all($conn,$tablename,$update_data, $where);
        $conn->trans_complete();
        if ($conn->trans_status() === FALSE)
        {
            // $conn->trans_rollback();
            cilog('error',"更新用户数据失败,开始回滚数据!");
            return $this->user_errcode['USER_UPDATE_USERINFO_ERR'];
        }
        else
        {
            // $conn->trans_commit();
            cilog('debug',"更新用户数据成功!");
            $key = $key = $this->user_redis_key['USER_INFO_PRE'].$uin;
            $this->cache->redis->delete($key);
            return 0;
        }
    }

    /**
     * @fun    搜索用户信息列表
     */
    public function search_user_list($conn,$page,$num,$query=array())
    {
        cilog('debug',"查询用户列表信息,参数如下:");
        cilog('debug',$query);

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => 0,
            'rows' => array(),
        );

        if($query === array()){
            $where = array();
        }else{
            foreach ($query as $key => $value){
                $where[$key] = $value;
            }
        }

        $count = $this->Model_t_uin->count(
            $conn,
            $tablename = $this->Model_t_uin->get_tablename(),
            $where
        );

        if((int)$count === 0){
            cilog('error',"找不到用户信息! [count:{$count}]");
            return $aRsp;
        }

        $user_list = $this->Model_t_uin->find_all(
            $conn,
            $select=NULL,
            $tablename=$this->Model_t_uin->get_tablename(),
            $where,
            $num,
            $page,
            $sort = 'f_create_time desc'
        );
        if (!$user_list){
            cilog('error',"获取用户列表信息失败!");
            return $this->user_errcode['USER_GET_USERINFO_LIST_ERR'];
        }

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => $count,
            'rows' => $user_list,
        );
        return $aRsp;
    }

    /**
     * @fun 通过email 获取用户信息
     * @param $conn
     * @param $str_email
     * @return mixed
     */
    public function get_uin_by_email($conn,$str_email)
    {
        // 校验邮件唯一性
        $tablename=$this->Model_t_uin->get_tablename();
        $where = array('f_email' => $str_email);
        $count = $this->Model_t_uin->count($conn,$tablename,$where);
        if((int)$count !== 1) {
            cilog('error',"邮箱不唯一 [email:{$str_email}]");
            return $this->user_errcode['USER_EMAIL_NOT_ONLY'];
        }

        $select = "f_uin,f_password,f_key,f_deal_pw,f_state";
        $sort = NULL;
        $arr_uin = $this->Model_t_uin->find_by_attributes($conn,$select,$tablename,$where, $sort);
        if(!$arr_uin){
            cilog('error',"该邮件地址不存在 [email:{$str_email}]");
            return $this->user_errcode['USER_EMAIL_NOT_EXIST'];
        }
        return $arr_uin;
    }

    /**
     * 检查用户是否ip
     */
    public function check_ip($conn,$userinfo)
    {
        $ip = getRealIpAddr();
        if($ip !== $userinfo['f_ip']){

            $conn->trans_start();
            // 发邮件给用户
            $message = "Dear ".$userinfo['f_email'].":<br/>"
                ."<p style='text-indent:2em'>Now you are logining in different ip! Please confirm whether is your visiting!</p>"
                ."<p style='text-indent:2em'>Your ip is ".$ip."</p>"
                .'<p>Thanks!</p>'."<p>Coincoming Customer Support</p>";
            $this->load->model("conf/Model_t_email_log");
            $this->Model_t_email_log->insert_email_log(
                $conn,
                $data=array(
                    'f_to_email' => $userinfo['f_email'],
                    'f_subject' => "User Login In Different Places",
                    'f_message' => $message,
                    'f_state' => 3,
                )
            );
            // 更新用户ip
            $this->Model_t_uin->update_all(
                $conn,
                $tablename = $this->Model_t_uin->get_tablename(),
                $data = array(
                    'f_modify_time' => timestamp2time(),
                    'f_ip' => $ip,
                )
            );
            $key = $key = $this->user_redis_key['USER_INFO_PRE'].$userinfo['f_uin'];
            $this->cache->redis->delete($key);

            $conn->trans_complete();
            if ($conn->trans_status() === FALSE)
            {
                cilog('error',"用户写入异地登录信息失败!");
                // return $this->user_errcode['USER_ADD_ERR'];
            }
            else
            {
                cilog('debug',"用户写入异地登录信息成功!");
                // return 0;
            }
        }
    }

    /**
     * 获取用户的成员用户
     */
    public function get_user_member_list($conn,$userinfo)
    {
        $list = $this->Model_t_uin->find_all(
            $conn,
            $select='f_uin,f_email',
            $tablename=$this->Model_t_uin->get_tablename(),
            $where = array(
                'f_invite_email' => $userinfo['f_email']
            ),
            $limit = 10000000,
            $page = 1,
            $sort = NULL
        );

        if(count($list) == 0){
            cilog('error',"用户的成员人数为0");
            return 0;
        }

        $rsp = array();
        foreach ($list as $row){
            $rsp[$row['f_uin']] = $row['f_email'];
        }
        return $rsp;
    }



    /**
     * 第三方平台登录
     */

    public function user_login_with_flat($conn,$plat_user_id,$plat_id){

        //本地数据库是否有此id
        $table_name = $this->Model_t_uin->get_tablename();
        $where = array('f_plat_user_id' => md5($plat_user_id),'f_plat_id'=>$plat_id);
        $count = $this->Model_t_uin->count($conn,$table_name,$where);
        if(empty($count)){
           cilog('error',"第三方未在本地注册");
           return $this->user_errcode['USER_NOT_LOCAL'];
        }
        $profile_info = $this->Model_t_uin->find_by_attributes($conn,"f_uin,f_email",$table_name,$where);
        if($profile_info){
            return $profile_info;
        }


    }


    /**
     * 第三方注册
     */
    public function register_with_plat($conn,$plat_user_id,$plat_id,$email,$str_password,$invite_user_mail){
        //判断plat_user_id和plat_id 本地是否存在
        $table_name = $this->Model_t_uin->get_tablename();
        $where = array('f_plat_user_id' => md5($plat_user_id),'f_plat_id'=>$plat_id);
        $count = $this->Model_t_uin->count($conn,$table_name,$where);
        if($count != 0){
            cilog('debug','本地存在');
            return $this->user_errcode['USER_LOCAL_EXIST'];
        }
        // 1 判断当前邮箱是否存在
        $table_name = $this->Model_t_uin->get_tablename();
        $where = array('f_email' => $email);
        $count = $this->Model_t_uin->count($conn,$table_name,$where);
        if($count != 0)
        {
            cilog('error',"email:{$email} 已经存在!");
            return $this->user_errcode['USER_EMAIL_EXIST'];
        }
        //获取uin
        $this->load->model("conf/Model_t_idmaker");
        $uin = $this->Model_t_idmaker->get_id_from_idmaker($conn,'UIN_ID');
        if (!$uin){
            return $this->user_errcode['USER_GET_IDMAKER_ERR'];
        }

        $pw_key = create_guid("uin_key");
        $last_pw = $this->get_last_pw($pw_key,$str_password);
        cilog('error','生成的最终的密码:'.$last_pw);


        $userinfo = array(
            'f_uin' => $uin,
            'f_plat_user_id' => $plat_user_id,
            'f_plat_id'=> $plat_id,
            'f_email' => $email,
            'f_password' => $str_password,
            'f_key' => $pw_key,
            'f_state' => $this->user_state['CAN_NOT_CHECK_EMAIL'],
            'f_invite_email' => $invite_user_mail,
            'f_ip' => getRealIpAddr(),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );

        $conn->trans_start();
        // 插入数据用户表数据
        $this->Model_t_uin->save($conn,$table_name,$userinfo);
        cilog('debug',"写入用户表成功! [uin:{$uin}]");

        // 添加用户资产信息
        $this->load->model("coin/Model_t_coin");
        $tablename = $this->Model_t_coin->get_tablename();
        $coin_list = $this->Model_t_coin->find_all($conn,'f_coin_id,f_abbreviation',$tablename,array(), 100, 1, 'f_coin_id asc');

        $this->load->model("finance/Model_t_finance_info");
        $tablename = $this->Model_t_finance_info->get_tablename();
        foreach ($coin_list as $row){
            $finance_info = array(
                'f_uin' => $uin,
                'f_coin_id' => $row['f_coin_id'],
                'f_coin_abbr' => $row['f_abbreviation'],
                'f_total_vol' => 0,
                'f_can_use_vol' => 0,
                'f_freeze_vol' => 0,
                'f_create_time' => timestamp2time(),
                'f_modify_time' => timestamp2time(),
            );
            $this->Model_t_finance_info->save($conn,$tablename,$finance_info);
            cilog('debug',"写入用户初始资产信息成功! [uin:{$uin}] [coin_id:{$row['f_coin_id']}]");
        }
        $conn->trans_complete();

        if ($conn->trans_status() === FALSE)
        {
            // $conn->trans_rollback();
            cilog('error',"添加用户数据失败,开始回滚数据!");
            return $this->user_errcode['USER_ADD_ERR'];
        }
        else
        {
            // $conn->trans_commit();
            cilog('debug',"添加用户数据成功!");
            return 0;
        }

    }


    /*
     * 第三方验证
     */
    public function check_plat_user_id($access_token,$plat_id){
        require_once APPPATH . '/libraries/Facebook/autoload.php';
        if($plat_id === 1){
            //验证token是否合法
            $fb = new Facebook\Facebook([
                'app_id' => $this->facebook['APP_ID'], // Replace {app-id} with your app id
                'app_secret' =>$this->facebook['APP_SECRET'],
                'default_graph_version' => 'v2.10',
            ]);
            try {
                $response = $fb->get('/me', $access_token);
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                cilog('error', 'Graph returned an error: ' . $e->getMessage());
                exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                cilog('error', 'Facebook SDK returned an error: ' . $e->getMessage());
                exit;
            }

            $me = $response->getGraphUser();
            if(!$me->getId()){
                cilog("error","此token不匹配");
                return $this->user_errcode['PLAT_NOT_USER'];
            }
            return 0;
        }
        if($plat_id === 2){


        }

    }


}