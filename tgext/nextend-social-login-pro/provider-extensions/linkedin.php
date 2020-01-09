<?php

class NextendSocialLoginPROProviderExtensionLinkedIn extends NextendSocialLoginPROProviderExtensionWithSyncData {

    /** @var NextendSocialPROProviderLinkedIn */
    protected $provider;

    public function providerEnabled() {

        parent::providerEnabled();

    }

    protected function getRemoteData($node = 'me') {
        if ($node === 'me') {
            return $this->provider->getMe();
        }

        return array();
    }
}