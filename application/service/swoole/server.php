<?php

class Server
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 1,                    //一般设置为服务器CPU数的1-4倍
            'daemonize' => 1,                     //1以守护进程执行,0调试用，在命令行输出
            'max_request' => 1000,
            'dispatch_mode' => 2,
            'task_worker_num' => 1,               //task进程的数量
            "task_ipc_mode " => 3,                //使用消息队列通信，并设置为争抢模式
            "heartbeat_check_interval" => 60,     //心跳检查自动close掉长时间没有数据来往的连接
            "heartbeat_idle_time" => 600,
            "log_file" => "log/taskqueueu.log",//日志
        ));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));
        $this->serv->on('connect', function ($serv, $fd) {
            echo "Client:Connect.\n";
        });
        $this->serv->on('close', function ($serv, $fd) {
            echo "Client: Close.\n";
        });
        $this->serv->start();
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        $serv->task($data);
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        $array = json_decode($data, true);
        if ($array['url']) {
            echo "onTask----go run {$task_id} \n";
            return $this->httpGet($array['url'], $array['param']);
        }
    }

    public function onFinish($serv, $task_id, $data)
    {
        //echo "Task {$task_id} finish\n";
        //echo "Result: {$data}\n";
    }

    protected function httpGet($url, $data)
    {
        if ($data) {
            $url .= '?' . http_build_query($data);
        }
        $curlObj = curl_init();                                //初始化curl，
        curl_setopt($curlObj, CURLOPT_URL, $url);              //设置网址
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);      //将curl_exec的结果返回
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);              //是否输出返回头信息
        $response = curl_exec($curlObj);                       //执行
        curl_close($curlObj);                                  //关闭会话
        return $response;
    }

}

$server = new Server();

?>