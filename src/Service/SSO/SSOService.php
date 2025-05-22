<?php

namespace App\Service\SSO;

use App\Entity\SSOIntegration;
use App\Entity\SSOIntegrationCertificate;
use App\Entity\SSOIntegrationData;
use App\Exceptions\SSOException;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SSOService extends BaseService
{
    public function __construct(
        protected EntityManagerInterface   $em,
        protected JWTEncoderInterface      $JWTEncoder,
        protected EventDispatcherInterface $eventDispatcher,
        protected ValidatorInterface       $validator,
    ) {
    }

    /**
     * @param array $params
     * @param int $integrationId
     * @return SSOIntegrationData
     * @throws ValidationException
     */
    public function createIntegrationData(array $params, int $integrationId): SSOIntegrationData
    {
        $certificate = $params['certificate'] ?? null;
        $integration = $this->em->getRepository(SSOIntegration::class)->find($integrationId);

        if (!$integration) {
            throw new NotFoundHttpException('Integration is not found');
        }

        $integrationData = new SSOIntegrationData($params);
        $integrationData->setIntegration($integration);
        $this->validate($this->validator, $integrationData);
        $this->em->persist($integrationData);

        if ($certificate) {
            $integrationDataCertificate = $this->createIntegrationDataCertificate($params, $integrationData);
            $integrationData->addCertificate($integrationDataCertificate);
        }

        $this->em->flush();

        return $integrationData;
    }

    /**
     * @param array $params
     * @param SSOIntegrationData $integrationData
     * @return SSOIntegrationData
     * @throws ValidationException
     */
    public function updateIntegrationData(array $params, SSOIntegrationData $integrationData): SSOIntegrationData
    {
        $certificate = $params['certificate'] ?? null;
        $integrationData->setAttributes($params);
        $this->validate($this->validator, $integrationData);

        if ($certificate) {
            $integrationDataCertificate = $this->createIntegrationDataCertificate($params, $integrationData);
            $integrationData->addCertificate($integrationDataCertificate);
        }

        $this->em->flush();

        return $integrationData;
    }

    /**
     * @param string $idpEntityId
     * @return SSOIntegrationData
     * @throws SSOException
     */
    public function getIntegrationDataByIdpEntityId(string $idpEntityId): SSOIntegrationData
    {
        $SSOIntegrationData = $this->em->getRepository(SSOIntegrationData::class)
            ->findOneBy(['idpEntityId' => $idpEntityId]);

        if (!$SSOIntegrationData) {
            throw new SSOException('Integration data is not found for IdP: ' . $idpEntityId, Response::HTTP_NOT_FOUND);
        }
        if ($SSOIntegrationData->isDisabled()) {
            throw new SSOException('Integration is disabled for IdP: ' . $idpEntityId, Response::HTTP_NOT_FOUND);
        }

        return $SSOIntegrationData;
    }

    /**
     * @param array $params
     * @param SSOIntegrationData $integrationData
     * @return SSOIntegrationCertificate
     * @throws ValidationException
     */
    public function createIntegrationDataCertificate(
        array              $params,
        SSOIntegrationData $integrationData
    ): SSOIntegrationCertificate {
        $integrationDataCertificate = new SSOIntegrationCertificate($params);
        $integrationDataCertificate->setIntegrationData($integrationData);
        $this->validate($this->validator, $integrationDataCertificate);
        $this->em->persist($integrationDataCertificate);
        $this->em->flush();

        return $integrationDataCertificate;
    }

    /**
     * @param int $certificateId
     * @param SSOIntegrationData $integrationData
     * @return SSOIntegrationCertificate
     */
    public function deleteIntegrationDataCertificate(
        int                $certificateId,
        SSOIntegrationData $integrationData
    ): SSOIntegrationCertificate {

        $SSOIntegrationCert = $this->em->getRepository(SSOIntegrationCertificate::class)->find($certificateId);

        if (!$SSOIntegrationCert) {
            throw new NotFoundHttpException('Integration data certificate is not found');
        }

        $integrationData->removeCertificate($SSOIntegrationCert);
        $this->em->remove($SSOIntegrationCert);
        $this->em->flush();

        return $SSOIntegrationCert;
    }
}
