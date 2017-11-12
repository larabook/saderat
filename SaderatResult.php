<?php namespace Larabookir\Saderat;
/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       http://pear.php.net/package/PackageName
 * @version    1.00
 */


class SaderatResult
{
    public $success;
    public $invoice_number;
    public $bank_receipt;
    public $repetitive;

    function __construct($success, $invoice_number, $bank_receipt, $repetitive)
    {
        $this->success = $success;
        $this->invoice_number = $invoice_number;
        $this->bank_receipt = $bank_receipt;
        $this->repetitive = $repetitive;
    }
}