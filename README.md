# saderat
A laravel package for connecting to saderat bank gateway (BSI)

Please inform us once you've encountered [bug](https://github.com/larabook/gateway/issues) or [isue](https://github.com/larabook/gateway/issues)  .
  
----------


**Installation**:

Run below statements on your terminal :

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
 


Configuration file is placed in config/saderat.php , open it and enter your banks credential:

You can make connection to bank by several way (Facade , Service container):

    try {
       
       $bank = app('saderat')->make(1000);  // ۱۰۰ تومان
       $bank->setCallback(url('/path/to/calback/route')); You can also change the callback  

        // ذخیره شماره invoice قبل از ارجاع کاربر به بانک
        $invoiceNumber = $bank->getInvoiceNumber();
        return $bank->go();
       
    } catch (Exception $e) {
       
       	echo $e->getMessage();
    }

you can call the gateway by these ways :
 
 1.By Facade :  Saderat::make(1000)
 
 2.By app helper :  app('saderat')->make(1000); 
 
 3.Using Dependency Injection all over the controller and routers ...
 
and in your callback :

    try {
        $data = app('saderat')->verify();
        // تراکنش با موفقیت سمت بانک تایید گردید
        // در این مرحله عملیات خرید کاربر را تکمیل میکنیم
        echo "شماره صورت حساب : " . $data->invoice_number . "<br>";
        echo "شماره مرجع بانک : " . $data->bank_receipt . "<br>";

    } catch (\App\Bank\Exceptions\RepetitiveException $e) {
        // تراکنش قبلا سمت بانک تاییده شده است و
        // کاربر احتمالا صفحه را مجددا رفرش کرده است
        // لذا تنها فاکتور خرید موفق را مجدد به کاربر نمایش میدهیم
        echo $e->getMessage() . "<br>";
        echo "شماره صورت حساب : " . $e->invoice_number . "<br>";
        echo "شماره مرجع بانک : " . $e->bank_receipt . "<br>";

    } catch (\App\Bank\Exceptions\BankException $e) {
        // نمایش خطای بانک
        echo $e->getMessage();
    }
