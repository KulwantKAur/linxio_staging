<?php

namespace App\Mailer\Render;

use Twig\Environment;

class TwigEmailRender extends EmailRender
{
    private $twig;

    /**
     * TwigEmailRender constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string $template
     * @param array $data
     * @return RenderedEmail
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $template, array $data): RenderedEmail
    {
        $data = $this->twig->mergeGlobals($data);
        $template = $this->twig->load($template);
        $subject = $template->renderBlock('subject', $data);
        $body = $template->render($data);

        return new RenderedEmail($subject, $body);
    }
}