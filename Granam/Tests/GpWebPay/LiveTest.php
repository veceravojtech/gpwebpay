<?php
namespace Granam\Tests\GpWebPay;

use Alcohol\ISO4217;
use Granam\GpWebPay\CardPayRequest;
use Granam\GpWebPay\CardPayRequestValues;
use Granam\GpWebPay\Codes\CurrencyCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\DigestSigner;
use Granam\GpWebPay\Provider;
use Granam\GpWebPay\Settings;
use Granam\Tests\Tools\TestWithMockery;
use Symfony\Component\Yaml\Yaml;

/**
 * @group online
 */
class LiveTest extends TestWithMockery
{
    /**
     * @var Settings
     */
    private $settings;

    protected function setUp()
    {
        $liveTestConfigFile = __DIR__ . '/../webpay_live_test_config.yml';
        if (is_readable($liveTestConfigFile)) {
            $config = Yaml::parse(file_get_contents($liveTestConfigFile));
            $this->settings = $this->createSettings($config);
        } else {
            self::markTestSkipped('Config for live test is not available in ' . $liveTestConfigFile);
        }
    }

    const PRIVATE_KEY_FILE_INDEX = 'privateKeyFile';
    const PRIVATE_KEY_PASSWORD_INDEX = 'privateKeyPassword';
    const PUBLIC_KEY_FILE_INDEX = 'publicKeyFile';
    const BASE_URL_FOR_REQUEST_INDEX = 'baseUrlForRequest';
    const MERCHANT_NUMBER_INDEX = 'merchantNumber';

    /**
     * @param array $config
     * @return Settings
     * @throws \LogicException
     */
    private function createSettings(array $config)
    {
        foreach ([self::PRIVATE_KEY_FILE_INDEX, self::PUBLIC_KEY_FILE_INDEX, self::BASE_URL_FOR_REQUEST_INDEX, self::MERCHANT_NUMBER_INDEX] as $required) {
            if (empty($config[$required])) {
                throw new \LogicException("Required config entry '{$required}' for live test is missing");
            }
        }

        return new Settings(
            $config[self::BASE_URL_FOR_REQUEST_INDEX],
            preg_match('~^\\/~', $config[self::PRIVATE_KEY_FILE_INDEX])
                ? $config[self::PRIVATE_KEY_FILE_INDEX] // absolute path
                : __DIR__ . '/../' . $config[self::PRIVATE_KEY_FILE_INDEX], // relative to config file
            $config[self::PRIVATE_KEY_PASSWORD_INDEX],
            preg_match('~^\\/~', $config[self::PUBLIC_KEY_FILE_INDEX])
                ? $config[self::PUBLIC_KEY_FILE_INDEX] // absolute path
                : __DIR__ . '/../' . $config[self::PUBLIC_KEY_FILE_INDEX], // relative to config file
            'http://example.com', // no response URL is needed
            $config[self::MERCHANT_NUMBER_INDEX]
        );
    }

    /**
     * @test
     */
    public function I_can_create_order()
    {
        self::markTestSkipped('Not yet working');
        $provider = new Provider($this->settings, new DigestSigner($this->settings));
        $ISO4217 = new ISO4217();
        $_POSTLIKE = [
            RequestDigestKeys::ORDERNUMBER => (string)time(),
            RequestDigestKeys::AMOUNT => 123,
            RequestDigestKeys::CURRENCY => $ISO4217->getByCode('EUR')['numeric'],
            RequestDigestKeys::DEPOSITFLAG => true,
        ];
        $cardPayRequest = $provider->createCardPayRequest(
            CardPayRequestValues::createFromArray($_POSTLIKE, new CurrencyCodes($ISO4217))
        );
        self::assertInstanceOf(CardPayRequest::class, $cardPayRequest);
        $curl = curl_init($cardPayRequest->getRequestUrlWithGetParameters());
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        self::assertTrue(
            curl_exec($curl),
            'Requesting a new order via GET fails, used URL ' . $cardPayRequest->getRequestUrlWithGetParameters()
        );
    }
}