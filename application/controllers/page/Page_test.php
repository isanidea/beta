<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class page_test  页面模板渲染
 */

class Page_test extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // $this->conn = $this->load->database('trade_user',TRUE);
    }

    public function index()
    {
        echo "hello";
//        $this->init_log();
//        $this->init_page();
//
//        $data = array(
//            'blog_title' => 'My Blog Title',
//            'blog_heading' => 'My Blog Heading'
//        );
//
//        $this->parser->parse('test/test', $data);
    }
}
