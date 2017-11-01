<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class ico  ico模块
 */

// require_once APPPATH . '/libraries/comm/captcha.php';
class Ico extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->load->service('ico/ico_service');
    }

    // ico list页面
    public function pList()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('ico/ico');
    }

    // ico 详情页面
    public function pDetail()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('ico/detail');
    }

    /**
     * @fun    获取ico列表
     *
     * state   【必填】ico状态 100 全部 1 活动中 2 待开始 3 已结束
     * page     当前页数
     * num      每页展示的总数
     * first    是否为精选项目 1 精选项目 其他参数不处理
     */
    public function get_ico_list()
    {
        $this->init_api();

        $state = get_post_valueI('state');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');
        $is_first = get_post_valueI('first');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $arr_list = array(1,2,3,100);
        if (!in_array($state,$arr_list)){
            cilog('error',"获取ico列表失败,状态参数错误 [state:{$state}]");
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        if ($state === 100){
            $aQuery['f_ico_state <>'] = 4;
        }else{
            $aQuery['f_ico_state'] = $state;
        }

        if($is_first === 1){
            $aQuery['f_is_first'] = 1;
        }

        $ico_list = $this->ico_service->get_ico_list($this->conn,$page,$num,$aQuery);
        if(!is_array($ico_list)){
            render_json_list(0,'',0,'');
        }

        $a = array();
        foreach ($ico_list['rows'] as $row){
            if($row['f_is_display'] == 0){
                continue;
            }

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
    public function get_ico_info()
    {
        $this->init_api();

        $ico_id = get_post_valueI('id');


        if ($ico_id < 10000){
            cilog('error',"ico_id错误 [id:{$ico_id}]");
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        $icoinfo = $this->ico_service->get_ico_info($this->conn,$ico_id);
        if(!is_array($icoinfo)){
            render_json($icoinfo,'');
        }

        // ico状态为删除态时,直接返回空数组
        $flag = $this->ico_service->check_ico_state($icoinfo,$this->ico_service->ico_state['DEL']);
        if($flag === 0){
            render_json(0,'',array());
        }

        if($icoinfo['f_is_display'] == 0){
            render_json();
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
            'coinid' => $icoinfo['f_coin_id'],
            'ico_price' => $icoinfo['f_ico_rate'],
        );
        render_json(0,'',$rsp);
    }

    /**
     * @fun     参与ico
     *
     * id      【必填】ico项目id
     * num     【必填】ico购买数量
     * pw      【必填】交易密码
     */
    public function join_ico()
    {
        $this->init_api();
        $this->check_login();

        $vol = get_post_value('num');
        $ico_id = get_post_valueI('id');
        $del_pw = get_post_value('pw');

        if($vol === ''){
            cilog('error',"vol参数错误 [vol:{$vol}]");
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        if ($ico_id < 10000){
            cilog('error',"ico_id错误 [id:{$ico_id}]");
            render_json($this->ico_service->ico_errcode['ICO_PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($del_pw)){
            cilog('error',"密码格式不对 [pw:{$del_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        // 获取用户信息
        $arr_userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($arr_userinfo)){
            render_json($arr_userinfo);
        }

        // 检验用户密码是否正确
        $flag = $this->user_service->check_pw($arr_userinfo['f_key'],$arr_userinfo['f_deal_pw'],$del_pw);
        if($flag !== 0) {
            render_json($flag);
        }

        // 获取ico详情
        $icoinfo = $this->ico_service->get_ico_info($this->conn,$ico_id);
        if(!is_array($icoinfo)){
            render_json($icoinfo,'');
        }
        $coin_id = $icoinfo['f_coin_id'];

        // 校验活动状态
        $flag = $this->ico_service->check_ico_state($icoinfo,$this->ico_service->ico_state['DURING']);
        if($flag !== 0){
            cilog('error',"ico状态不为活动中状态,不可购买 [state:{$icoinfo['f_state']}]");
            render_json($flag);
        }

        // 检验活动剩余量是否足额
        $left_vol = $icoinfo['f_goal_vol'] - $icoinfo['f_done_vol'];
        if ($vol > $left_vol){
            cilog('error',"ico活动剩余量不足 [left:{$left_vol}] [need:{$vol}]");
            render_json($this->ico_service->ico_errcode['ICO_HAVE_NOT_ENOUCH_VOL']);
        }

        // 校验用户的基础货币是否足额
        $this->load->service("finance/finance_service");
        $this->load->service("coin/coin_service");
        $coininfo = $this->coin_service->get_coin_info($this->conn, $coin_id);
        // 获取市场币种财务信息
        $market_coin_id = get_coinid_by_marketid($coininfo['f_market_type']);
        $market_coin_finance_info = $this->Model_t_finance_info->find_by_attributes(
            $conn=$this->conn,
            $select = NULL,
            $tablename = $this->Model_t_finance_info->get_tablename(),
            $where = array(
                'f_uin'=>$uin,
                'f_coin_id'=>$market_coin_id
            ),
            $sort = NULL
        );
        $need = $vol * $icoinfo['f_ico_rate'];
        if($market_coin_finance_info['f_can_use_vol'] < $need){
            cilog('error',"用户余额不足 [left:{$market_coin_finance_info['f_can_use_vol']}] [need:{$need}]");
            render_json($this->finance_service->finance_errcode['FINANCE_GET_DATA_ERR']);
        }

        $this->conn->trans_start();
        // 开始冻结用户购买的金额
        $this->load->model("finance/Model_t_finance_info");
        $this->Model_t_finance_info->pre_reduce_finance($this->conn,$market_coin_finance_info,$need);
        // 更新ico的完成量
        $this->load->model("ico/Model_t_ico_info");
        $this->Model_t_ico_info->update_all(
            $conn=$this->conn,
            $tablename=$this->Model_t_ico_info->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_done_vol' => $icoinfo['f_done_vol'] + $vol,
            ),
            $where = array(
                'f_ico_id' => $icoinfo['f_ico_id'],
            )
        );

        // 添加ico成功记录
        $this->load->model("ico/Model_t_ico_log");
        $tablename = $this->Model_t_ico_log->get_tablename();
        $data = array(
            'f_uin' => $uin,
            'f_ico_id' => $ico_id,
            'f_coin_id' => $icoinfo['f_coin_id'],
            'f_abbreviation' => $icoinfo['f_abbreviation'],
            'f_ico_name' => $icoinfo['f_ico_title'],
            'f_buy_vol' => $vol,
            'f_state' => 0,
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_ico_log->save($this->conn,$tablename,$data);

        $this->conn->trans_complete();
        if ($this->conn->trans_status() === FALSE) {
            // $conn->trans_rollback();
            cilog('error', "参加ico失败,开始回滚数据!");
            render_json($this->ico_service->ico_errcode['ICO_JOIN_VOL_ERR']);
        } else {
            // $conn->trans_commit();
            cilog('debug', "参加ico成功!");
            render_json(0);
        }
    }

    /**
     * @fun     参与ico成功列表
     *
     * page     当前页数
     * num      每页展示的总数
     */
    public function ico_log()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $list = $this->ico_service->get_ico_log_list($this->conn,$page,$num,$uin);

        if(!is_array($list)){
            render_json_list(0,'',0,'');
        }

        $a = array();

        foreach ($list['rows'] as $row){
            $b = array(
                'pro_name' => $row['f_ico_name'],
                'coinname' => $row['f_abbreviation'],
                'vol' => $row['f_buy_vol'],
                'state' => ((int)$row['f_state'] === 1) ? TRUE : FALSE,
                'addtime' => $row['f_create_time'],
            );
            array_push($a,$b);
        }
        render_json_list(0,'',$list['totalNum'],$a);
    }

    /**
     * @fun   获取团队成员ico记录
     *
     * page     当前页数
     * num      每页展示的总数
     */
    public function get_member_ico_list()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        // 获取用户信息
        $userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($userinfo)){
            render_json($userinfo);
        }

        // 获取成员列表
        $member_list = $this->user_service->get_user_member_list($this->conn,$userinfo);
        if(!is_array($member_list)){
            render_json($member_list);
        }

        $uin_list = array();
        foreach ($member_list as $key => $value){
            array_push($uin_list,$key);
        }

        // 开始db查询
        $this->load->model('ico/model_t_ico_log');
        $count = $this->model_t_ico_log->count_in(
            $conn=$this->conn,
            $tablename=$this->model_t_ico_log->get_tablename(),
            $where = array(),
            $key = 'f_uin',
            $arr_data = $uin_list
        );

        if($count == 0){
            render_json_list(0,'',0,'');
        }

        $ico_list = $this->model_t_ico_log->find_all_in(
            $conn=$this->conn,
            $select=NULL,
            $tablename=$this->model_t_ico_log->get_tablename(),
            $where = array(),
            $key = 'f_uin',
            $arr_data = $uin_list,
            $limit = $num,
            $page = $page,
            $sort = 'f_create_time desc'
        );

        $a = array();
        foreach ($ico_list as $row){
            $b = array(
                'email' => $member_list[$row['f_uin']],
                'pro_name' => $row['f_ico_name'],
                'coinname' => $row['f_abbreviation'],
                'vol' => $row['f_buy_vol'],
                'state' => ((int)$row['f_state'] === 1) ? TRUE : FALSE,
                'addtime' => $row['f_create_time'],
            );
            array_push($a,$b);
        }
        render_json_list(0,'',$count,$a);
    }


    /**
     * 以下是定时任务
     */

    /**
     * @fun    更新ico状态
     *
     * 定时任务  每隔一小时跑一次,扭转ico项目的状态
     * 获取全量的ico信息数据
     * 与ico项目起始时间比对,更新状态
     */
    public function update_ico_state($key)
    {
        $now = timestamp2time();
        $filename = "corn_ico_";
        cilog('debug',"\n ==== 开始更新ico状态 ====",$filename);
        if($key !== "rasine"){
            cilog("error","密钥信息错误",$filename);
            return 0;
        }
        $this->load->model('ico/Model_t_ico_info');
        $tablename = $this->Model_t_ico_info->get_tablename();

        $this->conn->trans_start();
        // 活动开始中
        $attributes = array(
            'f_ico_state' => $this->ico_service->ico_state['DURING']
        );
        $where = array(
            'f_start_time <=' => $now,
            'f_end_time >' => $now,
        );
        $this->Model_t_ico_info->update_all(
            $this->conn,
            $tablename,
            $attributes,
            $where
        );

        // 活动未开始
        $attributes = array(
            'f_ico_state' => $this->ico_service->ico_state['TO_BE_START']
        );
        $where = array(
            'f_start_time >' => $now,
        );
        $this->Model_t_ico_info->update_all(
            $this->conn,
            $tablename,
            $attributes,
            $where
        );

        // 活动已结束
        $attributes = array(
            'f_ico_state' => $this->ico_service->ico_state['DONE']
        );
        $where = array(
            'f_end_time <' => $now,
        );
        $this->Model_t_ico_info->update_all(
            $this->conn,
            $tablename,
            $attributes,
            $where
        );

        $this->conn->trans_complete();
        if ($this->conn->trans_status() === FALSE) {
            // $conn->trans_rollback();
            cilog('error', "更新ico状态失败!",$filename);
        } else {
            // $conn->trans_commit();
            cilog('debug', "更新ico状态成功!",$filename);
        }
        cilog('debug',"\n ==== 更新全流程结束 ====",$filename);
    }
}