<?php
namespace Granam\GpWebPay;

use Alcohol\ISO4217;
use Granam\GpWebPay\Codes\CurrencyCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\Tests\GpWebPay\LiveTest;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../tests_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GP WEbPay test</title>
</head>
<body>
<?php
$ISO4217 = new ISO4217();
$currencies = [];
foreach ($ISO4217->getAll() as $currency) {
    $currencies[$currency['numeric']] = $currency['alpha3'];
}

if (($_GET['price'] ?? null) !== null) {
    $liveTestConfigFile = __DIR__ . '/../../webpay_live_test_config.yml';
    if (!is_readable($liveTestConfigFile)) {
        throw new \RuntimeException('Can not read ' . $liveTestConfigFile);
    }
    $config = Yaml::parse(file_get_contents($liveTestConfigFile));
    foreach ([LiveTest::PRIVATE_KEY_FILE_INDEX, LiveTest::PUBLIC_KEY_FILE_INDEX,
                 LiveTest::BASE_URL_FOR_REQUEST_INDEX, LiveTest::MERCHANT_NUMBER_INDEX] as $required
    ) {
        if (empty($config[$required])) {
            throw new \LogicException("Required config entry '{$required}' for live test is missing");
        }
    }

    $settings = new Settings(
        $config[LiveTest::BASE_URL_FOR_REQUEST_INDEX],
        preg_match('~^\\/~', $config[LiveTest::PRIVATE_KEY_FILE_INDEX])
            ? $config[LiveTest::PRIVATE_KEY_FILE_INDEX] // absolute path
            : __DIR__ . '/../../' . $config[LiveTest::PRIVATE_KEY_FILE_INDEX], // relative to config file
        $config[LiveTest::PRIVATE_KEY_PASSWORD_INDEX],
        preg_match('~^\\/~', $config[LiveTest::PUBLIC_KEY_FILE_INDEX])
            ? $config[LiveTest::PUBLIC_KEY_FILE_INDEX] // absolute path
            : __DIR__ . '/../../' . $config[LiveTest::PUBLIC_KEY_FILE_INDEX], // relative to config file
        'http://example.com', // no response URL is needed
        $config[LiveTest::MERCHANT_NUMBER_INDEX]
    );
    $provider = new Provider($settings, new DigestSigner($settings));
    $values = $_GET;
    $values[RequestDigestKeys::ORDERNUMBER] = time();
    $values[RequestDigestKeys::DEPOSITFLAG] = true;
    $cardPayRequest = $provider->createCardPayRequest(
        CardPayRequestValues::createFromArray($values, new CurrencyCodes($ISO4217))
    );
    ?>
    <form action="<?= $cardPayRequest->getRequestUrlForGet() ?>" method="get">
        <label>Price <input type="number" value="<?= $values['price'] ?>" disabled
        </label><br>
        <label>Currency
            <select disabled>
                <option value="<?= $values['currency']; ?>" selected>
                    <?= $currencies[$values['currency']]; ?>
                </option>
            </select>
        </label><br>
        <button type="submit">Confirm via GET</button>
        <br>
    </form>
    <form action="<?= $cardPayRequest->getRequestUrlForPost() ?>" method="post">
        <?php foreach ($cardPayRequest as $name => $value) {
            ?><input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
        <?php } ?>
        <button type="submit">Confirm via POST</button>
        <br>
    </form>
    <a href="gp_web_pay.php">Reset</a>
<?php } else {
    ?>
    <form action="" method="get">
        <label>Price <input name="price" type="number" value="123.456"
        </label><br>
        <label>Currency
            <select name="currency">
                <?php foreach ($currencies as $currencyNumericCode => $currencyName) {
                    ?>
                    <option value="<?= $currencyNumericCode ?>">
                        <?= $currencyName ?>
                    </option>
                <?php } ?>
            </select><br>
            <button type="submit">Check</button>
        </label><br>
    </form>
<?php }
?>
</body>
</html>