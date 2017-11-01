<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class page_test  页面模板渲染
 */

class Page_test extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // $this->conn = $this->load->database('trade_user',TRUE);
        $this->conn = $this->get_db_conn();
    }

    public function index()
    {
        $this->init_log();
        $this->init_page();
        $a = array(
            'tt' => 1,
        );

        echo $a->tt;
//        $name = array(
//            array('first'=>"111",'second'=>22222),
//            array('first'=>"1",'second'=>2),
//            array('first'=>"1111111",'second'=>22222222),
//        );
//
//        $name1 = '';
//        foreach ($name as $key => $value){
//            $str = "<a href=''>{$value} - 这是一个a标签 - {$value}</a>";
//            $name1 = $name1.$str;
//        }
//
//        $data = array(
//            'title' => "zheshibiaoti",
//            'name' => $name1,
//        );
//
//        $this->load->view('test/test',array('data'=>$data));
    }

    // <p>{btp:id}:{btp:pw}</p>
    private function base($data,$temple)
    {
        $s = "";
        foreach ($data as $row){
            $temple =
            $s = $s . $temple;
        }
        return $s;
    }


    public function test_()
    {
        $this->load->service("finance/finance_service");
        $uin_list = array(10056,10054,10041,10057,10055,10073,10074);
        foreach ($uin_list as $uin){
            $coin_id = 10001;
            $attributes = array(
                'f_total_vol' => 0,
                'f_freeze_vol' => 0,
                'f_can_use_vol' => 100,
            );
            $this->finance_service->Model_t_finance_info->update_finance_info($this->conn,$uin,$coin_id,$attributes);
            $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);

            $coin_id = 10012;
            $attributes = array(
                'f_total_vol' => 0,
                'f_freeze_vol' => 0,
                'f_can_use_vol' => 100,
            );
            $this->finance_service->Model_t_finance_info->update_finance_info($this->conn,$uin,$coin_id,$attributes);
            $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        }
        render_json();
    }
}
