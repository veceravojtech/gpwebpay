<?php
namespace Granam\Tests\GpWebPay\Exceptions;

use Granam\GpWebPay\Codes\LanguageCodes;
use Granam\GpWebPay\Exceptions\GpWebPayErrorResponse;
use PHPUnit\Framework\TestCase;

class GpWebPayErrorResponseTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_ask_it_if_given_pr_code_means_error()
    {
        self::assertFalse(GpWebPayErrorResponse::isErrorCode(0));
        self::assertFalse(GpWebPayErrorResponse::isErrorCode(200));
        self::assertTrue(GpWebPayErrorResponse::isErrorCode(1));
        self::assertTrue(GpWebPayErrorResponse::isErrorCode(50));
    }

    /**
     * @test
     * @dataProvider provideCodesWithTextAndExpectedResult
     * @param int $prCode
     * @param int $srCode
     * @param string $resultText
     * @param string $expectedEnglishResultText
     * @param string $expectedCzechResultText
     * @param int $exceptionCode
     * @param \Exception $previousException
     */
    public function I_can_throw_this_special_exception_with_detailed_info(
        int $prCode,
        int $srCode,
        string $resultText,
        string $expectedEnglishResultText,
        string $expectedCzechResultText,
        int $exceptionCode = null,
        \Exception $previousException = null
    )
    {
        try {
            throw new GpWebPayErrorResponse(
                $prCode,
                $srCode,
                $resultText,
                $exceptionCode,
                $previousException
            );
        } catch (GpWebPayErrorResponse $gpWebPayResponseHasAnError) {
            self::assertSame($prCode, $gpWebPayResponseHasAnError->getPrCode());
            self::assertSame($srCode, $gpWebPayResponseHasAnError->getSrCode());
            self::assertSame($resultText, $gpWebPayResponseHasAnError->getResultText());
            self::assertSame((int)$exceptionCode, $gpWebPayResponseHasAnError->getCode());
            self::assertSame($previousException, $gpWebPayResponseHasAnError->getPrevious());
            $expectedErrorMessage = $expectedEnglishResultText;
            if ($resultText !== '') {
                $expectedErrorMessage = $resultText . ' - ' . $expectedErrorMessage;
            }
            $expectedErrorMessage .= "; error codes $prCode/$srCode";
            self::assertSame($expectedErrorMessage, $gpWebPayResponseHasAnError->getMessage());
            self::assertSame($expectedEnglishResultText, $gpWebPayResponseHasAnError->getLocalizedMessage());
            self::assertSame($expectedEnglishResultText, $gpWebPayResponseHasAnError->getLocalizedMessage(LanguageCodes::EN));
            self::assertSame($expectedCzechResultText, $gpWebPayResponseHasAnError->getLocalizedMessage(LanguageCodes::CS));
            // test of the reaction to unsupported language for error messages
            error_clear_last();
            $previousErrorReporting = ini_set('error_reporting', E_ALL ^ E_USER_WARNING);
            self::assertSame($expectedEnglishResultText, $gpWebPayResponseHasAnError->getLocalizedMessage($chinese = '汉语'));
            ini_set('error_reporting', $previousErrorReporting);
            $lastError = error_get_last();
            error_clear_last();
            self::assertArrayHasKey('message', $lastError);
            self::assertSame(
                "Unsupported language for error message requested: '汉语', 'en' is used instead",
                $lastError['message']
            );
        }
    }

    public function provideCodesWithTextAndExpectedResult()
    {
        return [
            [0 /* even OK can be thrown as an exception */, 0, '', 'OK', 'OK'],
            [1, 0, 'foo', 'Field too long', 'Pole příliš dlouhé', 123, new \Exception()],
            [4, 8, 'bar', 'Field is null (DEPOSITFLAG)', 'Pole je prázdné (DEPOSITFLAG)'],
        ];
    }

    /**
     * @test
     */
    public function I_can_find_out_easily_if_currency_was_refused()
    {
        $gpWebPayResponseHasAnError = new GpWebPayErrorResponse(3, 7);
        self::assertTrue($gpWebPayResponseHasAnError->isUnsupportedCurrency());
        $gpWebPayResponseHasAnError = new GpWebPayErrorResponse(3, 6);
        self::assertFalse($gpWebPayResponseHasAnError->isUnsupportedCurrency());
    }
}