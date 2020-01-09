<?php
/*!
 * Hybridauth
 * https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
 *  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\User;

/**
 * LinkedIn OAuth2 provider adapter.
 */
class LinkedIn extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'r_liteprofile r_emailaddress w_member_social';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.linkedin.com/v2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.linkedin.com/docs/oauth2';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $fields = [
            "id",
            "firstName",
            "lastName",
            "profilePicture(displayImage~:playableStreams)",
        ];


        $response = $this->apiRequest('me?projection=(' . implode(',', $fields) . ')');
        $data     = new Data\Collection($response);
		
        //$response = $this->apiRequest('people/~:(' . implode(',', $fields) . ')', 'GET', ['format' => 'json']);
        // $data     = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('id');
        $userProfile->firstName   = $data->filter('firstName')->filter('localized')->get('en_US');
        $userProfile->lastName    = $data->filter('lastName')->filter('localized')->get('en_US');
        $userProfile->photoURL    = $this->getUserPhotoUrl($data->filter('profilePicture')->filter('displayImage~')->get('elements'));
        $userProfile->email       = $this->getUserEmail();


        $userProfile->emailVerified = $userProfile->email;

        $userProfile->displayName = trim($userProfile->firstName . ' ' . $userProfile->lastName);

        return $userProfile;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://developer.linkedin.com/docs/share-on-linkedin
     */
    public function setUserStatus($status)
    {
        $status = is_string($status) ? ['comment' => $status] : $status;
        if (!isset($status['visibility'])) {
            $status['visibility']['code'] = 'anyone';
        }

        $headers = [
            'Content-Type' => 'application/json',
            'x-li-format'  => 'json',
        ];

        $response = $this->apiRequest('people/~/shares?format=json', 'POST', $status, $headers);

        return $response;
    }
}
