<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class admin  后台管理模块 api部分
 */
// require_once APPPATH . '/libraries/comm/captcha.php';
class Api extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->log_filename = NULL;
    }

    /**
     * 用户部分
     */

    /**
     * 获取用户信息
     *
     * uin      用户唯一标识
     */
    public function get_user_info()
    {

        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $uin = get_post_valueI('uin');

        cilog('debug', "参数信息: [uin:{$uin}]",$this->log_filename);

        if ($uin < 10000) {
            cilog('error', "uin参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $userinfo = $this->user_service->get_user_info($this->conn, $uin);

        if ($userinfo) {

            $data = unserialize($userinfo['f_pic_id']);
            $userinfo_i = array(
                'uin' => isset($userinfo['f_uin']) ? $userinfo['f_uin'] : 0,
                'email' => isset($userinfo['f_email']) ? $userinfo['f_email'] : '',
                'phone' => isset($userinfo['f_phone']) ? $userinfo['f_phone'] : '',
                'country' => isset($userinfo['f_country']) ? $userinfo['f_country'] : '',
                'prov' => isset($userinfo['f_prov']) ? $userinfo['f_country'] : '',
                'city' => isset($userinfo['f_city']) ? $userinfo['f_city'] : '',
                'dist' => isset($userinfo['f_dist']) ? $userinfo['f_dist'] : '',
                'addr_info' => isset($userinfo['f_addr_info']) ? $userinfo['f_addr_info'] : '',
                'truename' => isset($userinfo['f_truename']) ? $userinfo['f_truename'] : '',
                'idcard' => isset($userinfo['f_idcard']) ? $userinfo['f_idcard'] : '',
                'front' => is_array($data) ? get_upload_path($data['front']) : '',
                'pic_with_hand' => is_array($data) ? get_upload_path($data['pic_with_hand']) : '',
                'addtime' => isset($userinfo['f_create_time']) ? $userinfo['f_create_time'] : '',
            );
            render_json(0, '', $userinfo_i);
        } else {
            render_json($this->user_service->user_errcode['USER_GET_USERINFO_ERR']);
        }
    }

    /**
     * 获取用户列表信息
     *
     * uin      用户唯一标识
     * email    邮箱         模糊匹配
     * phone    手机         模糊匹配
     * truename 姓名         模糊匹配
     * idcard   身份证号      模糊匹配
     * state    用户状态
     * page     页码
     * num      当前最大展示数码
     */
    public function get_user_list()
    {
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $uin = get_post_valueI('uin');
        $email = get_post_value('email');
        $phone = get_post_value('phone');
        $truename = get_post_value('truename');
        $idcard = get_post_value('idcard');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');
        $state = get_post_valueI('state');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;
        $where = array();
        cilog('debug', "参数信息: [uin:{$uin}] [email:{$email}] [phone:{$phone}] [truename:{$truename}] [idcard:{$idcard}] [page:{$page}] [num:{$num}] [state:{$state}]",$this->log_filename);

        // 参数校验
        if (($uin === 0) && ($email === '') && ($phone === '') && ($truename === '') && ($idcard === '') && ($state === 0)) {
            cilog('error', "参数为空!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (($uin !== 0) && ($uin > 10000)) {
            $where['f_uin'] = $uin;
        }

        if ($email !== '') {
            $where['f_email LIKE'] = "%" . $email . "%";
        }

        if ($phone !== '') {
            $where['f_phone LIKE'] = "%" . $phone . "%";;
        }

        if ($truename !== '') {
            $where['f_truename LIKE'] = "%" . $truename . "%";
        }

        if ($idcard !== '') {
            $where['f_idcard LIKE'] = "%" . $idcard . "%";
        }

        $this->load->service("user/user_service");
        $state_list = array(
            $this->user_service->user_state['BASE'],
            $this->user_service->user_state['CHECK_ONE'],
            $this->user_service->user_state['CHECK_TWO'],
            $this->user_service->user_state['CHECK_THREE'],
            $this->user_service->user_state['SUBMIT_IDCARD'],
            $this->user_service->user_state['USER_REGISTER_SCU'],
            $this->user_service->user_state['CAN_NOT_CHECK_EMAIL'],
            $this->user_service->user_state['CAN_NOT_ALLOW'],
        );
        if (!in_array($state,$state_list)){
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($state !== $this->user_service->user_state['BASE']){
            $where['f_state'] = $state;
        }

        if ($where === array()) {
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $user_list = $this->user_service->search_user_list($this->conn, $page, $num, $where);
        if (!is_array($user_list)) {
            render_json_list($user_list);
        } else {
            $b = array();
            foreach ($user_list['rows'] as $row) {
                $a = array(
                    'uin' => $row['f_uin'],
                    'truename' => $row['f_truename'],
                    'email' => $row['f_email'],
                    'phone' => $row['f_phone'],
                    'state' => $row['f_state'],
                    'country' => $row['f_country'],
                    'addtime' => $row['f_create_time'],
                );
                array_push($b, $a);
            }
            render_json_list(0, '', $user_list['totalNum'], $b);
        }
    }

    /**
     * 获取需要实名验证的用户信息
     *
     * page     页码
     * num      当前最大展示数码
     */
    public function get_check_user_list()
    {
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;
        cilog('debug', "参数信息: [page:{$page}] [num:{$num}]",$this->log_filename);

        $where = array(
            'f_state' => $this->user_service->user_state['SUBMIT_IDCARD'],
        );

        $user_list = $this->user_service->search_user_list($this->conn, $page, $num, $where);
        if (!is_array($user_list)) {
            render_json_list($user_list);
        } else {
            $b = array();
            foreach ($user_list['rows'] as $row) {
                $a = array(
                    'uin' => $row['f_uin'],
                    'truename' => $row['f_truename'],
                    'email' => $row['f_email'],
                    'phone' => $row['f_phone'],
                    'state' => $row['f_state'],
                    'country' => $row['f_country'],
                    'addtime' => $row['f_create_time'],
                );
                array_push($b, $a);
            }
            render_json_list(0, '', $user_list['totalNum'], $b);
        }
    }

    /**
     * 用户身份实名验证
     *
     * uin      用户唯一标识
     * is_pass  是否通过 1 通过 2 不通过
     */
    public function user_idcard_auth()
    {
        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();


        $uin = get_post_valueI('uin');
        $is_pass = get_post_valueI('is_pass'); // 是否通过 1 通过 2 不通过

        cilog('debug', "参数信息: [uin:{$uin}]",$this->log_filename);

        if ($uin < 10000) {
            cilog('error', "uin参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (!in_array($is_pass, array(1, 2))) {
            cilog('error', "is_pass参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if ($is_pass === 1) {
            $state = $this->user_service->user_state['USER_REGISTER_SCU'];
        } else {
            $state = $this->user_service->user_state['CHECK_THREE'];
        }

        $userinfo = $this->user_service->get_user_info($this->conn, $uin);
        if (!$userinfo) {
            render_json($this->user_service->user_errcode['USER_GET_USERINFO_ERR']);
        }

        if ($userinfo['f_state'] != $this->user_service->user_state['SUBMIT_IDCARD']) {
            cilog('error', "无法校验用户实名验证身份! state:{$userinfo['f_state']}",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ERR_STATE']);
        }

        $flag = $this->user_service->update_user_info(
            $conn = $this->conn,
            $uin,
            $arr_userinfo_update = array(
                'f_state' => $state,
            )
        );
        render_json($flag);
    }


    /**
     * cms 部分
     */

    /**
     * 添加新闻信息
     * @param title 必填
     * @param content 必填
     * @param typeID 固定参数 1001 表示公告
     * @param addtime 非必填 能修改发布时间段对应表 create_time
     * @param power   权重 1-9999 越小越靠前
     */
    public function add_cms_info()
    {
        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $title = get_post_value('title');
        $content = get_post_value('content');
        $desc = get_post_value('desc');
        $typeId = get_post_valueI('typeId');
        //$author = get_post_value('author');
        $power = get_post_valueI('power');
        $addtime = get_post_value('addtime');

        if ($typeId < 1000) {
            cilog('error', "type_id 参数错误 [type_id:{$typeId}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (strlen($title) < 1) {
            cilog('error', "title 参数错误 [title:{$title}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (strlen($content) < 1) {
            cilog('error', "content 参数错误 [content:{$content}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (strlen($desc) < 1) {
            cilog('error', "desc 参数错误 [desc:{$desc}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($addtime !== '' && $this->oValidator->isDate($addtime)){
            cilog('error', "addtime参数错误 [add_time:{$addtime}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($power === 0) {
            $power = rand(2,9999);
        }elseif ($power > 9999){
            $power = rand(2,9999);
        }

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $this->load->service('user/user_service');
        $this->load->model("user/Model_t_admin");
        $admin_info = $this->Model_t_admin->find_by_attributes($this->conn,$select = NULL,$this->Model_t_admin->get_tablename(),$where = array('f_admin_id'=>$uin), $sort = NULL);
        if(!$admin_info){
            cilog('error',"查不到该管理者信息！ [uin:{$uin}]",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_NOT_EXSIT']);
        }
        $data = array(
            'f_title' => $title,
            'f_content' => $content,
            'f_desc' => $desc,
            'f_type_id' => $typeId,
            'f_author' =>  $admin_info['f_admin_user'],
            'f_power' => $power,
            'f_create_time' => ($addtime === '') ? timestamp2time() : $addtime,
        );

        $this->load->service("cms/cms_service");

        $result = $this->cms_service->add_case($this->conn, $data);
        render_json($result);
    }


    /**
     * @fun公告列表信息
     * @param typeId 类型id(必填）
     * @param page  页码(非必填）
     * @param num  当前页展示的最大列表数(非必填）
     */
    public function get_cms_list()
    {
        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');
        $type_id = get_post_valueI('type_id');

        if ($type_id < 1000) {
            cilog('error', "typeId 参数错误 ",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        cilog('debug', "参数信息: [page:{$page} num:{$num}]",$this->log_filename);
        $this->load->service('cms/cms_service');
        $cms_list = $this->cms_service->get_case_list($this->conn, $type_id, $page, $num);

        if (!is_array($cms_list)) {
            render_json_list(0, '', 0, '');
        }

        $a = array();

        foreach ($cms_list['rows'] as $row) {
            $b = array(
                'id'           =>    $row['f_case_id'],
                'title'        =>    $row['f_title'],
                'desc'         =>    $row['f_desc'],
                'type_id'      =>    (int)$row['f_type_id'],
                'addtime'      =>    $row['f_create_time'],
                'username'     =>    $row['f_author'],
                'state'        =>    $row['f_del_state'],
                'power'        =>    (int)$row['f_power'],
            );
            array_push($a, $b);
        }
        render_json_list(0, '', $cms_list['totalNum'], $a);

    }

    /**
     * @fun get_cms_info  某条记录的详情
     * @param id 具体cms的id
     * @param
     */
    public function get_cms_info()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $case_id = get_post_valueI('id');

        if ($case_id < 1000) {
            cilog('error', 'case_id 参数错误',$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('cms/cms_service');
        $case_info = $this->cms_service->get_case_info($this->conn, $case_id);
        if (!is_array($case_info)) {
            render_json(0, '', '', 0);
        }
        $data = $this->cms_service->export_case_2($case_info);
        if (isset($data['content'])) {
            $data['content'] = filter_value($data['content'], 1);
        }
        render_json(0, '', $data);

    }

    /**
     *  @fun update_cms_info 更新cms记录
     *  @param title cms标题
     *  @param desc cms 描述
     *  @param id cms该条的id
     *  @param content cms内容
     *  @param state 是否删除
     *  @param typeId 该条记录所属的类型
     *  @param power   权重 1-9999 越小越靠前
     */
    public function update_cms_info()
    {
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $title = get_post_value('title');
        $desc = get_post_value('desc');
        $case_id = get_post_valueI('id');
        $content = get_post_value('content');
        $state = get_post_valueI('state');
        $type_id = get_post_valueI('typeId');
        $addtime = get_post_value('addtime');
        $power = get_post_valueI('power');


        if ($case_id < 10000) {
            cilog('error', "case_id 参数错误 [case_id:{$case_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('cms/cms_service');
        $res = $this->cms_service->get_case_info($this->conn, $case_id);


        if (!is_array($res)) {
            render_json($this->cms_service->cms_errcode['CMS_GET_TYPE_ERR']);
        }

        if (($title !== '') && (strlen($title) < 1)) {
            cilog('error', "title 参数错误",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (($state !== false) && ($state !== 0) && ($state !== 1)) {
            cilog('error', "state 参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if (($type_id < 1000) && ($type_id !== 0)) {
            cilog('error', "type_id 参数错误 [type_id:{$type_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $type_id = ($type_id !== 0) ? $type_id : $res['f_type_id'];

        if ((strlen($content) < 1) && $content !== '') {
            cilog('error', "content 参数错误 [content:{$content}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if ((strlen($desc) < 1) && $desc !== '') {
            cilog('error', "desc 参数错误 [desc:{$desc}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($addtime !== '' && $this->oValidator->isDate($addtime)){
            cilog('error', "addtime 参数错误 [addtime:{$addtime}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->model("cms/Model_t_case");
        $this->load->service('cms/cms_service');
        $this->conn->trans_start();

        if($power === 0) {
            $power = $res['f_power'];
        }elseif ($power > 9999){
            $power = $res['f_power'];
        }elseif ($power === 1){
            // 置顶操作,查询之前是否有置顶的文章,如果有,调整权重
            $list = $this->cms_service->get_case_list($this->conn,$type_id,1,100,$aQuery=array('f_power' => 1));
            if(count($list['rows']) >= 1){
                foreach($list['rows'] as $row){
                    $p = rand(2,9999);
                    $id = $row['f_case_id'];
                    $this->Model_t_case->update_all(
                        $this->conn,
                        $tablename=$this->Model_t_case->get_tablename(),
                        $attributes=array('f_power' => $p,'f_modify_time' => timestamp2time()),
                        $where=array('f_case_id' => $id)
                    );
                    cilog('debug',"开始扭转之前的置顶消息权重! [id:{$id}] [old_power:{$row['f_power']}] [new_power:{$p}] [typeid:{$type_id}]");
                }
            }
        }
        cilog('debug',"power:{$power}");

        $new_case_info = array(
            'f_title' => ($title !== '') ? $title : $res['f_title'],
            'f_content' => ($content === '') ? $res['f_content']: $content,
            'f_type_id' => $type_id,
            'f_desc' => ($desc === '') ? $res['f_desc'] : $desc,
            'f_del_state' => ($state !== '') ? $state : $state['f_del_state'],
            'f_power' => $power,
            'f_create_time' => ($addtime !== '') ? $addtime : timestamp2time(),
            'f_modify_time' => timestamp2time()
        );

        $this->Model_t_case->update_all(
            $this->conn,
            $tablename=$this->Model_t_case->get_tablename(),
            $attributes=$new_case_info,
            $where=array('f_case_id' => $case_id)
        );

        $this->cms_service->del_top_case_redis_value($type_id);

        $this->conn->trans_complete();
        if ($this->conn->trans_status() === FALSE)
        {
            cilog('error',"修改cms信息失败,开始回滚数据!");
            render_json($this->cms_service->cms_errcode['CMS_UPDATE_CASE_INFO_ERR']);
        }
        else
        {
            cilog('debug',"修改cms信息成功!");
            render_json();
        }
    }



    /**
     * coin部分
     */

    /**
     * @fun  add_coin  添加货币基本信息
     * @param abbreviation 币种简称
     * @param coin_e_name 币种英文名
     * @param commission  比特币兑换率
     * @param open_price 初始购买价格
     * @param coin_name  币种中文名
     * @param create   开发者
     * @param block_time 区块确认时间
     * @param publish_time 发布时间
     * @param last_total_vol 货币总量
     * @param core_algorithm 核心算法
     * @param desc    内容简介
     * @param icon    币种logo地址
     * @param key_buttom_1 官网链接
     * @param key_buttom_2 论坛链接
     * @param key_buttom_3 钱包下载链接
     * @param key_buttom_4 区块浏览器
     * @param del_state 0 展示 1 不展示
     * @param market_type 1 人民币市场 2 比特币市场
     * @param atm_rate 提现手续费
     * @param high_price_24 24小时最高价
     * @param low_price_24 24小时最低价
     * @param deal_vol_24  24小时成交总量
     * @param rete_change_24 24小时涨跌*100%
     * @param last_price 最近价格
     * @param open_price 开盘价
     * @param close 收盘价
     * @param wallet_ip  钱包ip
     * @param wallet_port 钱包端口
     * @param user 钱包用户名
     * @param pw 钱包密码
     */
    public function add_coin()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $abbreviation = get_post_value('abbreviation');
        $coin_e_name = get_post_value('coin_e_name');
        $open_price = get_post_value('open_price');
        $commission = get_post_value('commission');
        $coin_name = get_post_value('coin_name');
        $creater = get_post_value('creater');
        $block_time = get_post_value('block_time');
        $publish_time = get_post_value('publish_time');
        $last_total_vol = get_post_valueI('last_total_vol');
        $core_algorithm = get_post_value('core_algorithm');
        $desc = get_post_value('desc');
        $icon = get_post_value('icon');
        $key_buttom_1 = get_post_value('key_buttom_1');
        $key_buttom_2 = get_post_value('key_buttom_2');
        $key_buttom_3 = get_post_value('key_buttom_3');
        $key_buttom_4 = get_post_value('key_buttom_4');
        $del_state = get_post_valueI('del_state');
        $market_type = get_post_valueI('market_type');
        $atm_rate = get_post_value('atm_rate');
        $high_price_24 = get_post_valueI('high_price_24');
        $low_price_24 = get_post_value('low_price_24');
        $deal_vol_24 = get_post_value('deal_vol_24');
        $rate_change_24 = get_post_value('rate_change_24');
        $last_price = get_post_value('last_price');
        $close_price = get_post_value('close_price');
        $wallet_ip = get_post_value('wallet_ip');
        $wallet_port = get_post_value('wallet_port');
        $wallet_user = get_post_value('user');
        $wallet_pw = get_post_value('pw');

        if(!in_array($del_state,array(0,1))){
            cilog('error', "del_state 参数错误 [del_state:{$del_state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!in_array($market_type,array(1,2))){
            cilog('error', "market_type 参数错误 [market_type:{$market_type}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }


        if (strlen($abbreviation) < 1) {
            cilog('error', "abbreviation 参数错误 [title:{$abbreviation}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        } else {

            $where = array(
                'f_abbreviation' => $abbreviation
            );

            $this->load->service('coin/coin_service');

            $result = $this->coin_service->check_abbreviation($this->conn, $where);

            if ($result !== 0) {
                render_json(0, '此简称已经存在', $result);
            }
        }

        $array = array(
            'f_abbreviation' => strtoupper($abbreviation),
            'f_coin_e_name' => $coin_e_name,
            'f_commission' => $commission,
            'f_open_price' => $open_price,
            'f_coin_name' => $coin_name,
            'f_creater' => $creater,
            'f_block_time' => $block_time,
            'f_publish_time' => $publish_time,
            'f_last_total_vol' => $last_total_vol,
            'f_core_algorithm' => $core_algorithm,
            'f_desc' => $desc,
            'f_icon' => $icon,
            'f_key_buttom_1' => $key_buttom_1,
            'f_key_buttom_2' => $key_buttom_2,
            'f_key_buttom_3' => $key_buttom_3,
            'f_key_buttom_4' => $key_buttom_4,
            'f_del_state' => $del_state,
            'f_market_type' => $market_type,
            'f_atm_rate' => $atm_rate,
            'f_high_price_24' => $high_price_24,
            'f_low_price_24' => $low_price_24,
            'f_deal_vol_24' => $deal_vol_24,
            'f_rate_change_24' => $rate_change_24,
            'f_last_price' => $last_price,
            'f_close_price' => $close_price,
            'f_wallet_ip' => $wallet_ip,
            'f_wallet_port' => $wallet_port,
            'f_user' => $wallet_user,
            'f_pw' => md5($wallet_pw),
            'f_create_time' => date("Y-m-d H:i:s", time())
        );

        $this->load->service('coin/coin_service');
        $res = $this->coin_service->add_coin_list($this->conn, $array);

        if ($res) {
            render_json($res);
        }
        render_json(0, '', '', 0);

    }

    /**
     * @fun update_coin 更新币种详情
     * @abbreviation
     */
    public function update_coin()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $coin_id = get_post_valueI('coin_id');

        if ($coin_id < 10000) {
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('coin/coin_service');
        $res = $this->coin_service->get_coin_info($this->conn, $coin_id);

        if (!is_array($res)) {
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->coin_service->coin_errcode['COIN_GET_DATA_ERR']);
        }

        $abbreviation = get_post_value('abbreviation');
        $coin_e_name = get_post_value('coin_e_name');
        $open_price = get_post_value('open_price');
        $commission = get_post_value('commission');
        $coin_name = get_post_value('coin_name');
        $creater = get_post_value('creater');
        $block_time = get_post_value('block_time');
        $publish_time = get_post_value('publish_time');
        $last_total_vol = get_post_value('last_total_vol');
        $core_algorithm = get_post_value('core_algorithm');
        $desc = get_post_value('desc');
        $icon = get_post_value('icon');
        $key_buttom_1 = get_post_value('key_buttom_1');
        $key_buttom_2 = get_post_value('key_buttom_2');
        $key_buttom_3 = get_post_value('key_buttom_3');
        $key_buttom_4 = get_post_value('key_buttom_4');
        $del_state = get_post_value('del_state');
        $market_type = get_post_valueI('market_type');
        $atm_rate = get_post_value('atm_rate');
        $high_price_24 = get_post_value('high_price_24');
        $low_price_24 = get_post_value('low_price_24');
        $deal_vol_24 = get_post_value('deal_vol_24');
        $rate_change_24 = get_post_value('rate_change_24');
        $last_price = get_post_value('last_price');
        $close_price = get_post_value('close_price');
        $wallet_ip = get_post_value('wallet_ip');
        $wallet_port = get_post_value('wallet_port');
        $wallet_user = get_post_value('wallet_user');
        $wallet_pw = get_post_value('wallet_pw');
        if(!in_array($del_state,array(0,1))){
            cilog('error', "del_state 参数错误 [del_state:{$del_state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!in_array($market_type,array(1,2)) && $market_type !== 0 ){
            cilog('error', "market_type 参数错误 [market_type:{$market_type}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        cilog('debug', "参数信息：[abbreviation:{$abbreviation}]
                  [coin_e_name:{$coin_e_name}] [open_price:{$open_price}] [commission:{$commission}] 
                  [coin_name:{$coin_name}] [creater:{$creater}] [block_time:{$block_time}] [publish]:{$publish_time}
                  [last_total_vol:{$last_total_vol}] [core_algorithm:{$core_algorithm}] [desc:{$desc}][icon:{$icon}]
                  [key_buttom_1:{$key_buttom_1}] [key_buttom_2:{$key_buttom_2}] [key_buttom_3:{$key_buttom_3}]
                  [key_buttom_4:{$key_buttom_4}] [del_state:{$del_state}] [market_type:{$market_type}]
                  [atm_rate:{$atm_rate}] [high_price_24:{$high_price_24}] [low_price_24:{$low_price_24}]
                  [deal_vol_24:{$deal_vol_24}] [rate_change_24:{$rate_change_24}] [last_price:{$last_price}]
                  [close_price:{$close_price}] [wallet_ip:{$wallet_ip}] [wallet_port:{$wallet_port}]
                  [user:{$wallet_user}][pw:{$wallet_pw}]",$this->log_filename);

        // 参数校验

        $attributes = array(
            'f_abbreviation' =>  ($abbreviation === '') ? $res['f_abbreviation'] : $abbreviation,

            'f_coin_e_name' => ($coin_e_name === '') ? $res['f_coin_e_name'] : $coin_e_name,
            'f_commission' => ($commission === '') ? $res['f_commission'] : $commission,
            'f_open_price' => ($open_price === '') ? $res['f_open_price'] : $open_price,
            'f_coin_name' => ($coin_name === '') ? $res['f_coin_name'] : $coin_name,
            'f_creater' => ($creater === '') ? $res['f_creater'] : $creater,
            'f_block_time' => ($block_time === '') ? $res['f_block_time'] : $block_time,
            'f_publish_time' => ($publish_time === '') ? $res['f_publish_time'] : $publish_time,
            'f_last_total_vol' => ($last_total_vol === 0) ? $res['f_last_total_vol'] : $last_total_vol,
            'f_core_algorithm' => ($core_algorithm === '') ? $res['f_core_algorithm'] : $core_algorithm,
            'f_desc' => ($desc === '') ? $res['f_desc'] : $desc,
            'f_icon' => ($icon === '') ? $res['f_icon'] : $icon,
            'f_key_buttom_1' => ($key_buttom_1 === '') ? $res['f_key_buttom_1'] : $key_buttom_1,
            'f_key_buttom_2' => ($key_buttom_2 === '') ? $res['f_key_buttom_2'] : $key_buttom_2,
            'f_key_buttom_3' => ($key_buttom_3 === '') ? $res['f_key_buttom_3'] : $key_buttom_3,
            'f_key_buttom_4' => ($key_buttom_4 === '') ? $res['f_key_buttom_4'] : $key_buttom_4,
            'f_del_state' => ($del_state === '') ? $res['f_del_state'] : $del_state,
            'f_market_type' => ($market_type === 0) ? $res['f_market_type'] : $market_type,
            'f_atm_rate' => ($atm_rate === '') ? $res['f_atm_rate'] : $atm_rate,
            'f_high_price_24' => ($high_price_24 === '') ? $res['f_high_price_24'] : $high_price_24,
            'f_low_price_24' => ($low_price_24 === '') ? $res['f_low_price_24'] : $low_price_24,
            'f_deal_vol_24' => ($deal_vol_24 === '') ? $res['f_deal_vol_24'] : $deal_vol_24,
            'f_rate_change_24' => ($rate_change_24 === '') ? $res['f_rate_change_24'] : $rate_change_24,
            'f_last_price' => ($last_price === '') ? $res['f_last_price'] : $last_price,
            'f_close_price' => ($close_price === '') ? $res['f_close_price'] : $close_price,
            'f_wallet_ip' => ($wallet_ip === '') ? $res['f_wallet_ip'] : $wallet_ip,
            'f_wallet_port' => ($wallet_port === '') ? $res['f_wallet_port'] : $wallet_port,
            'f_user' => ($wallet_user === '') ? $res['f_user'] : $wallet_user,
            'f_pw' => ($wallet_pw === '') ? $res['f_pw'] : $wallet_pw,
        );

        $where = array(
            'f_coin_id' => $coin_id
        );

        $result = $this->coin_service->update_coin_info($this->conn, $attributes, $where);

        if ($result === 0) {
            render_json($result);
        }
        render_json($this->coin_service->coin_errcode['COIN_UPDATE_DATA_ERR']);

    }


    /**
     * 查询具体一条币种
     * @fun find_coin_detail
     */
    public function find_coin_detail()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();
        $coin_id = get_post_valueI('coin_id');

        if ($coin_id < 10000) {
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }




        $this->load->service('coin/coin_service');

        $res = $this->coin_service->get_coin_info($this->conn, $coin_id);

        if (!is_array($res)) {
            render_json($this->coin_service->coin_errcode['COIN_GET_DATA_ERR']);
        }
        $_res = array(
            'coin_id' => $res['f_coin_id'],
            'coin_name' => $res['f_coin_name'],
            'coin_e_name' => $res['f_coin_e_name'],
            'abbreviation' => $res['f_abbreviation'],
            'creater' => $res['f_creater'],
            'block_time' => $res['f_block_time'],
            'publish_time' => $res['f_publish_time'],
            'last_total_vol' => $res['f_last_total_vol'],
            'desc' => $res['f_desc'],
            'icon' => $res['f_icon'],
            'core_algorithm' => $res['f_core_algorithm'],
            'key_buttom_1' => $res['f_key_buttom_1'],
            'key_buttom_2' => $res['f_key_buttom_2'],
            'key_buttom_3' => $res['f_key_buttom_3'],
            'key_buttom_4' => $res['f_key_buttom_4'],
            'del_state' => $res['f_del_state'],
            'market_type' => $res['f_market_type'],
            'commission' => $res['f_commission'],
            'atm_rate' => $res['f_atm_rate'],
            'high_price_24' => number_format($res['f_high_price_24'], 3),
            'low_price_24' => number_format($res['f_low_price_24'], 3),
            'deal_vol_24' => $res['f_deal_vol_24'],
            'rate_change_24' => $res['f_rate_change_24'],
            'wallet_ip' => $res['f_wallet_ip'],
            'wallet_port' => $res['f_wallet_port'],
            'wallet_user' => $res['f_user'],
            'wallet_pw' => $res['f_pw'],
            'last_price' => $res['f_last_price'],
            'close_price' => $res['f_close_price'],
            'open_price' => $res['f_open_price'],
            'create_time' => $res['f_create_time']

        );
        render_json(0, '', $_res);

    }

    /**
     * 获取币种列表信息
     * @fun find_coin_list
     */
    public function find_coin_list()
    {

        $this->init_log();
        $this->init_api();
        $this->check_admin_log();


        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;
        cilog('debug', "参数信息: [page:{$page}] [num:{$num}]",$this->log_filename);

        $this->load->service('coin/coin_service');

        $result = $this->coin_service->get_coin_list_($this->conn, $page, $num);


        if (!is_array($result)) {
            render_json($this->coin_service->coin_errcode['COIN_GET_DATA_ERR']);
        }

        $a = array();
        foreach ($result['rows'] as $row) {
            $b = array(
                'coin_id' => $row['f_coin_id'],
                'abbreviation' => $row['f_abbreviation'],
                'coin_e_name' => $row['f_coin_e_name'],
                'creater' => $row['f_creater'],
                'last_price' => $row['f_last_price'],
                'publish_time' => $row['f_publish_time'],
                'last_total_vol' => $row['f_last_total_vol'],
                'icon' => $row['f_icon'],
                'del_state' => $row['f_del_state']
            );
            array_push($a, $b);
        }
        render_json_list(0, '', $result['num'], $a);

    }



    /**
     * 订单部分
     */


    /**
     * @fun find_deal_list 订单查询
     *
     *
     *
     *
     * f_bdeal_id         int(8)        NO      PRI     0
     * f_export_id        varchar(16)   YES
     * f_uin              int(8)        YES             0
     * f_type             int(1)        YES             0
     * f_coin_id          int(8)        YES             0
     * f_coin_name        varchar(32)   YES
     * f_total_vol        double(64,8)  YES             0.00000000
     * f_total_money      double(64,8)  YES             0.00000000
     * f_post_deal_vol    double(64,8)  YES             0.00000000
     * f_post_deal_money  double(64,8)  YES             0.00000000
     * f_pre_deal_vol     double(64,8)  YES             0.00000000
     * f_pre_deal_money   double(64,8)  YES             0.00000000
     * f_state            int(1)        YES             0
     * f_price            double(64,8)  YES             0.00000000
     * f_create_time      timestamp     YES             (NULL)
     * f_modify_time      timestamp     YES             (NULL)
     *
     *
     */

    public function find_deal_list()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $state = get_post_valueI('state');
        $coin_id = get_post_valueI('coin_id');
        $export_id = get_post_value('export_id');
        $uin = get_post_valueI('uin');
        $begin_time = get_post_value('b_time');
        $end_time = get_post_value('e_time');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $where = array();

        if (!in_array($state, array(1, 2, 3, 100))) {
            cilog('error', "state 参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if ($coin_id < 10000) {
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        } else {
            $where['f_coin_id'] = $coin_id;
        }

        if ($state !== 100) {
            $where['f_state'] = $state;
        }

        if ($export_id !== '') {
            $where['f_export_id LIKE'] = "%" . $export_id . "%";
        }

        if (($uin > 10000)) {
            $where['f_uin LIKE'] = "%" . $uin . "%";
        }

        if ($begin_time !== '') {
            $begin_time = strtotime($begin_time);
            $where['UNIX_TIMESTAMP(f_create_time) >='] = $begin_time;
        }
        if ($end_time !== '') {
            $end_time = strtotime($end_time);
            $where['UNIX_TIMESTAMP(f_create_time) <='] = $end_time;
        }

        $this->load->service('deal/deal_service');

        $result = $this->deal_service->find_deal_list($this->conn, $where, $num, $page);

        if (!is_array($result)) {
            cilog('error', "订单获取错误",$this->log_filename);
            render_json_list();
        }

        $a = array();
        foreach ($result['rows'] as $row) {
            $b = array(
                'uin' => $row['f_uin'],
                'export_id' => $row['f_export_id'],
                'create_time' => $row['f_create_time'],
                'price' => number_format($row['f_price'], 3),
                'number' => number_format($row['f_pre_deal_vol'], 3),
                'state' => $row['f_state'],
                'coin_id' => $row['f_coin_id']
            );
            array_push($a, $b);
        }
        render_json_list(0, '', $result['totalNum'], $a);

    }


    /**
     * 订单详情
     */
    public function find_bdeal_detail()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $export_id = get_post_valueI('export_id');

        if ($export_id === '') {
            cilog('error', "[deal_id:{$export_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('deal/deal_service');

        $res = $this->deal_service->get_bdeal_info($this->conn, $export_id);

        if (!is_array($res)) {
            cilog('error', "查询订单详情失败",$this->log_filename);
            render_json($res);
        }

        $_res = array(
            'bdeal_id' => $res['f_bdeal_id'],
            'export_id' => $res['f_export_id'],
            'uin' => $res['f_uin'],
            'type' => $res['f_type'],
            'coin_id' => $res['f_coin_id'],
            'coin_name' => $res['f_coin_name'],
            'total_vol' => $res['f_total_vol'],
            'total_money' => $res['f_total_money'],
            'post_deal_vol' => $res['f_post_deal_vol'],
            'post_deal_money' => $res['f_post_deal_money'],
            'pre_deal_vol' => $res['f_pre_deal_vol'],
            'pre_deal_money' => $res['f_pre_deal_money'],
            'state' => $res['f_state'],
            'price' => $res['f_price'],
            'create_time' => $res['f_create_time']
        );
        render_json(0, '', $_res);
    }



    /**
     * 资产管理部分
     */

    /**
     * @fun
     * +---------------+--------------+------+-----+---------+-----------------------------+
     * | Field         | Type         | Null | Key | Default | Extra                       |
     * +---------------+--------------+------+-----+---------+-----------------------------+
     * | f_id          | int(8)       | NO   | PRI | NULL    | auto_increment              |
     * | f_uin         | int(8)       | YES  |     | NULL    |                             |
     * | f_coin_id     | int(8)       | YES  |     | 0       |                             |
     * | f_coin_abbr   | varchar(32)  | YES  |     |         |                             |
     * | f_coin_addr   | varchar(255) | YES  |     |         |                             |
     * | f_total_vol   | double(32,0) | YES  |     | 0       |                             |
     * | f_freeze_vol  | double(64,0) | YES  |     | 0       |                             |
     * | f_can_use_vol | double(64,0) | YES  |     | 0       |                             |
     * | f_create_time | timestamp    | YES  |     | NULL    |                             |
     * | f_modify_time | timestamp    | YES  |     | NULL    | on update CURRENT_TIMESTAMP |
     * +---------------+--------------+------+-----+---------+-----------------------------+
     *
     */
    public function get_finance()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $coin_id = get_post_valueI("coin_id");
        $uin = get_post_valueI('uin');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $begin_time = get_post_value('b_time');
        $end_time = get_post_value('e_time');

        $where = array();

        if ($begin_time !== '') {
            $begin_time = strtotime($begin_time);
            $where['UNIX_TIMESTAMP(f_create_time) >='] = $begin_time;
        }
        if ($end_time !== '') {
            $end_time = strtotime($end_time);
            $where['UNIX_TIMESTAMP(f_create_time) <='] = $end_time;
        }


        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;


        if ($coin_id < 10000) {
            cilog('error', "币种id 参数错误 [id:{$coin_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        } else {
            $where['f_coin_id'] = $coin_id;
        }

        if ($uin < 10000) {
            cilog('error', "uin 参数错误 [uin:{$uin}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        } else {
            $where['f_uin'] = $uin;
        }

        $this->load->service('finance/finance_service');

        $finance_list = $this->finance_service->get_finance($this->conn, $where, $num, $page);

        if (!is_array($finance_list)) {
            render_json_list(0, '', 0, '');
        }

        $data = array();
        foreach ($finance_list['rows'] as $row) {
            $a = array(
                'coin_id' => $row['f_coin_id'],
                'uin' => $row['f_uin'],
                'coinName' => $row['f_coin_abbr'],
                'coinVol' => $row['f_total_vol'],
                'freezeVol' => $row['f_freeze_vol'],
                'canUseVol' => $row['f_can_use_vol'],
                'coinAddr' => $row['f_coin_addr'],
            );
            array_push($data, $a);
        }

        render_json_list(0, '', $finance_list['total'], $data);


    }

    /**
     * @fun 提币记录 find_finance_log
     *+-------------------+--------------+------+-----+------------+-----------------------------+
     *| Field             | Type         | Null | Key | Default    | Extra                       |
     *+-------------------+--------------+------+-----+------------+-----------------------------+
     *| f_id              | int(8)       | NO   | PRI | NULL       | auto_increment              |
     *| f_type            | int(1)       | YES  |     | 0          |                             |
     *| f_uin             | int(8)       | YES  |     | 0          |                             |
     *| f_coin_id         | int(5)       | YES  |     | NULL       |                             |
     *| f_coin_addr       | varchar(64)  | YES  |     |            |                             |
     *| f_coin_key        | varchar(256) | YES  |     |            |                             |
     *| f_vol             | double(64,8) | YES  |     | 0.00000000 |                             |
     *| f_state           | int(1)       | YES  |     | 0          |                             |
     *| f_atm_rate_vol    | double(64,8) | YES  |     | 0.00000000 |                             |
     *| f_real_revice_vol | double(64,8) | YES  |     | 0.00000000 |                             |
     *| f_create_time     | timestamp    | YES  |     | NULL       |                             |
     *| f_modify_time     | timestamp    | YES  |     | NULL       | on update CURRENT_TIMESTAMP |
     *+-------------------+--------------+------+-----+------------+-----------------------------+
     * 提币记录
     */
    public function find_finance_log()
    {
        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $state = get_post_valueI('state');
        $coin_id = get_post_valueI('coin_id');
        $uin = get_post_valueI('uin');
        $begin_time = get_post_value('b_time');
        $end_time = get_post_value('e_time');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;


        // state 1 成功 2失败 3审核中 100 全部

        $this->load->service('finance/finance_service');

        $where = array(
            'f_type' => $this->finance_service->finance_type['COIN_OUT'], //提币
        );


        if (!in_array($state, array(1, 2, 3, 100))) {
            cilog('error', "state 参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if ($state !== 100) {
            $where['f_state'] = $state;
        }

        if ($coin_id < 10000) {
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        } else {
            $where['f_coin_id'] = $coin_id;
        }

        if ($uin !== 0) {
            $where['f_uin'] = $uin;
        }

        if ($begin_time) {
            $begin_time = strtotime($begin_time);
            $where['UNIX_TIMESTAMP(f_create_time) >='] = $begin_time;
        }
        if ($end_time) {
            $end_time = strtotime($end_time);
            $where['UNIX_TIMESTAMP(f_create_time) <='] = $end_time;
        }

        $this->load->service('finance/finance_service');

        $finance_list = $this->finance_service->_get_finance($this->conn, $where, $num, $page);

        if (!is_array($finance_list)) {
            cilog('error',$finance_list,$this->log_filename);
            render_json_list(0,'',0,'');
        }
        $data = array();
        foreach ($finance_list['rows'] as $row) {
            $a = array(
                'id'=>$row['f_id'],
                'uin' => $row['f_uin'],
                'coin_id' => $row['f_coin_id'],
                'coin_addr' => $row['f_coin_addr'],
                'vol' => number_format($row['f_vol'], 3),
                'state' => $row['f_state'],
                'atm_rate_vol' => $row['f_atm_rate_vol'],
                'real_revice_vol' => number_format($row['f_real_revice_vol'], 3),
                'create_time' => $row['f_create_time'],
            );
            array_push($data, $a);
        }

        render_json_list(0, '', $finance_list['total'], $data);


    }


    /**
     * @fun 充币交易记录 _find_finance_log
     */
    public function find_finance_charge()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $state = get_post_valueI('state');
        $coin_id = get_post_valueI('coin_id');
        $uin = get_post_valueI('uin');
        $begin_time = get_post_value('b_time');
        $end_time = get_post_value('e_time');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;


        // state 1 成功 2失败 3审核中 100 全部

        $this->load->service('finance/finance_service');

        $where = array(
            'f_type' => $this->finance_service->finance_type['COIN_IN'], //充币
        );


        if (!in_array($state, array(1, 2, 3, 100))) {
            cilog('error', "state 参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if ($state !== 100) {
            $where['f_state'] = $state;
        }

        if ($coin_id < 10000) {
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        } else {
            $where['f_coin_id'] = $coin_id;
        }

        if ($uin !== 0) {
            $where['f_uin'] = $uin;
        }

        if ($begin_time) {
            $begin_time = strtotime($begin_time);
            $where['UNIX_TIMESTAMP(f_create_time) >='] = $begin_time;
        }
        if ($end_time) {
            $end_time = strtotime($end_time);
            $where['UNIX_TIMESTAMP(f_create_time) <='] = $end_time;
        }

        $this->load->service('finance/finance_service');

        $finance_list = $this->finance_service->_get_finance($this->conn, $where, $num, $page);

        if (!is_array($finance_list)) {
            render_json_list(0, '', 0, '');
        }
        $data = array();
        foreach ($finance_list['rows'] as $row) {
            $a = array(
                'uin' => $row['f_uin'],
                'coin_id' => $row['f_coin_id'],
                'coin_addr' => $row['f_coin_addr'],
                'vol' => number_format($row['f_vol'], 3),
                'state' => $row['f_state'],
                'atm_rate_vol' => $row['f_atm_rate_vol'],
                'real_revice_vol' => number_format($row['f_real_revice_vol'], 3),
                'create_time' => $row['f_create_time'],
            );
            array_push($data, $a);
        }

        render_json_list(0, '', $finance_list['total'], $data);
    }


    /**
     * @fun  check_finance_state 提币审核
     *
     */
    public function check_finance_state()
    {

        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $state = get_post_valueI('state');// 1通过 2 不通过
        $order_id = get_post_valueI('id');
        $uin = get_post_valueI('uin');
        $coin_id = get_post_valueI('coin_id');

        // 参数校验
        if($state === 0){
            cilog('error', "state 参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($order_id === 0){
            cilog('error', "orderid 参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($uin < 10000){
            cilog('error', "uin 参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($coin_id < 10000){
            cilog('error', "coin_id 参数错误!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->model("finance/Model_t_finance_log");
        $this->load->service("finance/finance_service");
        $finance_log = $this-> Model_t_finance_log->find_by_attributes(
            $conn=$this->conn,
            $select = NULL,
            $tablename=$this->Model_t_finance_log->get_tablename(),
            $where = array(
                'f_id' => $order_id,
                'f_type' => $this->finance_service->finance_type['COIN_OUT']
            ),
            $sort = NULL
        );

        if(!$finance_log){
            cilog('error',"查不到提币记录信息！ [id:{$order_id}]",$this->log_filename);
            render_json();
        }

        // 校验用户
        if($finance_log['f_uin'] != $uin){
            cilog('error', "当前的记录的所属的uin和提交uin不一致! [uin:{$uin}] [log_uin:{$finance_log['f_uin']}]",$this->log_filename);
            render_json($this->finance_service->finance_errcode['FINANCE_LOG_NOT_SAME_UIN']);
        }

        // 校验单据状态
        if($finance_log['f_state'] != $this->finance_service->finance_state['DURING']){
            cilog('error',"当前的单据状态不为带审核! [state:{$finance_log['f_state']}]",$this->log_filename);
            render_json($this->finance_service->finance_errcode['FINANCE_LOG_STATE_ERR']);
        }

        // 获取用户财务信息
        $finance_info = $this->finance_service->get_finance_info($conn, $uin, $coin_id);
        if(!$finance_info){
            cilog('error',"获取财务信息失败",$this->log_filename);
            render_json($this->finance_service->finance_errcode['FINANCE_GET_DATA_ERR']);
        }

        if($state === $this->finance_service->finance_state['FAILED']){
            // 审核失败  扭转单据状态为失败,回退预冻结的金额,清除财务信息缓存
            $conn->trans_start();
            $this->Model_t_finance_log->update_all(
                $conn=$this->conn,
                $tablename=$this->Model_t_finance_log->get_tablename(),
                $attributes=array(
                    'f_modify_time' => timestamp2time(),
                    'f_state' => $this->finance_service->finance_state['FAILED']
                ),
                $where = array(
                    'f_id' => $order_id
                )
            );
            cilog('error', $this->finance_service->finance_state['FAILED'],$this->log_filename);
            $this->Model_t_finance_info->update_all(
                $conn=$this->conn,
                $tablename=$this->Model_t_finance_info->get_tablename(),
                $attributes=array(
                    'f_modify_time' => timestamp2time(),
                    'f_freeze_vol' => $finance_info['f_freeze_vol'] - $finance_log['f_vol'],
                    'f_can_use_vol' => $finance_info['f_can_use_vol'] + $finance_log['f_vol']
                ),
                $where = array(
                    'f_uin' => $uin,
                    'f_coin_id' => $coin_id,
                )
            );
            $conn->trans_complete();
            if ($conn->trans_status() === FALSE) {
                // $conn->trans_rollback();
                cilog('error', "更新币种数据失败,开始回滚数据!",$this->log_filename);
                render_json($this->finance_service->finance_errcode['FINANCE_UPDATE_DATA_ERR']);
            } else {
                // $conn->trans_commit();
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
                $this->cache->redis->delete($key);
                cilog('debug', "更新币种数据成功!",$this->log_filename);
                render_json();
            }
        }elseif($state === $this->finance_service->finance_state['SCUESS']){
            // 审核成功  扭转单据状态为成功,实际扣除财务信息,清除财务信息缓存
            $conn->trans_start();
            $this->Model_t_finance_log->update_all(
                $conn=$this->conn,
                $tablename=$this->Model_t_finance_log->get_tablename(),
                $attributes=array(
                    'f_modify_time' => timestamp2time(),
                    'f_state' => $this->finance_service->finance_state['SCUESS']
                ),
                $where = array(
                    'f_id' => $order_id
                )
            );
            $this->Model_t_finance_info->update_all(
                $conn=$this->conn,
                $tablename=$this->Model_t_finance_info->get_tablename(),
                $attributes=array(
                    'f_modify_time' => timestamp2time(),
                    'f_freeze_vol' => $finance_info['f_freeze_vol'] - $finance_log['f_vol'],
                    'f_can_use_vol' => $finance_info['f_can_use_vol'] - $finance_log['f_vol'],
                    'f_total_vol' => $finance_info['f_total_vol'] - $finance_log['f_vol'],
                ),
                $where = array(
                    'f_uin' => $uin,
                    'f_coin_id' => $coin_id,
                )
            );
            $conn->trans_complete();
            if ($conn->trans_status() === FALSE) {
                // $conn->trans_rollback();
                cilog('error', "更新币种数据失败,开始回滚数据!",$this->log_filename);
                render_json($this->finance_service->finance_errcode['FINANCE_UPDATE_DATA_ERR']);
            } else {
                // $conn->trans_commit();
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
                $this->cache->redis->delete($key);
                cilog('debug', "更新币种数据成功!",$this->log_filename);
                render_json();
            }
        }
    }


    /**
     * ico 模块
     */

    /**
     * @fun get_ico_list  获取ico列表
     *
     * state   【必填】ico状态 100 全部 1 活动中 2 待开始 3 已结束
     * page     当前页数
     * num      每页展示的总数
     * first    是否为精选项目 1 精选项目 2 非精选项目 100 全部
     */
    public function get_ico_list()
    {
        $this->init_log();
        $this->init_api();
        
        $this->check_admin_log();

        $state = get_post_valueI('state');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');
        $is_first = get_post_valueI('first');
        $is_display = get_post_value('is_display');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $this->load->service('ico/ico_service');

        $arr_list = array(1,2,3,100);

        if (!in_array($state,$arr_list)){
            cilog('error',"获取ico列表失败,状态参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        $aQuery = array();

        if ($state === 100){
            $aQuery['f_ico_state <>'] = 4;
        }else{
            $aQuery['f_ico_state'] = $state;
        }

        if($is_first === 1){
            $aQuery['f_is_first'] = 1;
        }elseif($is_first === 2){
            $aQuery['f_is_first'] = 2;
        }elseif($is_first === 100){

        }elseif($is_first ===0){

        }else{
            cilog('error',"is_first,状态参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        if($is_display ==='1' || $is_display ==='0'){
            $aQuery['f_is_display'] = $is_display;
        }


        $ico_list = $this->ico_service->get_ico_list($this->conn,$page,$num,$aQuery);
        if(!is_array($ico_list)){
            render_json_list(0,'',0,'');
        }

        $a = array();
        foreach ($ico_list['rows'] as $row){
            $b = array(
                'id' => $row['f_ico_id'],
                'state' => $row['f_ico_state'],
                'pic' => $row['f_pic'],
                'first' => $row['f_is_first'],
                'title' => $row['f_ico_title'],
                'desc' => mb_substr($row['f_desc'],0,30,'utf-8'),
                'abbr' => $row['f_abbreviation'],
                'total' => round($row['f_goal_vol'],3),
                'done' => round($row['f_done_vol'],3),
                'end' => $row['f_end_time'],
                'start' => $row['f_start_time'],
                'is_display' =>$row['f_is_display'],
                'ico_rate' => $row['f_ico_rate']
            );
            array_push($a,$b);
        }
        render_json_list(0,'',$ico_list['totalNum'],$a);
    }

    /**
     * @fun    获取ico详情
     *
     * id      ico_id
     */
    public function get_ico_detail()
    {
        $this->init_api();
        $this->init_log();
        
        $this->check_admin_log();

        $ico_id = get_post_valueI('id');

        $this->load->service('ico/ico_service');


        if ($ico_id < 10000){
            cilog('error',"ico_id错误 [id:{$ico_id}]",$this->log_filename);
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        $icoinfo = $this->ico_service->get_ico_info($this->conn,$ico_id);

        if(!is_array($icoinfo)){
            render_json($icoinfo,'');
        }

        // ico状态为删除状态时,直接返回空数组
        $flag = $this->ico_service->check_ico_state($icoinfo,$this->ico_service->ico_state['DEL']);

        if($flag === 0){
            render_json(0,'',array());
        }


        $rsp = array(
            'id' => $icoinfo['f_ico_id'],
            'state' => $icoinfo['f_ico_state'],
            'pic' => $icoinfo['f_pic'],
            'first' => $icoinfo['f_is_first'],
            'title' => $icoinfo['f_ico_title'],
            'desc' => $icoinfo['f_desc'],
            'abbr' => $icoinfo['f_abbreviation'],
            'total' => round($icoinfo['f_goal_vol'],3),
            'done' => round($icoinfo['f_done_vol'],3),
            'end' => $icoinfo['f_end_time'],
            'start' => $icoinfo['f_start_time'],
            'pro_desc' => filter_value($icoinfo['f_pro_desc'],1),
            'ico_detail' => filter_value($icoinfo['f_ico_detail'],1),
            'team_desc' => filter_value($icoinfo['f_team_desc'],1),
            'problem' => filter_value($icoinfo['f_ico_problem'],1),
            'coin_id' => $icoinfo['f_coin_id'],
            'is_display' => $icoinfo['f_is_display'],
            'ico_rate' => $icoinfo['f_ico_rate']
        );
        render_json(0,'',$rsp);
    }

    /**
     * @fun  add_ico_list
     *
     * +----------------+--------------+------+-----+------------+-----------------------------+
    | Field          | Type         | Null | Key | Default    | Extra                       |
    +----------------+--------------+------+-----+------------+-----------------------------+
    | f_ico_id       | int(8)       | NO   | PRI | 0          |                             |
    | f_ico_title    | varchar(64)  | YES  |     |            |                             |
    | f_desc         | varchar(128) | YES  |     |            |                             |
    | f_goal_vol     | double(64,8) | YES  |     | 0.00000000 |                             |
    | f_done_vol     | double(64,8) | YES  |     | 0.00000000 |                             |
    | f_start_time   | timestamp    | YES  |     | NULL       |                             |
    | f_end_time     | timestamp    | YES  |     | NULL       |                             |
    | f_is_first     | int(1)       | YES  |     | 0          |                             |
    | f_pic          | varchar(512) | YES  |     |            |                             |
    | f_pro_desc     | text         | YES  |     | NULL       |                             |
    | f_ico_detail   | text         | YES  |     | NULL       |                             |
    | f_team_desc    | text         | YES  |     | NULL       |                             |
    | f_ico_problem  | text         | YES  |     | NULL       |                             |
    | f_ico_state    | int(1)       | YES  |     | 0          |                             |
    | f_coin_id      | int(8)       | YES  |     | 0          |                             |
    | f_abbreviation | varchar(64)  | YES  |     | NULL       |                             |
    | f_create_time  | timestamp    | YES  |     | NULL       |                             |
    | f_modify_time  | timestamp    | YES  |     | NULL       | on update CURRENT_TIMESTAMP |
    +----------------+--------------+------+-----+------------+-----------------------------+
     */
    public function add_ico(){

        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        // 获取参数
        $ico_title = get_post_value('title');
        $desc = get_post_value('desc');
        $coin_id = get_post_valueI('coin_id');
        $goal_vol = get_post_value('total');    // 浮点数
        $start_time = get_post_value('start');
        $end_time = get_post_value('end');
        $pic = get_post_value('pic');           // url
        $ico_detail = get_post_value('ico_detail');
        $pro_desc = get_post_value('pro_desc');
        $team_desc = get_post_value('team_desc');
        $ico_problem = get_post_value('problem');
        $is_first = get_post_valueI('first');
        $is_display = get_post_value('is_display');
        $ico_rate = get_post_value('ico_rate');

        $ico_info = array(
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
            'f_done_vol' => 0
        );

        // 参数校验
        if((strlen($ico_title) < 1) OR (iconv_strlen($ico_title) > 64)){
            cilog('error',"参数错误 [title:{$ico_title}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_ico_title'] = $ico_title;

        if((strlen($desc) < 1) OR (iconv_strlen($desc) > 512)){
            cilog('error',"ico描述参数错误 [desc:{$desc}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_desc'] = $desc;

        if(((int)$goal_vol <= 0) OR ($goal_vol === '')){
            cilog('error',"ico目标总量参数错误 [goal:{$goal_vol}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_goal_vol'] = $goal_vol;

        if((iconv_strlen($pic) <= 32) OR ($pic === '')){
            cilog('error',"ico图片参数错误 [pic:{$pic}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_pic'] = $pic;

        if((strlen($ico_detail) <= 1) OR ($ico_detail === '')){
            cilog('error',"ico详情参数错误 [ico_detail:{$ico_detail}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_ico_detail'] = $ico_detail;

        if((strlen($pro_desc) <= 1) OR ($pro_desc === '')){
            cilog('error',"ico详情规则参数错误 [pro_desc:{$pro_desc}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_pro_desc'] = $pro_desc;

        if((strlen($team_desc) <= 1) OR ($team_desc === '')){
            cilog('error',"ico团队详情数据错误 [team_desc:{$team_desc}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_team_desc'] = $team_desc;

        if((strlen($ico_problem) <= 1) OR ($ico_problem === '')){
            cilog('error',"ico问题简介数据错误 [ico_problem:{$ico_problem}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_ico_problem'] = $ico_problem;

        if(!in_array($is_first,array(1,2))){
            cilog('error',"ico是否优先状态错误 [ico_first:{$is_first}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_is_first'] = $is_first;

        if($is_display === ''){
            cilog('error',"参数不能为空\$is_dispaly:[$is_display]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(($is_display !== '0') && ($is_display !== '1')){
            cilog('error',"参数错误 [is_display:{$is_display}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $ico_info['f_is_display'] = $is_display;

        if($ico_rate === ''){
            cilog('error',"ico_rate不能为空 [ico_rate:{$ico_rate}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_ico_rate'] = $ico_rate;


        $start = time2timestamp($start_time);
        $end = time2timestamp($end_time);
        if(($start === '') OR ($end === '') OR ($start > $end)){
            cilog('error',"ico开始结束时间错误! [start:{$start_time}] [end:{$end_time}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $ico_info['f_start_time'] = $start_time;
        $ico_info['f_end_time'] = $end_time;

        // 确认活动状态
        $nowtime = time2timestamp();
        $this->load->service("ico/ico_service");
        if($nowtime < time2timestamp($start_time)){
            // 未开始
            $ico_info['f_ico_state'] = $this->ico_service->ico_state['TO_BE_START'];
        }elseif (($nowtime >= $start) && ($nowtime < $end)){
            // 活动中
            $ico_info['f_ico_state'] = $this->ico_service->ico_state['DURING'];
        }elseif ($nowtime >= $end){
            // 已结束
            $ico_info['f_ico_state'] = $this->ico_service->ico_state['DONE'];
        }else{
            cilog('error',"ico开始结束时间错误! [start:{$start_time}] [end:{$end_time}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 获取币种信息
        $ico_info['f_coin_id'] = $coin_id;
        $this->load->service('coin/coin_service');
        $coin_info = $this->coin_service->get_coin_info($this->conn, $coin_id);
        if (!is_array($coin_info)) {
            render_json($this->coin_service->coin_errcode['COIN_GET_DATA_ERR']);
        }else{
            $ico_info['f_abbreviation'] = $coin_info['f_abbreviation'];
        }

        $this->load->model("conf/Model_t_idmaker");
        $ico_id = $this->Model_t_idmaker->get_id_from_idmaker($this->conn,'ICO_INFO_ID');

        if($ico_id){
            $ico_info['f_ico_id'] = $ico_id;
        }

        $this->load->model("ico/Model_t_ico_info");
        $flag = $this->Model_t_ico_info->save(
            $this->conn,
            $this->Model_t_ico_info->get_tablename(),
            $ico_info
        );

        if($flag === FALSE){
            render_json($flag);
        }else{
            render_json();
        }
    }


    /**
     * @fun update_ico_info
     */
    public function update_ico_info(){

        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $ico_id = get_post_valueI('ico_id');
        $abbreviation = get_post_value('abbr');
        $state = get_post_value('state');
        $is_first = get_post_value('first');
        $ico_title = get_post_value('title');
        $desc = get_post_value('desc');
        $goal_vol = get_post_value('total');
        $done_vol = get_post_value('done');
        $start_time = get_post_value('start');
        $end_time = get_post_value('end');
        $pic = get_post_value('pic');
        $pro_desc = get_post_value('pro_desc');
        $ico_detail = get_post_value('ico_detail');
        $team_desc = get_post_value('team_desc');
        $ico_problem = get_post_value('problem');
        $coin_id = get_post_valueI('coin_id');
        $is_display = get_post_value('is_display');//此处不能用valueI,因为这样填0和没填无法区分
        $ico_rate = get_post_value('ico_rate');



        if ($ico_id < 10000) {
            cilog('error', "ico_id 参数错误 [ico_id:{$ico_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('ico/ico_service');
        $res = $this->ico_service->get_ico_info($this->conn, $ico_id);

        if (!is_array($res)) {
            cilog('error', "ico_id 参数错误 [ico_id:{$ico_id}]",$this->log_filename);
            render_json($this->ico_service->ico_errcode['ICO_GET_ICO_INFO_ERR']);
        }

        if(($coin_id !== 0) && ($coin_id < 10000)){
            cilog('error', "coin_id 参数错误 [coin_id:{$coin_id}]",$this->log_filename);
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }


        if($abbreviation !== '' && $coin_id !== 0){
           $this->load->service('coin/coin_service');
           $where = array(
               'f_abbreviation' => $abbreviation,
               'f_coin_id' =>$coin_id
           );
           $flag = $this->coin_service->check_abbreviation($this->conn,$where);

           if((int)$flag !== 1){
                cilog('error',"abbr 和 coin_id 不一致");
                render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
           }
        }

        if($abbreviation === '' && $coin_id !== 0){
                cilog('error',"abbr 和 coin_id 不一致");
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        if($abbreviation !== '' && $coin_id === 0){
                cilog('error',"abbr 和 coin_id 不一致");
            render_json($this->conf_errcode['PARAM_ERR']);
        }


        $attr = array(
            'f_abbreviation' => ($abbreviation === '') ? $res['f_abbreviation'] : $abbreviation,
            'f_ico_state' => ($state === '') ? $res['f_ico_state'] : $state,
            'f_is_first' => ($is_first === '') ? $res['f_is_first'] : $is_first,
            'f_ico_title' => ($ico_title === '') ? $res['f_ico_title']:$ico_title,
            'f_desc' => ($desc === '') ? filter_value($res['f_desc'],1) : $desc,
            'f_goal_vol' => ($goal_vol === '') ? $res['f_goal_vol'] : $goal_vol,
            'f_done_vol' => ($done_vol === '') ? $res['f_done_vol'] : $done_vol,
            'f_start_time' => ($start_time === '') ? $res['f_start_time'] : $start_time,
            'f_end_time' => ($end_time === '') ? $res['f_end_time'] : $end_time,
            'f_pic' => ($pic === '') ? $res['f_pic'] : $pic,
            'f_pro_desc' => ($pro_desc === '') ? filter_value($res['f_pro_desc'],1) : $pro_desc,
            'f_ico_detail' => ($ico_detail === '') ? filter_value($res['f_ico_detail'],1) : $ico_detail,
            'f_team_desc' => ($team_desc === '') ? filter_value($res['f_team_desc'],1) : $team_desc,
            'f_ico_problem' => ($ico_problem === '') ? filter_value($res['f_ico_problem'],1) : $ico_problem,
            'f_coin_id' => ($coin_id === 0) ? $res['f_coin_id'] : $coin_id,
            'f_is_display' => ($is_display === '')?$res['f_is_display'] : $is_display,
            'f_ico_rate'  => ($ico_rate === '') ? $res['f_ico_rate'] : $ico_rate,
        );



        $this->load->service('ico/ico_service');
        $res = $this->ico_service->update_ico_info_($this->conn,$attr,$ico_id);
        if($res !== 0){
            render_json($this->ico_service->ico_errcode['ICO_UPDATE_INFO_ERR']);
        }else{
            render_json($res);
        }
    }

    /**
     * 获取ico查询结果列表
     *
     * ico_id     ico id
     * state      ico 发放状态 1 发放成功 2 发放失败 3 未发放  100 全部
     * page       页码
     * num        当前每页最大数量
     */
    public function get_ico_result_list()
    {
        $this->init_log();
        $this->init_api();

        $ico_id = get_post_valueI('id');
        $state = get_post_valueI('state');
        $num = get_post_valueI('num');
        $page = get_post_valueI('page');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        cilog('debug',"param [coin_id:{$ico_id}] [state:{$state}]",$this->log_filename);

        // 参数不能全为空
        if(($ico_id===0) && ($state===0)){
            cilog('error',"查询ico结果列表时,参数全部为0!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $where = array();
        if($ico_id > 10000){
            $where['f_ico_id'] = $ico_id;
        }else{
            cilog('error',"查询ico结果列表时,参数错误! [id:{$ico_id}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($state === 3){
            $where['f_state'] = 0;
        }elseif ($state === 1){
            $where['f_state'] = 1;
        }elseif ($state === 2){
            $where['f_state'] = 2;
        }elseif ($state !== 100){
            cilog('error',"参数错误 [state:{$state}]!",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->model('ico/Model_t_ico_log');
        $count = $this->Model_t_ico_log->count(
            $this->conn,
            $this->Model_t_ico_log->get_tablename(),
            $where
        );
        if((int)$count === 0){
            cilog('error',"查询结果为空!",$this->log_filename);
            render_json_list();
        }

        $list = $this->Model_t_ico_log->find_all(
            $conn=$this->conn,
            $select=NULL,
            $tablename=$this->Model_t_ico_log->get_tablename(),
            $where,
            $limit = $num,
            $page = $page,
            $sort = 'f_create_time desc'
        );

        if(!$list){
            cilog('error',"查询结果为空!",$this->log_filename);
            render_json_list();
        }

        $a = array();
        foreach ($list as $row){
            $b = array(
                'ico_id' => $row['f_ico_id'],
                'uin' => $row['f_uin'],
                'name' => $row['f_ico_name'],
                'coin_id' => $row['f_coin_id'],
                'abbr' => $row['f_abbreviation'],
                'vol' => $row['f_buy_vol'],
                'state' => $row['f_state'],
                'addtime' => $row['f_create_time']
            );
            array_push($a,$b);
        }
        render_json_list(0,'',$count,$a);
    }


    /**
     * 后台用户管理模块
     */

    /**
     * @fun admin_login 后台管理员登录
     *
     */
    public function admin_login(){

        $this->init_log();
        // $this->init_api();

        $name = get_post_value('name');
        $pwd = get_post_value('password');
        $img_code = get_post_value('img_code');

        cilog('debug',"param: [name:{$name}] [pw:{$pwd}] [img_code:{$img_code}]",$this->log_filename);

        if(strlen($name) < 2){
            cilog('error',"用户名不合法  [name:{$name}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($pwd)){
            cilog('error',"密码格式不对 [pwd:{$pwd}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

       if(!$this->oValidator->isImgCode($img_code)){
           cilog('error',"验证码格式不对 [img_code:{$img_code}]",$this->log_filename);
           render_json($this->conf_errcode['PARAM_ERR']);
       }

        // 验证图片验证码
        $flag = $this->check_img_code($img_code);
        if ($flag != 0){
           render_json($flag);
        }

        //验证用户名是否存在
        $this->load->model('user/Model_t_admin');
        $table_name = $this->Model_t_admin->get_tablename();
        $res = $this->Model_t_admin->count($this->conn,$table_name,array('f_admin_user'=>$name));
        if((int)$res === 0){
            cilog('error',"此用户名不存在",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_NOT_EXSIT']);
        }

        $result = $this->Model_t_admin->find_by_attributes($this->conn,$select = NULL,$table_name,$where = array('f_admin_user'=>$name), $sort = NULL);

        //验证管理员是否被禁用
        if((int)$result['f_admin_state'] === 2){
            cilog('error',"此用户不允许登录",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_STATE_ERR']);
        }

        //验证密码是否一致
        $this->load->service('user/user_service');

        $res = $this->user_service->check_pw($result['f_admin_key'],$result['f_admin_pwd'],$pwd);
        if($res != 0){
            cilog('error',"密码不匹配有错误",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_PW_CHECK_ERR']);
        }

        // 写login session
        $uin = $result['f_admin_id'];
        $skey = create_guid('admin_session_key');
        $this->user_service->set_session_($uin,$skey);
        cilog('debug',"用户登陆成功",$this->log_filename);
        // header("Location: http://trade.98bit.com");
        render_json();
    }



    /**
     * @fun admin_logout
     *
     */
    public function admin_logout()
    {
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $oSession = $this->get_session();
        $this->user_service->loginout($oSession);

        cilog("debug","管理员退出成功",$this->log_filename);
        render_json();
    }


    public function admin_user_list(){
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();
    }


    /**
     * @fun admin_update_pwd
     */
    public function admin_update_pwd(){
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();
        $repwd = get_post_value('repwd');

        $this->load->model('user/Model_t_admin');

        $table_name = $this->Model_t_admin->get_tablename();

//        $res = $this->Model_t_admin->update_all($this->conn,$table_name,$attributes, $where = array());
        //判断是否是超级管理员 90000013 就是超级管理员id
        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;
        if((int($uin) == 90000013)){

        }

        $this->load->model("user/Model_t_admin");
        $admin_info = $this->Model_t_admin->find_by_attributes($this->conn,$select = NULL,$this->Model_t_admin->get_tablename(),$where = array('f_admin_id'=>$uin), $sort = NULL);
        if(!$admin_info){
            cilog('error',"查不到该管理者信息！ [uin:{$uin}]",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_NOT_EXSIT']);
        }




    }


    /**
     * add_admin
     * 添加环境过滤
     */
    public function add_admin(){
        $this->init_log();
        $this->init_api();

//        if(ENVIRONMENT !== "development"){
//            // 不在测试环境下,直接报错404
//            show_404();
//        }

        $name = get_post_value('name');
        $pw = get_post_value('pw');

        $this->load->service('user/user_service');
        $this->load->model("conf/Model_t_idmaker");
        $uin = $this->Model_t_idmaker->get_id_from_idmaker($this->conn,'ADMIN_ID');
        if (!$uin){
            return $this->user_errcode['USER_GET_IDMAKER_ERR'];
        }
        $str_password=md5($pw);
        $pw_key = create_guid("user_key");
        cilog('debug',$pw_key,$this->log_filename);

        $last_pw = $this->user_service->get_last_pw($pw_key,$str_password);
        cilog('error','生成的最终的密码:'.$last_pw,$this->log_filename);

        $admin_info = array(
            'f_admin_id' =>$uin,
            'f_admin_user' => $name,
            'f_admin_pwd' => $last_pw,
            'f_admin_key' => $pw_key,
//            'f_admin_rule' =>'1,2,3,4',
            'f_admin_state' => 1,
            'f_create_time' =>timestamp2time(),
            'f_modify_time' =>timestamp2time(),
        );
        $this->load->model("user/Model_t_admin");
        $res = $this->Model_t_admin->save($this->conn,$this->Model_t_admin->get_tablename(),$admin_info);
        if($res === FALSE){
            cilog('debug',"添加admin管理员失败! [res:{$res}]",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_CREAT_ERR']);
        }
        render_json(0,'ok');
    }


    /**
     * 抽奖活动模块
     */

    /**
     * 获取抽奖结果
     *
     * state   发送状态  1 已发送 2 未发放 100 全部
     */
    public function get_lottery_result()
    {
        $this->init_log();
        $this->init_api();

        $state = get_post_valueI('state');
        $num = get_post_valueI('num');
        $page = get_post_valueI('page');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $state_list = array(1,2,100);
        if(!in_array($state,$state_list)){
            cilog('error',"参数错误 [state:{$state}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $where = array();
        if($state === 2){
            $where['f_is_send'] = 0;
        }elseif ($state === 1){
            $where['f_is_send'] = 1;
        }

        $this->load->model('act/Model_t_lottery_bingo_list');
        $count = $this->Model_t_lottery_bingo_list->count(
            $this->conn,
            $this->Model_t_lottery_bingo_list->get_tablename(),
            $where
        );
        if((int)$count === 0){
            cilog('error',"查询结果为空!",$this->log_filename);
            render_json_list();
        }

        $list = $this->Model_t_lottery_bingo_list->find_all(
            $conn=$this->conn,
            $select=NULL,
            $tablename=$this->Model_t_lottery_bingo_list->get_tablename(),
            $where,
            $limit = $num,
            $page = $page,
            $sort = 'f_create_time desc'
        );

        if(!$list){
            cilog('error',"查询结果为空!",$this->log_filename);
            render_json_list();
        }

        $a = array();
        foreach ($list as $row){
            $b = array(
                'uin' => $row['f_uin'],
                'level' => $row['f_prize_level'],
                'name' => $row['f_prize_name'],
                'state' => $row['f_is_send'],
                'email' => $row['f_email'],
                'addtime' => $row['f_create_time'],
            );
            array_push($a,$b);
        }
        render_json_list(0,'',$count,$a);
    }


    /**
     * 权限管理模块
     */

    /**
     * @fun   add_rule
     * @condition 非必填   不填代表添加即验证
     * @ 其他必填
     *
     */

    public function add_rule(){
        $this->init_log();
        $this->init_api();
        $data = array(
            "a"=>array(234,3,4),
            9999999
        );
        render_json(0,'',$data);

        $rule_name = get_post_value('title');
        $rule_mark = get_post_value('mark');
        $condition = get_post_value('condition');
        $status = get_post_value('status'); //是否禁用  0 禁用 1启用
        $module_id = get_post_valueI('mid');

        //参数验证
        if(($rule_name === '') || (iconv_strlen($rule_name)>32)){
            cilog("error","不能为空或者参数长度错误\$rule_name:[$rule_name]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(($rule_mark === '') || iconv_strlen($rule_mark)>64){
            cilog("error","不能为空或者参数长度错误\$rule_mark:[$rule_mark]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($condition !== '' && iconv_strlen($condition)>64 ){
            cilog('error',"参数长度错误\$condition:[$condition]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($status === ''){
            cilog('error',"status参数不能为空\$status:[$status]");
            render_json();
        }
        if($status !== '0' || $status !=='1'){
            cilog('error',"status参数错误,只能传1或者2 status:[$status]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        if($module_id === 0){
            cilog('error',"不能为空\$module_id:[$module_id]");
        }

        $this->load->service('auth/auth_service');
    }


    /**
     * @fun 后台统计页面 total
     *  统计收入：佣金+
     */

   public function stat_list(){
   }


    public function link_out_10(){

//       $a = file_get_contents("/data/logs/php/big/log-".date("Y-m-d",time()));
        $ip = getRealIpAddr();
        $key =$ip;
        $value = $this->cache->redis->get($key);
        if($value){
            $this->cache->redis->increment($key);
            $count = $this->cache->redis->get($key);
            if($count > 10){
                cilog('debug',"ip超出了访问次数,禁止访问! [ip:{$ip}]");
                $this->go_err();
            }
        }else{
            $this->cache->redis->increment($key);
            $value = $this->cache->redis->get($key);
            //限制时间是一个小时
            $this->cache->redis->save($key,$value,20);
        }
        $count = $this->cache->redis->get($key);
        echo '第 '.$count.' 次请求';

    }


    /**
     * 配置管理
     */

    // 获取配置信息
    public function get_conf_info()
    {
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $key = get_post_value('key');

        if(strlen($key) < 2){
            cilog('error',"参数错误 [key:{$key}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->model("conf/Model_t_conf");
        $data = $this->Model_t_conf->get_conf_info($this->conn,$key);
        render_json(0,'',$data);
    }

    // 更新配置信息
    public function update_conf_info()
    {
        $this->init_log();
        $this->init_api();
        $this->check_admin_log();

        $key = get_post_value('key');
        $value = get_post_value('value');

        if(strlen($key) < 2){
            cilog('error',"参数错误 [key:{$key}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!is_array($value)){
            cilog('error',"value参数错误,不是数组",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 当更新导航banner图片时
        $conf_info = array();
        if ($key === "banner_pic_info"){
            foreach ($value as $row){
                if(!isset($row['url']) OR !isset($row['click'])){
                    cilog('error',"value参数不和法",$this->log_filename);
                    cilog('error',$row,$this->log_filename);
                    render_json($this->conf_errcode['PARAM_ERR']);
                }
                $a = array(
                    'url' => $row['url'],
                    'click' => $this->oValidator->isUrl($row['click']) ? $row['click'] : "#",
                );
                array_push($conf_info,$a);
            }
        }else{
            cilog('error',"参数错误 [key:{$key}]",$this->log_filename);
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        // 开始更新数据
        $this->load->model("conf/Model_t_conf");
        $falg = $this->Model_t_conf->update_all(
            $conn=$this->conn,
            $tablename=$this->Model_t_conf->_tableName,
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_value' => serialize($conf_info),
            ),
            $where = array(
                'f_key' => $key
            )
        );
        $this->Model_t_conf->del_redis_key($key);
        render_json($falg);
    }

}
