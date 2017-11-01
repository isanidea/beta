<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Model
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        $this->swoole_server_ip = "127.0.0.1";
        $this->swoole_server_port = 9501;
    }


    public function connect()
    {
        if (!$this->client->connect($this->swoole_server_ip, $this->swoole_server_port, 1)) {
            throw new Exception(sprintf('Swoole Error: %s', $this->client->errCode));
        }
    }

    public function send($data)
    {
        if ($this->client->isConnected()) {
            if (!is_string($data)) {
                $data = json_encode($data);
            }

            return $this->client->send($data);
        } else {
            throw new Exception('Swoole Server does not connected.');
        }
    }

    public function close()
    {
        $this->client->close();
    }

    //$url请求的网址，paramArr请求的参数
    public function gorun($url, $paramArr)
    {
        $client = new Client();
        $client->connect();
        $data = array();
        $data["url"] = $url;
        $data["param"] = $paramArr;

        if ($client->send($data)) {
            //echo $i.'请求发送成功success'.time()."</br>";
        } else {
            //echo '请求发送失败fail'.time()."</br>";
        }
        $client->close();

    }
    //class end
}

?>