<?php

declare(strict_types=1);

namespace Endroid\QrCode\Writer;

/**
 * @deprecated since 6.0, use GdTrait instead. This class will be removed in 7.0.
 */
abstract readonly class AbstractGdWriter implements WriterInterface, ValidatingWriterInterface
{
    use GdTrait;
}
