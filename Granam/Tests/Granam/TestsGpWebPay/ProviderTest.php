<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayRequest;
use Granam\GpWebPay\CardPayRequestValues;
use Granam\GpWebPay\CardPayResponse;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\GpWebPay\Codes\ResponseDigestKeys;
use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\GpWebPay\DigestSignerInterface;
use Granam\GpWebPay\Provider;
use Granam\GpWebPay\SettingsInterface;
use Granam\Tests\Tools\TestWithMockery;

class ProviderTest extends TestWithMockery
{
    use  CardPayProviderInterfaceTest;

    /**
     * @test
     */
    public function I_can_create_request_by_it()
    {
        $provider = new Provider($this->createSettings($url = 'FOO://BAR'), $this->createDigestSigner($digest = 'dig'));
        $cardPayRequest = $provider->createCardPayRequest($values = $this->createCardPayRequestValues());
        self::assertInstanceOf(CardPayRequest::class, $cardPayRequest);
        self::assertSame(
            $digest,
            $cardPayRequest->getParametersForRequest()[RequestPayloadKeys::DIGEST],
            'Digest from settings was not propagated'
        );
        self::assertRegExp(
            '~' . preg_quote($url, '~') . '~',
            $cardPayRequest->getRequestUrl(),
            'Request URL from settings was not propagated'
        );
    }

    /**
     * @param string|null $url
     * @param string|null $merchantNumber
     * @return \Mockery\MockInterface|SettingsInterface
     */
    private function createSettings(string $url = null, string $merchantNumber = null)
    {
        $settings = $this->mockery(SettingsInterface::class);
        $settings->shouldReceive('getMerchantNumber')
            ->andReturn($merchantNumber ?? 'merchant number');
        $settings->shouldReceive('getUrlForResponse')
            ->andReturn('URL for response');
        $settings->shouldReceive('getBaseUrlForRequest')
            ->andReturn($url ?? 'base URL for request');

        return $settings;
    }

    /**
     * @param string $digest
     * @return \Mockery\MockInterface|DigestSignerInterface
     */
    private function createDigestSigner(string $digest = null)
    {
        $digestSigner = $this->mockery(DigestSignerInterface::class);
        if ($digest !== null) {
            $digestSigner->shouldReceive('createSignedDigest')
                ->andReturn($digest);
        }

        return $digestSigner;
    }

    /**
     * @return \Mockery\MockInterface|CardPayRequestValues
     * @throws \LogicException
     */
    private function createCardPayRequestValues()
    {
        $cardPayRequestValues = $this->mockery(CardPayRequestValues::class);
        $reflection = new \ReflectionClass(CardPayRequestValues::class);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC ^ \ReflectionMethod::IS_STATIC) as $reflectionMethod) {
            if (strpos($reflectionMethod->getName(), 'get') !== 0) {
                continue;
            }
            $docComment = $reflectionMethod->getDocComment();
            if (!preg_match('~@return\s+(?<returnType>\S+(?:\s*\S+)*)~', $docComment, $matches)) {
                throw new \LogicException(
                    "Method {$reflectionMethod->getName()} of class " . CardPayRequestValues::class
                    . ' has missing @return annotation'
                );
            }
            if (!preg_match('~(?<scalar>string|int)~', $matches['returnType'], $scalarMatches)) {
                continue;
            }
            $returnValue = null;
            if (strpos($matches['returnType'], 'null') === false) {
                switch ($scalarMatches['scalar']) {
                    case 'string' :
                        $returnValue = 'foo';
                        break;
                    case 'int' :
                        $returnValue = 123;
                        break;
                }
            }
            $cardPayRequestValues->shouldReceive($reflectionMethod->getName())
                ->andReturn($returnValue);
        }

        return $cardPayRequestValues;
    }

    /**
     * @test
     */
    public function I_can_create_response_by_it()
    {
        $provider = new Provider($this->createSettings(), $this->createDigestSigner());
        $values = [
            ResponseDigestKeys::OPERATION => 'foo',
            ResponseDigestKeys::ORDERNUMBER => 'bar',
            ResponseDigestKeys::PRCODE => 123,
            ResponseDigestKeys::SRCODE => 456,
            ResponsePayloadKeys::DIGEST => 'baz',
            ResponsePayloadKeys::DIGEST1 => 'qux',
        ];
        self::assertEquals(CardPayResponse::createFromArray($values), $provider->createCardPayResponse($values));
    }

    /**
     * @test
     */
    public function I_can_verify_response_by_it()
    {
        $provider = new Provider(
            $this->createSettings(null /* URL is not needed */, $merchantNumber = 'qux'),
            $this->createDigestSignerForVerification(
                $digest = 'bar',
                $parametersForDigest = ['foo'],
                true,
                $digest1 = 'baz',
                $merchantNumber,
                true
            )
        );
        self::assertTrue(
            $provider->verifyCardPayResponse(
                $this->createCardPayResponse($parametersForDigest, $digest, $digest1, false)
            )
        );
    }

    /**
     * @param array $parametersForDigest
     * @param string $digest
     * @param string $digest1
     * @param bool $hasError
     * @param int|null $prCode
     * @param int|null $srCode
     * @param string|null $resultText
     * @return \Mockery\MockInterface|CardPayResponse
     */
    private function createCardPayResponse(
        array $parametersForDigest,
        string $digest,
        string $digest1,
        bool $hasError,
        int $prCode = null,
        int $srCode = null,
        string $resultText = null
    )
    {
        $cardPayResponse = $this->mockery(CardPayResponse::class);
        $cardPayResponse->shouldReceive('getParametersForDigest')
            ->andReturn($parametersForDigest);
        $cardPayResponse->shouldReceive('getDigest')
            ->andReturn($digest);
        $cardPayResponse->shouldReceive('getDigest1')
            ->andReturn($digest1);
        $cardPayResponse->shouldReceive('hasError')
            ->andReturn($hasError);
        $cardPayResponse->shouldReceive('getPrCode')
            ->andReturn($prCode);
        $cardPayResponse->shouldReceive('getSrCode')
            ->andReturn($srCode);
        $cardPayResponse->shouldReceive('getResultText')
            ->andReturn($resultText);

        return $cardPayResponse;
    }

    /**
     * @param string $digestToVerify
     * @param array $responseParameters
     * @param bool $digestVerified
     * @param string $digest1ToVerify
     * @param string $merchantNumber
     * @param bool $digestVerified1
     * @return \Mockery\MockInterface|DigestSignerInterface
     */
    private function createDigestSignerForVerification(
        string $digestToVerify,
        array $responseParameters,
        bool $digestVerified,
        string $digest1ToVerify,
        string $merchantNumber,
        bool $digestVerified1
    )
    {
        $digestSigner = $this->mockery(DigestSignerInterface::class);
        $digestSigner->shouldReceive('verifySignedDigest')
            ->with($digestToVerify, $responseParameters)
            ->andReturn($digestVerified);
        $extendedResponseParameters = $responseParameters;
        $extendedResponseParameters[RequestPayloadKeys::MERCHANTNUMBER] = $merchantNumber;
        $digestSigner->shouldReceive('verifySignedDigest')
            ->with($digest1ToVerify, $extendedResponseParameters)
            ->andReturn($digestVerified1);

        return $digestSigner;
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @expectedExceptionMessageRegExp ~'foo'~
     */
    public function I_am_stopped_when_digest_does_not_match()
    {
        $provider = new Provider(
            $this->createSettings(null /* URL is not needed */, $merchantNumber = 'qux'),
            $this->createDigestSignerForVerification(
                $digest = 'bar',
                $parametersForDigest = ['foo'],
                false, // first digest as invalid
                $digest1 = 'baz',
                $merchantNumber,
                true
            )
        );
        $provider->verifyCardPayResponse($this->createCardPayResponse($parametersForDigest, $digest, $digest1, false));
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @expectedExceptionMessageRegExp ~'FOO'~
     */
    public function I_am_stopped_when_digest1_does_not_match()
    {
        $provider = new Provider(
            $this->createSettings(null /* URL is not needed */, $merchantNumber = 'qux'),
            $this->createDigestSignerForVerification(
                $digest = 'bar',
                $parametersForDigest = ['FOO'],
                true,
                $digest1 = 'baz',
                $merchantNumber,
                false // second digest as invalid
            )
        );
        $provider->verifyCardPayResponse($this->createCardPayResponse($parametersForDigest, $digest, $digest1, false));
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\GpWebPayResponseHasAnError
     * @expectedExceptionMessageRegExp ~^Time for service - .+123/456$~
     */
    public function I_am_stopped_when_response_reports_error()
    {
        $provider = new Provider(
            $this->createSettings(null /* URL is not needed */, $merchantNumber = 'qux'),
            $this->createDigestSignerForVerification(
                $digest = 'bar',
                $parametersForDigest = ['foo'],
                true,
                $digest1 = 'baz',
                $merchantNumber,
                true
            )
        );
        $provider->verifyCardPayResponse(
            $this->createCardPayResponse(
                $parametersForDigest,
                $digest,
                $digest1,
                true, /* response has error */
                123, // response error code PRCODE
                456, // response detail error code SRCODE
                'Time for service' // error message from GPWebPay
            )
        );
    }
}