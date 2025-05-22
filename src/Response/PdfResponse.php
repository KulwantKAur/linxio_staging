<?php

namespace App\Response;


use Symfony\Component\HttpFoundation\Response;

class PdfResponse extends Response
{
    /**
     * @param mixed $data
     * @param int $status
     * @param array $headers
     */
    public function __construct($data, $status = 200, $headers = [])
    {
        parent::__construct($data, $status, $headers);

        $this->headers->set('Content-Type', 'application/pdf');
    }

    public function getData()
    {
        return $this->getContent();
    }
}