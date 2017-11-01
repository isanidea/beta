<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Act  活动模块
 */

require_once APPPATH . '/libraries/comm/captcha.php';
class Act extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->prize_arr = array(
            '0' => array('id'=>1,'prize'=>'一个比特币','v'=>1),
            '1' => array('id'=>2,'prize'=>'iphone8','v'=>10),
            '2' => array('id'=>3,'prize'=>'ipad','v'=>11),
            '3' => array('id'=>4,'prize'=>'充电宝','v'=>12),
            '4' => array('id'=>5,'prize'=>'智能手环','v'=>200),
            '5' => array('id'=>6,'prize'=>'谢谢参与','v'=>90000),
        );
    }

    // 抽奖活动页面
    public function pActive()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('act/act_lottery');
    }

    /**
     * @fun   抽奖核心函数
     */
    private function get_rand($proArr) {
        $result = '';

        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }

    /**
     * @fun   用户抽奖
     */
    public function lottery()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        // 检查用户是否可以参加活动
        $this->load->service("user/user_service");
        $userinfo = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($userinfo)){
            render_json($userinfo);
        }

        cilog('error',"用户剩余次数:{$userinfo['f_can_join']}");

        if($userinfo['f_can_join'] == 0){
            cilog('error',"用户没有参加活动的资格!");
            render_json($this->conf_errcode['ACT_HAVE_NO_POWER_TO_LOTTERY']);
        }

        // 特殊处理 
        $act_white = $this->config->item('act_white');
        if(in_array($uin,$act_white)){
            cilog('error',"{$uin}在白名单中");
            $new = array();
            foreach ($this->prize_arr as $row) {
                $tmp = array(
                    'id' => $row['id'],
                    'prize' => $row['prize'],
                    'v' => 1,
                );
                array_push($new,$tmp);
            }
            $this->prize_arr = $new;
        }

        // 开始抽奖
        foreach ($this->prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $rid = $this->get_rand($arr); //根据概率获取奖项id
        $level = $this->prize_arr[$rid-1]['id']; //中奖项
        $name = $this->prize_arr[$rid-1]['prize'];

        // 更新用户状态、写入中奖记录
        $this->conn->trans_start();
        $this->load->model("user/Model_t_uin");
        $attributes = array(
            'f_can_join' => ($userinfo['f_can_join'] - 1),
            'f_modify_time' => timestamp2time(),
        );
        $tablename = $this->Model_t_uin->get_tablename();
        $this->Model_t_uin->update_all(
            $this->conn,
            $tablename,
            $attributes,
            array('f_uin' => $uin)
        );

        if($level < 6){
            $bingo_info = array(
                'f_uin' => $uin,
                'f_prize_level' => $level,
                'f_prize_name' => $name,
                'f_email' => $userinfo['f_email'],
                'f_create_time' => timestamp2time(),
                'f_modify_time' => timestamp2time(),
            );
            $this->load->model("act/Model_t_lottery_bingo_list");
            $tablename = $this->Model_t_lottery_bingo_list->get_tablename();
            $this->Model_t_lottery_bingo_list->save($this->conn,$tablename,$bingo_info);
        }

        $this->conn->trans_complete();
        if ($this->conn->trans_status() === FALSE)
        {
            $this->conn->trans_rollback();
            cilog('error',"用户抽奖成功失败,开始回滚数据!");
            return $this->conf_errcode['ACT_LOTTERY_ERR'];
        }
        else
        {
            $this->conn->trans_commit();
            $this->load->service("user/user_service");
            $key = $this->user_service->user_redis_key['USER_INFO_PRE'].$uin;
            $this->cache->redis->delete($key);
            cilog('debug',"用户抽奖成功!");
            render_json(0,'',$level);
        }
    }

    /**
     * @fun   获取用户中奖列表 前10个
     */
    public function get_bingo_list()
    {
        $this->init_log();
        $this->init_api();
        // $this->check_login();
        $num = get_post_valueI('num');
        $num = (($num<50) && ($num > 0)) ? $num : 10;

        $this->load->model("act/Model_t_lottery_bingo_list");
        $tablename = $this->Model_t_lottery_bingo_list->get_tablename();
        $list = $this->Model_t_lottery_bingo_list->find_all(
            $this->conn,
            NULL,
            $tablename,
            array(),
            $limit = $num,
            1,
            'f_create_time desc'
        );

        if(!is_array($list)){
            render_json($list);
        }

        $rsp = array();

        foreach ($list as $row){
            $a = array(
                'email' => hideStar($row['f_email']),
                'level' => $row['f_prize_level'],
                'is_send' => ($row['f_is_send'] == 1) ? TRUE : FALSE,
                'addtime' => $row['f_create_time'],
            );
            array_push($rsp,$a);

        }
        render_json(0,'',$rsp);
    }

    /**
     * 添加购买抽奖次数接口
     *
     * 暂定 10个fbi 1次机会
     */
    public function add_lottery_time()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $pw = get_post_value('pw');
        $num = get_post_valueI('num');

        if(!$this->oValidator->isPw($pw)){
            cilog('error',"登陆密码格式不对 [pw:{$pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($num === 0){
            cilog('error',"购买次数不对 [num:{$num}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $oSession = $this->get_session();
        $uin = $oSession->uin;
        $coin_id = 10003; // 默认使用fbi币
        $need_coin_num = 10 * $num;

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
        $flag = $this->user_service->check_pw($arr_userinfo['f_key'],$arr_userinfo['f_deal_pw'],$pw);
        if($flag !== 0) {
            render_json($flag);
        }

        // 查询用户财务信息
        $this->load->service('finance/finance_service');
        $finance_info = $this->finance_service->get_finance_info($this->conn, $uin, $coin_id);
        if(!$finance_info){
            cilog('error',"获取用户财务信息失败 [info:{$finance_info}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($finance_info['f_can_use_vol'] < $need_coin_num){
            cilog('error',"余额不足 [need:{$need_coin_num}] [have:{$finance_info['f_can_use_vol']}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 扣减财务信息,添加用户次数
        $this->load->model('finance/Model_t_finance_info');
        $this->load->model('user/Model_t_uin');
        $this->conn->trans_start();
        $this->Model_t_finance_info->pre_reduce_finance($this->conn,$finance_info,$need_coin_num);
        $this->Model_t_uin->update_all(
            $this->conn,
            $tablename = $this->Model_t_uin->get_tablename(),
            $attributes = array(
                'f_modify_time' => timestamp2time(),
                'f_can_join' => $arr_userinfo['f_can_join'] + $num,
            ),
            $where = array(
                'f_uin' => $uin
            )
        );
        $this->conn->trans_complete();
        if ($this->conn->trans_status() === FALSE) {
            // $conn->trans_rollback();
            cilog('error', "用户抽奖扣减金额失败!");
            render_json($this->finance_service->finance_errcode['FINANCE_REDUCE_LOTTERY_COIN_ERR']);
        } else {
            // $conn->trans_commit();
            $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
            $this->cache->redis->delete($key);
            $key = $this->user_service->user_redis_key['USER_INFO_PRE'] . $uin;
            $this->cache->redis->delete($key);
            cilog('debug', "用户抽奖扣减金额成功!");
            render_json();
        }
    }

    /**
     * 获取个人抽奖结果记录
     *
     * page   页码           非必填
     * num    当页展示最大数   非必填
     *
     */
    public function get_lottery_result()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $oSession = $this->get_session();
        $uin = $oSession->uin;

        $this->load->model("act/Model_t_lottery_bingo_list");
        $where = array(
            'f_uin' => $uin
        );
        $count = $this->Model_t_lottery_bingo_list->count(
            $this->conn,
            $this->Model_t_lottery_bingo_list->get_tablename(),
            $where
        );
        if((int)$count === 0){
            cilog('error',"查询不到结果");
            render_json_list();
        }

        $list = $this->Model_t_lottery_bingo_list->find_all(
            $conn=$this->conn,
            $select=NULL,
            $this->Model_t_lottery_bingo_list->get_tablename(),
            $where,
            $limit = $num,
            $page = $page,
            $sort = 'f_create_time desc'
        );

        if(!is_array($list)){
            cilog('error',"获取列表数据失败!");
            render_json_list();
        }

        $tmp = array();
        foreach ($list as $row){
            $a = array(
                'email' => hideStar($row['f_email']),
                'level' => $row['f_prize_level'],
                'is_send' => ($row['f_is_send'] == 1) ? TRUE : FALSE,
                'addtime' => $row['f_create_time'],
            );
            array_push($tmp,$a);
        }
        render_json_list(0,'',$count,$tmp);
    }
}