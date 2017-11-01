<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Test extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->get_db_conn();
    }

    public function test()
    {
        $this->load->service("finance/finance_service");
        $uin_list = array(10054,10056,10041,10057,10055,10073,10074,10075,10072,10064,10077);
        // $uin_list = array(10054);
        foreach ($uin_list as $uin){
            $coin_id = 10001;
            $attributes = array(
                'f_total_vol' => 0,
                'f_freeze_vol' => 0,
                'f_can_use_vol' => 10000,
            );
            $this->finance_service->Model_t_finance_info->update_finance_info($this->conn,$uin,$coin_id,$attributes);
            $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);

            $coin_id = 10012;
            $attributes = array(
                'f_total_vol' => 0,
                'f_freeze_vol' => 0,
                'f_can_use_vol' => 10000,
            );
            $this->finance_service->Model_t_finance_info->update_finance_info($this->conn,$uin,$coin_id,$attributes);
            $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        }
        render_json(0,'重置ok','');
    }

    function test_fun()
    {
        $timedata = math_sub(10.976,1.98);
        var_dump($timedata);
    }

    function get_total_millisecond()
    {
        $time = explode (" ", microtime () );
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode ( ".", $time );
        $time = $time2 [0];
        return $time;
    }

    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}