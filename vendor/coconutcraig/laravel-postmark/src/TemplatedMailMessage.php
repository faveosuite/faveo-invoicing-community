<?php

namespace CraigPaul\Mail;

use Illuminate\Notifications\Messages\MailMessage as Message;

class TemplatedMailMessage extends Message
{
    protected ?string $alias = null;

    protected array $data = [];

    protected ?int $id = null;

    public $view = 'postmark::template';

    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
            'model' => $this->data,
        ];
    }

    public function identifier(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function include(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
