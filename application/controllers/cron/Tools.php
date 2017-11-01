<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class 定时任务
 *
 */
class Tools extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->load->model('user/Model_t_uin');
        $this->load->model("finance/Model_t_finance_info");
        $this->load->service("user/user_service");
        $this->load->service("finance/finance_service");
        $this->log_type = 'info';
        $this->log_filename = "cron_tools_";
    }

    // 完成3级验证的用户
    public function mail_to_all_3_scu($key)
    {
        $log_filename = $this->log_filename;
        $this->init_cron($key,$log_filename,'mail_to_all_3_scu');
        $tablename = $this->Model_t_uin->get_tablename();
        $limit = 100;

        // 获取已经完成3级验证的用户
        $where = array(
            'f_state' => 5
        );
        $count = $this->Model_t_uin->count($this->conn,$tablename,$where);
        if($count == 0){
            cilog('debug',"获取用户数为0,直接退出",$this->log_filename);
            return 0;
        }

        $round = ceil($count / $limit);
        cilog('error',"总共有数据{$count}条,共执行{$round}轮,每轮执行{$limit}条!",$this->log_filename);

        for($i=1;$i<=$round;$i++){
            cilog('debug',"开始第{$i}轮",$this->log_filename);
            $list = $this->Model_t_uin->find_all(
                $conn = $this->conn,
                $select='f_state,f_email,f_uin',
                $tablename,
                $where,
                $limit,
                $page = $i,
                $sort = "f_create_time desc"
            );

            foreach ($list as $row){
                $sublect = "Congratulations！You will get 1000 CCTs for free！";
                $to = array($row['f_email']);
                $message = "<p>Dear {$row['f_email']},</p>".
                           "<p>Welcome to Coincoming!</p>".
                           "<p>As the first 10,000 verified users, you will receive 1000 CCT for free. CCT will be distributed within 15 working days after ICO officially ended.</p>".
                            "<p>What are CCTs used for?</p>".
                            "<p>Tokens of our bitcoin exchange.</p>".
                            "<p>Two Functions.</p>".
                            "<p>1.To participate in the lucky draw per day and have chance to earn 1 Bitcoin.</p>".
                            "<p>2.To share 30% of the profits of platform per month.</p>".
                            "<p>The Whitepaper, which contains all information relevant to the CCT and the offering, is available here:  http://st.coincoming.com/whitepaper/</p>".
                            "<p>Feel free to join our new Telegram Channel here: https://t.me/CoinComingex</p>".
                            "<p>ICO link：http://trade.coincoming.com/ico/pList</p>".
                            "<p>Thanks,</p>".
                            "<p>CoinComing Team</p>";
                $flag = send_mail_v($sublect,$message,$to,$arr_cc=NULL,$arr_filename=NULL);
                if ($flag){
                    cilog('debug',"邮件发送成功! [to:{$row['f_email']}]",$this->log_filename);
                }else{
                    cilog('error',"邮件发送失败! [to:{$row['f_email']}]",$this->log_filename);
                }
            }
        }
        cilog('debug',"全流程结束",$this->log_filename);
    }

    // 全体邮箱验证通过的用户
    public function mail_to_all_scu($key)
    {
        $log_filename = $this->log_filename;
        $this->init_cron($key,$log_filename,'mail_to_all_scu');
        $tablename = $this->Model_t_uin->get_tablename();
        $limit = 100;

        // 获取已经完成3级验证的用户
        $where = array(
//            'f_email' => "956378949@qq.com",
            'f_state >=' => 1,
            'f_state <=' => 5,
        );
        $count = $this->Model_t_uin->count($this->conn,$tablename,$where);
        if($count == 0){
            cilog('debug',"获取用户数为0,直接退出",$this->log_filename);
            return 0;
        }

        $round = ceil($count / $limit);
        cilog('error',"总共有数据{$count}条,共执行{$round}轮,每轮执行{$limit}条!",$this->log_filename);

        for($i=1;$i<=$round;$i++){
            cilog('debug',"开始第{$i}轮",$this->log_filename);
            $list = $this->Model_t_uin->find_all(
                $conn = $this->conn,
                $select='f_state,f_email,f_uin',
                $tablename,
                $where,
                $limit,
                $page = $i,
                $sort = "f_create_time desc"
            );

            foreach ($list as $row){
                $sublect = "Thank you for your support！Purchase your ICO CCT Now!";
                $to = array($row['f_email']);
                $message = "<p>Dear {$row['f_email']},</p>".
                    "<p>Welcome to Coincoming! Thank you for your support.</p>".
                    "<p>Coincoming is pleased to announce its ICO project will begin at 00:00 on October 15. (Hong Kong time). The ICO will be divided into three stages.</p>".
                    "<p>Stage I,  15%  discount ，1 BTC = 23000 CCT，period ：15th, October, 2017 to 25th, October, 2017</p>".
                    "<p>Stage II，10%  discount ,  1 BTC = 22000 CCT  Period: 25th, October, 2017 to 5th, November, 2017</p>".
                    "<p>Stage III,  1 BTC = 20000 CCT  Period: 5th, November, 2017 to 15th, November, 2017</p>".
                    "<p>The Whitepaper, which contains all information relevant to the CCT and the offering, is available here:  http://st.coincoming.com/whitepaper/</p>".
                    "<p>Feel free to join our new Telegram Channel here: https://t.me/CoinComingex</p>".
                    "<p>ICO link：http://trade.coincoming.com/ico/pList</p>".
                    "<p>Thanks,</p>".
                    "<p>CoinComing Team</p>";
                $flag = $this->send_mail_v($sublect,$message,$to,$arr_cc=NULL,$arr_filename=NULL);
                if ($flag){
                    cilog('debug',"邮件发送成功! [to:{$row['f_email']}]",$this->log_filename);
                }else{
                    cilog('error',"邮件发送失败! [to:{$row['f_email']}]",$this->log_filename);
                }
            }
        }
        cilog('debug',"全流程结束",$this->log_filename);
    }

    // 发邮件
    function send_mail_v($subject, $message, $arr_to, $arr_cc = NULL, $arr_filename = NULL)
    {
        require APPPATH . '/libraries/comm/mail/PHPMailer.php';
        require APPPATH . '/libraries/comm/mail/SMTP.php';

        //实例化PHPMailer核心类
        $mail = new PHPMailer();

        $mail_config = array(
            'host' => 'smtp.exmail.qq.com',
            'port' => 465,
            'from' => 'server@coincoming.com',
            'user' => 'server@coincoming.com',
            'password' => 'Fbi123456OK',
            'name' => 'CoinComing Team',
            'charset' => 'UTF-8',
        );

        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        // $mail->SMTPDebug = 1;
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $mail_config['host'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $mail_config['port'];;
        $mail->Hostname = 'http://www.coincoming.com';
        $mail->CharSet = $mail_config['charset'];
        $mail->FromName = $mail_config['name'];
        $mail->Username = $mail_config['from'];
        $mail->Password = $mail_config['password'];
        $mail->From = $mail_config['from'];

        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);

        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        if ($arr_to !== NULL) {
            foreach ($arr_to as $el) {
                $mail->addAddress($el);
            }
        }

        //添加该邮件的主题
        $mail->Subject = $subject;

        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $message;

        // 添加抄送
        if ($arr_cc !== NULL) {
            foreach ($arr_cc as $el) {
                $mail->addCC($el);
            }
        }

        // 添加附件
        if ($arr_filename !== NULL) {
            foreach ($arr_filename as $el) {
                $mail->addAttachment($el, basename($el));
            }
        }

        $status = $mail->send();

        if ($status) {
            return true;
        } else {
            cilog('error', "邮件发送失败!");
            cilog('error', $status);
            return false;
        }
    }

    // 给用户发放1000 cct
    public function send_cct_coin($key)
    {
        $log_filename = "send_cct_coin_";
        $this->init_cron($key,$log_filename,"send_cct_coin");
        $user_data_list = array(
            'grablewski@ya.ru',
            'semy44901@gmail.com',
            'kji54954@gmail.com',
            'rolandocrv@gmail.com',
            'cdotm@naver.com',
            'philos0579@gmail.com',
            'emeho123@naver.com',
            'm30895588@gmail.com',
            'muhyeon1054@gmail.com',
            'smlink7788@gmail.com',
            'chp7700@naver.com',
            'lamp4777@gmail.com',
            'carpediem_jo@naver.com',
            'esj59777@gmail.com',
            'ogju4525@naver.com',
            'gkgksj91@naver.com',
            'xrayzang@hanmail.net',
            'paki50@naver.com',
            'lky3701@naver.com',
            'cjw5121@gmail.com',
            'whddnstls321@naver.com',
            'stacyjo3@naver.com',
            'ysblovelsh52@gmail.com',
            'jss1561@naver.com',
            'stacyjo3@gmail.com',
            'rnfkdi112@gmail.com',
            'reuben.resuello@gmail.com',
            'pcdong7@naver.com',
            'keh19999@naver.com',
            'rlarhdwp@naver.com',
            'parkkwanseok@gmail.com',
            'ekany1@naver.com',
            'dongueri02@gmail.com',
            'dongueri01@gmail.com',
            'dongueri00@gmail.com',
            'wooju96@hanmail.net',
            'dongueri@gmail.com',
            'hhj03311@naver.com',
            'nim2ji@naver.com',
            'dongbbae@naver.com',
            'sjaj1548@gmail.com',
            'tjdusvkvk0460@hanmail.net',
            'aorr2224@gmail.com',
            'leeji5915@gmail.com',
            'sysic8026@gmail.com',
            'ksmin0975@gmail.com',
            'mjtaek7@gmail.com',
            'ks10171@naver.com',
            't1dog@naver.com',
            'alnikmed@gmail.com',
            'hgjung5670@gmail.com',
            'chm2759@gmail.com',
            'jsson01234@gmail.com',
            'jsson002@gmail.com',
            'lawofjin@gmail.com',
            'hicoco85@gmail.com',
            'raygbiz@gmail.com',
            'kingying@naver.com',
            'ethsu1234@gmail.com',
            'vip6211@naver.com',
            'czybear@qq.com',
        );
        $coin_id = 10010;

        foreach ($user_data_list as $row)
        {
            $userinfo = $this->user_service->get_uin_by_email($this->conn,$row);
            if($userinfo['f_uin']){
                $uin = $userinfo['f_uin'];
            }else{
                cilog('error',"找不到该用户数据 [email:{$row}]",$log_filename);
                continue;
            }

            $finance_info =$this->finance_service->get_finance_info($this->conn, $uin, $coin_id);
            cilog('debug',"初始数据如下: [uin:{$uin}] [coin_id:{$coin_id}] [can_use:{$finance_info['f_can_use_vol']}] [freeze:{$finance_info['f_freeze_vol']}] [email:{$row}]",$log_filename);

            // 开始更新数据信息
            $attributes = array(
                'f_modify_time' => timestamp2time(),
                'f_can_use_vol' => 0,
                'f_freeze_vol' => 1000,
                'f_total_vol' => 0,
            );
            $where = array(
                'f_uin' => $uin,
                'f_coin_id' => $coin_id,
            );
            $tablename = $this->Model_t_finance_info->get_tablename();
            $flag = $this->Model_t_finance_info->update_all($this->conn,$tablename,$attributes, $where);
            if($flag === 0){
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
                $this->cache->redis->delete($key);
                cilog('debug',"更新数据成功! [uin:{$uin}] [coin_id:{$coin_id}] [can_use:{$attributes['f_can_use_vol']}] [freeze:{$attributes['f_freeze_vol']}] [email:{$row}]",$log_filename);
            }else{
                cilog('error',"更新数据失败! [uin:{$uin}] [coin_id:{$coin_id}] [can_use:{$attributes['f_can_use_vol']}] [freeze:{$attributes['f_freeze_vol']}] [email:{$row}]",$log_filename);
            }

        }
        cilog('debug',"done",$log_filename);
    }

    public function testfun()
    {
        echo $this->input->ip_address();
    }
}