<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\Settings;
use Symfony\Component\Yaml\Yaml;

class TestSettingsFactory extends Settings
{
    const PRIVATE_KEY_FILE_INDEX = 'privateKeyFile';
    const PRIVATE_KEY_PASSWORD_INDEX = 'privateKeyPassword';
    const PUBLIC_KEY_FILE_INDEX = 'publicKeyFile';
    const BASE_URL_FOR_REQUEST_INDEX = 'baseUrlForRequest';
    const MERCHANT_NUMBER_INDEX = 'merchantNumber';
    const URL_FOR_RESPONSE_INDEX = 'urlForResponse';

    /**
     * @return Settings
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function createTestSettings(): Settings
    {
        $config = Yaml::parse(file_get_contents(self::findLiveTestConfigFile()));

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
     * @return string
     * @throws \RuntimeException
     */
    private static function findLiveTestConfigFile(): string
    {
        $liveTestConfigFile = __DIR__ . '/../webpay_live_test_config.yml'; // directly in this library, together with dist version
        if (is_readable($liveTestConfigFile)) {
            return $liveTestConfigFile;
        }
        $maxHierarchySteps = 5;
        $step = 0;
        $dirToStartSearchUpward = __DIR__ . '/../../../../'; // one level out of this library
        do {
            $liveTestConfigFile = $dirToStartSearchUpward . str_repeat('../', $step) . 'webpay_live_test_config.yml';
            if (is_readable($liveTestConfigFile)) {
                return $liveTestConfigFile;
            }
        } while (++$step < $maxHierarchySteps || in_array('composer.json', scandir(dirname($liveTestConfigFile)), true));

        throw new \RuntimeException(
            "Could not find webpay_live_test_config.yml in {$dirToStartSearchUpward} nor in any of its {$step} parents"
        );
    }
}