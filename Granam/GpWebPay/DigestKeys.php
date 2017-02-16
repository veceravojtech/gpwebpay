<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class DigestKeys extends StrictObject implements Codes
{
    const OPERATION = 'OPERATION';
    const ORDERNUMBER = 'ORDERNUMBER';
    const MERCHANTNUMBER = 'MERCHANTNUMBER';
    const AMOUNT = 'AMOUNT';
    const CURRENCY = 'CURRENCY';
    const DEPOSITFLAG = 'DEPOSITFLAG';
    const MERORDERNUM = 'MERORDERNUM';
    const URL = 'URL';
    const DESCRIPTION = 'DESCRIPTION';
    const MD = 'MD';
    const PRCODE = 'PRCODE';
    const SRCODE = 'SRCODE';
    const RESULTTEXT = 'RESULTTEXT';
    const DIGEST = 'DIGEST';
    const DIGEST1 = 'DIGEST1';
    const USERPARAM1 = 'USERPARAM1';
    const FASTPAYID = 'FASTPAYID';
    const PAYMETHOD = 'PAYMETHOD';
    const DISABLEPAYMETHOD = 'DISABLEPAYMETHOD';
    const PAYMETHODS = 'PAYMETHODS';
    const EMAIL = 'EMAIL';
    const REFERENCENUMBER = 'REFERENCENUMBER';
    const ADDINFO = 'ADDINFO';

    /**
     * @return array|string[]
     */
    public static function getDigestKeys()
    {
        return [
            self::OPERATION,
            self::ORDERNUMBER,
            self::MERCHANTNUMBER,
            self::AMOUNT,
            self::CURRENCY,
            self::DEPOSITFLAG,
            self::MERORDERNUM,
            self::URL,
            self::DESCRIPTION,
            self::MD,
            self::PRCODE,
            self::SRCODE,
            self::RESULTTEXT,
            self::DIGEST,
            self::DIGEST1,
            self::USERPARAM1,
            self::FASTPAYID,
            self::PAYMETHOD,
            self::DISABLEPAYMETHOD,
            self::PAYMETHODS,
            self::EMAIL,
            self::REFERENCENUMBER,
            self::ADDINFO,
        ];
    }
}