<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_send_ico extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->load->model('ico/Model_t_ico_info');
        $this->load->model('ico/Model_t_ico_log');
        $this->load->model('finance/Model_t_finance_info');
        $this->load->service("finance/finance_service");
        $this->load->service("coin/coin_service");
        $this->log_filename = NULL;
    }

    public function send_ico_result($key,$ico_id)
    {
        $log_filename = "send_ico_result_";
        $fun_title = "开始发放ico [ico_id:{$ico_id}]";
        $this->init_cron($key, $log_filename, $fun_title);

        // 检查ico状态
        $icoinfo = $this->Model_t_ico_info->find_by_attributes(
            $conn=$this->conn,
            $select = NULL,
            $tablename=$this->Model_t_ico_info->get_tablename(),
            $where = array(
                'f_ico_id' => $ico_id,
            ),
            $sort = 'f_create_time desc'
        );

        $nowtime = time2timestamp();
        if(time2timestamp($icoinfo['f_end_time']) > $nowtime){
            cilog('error',"活动未结束,无法发送ico需要的币! 退出程序!",$log_filename);
            return -1;
        }

        $count = $this->Model_t_ico_log->count(
            $conn=$this->conn,
            $tablename=$this->Model_t_ico_log->get_tablename(),
            $where = array(
                'f_ico_id' => $ico_id,
                'f_state' => 0
            )
        );

        if($count <= 0){
            cilog('error',"获取ico购买数量为空,退出程序! [count:{$count}]",$log_filename);
            return -1;
        }

        $limit = 100;
        $round = ceil($count/$limit);
        cilog('debug',"共有{$count} 条数据需要处理,每次处理{$limit}条数据,共处理{$round}轮!",$log_filename);
        for($i=1;$i<=$round;$i++){
            $list = $this->Model_t_ico_log->find_all(
                $conn=$this->conn,
                $select=NULL,
                $tablename=$this->Model_t_ico_log->get_tablename(),
                $where = array(
                    'f_ico_id' => $ico_id,
                    'f_state' => 0
                ),
                $limit = $limit,
                $page = $i,
                $sort = 'f_create_time desc'
            );
            foreach ($list as $row){
                $uin = $row['f_uin'];
                $coin_id = $row['f_coin_id'];
                $vol = $row['f_buy_vol'];
                $need_reduce_freeze_vol=0;

                $coininfo = $this->coin_service->get_coin_info($this->conn, $coin_id);
                // 获取市场币种财务信息
                $market_coin_id = get_coinid_by_marketid($coininfo['f_market_type']);
                $market_coin_finance_info = $this->finance_service->get_finance_info($conn, $uin, $market_coin_id);
                $finance_info = $this->finance_service->get_finance_info($conn, $uin, $coin_id);
                $need = $vol * $icoinfo['f_ico_rate'];

                $conn->trans_start();
                // 增加ico币种
                $this->Model_t_finance_info->update_all(
                    $conn,
                    $tablename=$this->Model_t_finance_info->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_can_use_vol' => $finance_info['f_can_use_vol'] + $vol,
                        'f_total_vol' => $finance_info['f_total_vol'] + $vol,
                    ),
                    $where = array(
                        'f_uin' => $uin,
                        'f_coin_id' => $coin_id
                    )
                );
                // 扣减市场币种冻结
                $this->Model_t_finance_info->update_all(
                    $conn,
                    $tablename=$this->Model_t_finance_info->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_freeze_vol' => $market_coin_finance_info['f_freeze_vol'] - $need,
                    ),
                    $where = array(
                        'f_uin' => $uin,
                        'f_coin_id' => $market_coin_id
                    )
                );
                $this->Model_t_ico_log->update_all(
                    $conn,
                    $tablename=$this->Model_t_ico_log->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => 1,
                    ),
                    $where = array(
                        'f_id' => $row['f_id'],
                    )
                );
                $conn->trans_complete();
                if ($conn->trans_status() === FALSE) {
                    cilog('error', "发送ico币种失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [vol:{$vol}]",$log_filename);
                    continue;
                } else {
                    cilog('debug', "发送ico币种成功! [uin:{$uin}] [coin_id:{$coin_id}] [vol:{$vol}]",$log_filename);
                    continue;
                }
            }
            cilog('debug',"第{$i}轮处理完毕!",$log_filename);
        }
        cilog('debug',"ico全部发放完毕!",$log_filename);
    }
}