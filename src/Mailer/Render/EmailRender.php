<?php

namespace App\Mailer\Render;

abstract class EmailRender
{
    abstract public function render(string $template, array $data): RenderedEmail;
}