<?php
namespace Granam\Tests\GpWebPay;

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
        $liveTestConfigFile = __DIR__ . '/../webpay_live_test_config.ymls';
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
            $config[self::PRIVATE_KEY_FILE_INDEX],
            $config[self::PRIVATE_KEY_PASSWORD_INDEX],
            $config[self::PUBLIC_KEY_FILE_INDEX],
            'http://example.com', // no response URL is needed
            $config[self::MERCHANT_NUMBER_INDEX],
            '' // no gateway is needed
        );
    }

    /**
     * @test
     */
    public function toBeContinued()
    {
        self::fail();
    }
}