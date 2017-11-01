<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class 定时任务
 *
 * /usr/local/php7/bin/php index.php cron/cron_matchmaking_trading/trade rasine 10012
 */

class Cron_v2 extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->get_db_conn();
        $this->load->model("coin/Model_t_coin");
        $this->load->model("deal/Model_t_bdeal");
        $this->load->model("deal/Model_t_deal");
        $this->load->model("finance/Model_t_finance_info");
        $this->load->service('deal/deal_service');
        $this->load->service("finance/finance_service");
        $this->load->service("coin/coin_service");
        $this->trade_more_key = "trade_more_bdealid";
        $this->log_filename = "matchmaking_trading_";
        $this->redis_key = $this->deal_service->deal_redis_key['TO_BE_DEAL']."10012";
    }

    // 初始比较
    public function test()
    {
        var_dump($this->get_total_btc(1.234,2.34));
    }

    // 计算佣金
    private function get_commission($coin_info,$price,$num)
    {
        $data = math_div($coin_info['f_commission'],100);
        $total = math_mul($price,$num);
        $commission = math_mul($data,$total);
        cilog('debug',"[fun:get_commission]获取当前交易佣金为:{$commission} [price:{$price}] [num:{$num}] [rate:{$coin_info['f_commission']}]",$this->log_filename);
        return $commission;
    }

    // 计算买家退单时的退单btc  单位btc
    private function get_back_btc($sell_price,$buy_price,$deal_num,$state)
    {
        $back_btc = 0;
        if($state == $this->deal_service->state['DEAL_DONE']){
            $back_btc = math_mul(abs(math_sub($sell_price , $buy_price)) , $deal_num);
        }
        cilog('debug',"获取买家成交后多余的btc总额为:{$back_btc}",$this->log_filename);
        return $back_btc;
    }

    // 获取交易的比特币总额
    private function get_total_btc($deal_price,$deal_num){
        $data = math_mul($deal_num , $deal_price);
        cilog('debug',"获取当前的交易总额为:{$data} [price:{$deal_price}] [num:{$deal_num}]",$this->log_filename);
        return $data;
    }

    private function err_exit()
    {
        $this->cache->redis->delete($this->redis_key);
        exit();
    }

    // 获取卖一列表
    private function get_sell_one_list($coin_id,$buy_price,$num=NULL)
    {
        $num = isset($num) ? $num : 10;
        $sell_list = $this->deal_service->Model_t_bdeal->find_all(
            $conn = $this->conn,
            $select=NULL,
            $tablename = $this->deal_service->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_type' => $this->deal_service->type['SELL'],
                'f_state' => $this->deal_service->state['DEAL_DURING'],
                'f_coin_id' => $coin_id,
                'f_price <=' => $buy_price,
            ),
            $limit = $num,
            $page = 1,
            $sort = 'f_price asc'
        );
        return $sell_list;
    }

    // 获取买一列表 价格 顺序
    private function get_buy_one_list($coin_id,$sell_price,$num=NULL)
    {
        $num = isset($num) ? $num : 10;
        $buy_list = $this->deal_service->Model_t_bdeal->find_all(
            $conn = $this->conn,
            $select=NULL,
            $tablename = $this->deal_service->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_type' => $this->deal_service->type['BUY'],
                'f_state' => $this->deal_service->state['DEAL_DURING'],
                'f_coin_id' => $coin_id,
                'f_price >=' => $sell_price
            ),
            $limit = $num,
            $page = 1,
            $sort = 'f_price desc'
        );
        return $buy_list;
    }
}