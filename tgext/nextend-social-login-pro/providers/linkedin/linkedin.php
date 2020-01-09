<?php

class NextendSocialPROProviderLinkedIn extends NextendSocialProvider {

    /** @var NextendSocialProviderLinkedInClient */
    protected $client;

    protected $color = '#0274b3';

    protected $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="M18.66 24.03v-8.38c0-1.72-.5-3.44-2.5-3.44-2.02 0-2.85 1.72-2.85 3.48v8.34H7.94V8.69h5.37v2.06c1.4-1.8 2.63-2.55 4.85-2.55C20.4 8.2 24 9.25 24 15.3v8.73h-5.34zM3 6.4c-1.66 0-3-1.2-3-2.7C0 2.2 1.34 1 3 1c1.65 0 2.99 1.21 2.99 2.7 0 1.5-1.34 2.71-3 2.71zm2.67 17.62H.3V8.69h5.37v15.34z"/></svg>';

    protected $sync_fields = array();

    public function __construct() {
        $this->id    = 'linkedin';
        $this->label = 'LinkedIn';

        $this->path = dirname(__FILE__);

        $this->requiredFields = array(
            'client_id'     => 'Client ID',
            'client_secret' => 'Client Secret'
        );

        parent::__construct(array(
            'client_id'     => '',
            'client_secret' => '',
            'login_label'   => 'Continue with <b>LinkedIn</b>',
            'link_label'    => 'Link account with <b>LinkedIn</b>',
            'unlink_label'  => 'Unlink account from <b>LinkedIn</b>',
            'ask_email'     => 'always'
        ));
    }

    protected function forTranslation() {
        __('Continue with <b>LinkedIn</b>', 'nextend-facebook-connect');
        __('Link account with <b>LinkedIn</b>', 'nextend-facebook-connect');
        __('Unlink account from <b>LinkedIn</b>', 'nextend-facebook-connect');
    }

    public function validateSettings($newData, $postedData) {
        $newData = parent::validateSettings($newData, $postedData);

        foreach ($postedData AS $key => $value) {

            switch ($key) {
                case 'tested':
                    if ($postedData[$key] == '1' && (!isset($newData['tested']) || $newData['tested'] != '0')) {
                        $newData['tested'] = 1;
                    } else {
                        $newData['tested'] = 0;
                    }
                    break;
                case 'client_id':
                case 'client_secret':
                    $newData[$key] = trim(sanitize_text_field($value));
                    if ($this->settings->get($key) !== $newData[$key]) {
                        $newData['tested'] = 0;
                    }

                    if (empty($newData[$key])) {
                        \NSL\Notices::addError(sprintf(__('The %1$s entered did not appear to be a valid. Please enter a valid %2$s.', 'nextend-facebook-connect'), $this->requiredFields[$key], $this->requiredFields[$key]));
                    }
                    break;
                case 'redirect':
                case 'redirect_reg':
                case 'load_style':
                    $newData[$key] = trim(sanitize_text_field($value));
                    break;
            }
        }

        return $newData;
    }

    /**
     * @return NextendSocialProviderLinkedInClient
     */
    public function getClient() {
        if ($this->client === null) {

            require_once dirname(__FILE__) . '/linkedin-client.php';

            $this->client = new NextendSocialProviderLinkedInClient($this->id);

            $this->client->setClientId($this->settings->get('client_id'));
            $this->client->setClientSecret($this->settings->get('client_secret'));
            $this->client->setRedirectUri($this->getRedirectUri());
        }

        return $this->client;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getCurrentUserInfo() {
        $fields = array(
            'id',
            'firstName',
            'lastName',
            'profilePicture(displayImage~:playableStreams)'
        );

        $lite_profile = $this->getClient()
                             ->get('/me?projection=(' . implode(',', $fields) . ')');
        $email        = $this->getClient()
                             ->get('/emailAddress?q=members&projection=(elements*(handle~))');
        $profile_raw  = array_merge($lite_profile, $email);

        $profile = array();
        if (isset($profile_raw['id'])) {
            $profile['id'] = $profile_raw['id'];
        }
        if (isset($profile_raw['firstName']['localized']['en_US'])) {
            $profile['firstName'] = $profile_raw['firstName']['localized']['en_US'];
        }
        if (isset($profile_raw['lastName']['localized']['en_US'])) {
            $profile['lastName'] = $profile_raw['lastName']['localized']['en_US'];
        }
        if (isset($profile_raw['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'])) {
            $profile['profilePicture'] = $profile_raw['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'];
        }
        if (isset($profile_raw['elements'][0]['handle~']['emailAddress'])) {
            $profile['emailAddress'] = $profile_raw['elements'][0]['handle~']['emailAddress'];
        }

        return $profile;

    }

    public function getMe() {
        return $this->authUserData;
    }

    public function getAuthUserData($key) {
        switch ($key) {
            case 'id':
                return $this->authUserData['id'];
            case 'email':
                return $this->authUserData['emailAddress'];
            case 'name':
                return $this->authUserData['name'];
            case 'first_name':
                return $this->authUserData['firstName'];
            case 'last_name':
                return $this->authUserData['lastName'];
        }

        return parent::getAuthUserData($key);
    }

    public function syncProfile($user_id, $provider, $access_token) {

        if ($this->needUpdateAvatar($user_id)) {
            $this->updateAvatar($user_id, $this->authUserData['profilePicture']);
        }

        $this->storeAccessToken($user_id, $access_token);
    }

    public function getIcon() {
        return plugins_url('/providers/' . $this->id . '/' . $this->id . '.png', NSL_PRO_PATH_PLUGIN);
    }

    public function deleteLoginPersistentData() {
        parent::deleteLoginPersistentData();

        if ($this->client !== null) {
            $this->client->deleteLoginPersistentData();
        }
    }

    public function getAvatar($user_id) {

        if (!$this->isUserConnected($user_id)) {
            return false;
        }

        $picture = $this->getUserData($user_id, 'profile_picture');
        if (!$picture || $picture == '') {
            return false;
        }

        return $picture;
    }
}

NextendSocialLogin::addProvider(new NextendSocialPROProviderLinkedIn());