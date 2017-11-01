<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * comm  系统公共服务 0x2000
 */

class Comm_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_id($id_type)
    {
        $this->load->model("comm/Model_t_idmaker");
        return $this->Model_t_idmaker->get_id_from_idmaker($id_type);
    }


}