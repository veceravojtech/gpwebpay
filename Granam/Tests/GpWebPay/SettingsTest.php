<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{

    /**
     * @test
     */
    public function I_can_create_it()
    {
        $requestBaseUrl = 'http://example.com';
        $privateKeyFile = __DIR__ . '/files/testing_private_key.pem';
        $privateKeyPassword = '1234567'; // password
        $publicKeyFile = __DIR__ . '/files/testing_public_key.pub';
        $responseUrl = 'https://example.com/gp-webpay/response';
        $merchantNumber = '123456';
        $gatewayKey = 'foo';

        foreach (['new', 'production', 'test'] as $howToCreate) {
            switch ($howToCreate) {
                case 'production' :
                    $settings = Settings::createForProduction(
                        $privateKeyFile,
                        $privateKeyPassword,
                        $publicKeyFile,
                        $responseUrl,
                        $merchantNumber,
                        $gatewayKey
                    );
                    $requestBaseUrl = Settings::PRODUCTION_REQUEST_URL;
                    break;
                case 'test' :
                    $settings = Settings::createForTest(
                        $privateKeyFile,
                        $privateKeyPassword,
                        $publicKeyFile,
                        $responseUrl,
                        $merchantNumber,
                        $gatewayKey
                    );
                    $requestBaseUrl = Settings::TEST_REQUEST_URL;
                    break;
                case 'new' :
                default :
                    $settings = new Settings(
                        $requestBaseUrl,
                        $privateKeyFile,
                        $privateKeyPassword,
                        $publicKeyFile,
                        $responseUrl,
                        $merchantNumber,
                        $gatewayKey
                    );
            }
            self::assertSame($requestBaseUrl, $settings->getBaseUrlForRequest());
            self::assertSame($privateKeyFile, $settings->getPrivateKeyFile());
            self::assertSame($privateKeyPassword, $settings->getPrivateKeyPassword());
            self::assertSame($publicKeyFile, $settings->getPublicKeyFile());
            self::assertSame($responseUrl, $settings->getUrlForResponse());
            self::assertSame($merchantNumber, $settings->getMerchantNumber());
            self::assertSame($gatewayKey, $settings->getGatewayKey());
        }
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\InvalidUrl
     * @expectedExceptionMessageRegExp ~localhost~
     */
    public function I_can_not_create_settings_with_invalid_request_base_url()
    {
        new Settings(
            'localhost', // protocol missing
            __DIR__ . '/files/testing_private_key.pem',
            '1234567', // password
            __DIR__ . '/files/testing_public_key.pub',
            '',
            '',
            ''
        );
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @expectedExceptionMessageRegExp ~on keychain~
     */
    public function I_can_not_create_settings_with_unreachable_private_key_file()
    {
        new Settings(
            'http://example.com',
            'on keychain',
            '1234567', // password
            __DIR__ . '/files/testing_public_key.pub',
            '',
            '',
            ''
        );
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     */
    public function I_can_not_create_settings_with_invalid_private_key_password()
    {
        new Settings(
            'http://example.com',
            __DIR__ . '/files/testing_private_key.pem',
            'knock knock', // password
            __DIR__ . '/files/testing_public_key.pub',
            '',
            '',
            ''
        );
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @expectedExceptionMessageRegExp ~in a cloud~
     */
    public function I_can_not_create_settings_with_unreachable_public_key_file()
    {
        new Settings(
            'http://example.com',
            __DIR__ . '/files/testing_private_key.pem',
            1234567, // password
            'in a cloud',
            '',
            '',
            ''
        );
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\InvalidUrl
     * @expectedExceptionMessageRegExp ~/dev/null~
     */
    public function I_can_not_create_settings_with_invalid_response_url()
    {
        new Settings(
            'http://example.com',
            __DIR__ . '/files/testing_private_key.pem',
            1234567, // password
            __DIR__ . '/files/testing_public_key.pub',
            '/dev/null',
            '',
            ''
        );
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\ValueTooLong
     * @expectedExceptionMessageRegExp ~300~
     */
    public function I_can_not_create_settings_with_too_long_response_url()
    {
        new Settings(
            'http://example.com',
            __DIR__ . '/files/testing_private_key.pem',
            1234567, // password
            __DIR__ . '/files/testing_public_key.pub',
            'http://example.com/' . str_repeat('u', 301 - strlen('http://example.com/')),
            '',
            ''
        );
    }
}