<?php
namespace Granam\GpWebPay;

interface CardPayProviderInterface
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
     * @param CardPayResponse $response
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayResponseHasAnError
     */
    public function verifyCardPayResponse(CardPayResponse $response): bool;
}