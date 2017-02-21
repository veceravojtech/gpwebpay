<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayRequest;
use Granam\GpWebPay\CardPayRequestValues;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\GpWebPay\DigestSignerInterface;
use Granam\GpWebPay\Provider;
use Granam\GpWebPay\SettingsInterface;
use Granam\Tests\Tools\TestWithMockery;

class ProviderTest extends TestWithMockery
{
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
     * @param string $url
     * @return \Mockery\MockInterface|SettingsInterface
     */
    private function createSettings(string $url)
    {
        $settings = $this->mockery(SettingsInterface::class);
        $settings->shouldReceive('getMerchantNumber')
            ->andReturn('merchant number');
        $settings->shouldReceive('getUrlForResponse')
            ->andReturn('URL for response');
        $settings->shouldReceive('getBaseUrlForRequest')
            ->andReturn($url);

        return $settings;
    }

    /**
     * @param string $digest
     * @return \Mockery\MockInterface|DigestSignerInterface
     */
    private function createDigestSigner(string $digest)
    {
        $digestSigner = $this->mockery(DigestSignerInterface::class);
        $digestSigner->shouldReceive('createSignedDigest')
            ->andReturn($digest);

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
}