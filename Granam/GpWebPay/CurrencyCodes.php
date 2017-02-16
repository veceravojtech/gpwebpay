<?php
namespace Granam\GpWebPay;

use Alcohol\ISO4217;
use Granam\Strict\Object\StrictObject;

/**
 * Currency codes are in (number) format ISO 4217:2001, see GP_webpay_HTTP_EN.pdf / GP_webpay_HTTP.pdf
 */
class CurrencyCodes extends StrictObject implements Codes
{
    /**
     * @var ISO4217
     */
    private $iso4217;

    /**
     * @param ISO4217 $iso4217
     */
    public function __construct(ISO4217 $iso4217)
    {
        $this->iso4217 = $iso4217;
    }

    /**
     * @param int $code
     * @return bool
     */
    public function isCurrencyNumericCode(int $code)
    {
        $unifiedCode = sprintf("%'03d", $code);

        foreach ($this->iso4217->getAll() as $currency) {
            if ($currency['numeric'] === $unifiedCode) {
                return true;
            }
        }

        return false;
    }
}