<?php
require_once NSL_PATH . '/includes/oauth2.php';

class NextendSocialProviderYahooClient extends NextendSocialOauth2 {

    protected $access_token_data = array(
        'access_token' => '',
        'expires_in'   => -1,
        'created'      => -1
    );

    protected $endpointAuthorization = 'https://api.login.yahoo.com/oauth2/request_auth';

    protected $endpointAccessToken = 'https://api.login.yahoo.com/oauth2/get_token';

    protected $endpointRestAPI = 'https://social.yahooapis.com/v1/user/';

    protected $defaultRestParams = array(
        'format' => 'json',
    );

    protected $scopes = array();


    public function getYahooOpenID() {
        if (isset($this->access_token_data['id_token']) && !empty($this->access_token_data['id_token'])) {
            return $this->access_token_data['id_token'];
        }

        return false;

    }

    /**
     * @param string $api_permission
     */
    public function setApiPermissionScope($api_permission) {
        switch ($api_permission) {
            case 'r':
                $this->scopes[] = 'openid sdps-r';
                break;
            case 'rw':
                $this->scopes[] = 'sdpp-w';
                break;
        }
    }
}

