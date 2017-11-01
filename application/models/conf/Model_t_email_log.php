<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    邮件发送流水
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_email_log extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_email_log';

    function __construct()
    {
        parent::__construct();
    }

    // 写入一条邮件流水
    function insert_email_log($conn,$data)
    {
        $tablename = $this->_tableName;
        $data['f_create_time'] = timestamp2time();
        $data['f_modify_time'] = timestamp2time();
        return $this->save($conn,$tablename,$data);
    }
}