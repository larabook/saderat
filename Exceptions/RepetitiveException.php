<?php namespace Larabookir\Saderat\Exceptions;
/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       https://github.com/larabook/gateway/
 * @version    1.00
 */


class RepetitiveException extends \RuntimeException
{
    protected $code = 101;
    protected $message = 'تراکنش قبلا سمت بانک تایید شده است';
    public $invoice_number;
    public $bank_receipt;

    function __construct($invoice_number, $bank_receipt)
    {
        $this->invoice_number = $invoice_number;
        $this->bank_receipt = $bank_receipt;
    }
}