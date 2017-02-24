<?php
namespace Granam\GpWebPay;

interface CardPayProvider
{
    /**
     * @param CardPayRequestValues $requestValues
     * @return CardPayRequest
     */
    public function createCardPayRequest(CardPayRequestValues $requestValues): CardPayRequest;

    /**
     * @param array $valuesFromGetOrPost
     * @return CardPayResponse
     */
    public function createCardPayResponse(array $valuesFromGetOrPost): CardPayResponse;

    /**
     * @param PayResponse $response
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayErrorResponse
     */
    public function verifyPayResponse(PayResponse $response): bool;
}