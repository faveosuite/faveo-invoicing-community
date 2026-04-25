<?php

namespace CraigPaul\Mail;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class PostmarkServerTokenHeader extends UnstructuredHeader
{
    public const NAME = 'X-Postmark-Server-Token';

    public function __construct(string $value)
    {
        parent::__construct(self::NAME, $value);
    }
}
