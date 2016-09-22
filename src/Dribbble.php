<?php

namespace Nahid\DribbbleClient;

use duncan3dc\Sessions\SessionInstance;

class Dribbble
{
    protected $config;
    public $session;
    protected $code = '';
    protected $errors = null;

    public $url = 'https://api.dribbble.com/v1';
    public $answers;

    public function __construct($config = null)
    {
        $confManager = new ConfigManager($config);
        $this->session = new SessionInstance('php-dribble-api');
        $this->config = $confManager->config;
        $this->code = isset($_GET['code']) ? $_GET['code'] : null;
    }

    public function __call($method, $params)
    {
        $method = $this->fromCamelCase($method);
        $param = count($params) > 0 ? '/'.implode('/', $params) : '';
        $this->url .= '/'.$method.$param;

        return $this;
    }

    public function makeAuthLink($caption = 'Authentication', $scope = '', $state = null)
    {
        $state = !is_null($state)?md5($state):'';
        return 'https://dribbble.com/oauth/authorize
?client_id='.$this->config->get('client_id').'&redirect_uri='.$this->config->get('redirect_uri').'&scope='. rawurlencode($scope).'&state='. $state;
    }

    public function getAccessToken()
    {
        if ($this->isExpired()) {
            $url = 'https://dribbble.com/oauth/token';
            $expires = 86000;

        // Initialize curl
            $ch = curl_init();

            // Set the options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                'client_id='.$this->config->get('client_id').'&client_secret='.$this->config->get('client_secret').'&code='.$this->code.'&redirect_uri='.$this->config->get('redirect_uri'));

            $result = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($result);

            $this->session->set('accessToken', $data->access_token);
            $this->session->set('expires', time() + $expires);
            return $data->access_token;
        }

        return $this->session->get('accessToken');
    }


     public function get($page = 1, $pageSize = 100, $sort = '', $order = 'desc')
    {
        $accessTokenUri = $this->makeAccessTokenQueryString();

        

        $this->url .= '?page='.$page.'&per_page='.$pageSize.'&sort='.$sort. '&' . $accessTokenUri;
        $data = $this->getDataUsingCurl();

        return $data;
    }


    public function me()
    {
        $this->url .= '/user';

        return $this;
    }

    public function user($username)
    {
        $this->url .= '/users/'.$username;

        return $this;
    } 

    public function org($orgname)
    {
        $this->url .= '/orgs/'.$orgname;

        return $this;
    }


    public function getTotalStars()
    {
        $totalStars = 0;
        $url = $this->url;
        $user = $this->info(); 
        $this->url = $url;
        $repos = $this->repos()->get(1, $user->public_repos);
        
        foreach ($repos as $repo) {
            $totalStars += $repo->stargazers_count;
        }

        return $totalStars;
    }

    protected function getDataUsingCurl()
    {
        $ch = curl_init();

        // Set the options
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');  // Required by API
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $result = curl_exec($ch);
        curl_close($ch);

        $obj = json_decode($result);

        return $obj;
    }

    protected function makeAccessTokenQueryString()
    {
        $accessToken = '';
        $accessTokenUri = '';
        $accessToken = $this->getAccessToken();
        $accessTokenUri = 'access_token='.$accessToken;

        return $accessTokenUri;
    
    }

    public function isExpired()
    {
        if (time() > $this->session->get('expires')) {
            return true;
        }

        return false;
    }

    public static function fromCamelCase($str, $glue = '-')
    {
        $str[0] = strtolower($str[0]);

        return strtolower(preg_replace('/([A-Z])/', $glue . strtolower('\\1'), $str));
    }
}
