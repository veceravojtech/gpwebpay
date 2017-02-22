<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayRequestValues;
use Granam\GpWebPay\Codes\CurrencyCodes;
use Granam\GpWebPay\Codes\PayMethodCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Tests\Tools\TestWithMockery;

class CardPayRequestValuesTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideParameters
     * @param int $orderNumber
     * @param float $price
     * @param int $currencyNumericCode
     * @param bool $depositFlag
     * @param string|null $merchantNote
     * @param string|null $description
     * @param int|null $merchantOrderIdentification
     * @param string|null $lang
     * @param string|null $payMethod
     * @param string|null $disabledPayMethod
     * @param array|null $payMethods
     * @param string|null $cardHolderEmail
     * @param string|null $referenceNumber
     * @param string|null $addInfo
     * @param string|null $fastPayId
     */
    public function I_can_create_it_both_directly_and_from_array(
        int $orderNumber,
        float $price,
        int $currencyNumericCode,
        bool $depositFlag,
        string $merchantNote = null,
        string $description = null,
        int $merchantOrderIdentification = null,
        string $lang = null,
        string $payMethod = null,
        string $disabledPayMethod = null,
        array $payMethods = null,
        string $cardHolderEmail = null,
        string $referenceNumber = null,
        string $addInfo = null,
        string $fastPayId = null
    )
    {
        $currencyCodes = $this->createCurrencyCodes($currencyNumericCode, $currencyPrecision = 0);
        $fromArrayCardPayRequestValues = CardPayRequestValues::createFromArray(
            [
                RequestDigestKeys::ORDERNUMBER => $orderNumber,
                RequestDigestKeys::AMOUNT => $price,
                RequestDigestKeys::CURRENCY => $currencyNumericCode,
                RequestDigestKeys::DEPOSITFLAG => $depositFlag,
                // optional
                strtolower(RequestDigestKeys::MD) => $merchantNote,
                RequestDigestKeys::DESCRIPTION => $description,
                lcfirst(RequestDigestKeys::MERORDERNUM) => $merchantOrderIdentification,
                RequestPayloadKeys::LANG => $lang,
                RequestDigestKeys::PAYMETHOD => $payMethod,
                RequestDigestKeys::DISABLEPAYMETHOD => $disabledPayMethod,
                RequestDigestKeys::PAYMETHODS => $payMethods,
                RequestDigestKeys::EMAIL => $cardHolderEmail,
                RequestDigestKeys::REFERENCENUMBER => $referenceNumber,
                RequestDigestKeys::ADDINFO => $addInfo,
                RequestDigestKeys::FASTPAYID => $fastPayId,
            ],
            $currencyCodes
        );
        $newCardPayRequestValues = new CardPayRequestValues(
            $currencyCodes,
            $orderNumber,
            $price,
            $currencyNumericCode,
            $depositFlag,
            $merchantNote,
            $description,
            $merchantOrderIdentification,
            $lang,
            $payMethod,
            $disabledPayMethod,
            $payMethods,
            $cardHolderEmail,
            $referenceNumber,
            $addInfo,
            $fastPayId
        );
        self::assertEquals($fromArrayCardPayRequestValues, $newCardPayRequestValues);
    }

    /**
     * @param string $expectedCurrencyCode
     * @param int $currencyPrecision
     * @return \Mockery\MockInterface|CurrencyCodes
     */
    private function createCurrencyCodes(string $expectedCurrencyCode, int $currencyPrecision)
    {
        $currencyCodes = $this->mockery(CurrencyCodes::class);
        $currencyCodes->shouldReceive('getCurrencyPrecision')
            ->with($expectedCurrencyCode)
            ->andReturn($currencyPrecision);
        $currencyCodes->shouldReceive('isCurrencyNumericCode')
            ->with($expectedCurrencyCode)
            ->andReturn(true);
        $currencyCodes->shouldReceive('isCurrencyNumericCode')
            ->andReturn(false);

        return $currencyCodes;
    }

    public function provideParameters()
    {
        $orderNumber = 123;
        $price = 456.789;
        $currencyNumericCode = 978; // EUR
        $depositFlag = true;
        $merchantNote = 'Just a note';
        $description = 'Bought happiness';
        $merchantOrderIdentification = 135;
        $lang = 'cs';
        $payMethod = PayMethodCodes::CRD;
        $disabledPayMethod = PayMethodCodes::BTNCS;
        $payMethods = PayMethodCodes::getPayMethodCodes();
        $cardHolderEmail = 'someone@example.com';
        $referenceNumber = 246;
        $addInfo = '<?xml ?>';
        $fastPayId = 1470;

        return [
            // all values
            [
                $orderNumber,
                $price,
                $currencyNumericCode,
                $depositFlag,
                $merchantNote,
                $description,
                $merchantOrderIdentification,
                $lang,
                $payMethod,
                $disabledPayMethod,
                $payMethods,
                $cardHolderEmail,
                $referenceNumber,
                $addInfo,
                $fastPayId,
            ],
            // only required
            [
                $orderNumber,
                $price,
                $currencyNumericCode,
                $depositFlag,
            ],
        ];
    }
}