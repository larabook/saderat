<?php namespace Larabookir\Saderat;

/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       https://github.com/larabook/gateway/
 * @version    1.00
 */

use Larabookir\Saderat\Exceptions\BankException;

class SaderatResolver
{
    
    function make($amount = null, $invoice_number = null)
    {
        $service = new SaderatService();
        if ($amount)
            $service->setAmount($amount);

        if ($invoice_number)
            $service->setInvoiceNumber($invoice_number);

        return $service;
    }


    function verify()
    {
        if (request()->get('RESCODE') == '00') {
            $service = new SaderatService();
            $service->setInvoiceNumber(request()->get('CRN'));
            $service->setBankReceiptNumber(request()->get('TRN'));
            return $service->verify();
        }
        throw new  BankException('پرداخت موفقیت آمیز نبوده است', 200);
    }


}