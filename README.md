# saderat
A laravel package for connecting to saderat bank gateway (BSI)

Please inform us once you've encountered [bug](https://github.com/larabook/gateway/issues) or [isue](https://github.com/larabook/gateway/issues)  .
  
----------


**Installation**:

Run below statements in your terminal :

STEP 1 : 

    composer require larabook/saderat
    
STEP 2 : Add `provider` and `facade` in config/app.php

    'providers' => [
      ...
      Larabookir\Saderat\SaderatServiceProvider::class, // <-- add this line at the end of provider array
    ],


    'aliases' => [
      ...
      'Saderat' => Larabookir\Saderat\Facade\Saderat::class, // <-- add this line at the end of aliases array
    ]

Step 3:  
    php artisan vendor:publish --provider="Larabookir\Saderat\SaderatServiceProvider" 

or:  
    php artisan vendor:publish --provider=Larabookir\Saderat\SaderatServiceProvider
 



Configuration file is placed in config/saderat.php right now , open it and enter your banks credential there.


You can make connection to bank by several ways (Facade , Service container):

    try {

      $bank = app('saderat')->make(1000);  // ۱۰۰ تومان
      $bank->setCallback(url('/path/to/calback/route')); You can also change the callback  

      // در این مرحله شماره سند تولید شده را قبل از ارجاع کاربر به بانک
      // در بانک اطلاعات ذخیره میکنیم

      $invoiceNumber = $bank->getInvoiceNumber();
      return $bank->go();
       
    } catch (Exception $e) {

      echo $e->getMessage();
    }
 
 
and in your callback :

    try {
        $data = app('saderat')->verify();
        // تراکنش با موفقیت سمت بانک تایید گردید
        // در این مرحله عملیات خرید کاربر را تکمیل میکنیم
        
        echo "شماره سند : " . $data->invoice_number . "<br>";
        echo "شماره مرجع بانک : " . $data->bank_receipt . "<br>";

    } catch (\Larabookir\Saderat\Exceptions\RepetitiveException $e) {
        // تراکنش قبلا سمت بانک تاییده شده است و
        // کاربر احتمالا صفحه را مجددا رفرش کرده است
        // لذا تنها فاکتور خرید قبل را مجدد به کاربر نمایش میدهیم

        echo $e->getMessage() . "<br>";
        echo "شماره سند : " . $e->invoice_number . "<br>";
        echo "شماره مرجع بانک : " . $e->bank_receipt . "<br>";

    } catch (\Larabookir\Saderat\Exceptions\BankException $e) {
        // نمایش خطای بانک
        echo $e->getMessage();
    }
