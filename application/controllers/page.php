<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class yemian
 */
// require_once APPPATH . '/libraries/comm/captcha.php';
class Page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->load->library("Ci_smarty");
        $this->log_filename = NULL;
    }

    public function index()
    {


        $this->init_page();
        $this->init_log();
        $data['title'] = '标题';
        $data['num'] = '123456789';
//        $this->cismarty->assign('data',$data); // 亦可
        $this->assign('data',$data);
        $this->assign('tmp','hello');
        $this->display('test.html'); // 亦可
        $this->display('test.html');

    }
}
