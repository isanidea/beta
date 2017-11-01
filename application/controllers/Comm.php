<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class comm  公共接口模块
 */

// require_once APPPATH . '/libraries/comm/captcha.php';
class Comm extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // $this->conn = $this->load->database('trade_user',TRUE);
    }

    /**
     * 对外展示注册人数接口
     */
    public function get_reg_num()
    {
        $this->init_log();

        // 参数校验
        $reg_key = $_SERVER['REQUEST_TIME'];
        $nowtimestamp = time2timestamp();
        $data = $nowtimestamp - $reg_key;
        if($data > 120){
            cilog('error',"接口请求不合法,时间不对! time:".timestamp2time($reg_key));
            render_json();
        }

        $key = "reg_num_redis";
        $value = $this->cache->redis->get($key);
        if(!$value){
            $value = 11000;
        }
        $value = $value + rand(1,20);
        $this->cache->redis->save($key,$value,864000);
        render_json(0,'',$value);
    }

    /**
     * 获取首页banner
     */
    public function get_banner()
    {
        $this->init_log();
        $this->init_api();
        $conn = $this->load->database('trade_user',TRUE);
        $this->load->model("conf/Model_t_conf");
        $banner_info_arr = $this->Model_t_conf->get_conf_info($conn,"banner_pic_info");
        render_json(0,'',$banner_info_arr);
    }

    /**
     * 获取置顶新闻
     */
    public function get_top_notice()
    {
        $this->init_log();
        $this->init_api();
        $this->load->service("cms/cms_service");
        $conn = $this->load->database('trade_user',TRUE);
        $type_id = 1001;
        $data = $this->cms_service->get_top_case($conn,$type_id);
        render_json(0,'',$data);
    }

}