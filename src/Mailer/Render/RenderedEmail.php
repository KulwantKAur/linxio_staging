<?php

namespace App\Mailer\Render;

class RenderedEmail
{
    private $subject;
    private $body;

    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function subject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }
}