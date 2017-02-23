<?php
namespace Granam\GpWebPay;

interface PayRequest extends \IteratorAggregate
{
    /**
     * @return string
     */
    public function getRequestUrlForGet(): string;

    /**
     * @return array
     */
    public function getParametersForRequest(): array;
}