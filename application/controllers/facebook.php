<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class user  用户模块
 */

// require_once APPPATH . '/libraries/comm/captcha.php';
require_once APPPATH . '/libraries/Facebook/autoload.php';

class Facebook extends MY_Controller
{
    public $face_book;

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->face_book = array(
            "APP_ID" => "289014404949541",
            "APP_SECRET" =>"a9290d57820637061190e81b247fd650",
        );

    }
    //登录按钮
    public function login()
    {
        $fb = new Facebook\Facebook([
            'app_id' => $this->face_book['APP_ID'], // Replace {app-id} with your app id
            'app_secret' =>$this->face_book['APP_SECRET'],
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();
//        $_SESSION['FBRLH_state']=$_GET['state'];
        $permissions = ['email','public_profile']; // Optional permissions
        $loginUrl = $helper->getLoginUrl('http://'.$_SERVER['SERVER_NAME'].'/facebook/fb_callback', $permissions);
        echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
    }

    //获取token_access
    public function fb_callback()
    {
        $fb = new Facebook\Facebook([
            'app_id' => $this->face_book['APP_ID'], // Replace {app-id} with your app id
            'app_secret' => $this->face_book['APP_SECRET'],
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $_SESSION['FBRLH_state']=$_GET['state'];
        var_dump($_SESSION);

        echo $_GET['state']."<br/>";

//        if (isset($_GET['state'])) {
//            $helper->getPersistentDataHandler()->set('state', $_GET['state']);
//        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }


        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        // Logged in
        echo '<h3>Access Token</h3>';
        var_dump($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
//        $tokenMetadata->validateAppId('596848104039743'); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
//        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }

            echo '<h3>Long-lived</h3>';
            var_dump($accessToken->getValue());
        }

        $_SESSION['fb_access_token'] = (string) $accessToken;

        // User is logged in with a long-lived access token.
        // You can redirect them to a members-only page.
        //header('Location: https://example.com/members.php');

        //获取用户信息

        try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields=id,name,email,iphone', "$accessToken");
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

        $user = $response->getGraphUser();
        var_dump($user);






    }




    public function user_with_facebook(){
        $this->init_log();
        $this->init_api();

        $str_email = get_post_value('email');
    }

}