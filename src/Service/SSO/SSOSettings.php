<?php

namespace App\Service\SSO;

class SSOSettings
{
    /**
     * @param array $settings
     */
    public function __construct(private array $settings = [])
    {
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param string $entityId
     * @return array
     */
    public function setIdpEntityId(string $entityId): array
    {
        $this->settings['idp']['entityId'] = $entityId;

        return $this->settings;
    }

    /**
     * @param string $idpSSOURL
     * @return array
     */
    public function setIdpSSOURL(string $idpSSOURL): array
    {
        $this->settings['idp']['singleSignOnService']['url'] = $idpSSOURL;

        return $this->settings;
    }

    /**
     * @param string $idpSLOURL
     * @return array
     */
    public function setIdpSLOURL(string $idpSLOURL): array
    {
        $this->settings['idp']['singleLogoutService']['url'] = $idpSLOURL;

        return $this->settings;
    }

    /**
     * @param string $IdpX509cert
     * @return array
     */
    public function setIdpX509cert(string $IdpX509cert): array
    {
        $this->settings['idp']['x509cert'] = $IdpX509cert;

        return $this->settings;
    }

    /**
     * @param string $entityId
     * @return array
     */
    public function setSPEntityId(string $entityId): array
    {
        $this->settings['sp']['entityId'] = $entityId;

        return $this->settings;
    }

    /**
     * @param string $spAcsURL
     * @return array
     */
    public function setSPAcsURL(string $spAcsURL): array
    {
        $this->settings['sp']['assertionConsumerService']['url'] = $spAcsURL;

        return $this->settings;
    }

    /**
     * @param string $spSLOURL
     * @return array
     */
    public function setSPSLOURL(string $spSLOURL): array
    {
        $this->settings['sp']['singleLogoutService']['url'] = $spSLOURL;

        return $this->settings;
    }

    /**
     * @param string $privateKey
     * @return array
     */
    public function setSPPrivateKey(string $privateKey): array
    {
        $this->settings['sp']['privateKey'] = $privateKey;

        return $this->settings;
    }
}
