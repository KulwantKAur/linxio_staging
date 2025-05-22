<?php

namespace App\Response;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CsvResponse extends Response
{
    private $serializer;
    private $data;
    private $context = [];
    private ?User $user;

    public function __construct(
        array $data = [],
        $status = 200,
        $headers = [],
        $noCsvHeader = true,
        $defaultContext = [],
        ?User $user = null
    ) {
        parent::__construct('', $status, $headers);
        $this->user = $user;

        $this->serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder($defaultContext)]);
        $this->setData($data, ['no_headers' => $noCsvHeader]);
    }

//    /**
//     * @param array context
//     *
//     * @return $this
//     */
//    public function setContext(array $context)
//    {
//        $data = $this->serializer->decode($this->data, 'csv', $this->context);
//        $this->context = $context;
//
//        return $this->setData($data);
//    }

    /**
     * @param $data
     * @param array $context
     *
     * @return $this
     */
    public function setData($data, array $context)
    {
        if ($this->user && ($data ?? null)) {
            $firstKey = array_key_first($data[0] ?? []);
            $data[][$firstKey] = '';
            $data[][$firstKey] = '';
            $data[][$firstKey] = 'Timezone: ' . $this->user->getTimezoneText();
        }

        $this->data = $this->serializer->encode($data, 'csv', $context);

        return $this->update();
    }

    /**
     * @return $this
     */
    protected function update()
    {
        $disposition = $this->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('file_%s.csv', time()));
        $this->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $this->headers->set('Content-Disposition', $disposition);

        return $this->setContent($this->data);
    }

    public function getData()
    {
        return $this->data;
    }
}