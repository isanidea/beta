<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class admin  后台管理模块 页面部分
 */

// require_once APPPATH . '/libraries/comm/captcha.php';
class Admin_page extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // $this->conn = $this->load->database('trade_user',TRUE);
        $beta_admin_login_url = "http://trade.test.com/admin/admin_page/login";
        $idc_admin_login_url = "http://trade.coincoming.com/admin/admin_page/login";
        $this->admin_login_url = (ENVIRONMENT === "development") ? $beta_admin_login_url : $idc_admin_login_url;
    }

    // 后台登陆页面
    public function login()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view("admin/login");
    }

    // 后台首页
    public function home()
    {
        $this->init_log();
        $this->init_page();
        $flag = $this->user_service->check_online_($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            header("Location: {$this->admin_login_url}");
            exit();
        }
        $this->load->view("admin/index");
    }

    public function logout(){

        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online_($this->oSession);
        if ($flag === 0){
            $oSession = $this->get_session();
            $this->user_service->loginout($oSession);
        }
        
        header("Location: {$this->admin_login_url}");
        exit();
    }
}