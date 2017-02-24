<?php
namespace Granam\Tests\GpWebPay;

use Alcohol\ISO4217;
use Granam\GpWebPay\CardPayRequest;
use Granam\GpWebPay\CardPayRequestValues;
use Granam\GpWebPay\Codes\CurrencyCodes;
use Granam\GpWebPay\Codes\LanguageCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\GpWebPay\DigestSigner;
use Granam\GpWebPay\Provider;
use Granam\GpWebPay\Settings;
use Granam\Tests\Tools\TestWithMockery;
use PHPHtmlParser\Dom;
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
    const URL_FOR_RESPONSE_INDEX = 'urlForResponse';

    /**
     * @param array $config
     * @return Settings
     * @throws \LogicException
     */
    private function createSettings(array $config): Settings
    {
        foreach ([self::PRIVATE_KEY_FILE_INDEX, self::PUBLIC_KEY_FILE_INDEX, self::BASE_URL_FOR_REQUEST_INDEX,
                     self::MERCHANT_NUMBER_INDEX, self::URL_FOR_RESPONSE_INDEX] as $required) {
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
            $config[self::URL_FOR_RESPONSE_INDEX],
            $config[self::MERCHANT_NUMBER_INDEX]
        );
    }

    /**
     * @test
     */
    public function I_can_create_order()
    {
        self::markTestSkipped();
        $provider = new Provider($this->settings, new DigestSigner($this->settings));
        $ISO4217 = new ISO4217();
        $_POSTLIKE = [
            RequestDigestKeys::ORDERNUMBER => (string)time(),
            RequestDigestKeys::AMOUNT => '123.45',
            RequestDigestKeys::CURRENCY => (string)$ISO4217->getByCode('EUR')['numeric'],
            RequestDigestKeys::DEPOSITFLAG => '1',
            RequestPayloadKeys::LANG => LanguageCodes::EN,
        ];
        $cardPayRequest = $provider->createCardPayRequest(
            CardPayRequestValues::createFromArray($_POSTLIKE, new CurrencyCodes($ISO4217))
        );
        self::assertInstanceOf(CardPayRequest::class, $cardPayRequest);

        $response = $this->fetchResponse($cardPayRequest);
        $parser = (new Dom())->loadStr($response, []);
        $htmlNode = $this->getSingleNode('html', $parser);
        self::assertSame(
            'http://gpe.cz/gpwebpay/functions',
            $htmlNode->getTag()->getAttribute('xmlns:f')['value'] ?? '',
            'Unexpected response content from GpWebPay'
        );
        $headTitleNode = $this->getSingleNode('head title', $parser);
        self::assertSame(
            '3D Secure payment gateway',
            $headTitleNode->text(),
            'Unexpected response content from GpWebPay'
        );
        $buttonSend = $this->getSingleNode('button#send', $parser);
        self::assertSame('Pay 123.45 EUR', $buttonSend->text(true));
    }

    private function fetchResponse(CardPayRequest $cardPayRequest): string
    {
        $curl = curl_init($cardPayRequest->getRequestUrlWithGetParameters());
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if ($response) {
            curl_close($curl);

            return (string)$response;
        }
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curlError !== 'No OCSP response received') {
            self::fail(
                'Requesting a new order via GET fails, got CURL error ' . $curlError
                . '; used URL ' . $cardPayRequest->getRequestUrlWithGetParameters()
            );

            return '';
        }

        if (!is_callable('shell_exec') || strpos(ini_get('disable_functions'), 'shell_exec') !== false) {
            self::fail(
                'Requesting a new order via GET fails because of by CURL used openssl with bug'
                . ', see @link https://github.com/curl/curl/issues/219'
                . '; used URL ' . $cardPayRequest->getRequestUrlWithGetParameters()
            );

            return '';
        }
        $response = shell_exec(
            'curl --connect-timeout 15 --max-redirs 5 --location 2>/dev/null '
            . escapeshellarg($cardPayRequest->getRequestUrlWithGetParameters())
        );
        self::assertNotEmpty($response);

        return (string)$response;
    }

    /**
     * @param string $selector
     * @param Dom $parser
     * @return Dom\HtmlNode
     */
    private function getSingleNode(string $selector, Dom $parser): Dom\HtmlNode
    {
        $wrappedTag = $parser->find($selector);
        self::assertCount(1, $wrappedTag, $selector . ' has not been found');
        $stillWrappedTag = current($wrappedTag);
        self::assertCount(1, $stillWrappedTag, $selector . ' has not been found');
        $tag = current($stillWrappedTag);
        self::assertInstanceOf(Dom\HtmlNode::class, $tag, $selector . ' has not been found');

        return $tag;
    }
}