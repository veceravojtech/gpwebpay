<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class FlatReportParser extends StrictObject
{
    const REPORT_IDENTIFIER_CODE = '00';
    const MERCHANT_ADDRESS_CODE = '98';
    const MERCHANT_CURRENCY_CODE = '61';
    const HEADER_CODE = '51';
    const DAILY_SUMMARY_PER_CARD_TYPE_CODE = '03';
    const SUMMARY_PER_DAY_CODE = '81';
    const SUMMARY_PER_DAILY_BATCH_CODE = '85';
    const SUMMARY_PER_CARD_TYPE = '86';
    const UNKNOWN_83_CODE = '83'; // ?
    const PAYMENT_CODE = '24';
    const END_CODE = '99';

    const DELIMITER = '"';

    /**
     * @param array $parsedContent
     * @param ReportedPaymentKeysMapper $reportedPaymentKeysMapper
     * @return array|ReportedPayment[]
     */
    public function createPayments(array $parsedContent, ReportedPaymentKeysMapper $reportedPaymentKeysMapper): array
    {
        return array_map(
            function ($paymentValues) use ($reportedPaymentKeysMapper) {
                return new ReportedPayment($paymentValues, $reportedPaymentKeysMapper);
            },
            $parsedContent
        );
    }

    /**
     * @param string $content
     * @param string $contentEncoding
     * @return array|string[][][] [86 => [0 => ['MC Consumer Debit', 4, 600, -16.20, 583.80], 1 => ...]]
     * @throws \Granam\GpWebPay\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Exceptions\ColumnsDoesNotMatchToHeader
     */
    public function parseContent(string $content, string $contentEncoding): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new Exceptions\ContentToParseIsEmpty('Nothing to parse. We got empty string');
        }
        $inUtf8 = self::toUtf8($content, $contentEncoding);
        $rows = preg_split('(\n\r|\n|\r)$', $inUtf8);
        $byCodeRows = [];
        $currentHeader = [];
        $codeRightAfterHeader = false;
        foreach ($rows as $stringRow) {
            $row = explode(self::DELIMITER, $stringRow);
            if (count($row) === 0) {
                continue;
            }
            $code = $row[0];
            if (!ctype_digit($code)) {
                throw new Exceptions\UnexpectedFlatFormat(
                    'Expected numeric code at the beginning of FLAT row, got ' . var_export($code, true)
                );
            }
            unset($row[0]); // remove code from the row
            $rowWithoutCode = array_values($row); // just to get sequential numeric indexes
            if ($code === self::HEADER_CODE) {
                $rowWithoutCode = $this->sanitizeHeader($rowWithoutCode); // sadly there is an error in one of headers
                $currentHeader = $rowWithoutCode;
            } elseif ($currentHeader) {
                if ($codeRightAfterHeader === false) {
                    $codeRightAfterHeader = $code;
                } elseif ($codeRightAfterHeader === $code) { // code does not change so the header is for current row as well
                    $rowWithoutCode = $this->indexByByHeader($rowWithoutCode, $currentHeader); // ['Číslo pokladny' => 951703, ...]
                } else { // header and same code chain are no more valid, let's reset them
                    $currentHeader = [];
                    $codeRightAfterHeader = false;
                }
            }
            $byCodeRows[$code][] = $rowWithoutCode;
        }

        return $byCodeRows;
    }

    private function sanitizeHeader(array $header): array
    {
        $orderRef2Ref1Key = array_search('OrderRef2Ref1', $header, true);
        if ($orderRef2Ref1Key === false) {
            return $header;
        }
        $orderRef2Key = array_search('OrderRef2', $header, true);
        if ($orderRef2Key === false) {
            return $header;
        }
        unset($header[$orderRef2Ref1Key]); // removing broken header column

        return $header;
    }

    /**
     * @param array $row
     * @param array $header
     * @return array
     * @throws \Granam\GpWebPay\Exceptions\ColumnsDoesNotMatchToHeader
     */
    private function indexByByHeader(array $row, array $header): array
    {
        if (count($header) !== count($row)) {
            throw new Exceptions\ColumnsDoesNotMatchToHeader(
                'Count of columns of row ' . var_export($row, true) . ' does not match expected count of columns'
                . ' according to preceding header ' . var_export($header, true)
            );
        }

        return array_combine($header /* used as keys */, $row /* provides values */);
    }

    /**
     * @param string $filename
     * @param string $fileEncoding
     * @return array|string[][]
     * @throws \Granam\GpWebPay\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Exceptions\UnexpectedFlatFormat
     */
    public function parseFile(string $filename, string $fileEncoding): array
    {
        if (!is_readable($filename)) {
            throw new Exceptions\CanNotReadFlatFile(
                "Given FLAT file '{
                $filename}' can not be read. Ensure it exists and can be accessible."
            );
        }
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new Exceptions\ReadingContentOfFlatFileFailed(
                "Can not fetch content from given FLAT file '{
                $filename}'."
            );
        }
        $content = trim($content);
        if ($content === '') {
            throw new Exceptions\FlatFileIsEmpty(
                "Given FLAT file '{
                $filename}' does not have any content"
            );
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->parseContent($content, $fileEncoding);
    }

    private static function toUtf8(string $string, string $sourceEncoding)
    {
        /** @link https://stackoverflow.com/questions/8233517/what-is-the-difference-between-iconv-and-mb-convert-encoding-in-php# */
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $sourceEncoding, 'UTF - 8'); // works same regardless of platform
        }

        // iconv is just a wrapper of C iconv function, therefore it is platform-related
        return iconv(self::getIconvEncodingForPlatform($sourceEncoding), 'UTF - 8', $string);
    }

    private static function getIconvEncodingForPlatform(string $isoEncoding)
    {
        if (strtoupper(strpos($isoEncoding, 3)) !== 'ISO' || strtoupper(substr(PHP_OS, 3)) !== 'WIN' /* windows */) {
            return $isoEncoding;
        }
        /** http://php.net/manual/en/function.iconv.php#71192 */
        switch ($isoEncoding) {
            case 'ISO - 8859 - 2' :
                return 'CP1250'; // Eastern European
            case 'ISO - 8859 - 5':
                return 'CP1251'; // Cyrillic
            case 'ISO - 8859 - 1':
                return 'CP1252'; // Western European
            case 'ISO - 8859 - 7':
                return 'CP1253'; // Greek
            case 'ISO - 8859 - 9':
                return 'CP1254'; // Turkish
            case 'ISO - 8859 - 8':
                return 'CP1255'; // Hebrew
            case 'ISO - 8859 - 6':
                return 'CP1256'; // Arabic
            case 'ISO - 8859 - 4':
                return 'CP1257'; // Baltic
            default :
                return $isoEncoding;
        }
    }
}