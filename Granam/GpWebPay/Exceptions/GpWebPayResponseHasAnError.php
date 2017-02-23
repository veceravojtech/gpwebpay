<?php
namespace Granam\GpWebPay\Exceptions;

use Granam\GpWebPay\Codes\LanguageCodes;
use Granam\GpWebPay\Codes\PrCodes;
use Granam\GpWebPay\Codes\SrCodes;

class GpWebPayResponseHasAnError extends \RuntimeException implements Runtime
{
    const OK_CODE = 0;
    const ADDITIONAL_INFO_REQUEST_CODE = 200;

    /**
     * @param int $prCode
     * @return bool
     */
    public static function isErrorCode(int $prCode)
    {
        return $prCode !== self::OK_CODE && $prCode !== self::ADDITIONAL_INFO_REQUEST_CODE;
    }

    /** @var int */
    private $prCode;
    /** @var int */
    private $srCode;
    /** @var string|null */
    private $resultText;

    /**
     * @param int $prCode
     * @param int $srCode
     * @param string $resultText
     * @param int $exceptionCode
     * @param \Exception $previousException
     */
    public function __construct(
        int $prCode,
        int $srCode,
        string $resultText = '',
        $exceptionCode = null, // intentionally without scalar type hint
        \Exception $previousException = null
    )
    {
        $this->prCode = $prCode;
        $this->srCode = $srCode;
        $this->resultText = $resultText ? trim($resultText) : '';
        parent::__construct(
            ($this->resultText
                ? "{$this->resultText} - "
                : ''
            ) . $this->getLocalizedMessage(LanguageCodes::EN) . "; error codes {$prCode}/{$srCode}",
            $exceptionCode, // will be internally converted to int
            $previousException
        );
    }

    /**
     * @return int
     */
    public function getPrCode()
    {
        return $this->prCode;
    }

    /**
     * @return int
     */
    public function getSrCode()
    {
        return $this->srCode;
    }

    /**
     * @return string|null
     */
    public function getResultText()
    {
        return $this->resultText;
    }

    /**
     * @param string $languageCode
     * @return string
     */
    public function getLocalizedMessage(string $languageCode = LanguageCodes::EN)
    {
        $languageCode = strtolower(trim($languageCode));
        if ($languageCode !== LanguageCodes::CS && $languageCode !== LanguageCodes::EN) {
            trigger_error(
                "Unsupported language for error message requested: '$languageCode'"
                . ', \'' . LanguageCodes::EN . '\' is used instead',
                E_USER_WARNING
            );
            $languageCode = LanguageCodes::EN;
        }
        $message = PrCodes::getLocalizedMainMessage($this->prCode, $languageCode);
        $detailMessage = SrCodes::getLocalizedDetailMessage($this->srCode, $languageCode);
        if ($detailMessage) {
            $message .= ' (' . $detailMessage . ')';
        }

        return $message;
    }
}