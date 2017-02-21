<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayResponse;
use Granam\GpWebPay\Codes\ResponseDigestKeys;
use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\GpWebPay\Exceptions\BrokenResponse;

class CardPayResponseTest extends PayResponseTest
{
    /**
     * @test
     * @dataProvider provideValuesForResponse
     * @param string $operation
     * @param string $orderNumber
     * @param int $prCode
     * @param int $srCode
     * @param string $digest
     * @param string $digest1
     * @param string|null $merOrderNum
     * @param string|null $md
     * @param string|null $resultText
     * @param string|null $userParam1
     * @param string|null $addInfo
     * @param bool $hasError
     */
    public function I_can_use_it(
        string $operation,
        string $orderNumber,
        int $prCode,
        int $srCode,
        string $digest,
        string $digest1,
        string $merOrderNum = null,
        string $md = null,
        string $resultText = null,
        string $userParam1 = null,
        string $addInfo = null,
        bool $hasError
    )
    {
        $cardPayResponse = new CardPayResponse(
            $operation,
            $orderNumber,
            $prCode,
            $srCode,
            $digest,
            $digest1,
            $merOrderNum,
            $md,
            $resultText,
            $userParam1,
            $addInfo
        );
        self::assertSame($hasError, $cardPayResponse->hasError());
        self::assertSame($digest, $cardPayResponse->getDigest());
        self::assertSame($digest1, $cardPayResponse->getDigest1());
        self::assertSame($prCode, $cardPayResponse->getPrCode());
        self::assertSame($srCode, $cardPayResponse->getSrCode());
        self::assertSame($resultText, $cardPayResponse->getResultText());
        $parameters = [
            ResponseDigestKeys::OPERATION => $operation,
            ResponseDigestKeys::ORDERNUMBER => $orderNumber,
            ResponseDigestKeys::MERORDERNUM => $merOrderNum,
            ResponseDigestKeys::MD => $md,
            ResponseDigestKeys::PRCODE => $prCode,
            ResponseDigestKeys::SRCODE => $srCode,
            ResponseDigestKeys::RESULTTEXT => $resultText,
            ResponseDigestKeys::USERPARAM1 => $userParam1,
            ResponseDigestKeys::ADDINFO => $addInfo,
        ];
        self::assertSame(
            array_filter(
                $parameters,
                function ($value) {
                    return $value !== null;
                }
            ),
            $cardPayResponse->getParametersForDigest()
        );
        $parameters[ResponsePayloadKeys::DIGEST] = $digest;
        $parameters[ResponsePayloadKeys::DIGEST1] = $digest1;
        self::assertEquals($cardPayResponse, CardPayResponse::createFromArray($parameters));
    }

    public function provideValuesForResponse()
    {
        return [
            ['foo', 'bar', 0, 0, 'baz', 'qux', 'FOO', 'BAR', 'BAZ', 'QUX', 'FooBar', false],
            ['foo', 'bar', 0, 123, 'baz', 'qux', 'FOO', null, 'BAR', null, 'BAZ', false],
            ['foo', 'bar', 456, 0, 'baz', 'qux', null, null, null, null, null, true],
        ];
    }

    /**
     * @test
     * @dataProvider provideValuesForResponse
     * @param string $operation
     * @param string $orderNumber
     * @param int $prCode
     * @param int $srCode
     * @param string $digest
     * @param string $digest1
     * @param string|null $merOrderNum
     * @param string|null $md
     * @param string|null $resultText
     * @param string|null $userParam1
     * @param string|null $addInfo
     */
    public function I_can_not_create_it_from_array_with_missing_required_value(
        string $operation,
        string $orderNumber,
        int $prCode,
        int $srCode,
        string $digest,
        string $digest1,
        string $merOrderNum = null,
        string $md = null,
        string $resultText = null,
        string $userParam1 = null,
        string $addInfo = null
    )
    {
        $parameters = [
            ResponseDigestKeys::OPERATION => $operation,
            ResponseDigestKeys::ORDERNUMBER => $orderNumber,
            ResponseDigestKeys::MERORDERNUM => $merOrderNum,
            ResponseDigestKeys::MD => $md,
            ResponseDigestKeys::PRCODE => $prCode,
            ResponseDigestKeys::SRCODE => $srCode,
            ResponseDigestKeys::RESULTTEXT => $resultText,
            ResponseDigestKeys::USERPARAM1 => $userParam1,
            ResponseDigestKeys::ADDINFO => $addInfo,
            ResponsePayloadKeys::DIGEST => $digest,
            ResponsePayloadKeys::DIGEST1 => $digest1,
        ];
        foreach ([ResponseDigestKeys::OPERATION, ResponseDigestKeys::ORDERNUMBER, ResponseDigestKeys::PRCODE, ResponseDigestKeys::SRCODE, ResponsePayloadKeys::DIGEST, ResponsePayloadKeys::DIGEST1] as $requiredParameter) {
            $invalidParameters = $parameters;
            $invalidParameters[$requiredParameter] = null;
            try {
                CardPayResponse::createFromArray($invalidParameters);
                self::fail('Expected ' . BrokenResponse::class . ' has not been thrown');
            } catch (BrokenResponse $brokenResponse) {
                self::assertRegExp('~' . preg_quote($requiredParameter, '~') . '~', $brokenResponse->getMessage());
            }
        }
    }
}