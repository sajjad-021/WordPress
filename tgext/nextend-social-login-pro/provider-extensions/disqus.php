<?php

class NextendSocialLoginPROProviderExtensionDisqus extends NextendSocialLoginPROProviderExtensionWithSyncData {

    /** @var NextendSocialPROProviderDisqus */
    protected $provider;

    public function providerEnabled() {

        parent::providerEnabled();
    }

    protected function getRemoteData($node = 'me') {
        switch ($node) {
            case 'me':
                return $this->provider->getMe();
        }

        return array();
    }
}