<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class coin  币种模块
 */

require_once APPPATH . '/libraries/comm/captcha.php';
class Coin extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->load->service('coin/coin_service');
    }

    /**
     * @fun    添加币种信息
     * @param  name    币种名称 例如:BIT
     * @param  desc    币种描述 例如:比特币
     */
    public function add_coin()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $name = get_post_value('name');
        $desc = get_post_value('desc');

        $flag = $this->coin_service->add_coin($this->conn,$name,$desc);
        if(!$flag){
            render_json($flag);
        }
        render_json(0);
    }

    /**
     * @fun    获取币种详情
     * @param  coinid    币种id
     */
    public function get_coin_info()
    {
        $this->init_log();
        $this->init_api();

        $int_coinid = get_post_valueI('coinid');

        if ($int_coinid < 10000){
            cilog('error',"币种id 参数错误 [id:{$int_coinid}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $coin_info = $this->coin_service->get_coin_info($this->conn,$int_coinid);
        if (!$coin_info){
            render_json(0);
        }

        $flag = $this->coin_service->check_coin_info($coin_info);
        if ($flag !== 0){
            render_json(0);
        }

        $rep = array(
            "coin_id" => $coin_info['f_coin_id'],
            "coin_name" => $coin_info['f_abbreviation'],
            "coin_desc" => $coin_info['f_coin_name'],
            "last_price" => $coin_info['f_last_price'],
            "24rate_change" => $coin_info['f_rate_change_24'],
            "24high" => $coin_info['f_high_price_24'],
            "24low" => $coin_info['f_low_price_24'],
            "24ltc_vol" => $coin_info['f_deal_vol_24'],
            "24btc_vol" => "1815.9552",
            "commission" => $coin_info['f_commission'],
            "market" => $coin_info['f_market_type'],
            "atm_rate" => $coin_info['f_atm_rate'],
            "buy_one" => $coin_info['f_open_price'],
            "sell_one" => $coin_info['f_close_price'],
        );

        render_json(0,'',$rep);
    }

    /**
     * @fun    获取币种细节
     * @param  coinid    币种id
     */
    public function get_coin_datail()
    {
        $this->init_log();
        $this->init_api();

        $int_coinid = get_post_valueI('coinid');

        if ($int_coinid < 10000){
            cilog('error',"币种id 参数错误 [id:{$int_coinid}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $coin_info = $this->coin_service->get_coin_info($this->conn,$int_coinid);
        if (!is_array($coin_info)){
            render_json(0);
        }

        $flag = $this->coin_service->check_coin_info($coin_info);
        if ($flag !== 0){
            render_json(0);
        }

        $rep = array(
            "coin_id" => $coin_info['f_coin_id'],                // 币种id
            "coin_name" => $coin_info['f_coin_name'],            // 币种名称
            "coin_ename" => $coin_info['f_coin_e_name'],         // 币种英文名
            "abbreviation" => $coin_info['f_abbreviation'],      // 币种简称
            "creater" => $coin_info['f_creater'],                // 开发者
            "blockTime" => $coin_info['f_block_time'],           // 单位秒
            "publishTime" => $coin_info['f_publish_time'],       // 发布时间
            "total_vol" => $coin_info['f_last_total_vol'],       // 总量
            "algorithm" => $coin_info['f_core_algorithm'],       // 核心算法
            "desc" => $coin_info['f_desc'],                      // 简介
            "icon" => $coin_info['f_icon'],                      // 图标
            "buttom1" => $coin_info['f_key_buttom_1'],           // 官网链接
            "buttom2" => $coin_info['f_key_buttom_2'],           // 论坛链接
            "buttom3" => $coin_info['f_key_buttom_3'],           // 钱包下载链接
            "buttom4" => $coin_info['f_key_buttom_4'],           // 区块浏览器
        );

        render_json(0,'',$rep);
    }

    /**
     * @fun    获取币种详情列表
     * @param   page       页码
     * @param   num        每页展示最大数据量
     */
    public function get_coin_list()
    {
        $this->init_log();
        $this->init_api();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $coin_list = $this->coin_service->get_coin_list($this->conn,$page,$num);
        if (!is_array($coin_list)){
            render_json_list(0);
        }

        $a = array();
        foreach ($coin_list['rows'] as $row){

            $flag = $this->coin_service->check_coin_info($row);
            if ($flag !== 0){
                array_push($a,array());
            }

            $b = array(
                "coin_id" => $row['f_coin_id'],
                "coin_name" => $row['f_abbreviation'],
                "coin_desc" => $row['f_coin_name'],
                "coinIcon" => $row['f_icon'],
                "last_price" => $row['f_last_price'],
                "24rate_change" => $row['f_rate_change_24'],
                "24ltc_vol" => number_format($row['f_deal_vol_24'],3,'.',''),
                "24high" => $row['f_high_price_24'],
                "24low" => $row['f_low_price_24'],
                "market" => $row['f_market_type'],
            );
            array_push($a,$b);
        }
        render_json_list(0,0,$coin_list['num'],$a);
    }
}