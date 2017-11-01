<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class 定时任务 用户补充工具
 *
 * /usr/local/php7/bin/php index.php cron_kline get_kline_data_write2redis rasine 60 1000
 */
class Cron_user_tool extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->load->model('user/Model_t_uin');
        $this->load->model("finance/Model_t_finance_info");
        $this->load->service("user/user_service");
        $this->log_type = 'info';
        $this->log_filename = "Cron_user_tool_";
    }

    public function add_finance_account()
    {
        $conn = $this->conn;
        $tablename = $this->Model_t_uin->get_tablename();
        $tablename_finance_info = $this->Model_t_finance_info->get_tablename();
        $where = array();
        $limit = 200;
        $sort = 'f_create_time asc';

        $count = $this->Model_t_uin->count($conn,$tablename,$where);
        if($count<1){
            cilog('error',"找不到用户信息!退出程序!",$this->log_filename);
            return -1;
        }

        $list_coin = array(
            array('f_coin_id' => 10001,'f_coin_abbr' => 'BTC'),
            array('f_coin_id' => 10002,'f_coin_abbr' => 'ETH'),
            array('f_coin_id' => 10003,'f_coin_abbr' => 'FBI'),
            array('f_coin_id' => 10006,'f_coin_abbr' => 'LTC'),
            array('f_coin_id' => 10007,'f_coin_abbr' => 'BCC'),
            array('f_coin_id' => 10009,'f_coin_abbr' => 'ETC'),
        );

        $round = ceil($count/$limit);
        cilog('debug',"总共有数据{$count}条! 每轮处理{$limit}条,共有{$round}轮!");
        for($i=1;$i<=$round;$i++){
            cilog('debug',"第{$i}轮开始!",$this->log_filename);
            $user_list = $this->Model_t_uin->find_all($conn,NULL,$tablename,$where,$limit,$i,$sort);
            foreach ($user_list as $row){
                foreach ($list_coin as $coin_row){
                    // 查询用户——币种财务信息是否存在
                    $where_finance = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_row['f_coin_id']
                    );
                    $num = $this->Model_t_finance_info->count($conn,$tablename_finance_info,$where_finance);
                    if($num != 0){
                        cilog('error',"该用户已经有了财务信息,跳过![uin:{$row['f_uin']}] [coin_id:{$coin_row['f_coin_id']}]",$this->log_filename);
                        continue;
                    }else{
                        cilog('debug',"该用户没有财务信息,生成财务信息![uin:{$row['f_uin']}] [coin_id:{$coin_row['f_coin_id']}]",$this->log_filename);
                        $finance_info = array(
                            'f_uin' => $row['f_uin'],
                            'f_coin_id' => $coin_row['f_coin_id'],
                            'f_coin_abbr' => $coin_row['f_coin_abbr'], // 简称
                            'f_coin_addr' => '', // 地址
                            'f_total_vol' => 0,
                            'f_freeze_vol' => 0,
                            'f_can_use_vol' => 0,
                            'f_create_time' => timestamp2time(),
                            'f_modify_time' => timestamp2time(),
                        );
                        $flag = $this->Model_t_finance_info->save($conn,$tablename_finance_info,$finance_info);
                        if($flag === FALSE){
                            cilog('error',"添加财务信息失败! [uin:{$row['f_uin']}] [coin_id:{$coin_row['f_coin_id']}]",$this->log_filename);
                        }else{
                            cilog('debug',"添加财务信息成功!",$this->log_filename);
                        }
                    }
                }
            }
        }
        cilog('error',"全流程处理结束!",$this->log_filename);
    }
}