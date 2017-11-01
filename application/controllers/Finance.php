<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Finance  资产管理模块
 */

require_once APPPATH . '/libraries/comm/captcha.php';
class Finance extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->get_db_conn();
        $this->load->service('finance/finance_service');
    }

    /**
     * @fun    获取用户的某一币种的资产详情
     * coinId   int    币种id(必填)
     */
    public function get_user_finance()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $coin_id = get_post_valueI("coinId");

        if ($coin_id < 10000){
            cilog('error',"币种id 参数错误 [id:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $user_finance = $this->finance_service->get_finance_info($this->conn,$uin,$coin_id);
        if(!is_array($user_finance)){
            render_json(0);
        }
        $rsp = array(
            'id' => $user_finance['f_coin_id'],
            'coinName' => $user_finance['f_coin_abbr'],
            'coinVol' => $user_finance['f_can_use_vol'] + $user_finance['f_freeze_vol'],
            'freezeVol' => $user_finance['f_freeze_vol'],
            'canUseVol' => $user_finance['f_can_use_vol'],
            'coinAddr' => $user_finance['f_coin_addr'],
        );
        render_json(0,'',$rsp);
    }

    /**
     * @fun     获取用户的所有币种的资产详情
     * page     int    当前页码
     * num      int    每页展示最大数据量
     */
    public function get_finance()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $num = get_post_valueI('num');
        $page = get_post_valueI('page');

        $num = (($num >0) && ($num <=50)) ? $num : 10;
        $page = (($page >=1) && ($page <=50)) ? $page : 1;

        $finance_list = $this->finance_service->get_uin_finance($this->conn,$uin,$num, $page);

        if(!is_array($finance_list)) {
            render_json_list($finance_list);
        }

        $data = array();
        foreach ($finance_list['rows'] as $row){
            $a = array(
                'id' => $row['f_coin_id'],
                'coinName' => $row['f_coin_abbr'],
                'coinVol' => $row['f_can_use_vol'] + $row['f_freeze_vol'],
                'freezeVol' => $row['f_freeze_vol'],
                'canUseVol' => $row['f_can_use_vol'],
                'coinAddr' => $row['f_coin_addr'],
            );
            array_push($data,$a);
        }

        render_json_list(0,'',$finance_list['total'],$data);
    }

    /**
     * @fun    获取用户流水信息
     * type     int    财务类型(必填)   0 默认 1 充币 2 提币 3 买入 4 卖出
     * coinId   int    币种id(必填)
     * page     int    当前页码
     * num      int    每页展示最大数据量
     */
    public function get_finance_log()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $num       = get_post_valueI('num');
        $page      = get_post_valueI('page');
        $type      = get_post_valueI('type');
        $coin_id   = get_post_valueI('coinId');

        $num = (($num >0) && ($num <=50)) ? $num : 10;
        $page = (($page >=1) && ($page <=50)) ? $page : 1;

        if ($coin_id < 10000){
            cilog('error',"币种id 参数错误 [id:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $list_type = array(
            $this->finance_service->finance_type['COIN_IN'],
            $this->finance_service->finance_type['COIN_OUT'],
            $this->finance_service->finance_type['BUY'],
            $this->finance_service->finance_type['SELL'],
        );
        if (!in_array($type,$list_type)){
            cilog('error',"财务流水类型错误 参数错误 [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $finance_log_list = $this->finance_service->get_uin_finance_log($this->conn,$uin,$type,$coin_id,$num,$page);
        if((int)$finance_log_list['total'] === 0) {
            // 找不到财务流水信息
            render_json_list();
        }

        $data = array();
        foreach ($finance_log_list['rows'] as $row){
            $a = array(
                'id' => $row['f_id'],
                'state' => $row['f_state'],
                'coinVol' => $row['f_vol'],
                'coinAddr' => $row['f_coin_addr'],
                'addtime' => $row['f_create_time'],
            );
            array_push($data,$a);
        }

        render_json_list(0,'',$finance_log_list['total'],$data);
    }

    /**
     * @fun    提币
     *
     * coin_addr   提币地址
     * coin_id     币种id
     * coin_num    提取数量
     * pw          交易密码
     */
    public function coin_out()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $coin_addr  = get_post_value('coin_addr');     // 提取的币种地址
        $coin_id    = get_post_valueI('coin_id');      // 币种id
        $coin_num   = get_post_value('coin_num');      // 提取数量
        $deal_pw    = get_post_value('pw');            // 交易密码

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        if(!$this->oValidator->isPw($deal_pw)){
            cilog('error',"密码格式不对 [pw:{$deal_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(strlen($coin_addr) <= 10){
            cilog('error',"币种地址格式不对,长度不能小于10位 [addr:{$coin_addr}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($coin_num < 0.01){
            cilog('error',"币种数量不对,不能小于0.01! [num:{$coin_num}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($coin_id === 0){
            cilog('error',"币种id格式不对 [id:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('user/user_serice');
        $userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($userinfo)){
            cilog('error','获取用户信息失败,提币失败!');
            render_json($this->user_service->finance_errcode['FINANCE_COIN_OUT_ERR']);
        }

        // 完成验证的用户才可以提币
        if($userinfo['f_state'] != $this->user_service->user_state['USER_REGISTER_SCU']){
            cilog('error',"用户状态不合法,无法提币 [state:{$userinfo['f_state']}]");
            render_json($this->finance_service->finance_errcode['FINANCE_USER_STATE_ERR']);
        }

        // 校验用户账号密码
        $flag = $this->user_service->check_pw($userinfo['f_key'],$userinfo['f_deal_pw'],$deal_pw);
        if($flag != 0 ){
            cilog('error',"用户账号密码错误!无法提币!");
            render_json($this->user_service->user_errcode['USER_PW_CHECK_ERR']);
        }

        // 获取币种信息
        $this->load->service("coin/coin_service");
        $coin_info = $this->coin_service->get_coin_info($this->conn, $coin_id);
        if(!$coin_info){
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 获取币种财务信息
        $finance_info = $this->finance_service->get_finance_info($this->conn, $uin, $coin_id);
        if(!is_array($finance_info)){
            cilog('error',"获取币种财务信息失败! [uin:{$uin}] [coinid:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if((int)$coin_id === 10001){
            $flag = $this->finance_service->btc_coin_out($this->conn,$coin_info,$finance_info,$userinfo,$coin_num,$coin_addr);
        }else{
            $market_finance_info = $this->finance_service->get_market_finance($this->conn,$uin);
            if(!is_array($market_finance_info)){
                render_json($this->conf_errcode['PARAM_ERR']);
            }
            $flag = $this->finance_service->coin_out_v($this->conn,$coin_info,$finance_info,$market_finance_info,$userinfo,$coin_num,$coin_addr);
        }

        if($flag != 0 ){
            render_json($flag);
        }else{
            render_json(0);
        }
    }

    /**
     * @fun    取消提币
     *
     * id   流水id
     * pw   交易密码
     */
    public function coin_out_cancel()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $coin_log_id = get_post_value('id');      // 流水id
        $deal_pw     = get_post_value('pw');      // 交易密码

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        if(!$this->oValidator->isPw($deal_pw)){
            cilog('error',"密码格式不对 [pw:{$deal_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->load->service('user/user_serice');
        $userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($userinfo)){
            cilog('error',"获取用户信息失败,提币失败! [uin:{$uin}]");
            render_json($this->user_service->finance_errcode['FINANCE_COIN_OUT_ERR']);
        }

        // 校验用户账号密码
        $flag = $this->user_service->check_pw($userinfo['f_key'],$userinfo['f_deal_pw'],$deal_pw);
        if($flag != 0 ){
            cilog('error',"用户账号密码错误!无法提币!");
            render_json($this->user_service->user_errcode['USER_PW_CHECK_ERR']);
        }

        // 通过流水id获取财务流水详情
        $finance_log = $this->finance_service->Model_t_finance_log->get_finance_log_by_id($this->conn,$coin_log_id);
        if(!is_array($finance_log)){
            cilog('error',"获取流水详情失败! [id:{$coin_log_id}]");
            render_json($this->finance_service->finance_errcode['FINANCE_GET_LOG_ERR']);
        }

        // 验证流水单中uin与当前用户是否一致
        if($finance_log['f_uin'] != $uin){
            cilog('error',"验证流水单中uin与当前用户不一致! [uin_user:{$uin}] [uin_log:{$finance_log['f_uin']}]");
            render_json($this->finance_service->finance_errcode['FINANCE_LOG_NOT_SAME_UIN']);
        }
        $coin_id = $finance_log['f_coin_id'];

        // 获取币种财务信息
        $finance_info = $this->finance_service->get_finance_info($this->conn, $uin, $coin_id);
        if(!is_array($finance_info)){
            cilog('error',"获取币种财务信息失败! [uin:{$uin}] [coinid:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if((int)$coin_id === 10001){
            $flag = $this->finance_service->cancel_btc_coin_out($this->conn,$finance_info,$userinfo,$finance_log);
        }else{
            $market_finance_info = $this->finance_service->get_market_finance($this->conn,$uin);
            if(!is_array($market_finance_info)){
                render_json($this->conf_errcode['PARAM_ERR']);
            }
            $flag = $this->finance_service->cancel_coin_out_v($this->conn,$finance_info,$market_finance_info,$userinfo,$finance_log);
        }

        if($flag !== 0){
            render_json($flag);
        }else{
            render_json();
        }
    }

}