<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/easybitcoin.php';
class Welcome extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	// 网站首页
	public function index()
    {
        $this->init_log();
        $this->init_page();
        $this->load->service("cms/cms_service");
        $conn = $this->load->database('trade_user',TRUE);

        // 获取首页的置顶消息
        $type_id = 1001;
        $base_url = "http://trade.coincoming.com/cms/pdetail?id=";
        $data = $this->cms_service->get_top_case($conn,$type_id);

        // 获取banner图片信息
        $this->load->model("conf/Model_t_conf");
        $banner_info_arr = $this->Model_t_conf->get_conf_info($conn,"banner_pic_info");
        $str_banner_info='';
        if(is_array($banner_info_arr)){
            foreach ($banner_info_arr as $row){
                $tmp = "<a href=\"{$row['click']}\"><img class=\"owl-lazy\" data-src=\"{$row['url']}\"></a>";
                $str_banner_info = $str_banner_info.$tmp;
            }
        }

        $data_arr = array(
            'cms_title' => isset($data['f_title']) ? $data['f_title'] : "",
            'cms_title_url' => isset($data['f_case_id']) ? $base_url.$data['f_case_id']:"#",
            'banner_pic_info' => $str_banner_info
        );
        $this->load->view('index',$data_arr);
    }

    public function test()
    {
        $conn = $this->load->database('trade_user',TRUE);
        $this->load->model("user/Model_t_uin");
        $list = $this->Model_t_uin->find_all_in(
            $conn,
            $select=NULL,
            $tablename=$this->Model_t_uin->get_tablename(),
            $where = array(
                'f_state > ' => 2
            ),
            $key = 'f_state',
            $arr_data = array(3,4) ,
            $limit = 10,
            $page = 0,
            $sort = 'f_create_time desc'
        );
        render_json(0,'',$list);
    }

    public function upload_file_(){
        $this->init_log();
        $this->init_page();
        $aa = get_post_value("aa");
        $file_array = unserialize($aa);
        $array = array();
        foreach($file_array as $k=>$v){
            if(is_file($v)){
                array_push($array,$v);
            }
            echo '不存在'.$v.'<br>';
        }
        unset($check_file);

        $_SERVER['HTTP_HOST'];

    }

    public function upload_file(){
        $this->init_log();
        $this->init_page();



    }

}
