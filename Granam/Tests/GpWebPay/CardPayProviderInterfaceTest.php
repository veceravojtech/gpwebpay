<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CardPayProviderInterface;

trait CardPayProviderInterfaceTest
{
    /**
     * @test
     */
    public function I_can_use_it_as_card_pay_provider()
    {
        $sutClass = self::getSutClass();
        self::assertTrue(
            is_a($sutClass, CardPayProviderInterface::class, true),
            $sutClass . ' should implement ' . CardPayProviderInterface::class
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