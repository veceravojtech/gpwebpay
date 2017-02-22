<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayRequestValues;
use Granam\GpWebPay\Codes\CurrencyCodes;
use Granam\GpWebPay\Codes\PayMethodCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\GpWebPay\Exceptions\ValueTooLong;
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
     * @param int|null $merchantOrderIdentification
     * @param string|null $description
     * @param string|null $merchantNote
     * @param string|null $fastPayId
     * @param string|null $payMethod
     * @param string|null $disabledPayMethod
     * @param array|null $payMethods
     * @param string|null $cardHolderEmail
     * @param string|null $referenceNumber
     * @param string|null $addInfo
     * @param string|null $lang
     */
    public function I_can_create_it_both_directly_and_from_array(
        int $orderNumber,
        float $price,
        int $currencyNumericCode,
        bool $depositFlag,
        int $merchantOrderIdentification = null,
        string $merchantNote = null,
        string $description = null,
        string $fastPayId = null,
        string $payMethod = null,
        string $disabledPayMethod = null,
        array $payMethods = null,
        string $cardHolderEmail = null,
        string $referenceNumber = null,
        string $addInfo = null,
        string $lang = null
    )
    {
        $currencyCodes = $this->createCurrencyCodes($currencyNumericCode, $currencyPrecision = 2);
        $arrayParameters = [
            RequestDigestKeys::ORDERNUMBER => $orderNumber,
            RequestDigestKeys::AMOUNT => $price,
            RequestDigestKeys::CURRENCY => $currencyNumericCode,
            RequestDigestKeys::DEPOSITFLAG => $depositFlag,
            // optional
            lcfirst(RequestDigestKeys::MERORDERNUM) => $merchantOrderIdentification,
            RequestDigestKeys::DESCRIPTION => $description,
            strtolower(RequestDigestKeys::MD) => $merchantNote,
            RequestDigestKeys::FASTPAYID => $fastPayId,
            RequestDigestKeys::PAYMETHOD => $payMethod,
            RequestDigestKeys::DISABLEPAYMETHOD => $disabledPayMethod,
            RequestDigestKeys::PAYMETHODS => $payMethods,
            RequestDigestKeys::EMAIL => $cardHolderEmail,
            RequestDigestKeys::REFERENCENUMBER => $referenceNumber,
            RequestDigestKeys::ADDINFO => $addInfo,
            RequestPayloadKeys::LANG => $lang,
        ];
        $arrayParameters = array_filter($arrayParameters, function ($parameter) {
            return $parameter !== null || random_int(0, 1) > 1; // to cover all combinations
        });
        $fromArrayCardPayRequestValues = CardPayRequestValues::createFromArray($arrayParameters, $currencyCodes);
        $newCardPayRequestValues = new CardPayRequestValues(
            $currencyCodes,
            $orderNumber,
            $price,
            $currencyNumericCode,
            $depositFlag,
            $merchantOrderIdentification,
            $description,
            $merchantNote,
            $fastPayId,
            $payMethod,
            $disabledPayMethod,
            $payMethods,
            $cardHolderEmail,
            $referenceNumber,
            $addInfo,
            $lang
        );
        self::assertEquals($fromArrayCardPayRequestValues, $newCardPayRequestValues);
        self::assertSame((int)round($price * 100), $newCardPayRequestValues->getAmount());
    }

    /**
     * @param string|null $expectedCurrencyCode
     * @param int|null $currencyPrecision
     * @param bool $isValidCurrencyCode = true
     * @return \Mockery\MockInterface|CurrencyCodes
     */
    private function createCurrencyCodes(
        string $expectedCurrencyCode = null,
        int $currencyPrecision = null,
        bool $isValidCurrencyCode = true
    )
    {
        $currencyCodes = $this->mockery(CurrencyCodes::class);
        $currencyCodes->shouldReceive('getCurrencyPrecision')
            ->with($expectedCurrencyCode)
            ->andReturn($currencyPrecision);
        $currencyCodes->shouldReceive('isCurrencyNumericCode')
            ->with($expectedCurrencyCode)
            ->andReturn($isValidCurrencyCode);
        $currencyCodes->shouldReceive('isCurrencyNumericCode')
            ->andReturn(!$isValidCurrencyCode);

        return $currencyCodes;
    }

    public function provideParameters()
    {
        $orderNumber = 123;
        $price = 456.789;
        $currencyNumericCode = 978; // EUR
        $depositFlag = true;
        $merchantOrderIdentification = 135;
        $description = 'Bought happiness';
        $merchantNote = 'Just a note';
        $fastPayId = 1470;
        $payMethod = PayMethodCodes::CRD;
        $disabledPayMethod = PayMethodCodes::BTNCS;
        $payMethods = PayMethodCodes::getPayMethodCodes();
        $cardHolderEmail = 'someone@example.com';
        $referenceNumber = 246;
        $addInfo = '<?xml ?>';
        $lang = 'cs';

        return [
            // all values
            [
                $orderNumber,
                $price,
                $currencyNumericCode,
                $depositFlag,
                $merchantOrderIdentification,
                $description,
                $merchantNote,
                $fastPayId,
                $payMethod,
                $disabledPayMethod,
                $payMethods,
                $cardHolderEmail,
                $referenceNumber,
                $addInfo,
                $lang,
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

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\InvalidRequest
     * @expectedExceptionMessageRegExp ~DEPOSITFLAG~
     */
    public function I_can_not_create_it_from_array_without_all_required_parameters()
    {
        CardPayRequestValues::createFromArray(
            [
                RequestDigestKeys::ORDERNUMBER => 123,
                RequestDigestKeys::AMOUNT => 456,
                RequestDigestKeys::CURRENCY => 789,
                // missing deposit flag
            ],
            $this->createCurrencyCodes()
        );
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\InvalidRequest
     * @expectedExceptionMessageRegExp ~PAYMETHODS.+string~
     */
    public function I_can_not_create_it_from_array_with_invalid_list_of_payment_methods()
    {
        CardPayRequestValues::createFromArray(
            [
                RequestDigestKeys::ORDERNUMBER => 123,
                RequestDigestKeys::AMOUNT => 456,
                RequestDigestKeys::CURRENCY => 789,
                RequestDigestKeys::DEPOSITFLAG => true,
                RequestDigestKeys::PAYMETHODS => PayMethodCodes::CRD // should be array - cause exception
            ],
            $this->createCurrencyCodes()
        );
    }

    /**
     * @test
     * @dataProvider provideTooLongParameters
     * @expectedExceptionMessageRegExp ~~
     * @param int $orderNumber
     * @param float $price
     * @param int $currencyNumericCode
     * @param int $currencyPrecision
     * @param bool $depositFlag
     * @param int|null $merchantOrderIdentification
     * @param string|null $description
     * @param string|null $merchantNote
     * @param string|null $fastPayId
     * @param string|null $payMethod
     * @param string|null $disabledPayMethod
     * @param array|null $payMethods
     * @param string|null $cardHolderEmail
     * @param string|null $referenceNumber
     * @param string|null $addInfo
     * @param string|null $lang
     */
    public function I_can_not_create_it_with_too_long_parameters(
        int $orderNumber,
        float $price,
        int $currencyNumericCode,
        int $currencyPrecision,
        bool $depositFlag,
        int $merchantOrderIdentification = null,
        string $merchantNote = null,
        string $description = null,
        string $fastPayId = null,
        string $payMethod = null,
        string $disabledPayMethod = null,
        array $payMethods = null,
        string $cardHolderEmail = null,
        string $referenceNumber = null,
        string $addInfo = null,
        string $lang = null
    )
    {
        $parameters = func_get_args();
        unset($parameters[3]); // removing currency precision
        $parameters = array_values($parameters); // re-indexing
        $parametersCount = count($parameters);
        foreach ($parameters as $index => $tooLongParameter) {
            if ($tooLongParameter === null || in_array($index, [2 /* currency */, 3 /* deposit */], true)) {
                continue;
            }
            $roulette = array_fill(0, $parametersCount, null);
            $roulette[0] = 123; // order number
            $roulette[1] = 456.789; // price
            $roulette[2] = 978; // EUR
            $roulette[3] = true; // deposit flag
            $roulette[$index] = $tooLongParameter; // can overload even one of just set required parameter
            try {
                new CardPayRequestValues(
                    $this->createCurrencyCodes($roulette[2], $currencyPrecision),
                    $roulette[0],
                    $roulette[1],
                    $roulette[2],
                    $roulette[3],
                    $roulette[4],
                    $roulette[5],
                    $roulette[6],
                    $roulette[7],
                    $roulette[8],
                    $roulette[9],
                    $roulette[10],
                    $roulette[11],
                    $roulette[12],
                    $roulette[13],
                    $roulette[14]
                );
                $valueLength = strlen($roulette[$index]);
                $parametersReflections = (new \ReflectionClass(CardPayRequestValues::class))
                    ->getMethod('__construct')->getParameters();
                array_shift($parametersReflections); // removes CurrencyCodes to get value parameters only
                self::fail(
                    'Expected ' . ValueTooLong::class . ' to be thrown because of too long value for'
                    . " '{$parametersReflections[$index]->getName()}': (length {$valueLength}) '{$roulette[$index]}'"
                );
            } catch (ValueTooLong $valueTooLong) {
                self::assertNotEmpty($valueTooLong->getMessage());
            }
        }
    }

    public function provideTooLongParameters()
    {
        $orderNumber = 1234567891234567;
        $price = 1234567891234567;
        $currencyNumericCode = 978; // EUR
        $currencyPrecision = 2;
        $depositFlag = true;
        $merchantOrderIdentification = null;
        $description = str_repeat('b', 256);
        $merchantNote = str_repeat('a', 256);
        $fastPayId = (int)str_repeat('4', 16);
        $payMethod = null;
        $disabledPayMethod = null;
        $payMethods = null;
        $cardHolderEmail = str_repeat('c', 256);
        $referenceNumber = str_repeat('d', 21);
        $addInfo = str_repeat('e', 24001);
        $lang = null;

        return [
            [
                $orderNumber,
                $price,
                $currencyNumericCode,
                $currencyPrecision,
                $depositFlag,
                $merchantOrderIdentification,
                $description,
                $merchantNote,
                $fastPayId,
                $payMethod,
                $disabledPayMethod,
                $payMethods,
                $cardHolderEmail,
                $referenceNumber,
                $addInfo,
                $lang,
            ],
        ];
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\UnknownCurrency
     * @expectedExceptionMessageRegExp ~789~
     */
    public function I_can_not_use_unsupported_currency_code()
    {
        new CardPayRequestValues(
            $this->createCurrencyCodes(789, 321, false /* unsupported/unknown currency code */),
            123,
            456,
            789,
            false
        );
    }

    /**
     * @test
     */
    public function My_description_is_sanitized_with_warning_if_out_of_allowed_ascii_range()
    {
        $messageOutOfAllowedAsciiRange = 'こんにちは';
        error_clear_last();
        $previousErrorReporting = ini_set('error_reporting', -1 ^ E_USER_WARNING);
        new CardPayRequestValues(
            $this->createCurrencyCodes(789, 321),
            123,
            456,
            789,
            false,
            null,
            $messageOutOfAllowedAsciiRange // description
        );
        ini_set('error_reporting', $previousErrorReporting);
        $lastError = error_get_last();
        error_clear_last();
        self::assertNotEmpty($lastError);
        self::assertSame(E_USER_WARNING, $lastError['type']);
        self::assertRegExp(<<<'REGEXP'
~DESCRIPTION.+\D5\D.+'こ'.+'ko'.+'ん'.+'n'.+'に'.+'ni'.+'ち'.+'chi'.+'は'.+'ha'~s
REGEXP
            ,
            $lastError['message']
        );
    }

    /**
     * @test
     */
    public function My_description_is_truncated_with_warning_if_contains_character_which_can_not_be_detected_by_regexp()
    {
        $nonDetectableCharacter = chr(128) . chr(129);
        error_clear_last();
        $previousErrorReporting = ini_set('error_reporting', -1 ^ E_USER_WARNING);
        new CardPayRequestValues(
            $this->createCurrencyCodes(789, 321),
            123,
            456,
            789,
            false,
            null,
            $nonDetectableCharacter // description
        );
        ini_set('error_reporting', $previousErrorReporting);
        $lastError = error_get_last();
        error_clear_last();
        self::assertNotEmpty($lastError);
        self::assertSame(E_USER_WARNING, $lastError['type']);
        self::assertRegExp(
            '~DESCRIPTION.+128,129~s',
            $lastError['message']
        );
    }
}