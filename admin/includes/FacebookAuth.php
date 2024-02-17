<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/blogoopklas2024/vendor/autoload.php';

use League\OAuth2\Client\Provider\Facebook;

class FacebookAuth
{
    private $provider;

    public function __construct()
    {
        $this->provider = new Facebook([
            'clientId'          => FACEBOOK_APP_ID,
            'clientSecret'      => FACEBOOK_APP_SECRET,
            'redirectUri'       => FACEBOOK_REDIRECT_URI,
            'graphApiVersion'   => 'v19.0',
        ]);
    }

    public function getAuthorizationUrl()
    {
        // Optional: Session state to mitigate CSRF
        $options = [
            'scope' => ['email'], // Add other scopes/permissions as needed
        ];

        return $this->provider->getAuthorizationUrl($options);
    }

    public function getAccessToken($code)
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public function getUserDetails($accessToken)
    {
        try {
            $resourceOwner = $this->provider->getResourceOwner($accessToken);
            return $resourceOwner->toArray();
        } catch (\Exception $e) {
            error_log('Error getting user details: ' . $e->getMessage());
            throw $e;
        }
    }
}
