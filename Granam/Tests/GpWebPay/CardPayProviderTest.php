<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayProvider;

trait CardPayProviderTest
{
    /**
     * @test
     */
    public function I_can_use_it_as_card_pay_provider()
    {
        $sutClass = self::getSutClass();
        self::assertTrue(
            is_a($sutClass, CardPayProvider::class, true),
            $sutClass . ' should implement ' . CardPayProvider::class
        );
    }

    /**
     * @param string|null $sutTestClass
     * @param string $regexp
     * @return mixed
     */
    protected static function getSutClass($sutTestClass = null, $regexp = '~\\\Tests(.+)Test$~')
    {
        if (is_callable('parent::' . __METHOD__)) {
            $parentCall = 'parent::' . __METHOD__;

            return $parentCall($sutTestClass, $regexp);
        }

        return preg_replace($regexp, '$1', $sutTestClass ?: get_called_class());
    }
}