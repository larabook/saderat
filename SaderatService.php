<?php namespace Larabookir\Saderat;
/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       https://github.com/larabook/gateway/
 * @version    1.00
 */

use Larabookir\Saderat\Exceptions\BankException;
use Larabookir\Saderat\Exceptions\RepetitiveException;

class SaderatService
{
    /*
     * Your merchant Id
     */
    protected $merchant_id = '';

    /*
     * Your terminal Id
     */
    protected $terminal_id = '';

    /*
     * Bank WSDL address
     */
    protected $token_service_wsdl = 'https://mabna.shaparak.ir/TokenService?wsdl';
    protected $transaction_reference_wsdl = 'https://mabna.shaparak.ir/TransactionReference/TransactionReference?wsdl';

    /**
     * شماره crn
     * @var
     */
    public $invoice_number;


    /**
     * رسید تراکنش بان پس از عملیات موفق سفارش
     * @var
     */
    protected $bank_receipt;

    /**
     * آدرس صفحه ای که بانک کاربر را پس از انجام تراکنش به آن هدایت میکند
     * @var
     */
    protected $callback_url;

    /**
     * مبلغ پرداخت به ریال
     * @var
     */
    protected $amount;

    /**
     * کلید خصوصی
     * @var string
     */
    private $private_key;

    /**
     * کلید عمومی
     * @var resource
     */
    protected $key_resource;


    /**
     * تعداد تلاش برای ارتباط به سرور درصورت وجود خطا
     * @var int
     */
    protected $max_request_attempts = 3;

    function __construct()
    {
        include_once(__DIR__ . '/Assets/nusoap.php');


        $this->merchant_id = config('saderat.merchant_id');
        $this->terminal_id = config('saderat.terminal_id');
        // set default invoice number
        $this->setInvoiceNumber(static::uniqueID());

        /**
         * get key resource to start based on public key
         */
        if (!$public_key = @file_get_contents(__DIR__ . '/Assets/merchant_public_key.txt'))
            throw new \Exception('خطای دریافت کلید عمومی');

        if (!config()->has('saderat.private_key'))
            throw new \Exception('تنظیمات مربوط به کلید خصوصی یافت نشد.');

        $this->private_key =
            '-----BEGIN PRIVATE KEY-----' . PHP_EOL .
            trim(config('saderat.private_key')) . PHP_EOL .
            '-----END PRIVATE KEY-----';

        $this->key_resource = openssl_get_publickey($public_key);
    }

    /**
     * Get unique number according to millisecond
     * @return string
     */
    static function uniqueID()
    {
        return substr(str_pad(str_replace('.', '', microtime(true)), 12, 0), 0, 12);
    }

    /**
     * تعیین آدرس URL که بانک پس از انجام تراکنش کاربر را به آن ارجاع میدهد
     *
     * @param $url
     */
    function setCallbackUrl($url)
    {
        $this->callback_url = url_modify(['_token' => csrf_token()], url(trim($url)));
    }

    /**
     * تعین مبلغ تراکنش (به ریال)
     *
     * @param $amount
     *
     * @throws \Exception
     */
    function setAmount($amount)
    {
        if ($amount < 1000)
            throw new \Exception('حداقل مبلغ تراکنش ۱۰۰۰ ریال می باشد');

        $this->amount = intval($amount);
    }

    /**
     * متد تعیید شماره Invoice برای ارسال به بانک
     * تعیین کننده مقدار CRN در مستندات بانک
     *
     * @param $num
     */
    function setInvoiceNumber($num)
    {
        $this->invoice_number = intval($num);
    }

    /**
     * شماره Invoice را برمگیرداند
     */
    function getInvoiceNumber()
    {
        return $this->invoice_number;
    }


    /**
     * Set Bank receipt value given from bank request
     *
     * @param $num
     */
    function setBankReceiptNumber($num)
    {
        $this->bank_receipt = $num;
    }

    /**
     * درصورتی که تراکنش تایید شده باشد
     * شماره مرجع بانک را برمیگرداند
     * این متد بعد از مرحله verify مقدار عددی برمیگرداند
     * @return mixed
     */
    function getBankReceiptNumber()
    {
        return $this->bank_receipt;
    }

    /**
     * Makes a request to the bank and gives a token
     * این متد یک درخواست جهت دریافت توکن به بانک ارسال میکند
     */
    protected function request()
    {
        /**
         * Make a signature temporary
         * Note: each paid has it's own specific signature
         */
        $signature = $this->generate_signature(
            $this->amount,
            $this->invoice_number,
            $this->merchant_id,
            $this->callback_url,
            $this->terminal_id
        );


        // Make proper array of token params
        $params = [
            'Token_param' =>
                [
                    'AMOUNT'        => $this->enc($this->amount),
                    'CRN'           => $this->enc($this->invoice_number),
                    'MID'           => $this->enc($this->merchant_id),
                    'REFERALADRESS' => $this->enc($this->callback_url),
                    'SIGNATURE'     => base64_encode($signature),
                    'TID'           => $this->enc($this->terminal_id),
                ],
        ];

        // Send params to bank to get token
        $result = $this->call($this->token_service_wsdl, 'reservation', $params);

        // if any errors occurred
        if ($result['result'] != 0)
            throw new BankException(null, $result['result']);

        // State whether signature is okay or not
        $verify_result = openssl_verify($result['token'], base64_decode($result['signature']), $this->key_resource);

        if ($verify_result == 1)
            return $result['token'];
        elseif ($verify_result == 0)
            throw new BankException('خطا در ارسال درخواست به بانک');
        else
            throw new BankException('عدم تطبیق امضا دیجیتال بانک');

    }

    /**
     * Forward user to the bank
     */
    public function go()
    {
        if ($token = $this->request()) {
            return $this->forwarder($token);
        }

        throw new \Exception('خطا انتقال به بانک');
    }

    /**
     * Verify received data from bank
     */
    public function verify()
    {
        /**
         * Make a signature temporary
         * Note: each paid has it's own specific signature
         */
        $signature = $this->generate_signature(
            $this->merchant_id,
            $this->bank_receipt,
            $this->invoice_number
        );

        #dd($this);
        // Make proper array of token params
        $params = [
            'SaleConf_req' => [
                'MID'       => $this->enc($this->merchant_id),
                'CRN'       => $this->enc($this->invoice_number),
                'TRN'       => $this->enc($this->bank_receipt),
                'SIGNATURE' => base64_encode($signature),
            ],
        ];
        
        // Send params to bank to get token
        $result = $this->call($this->transaction_reference_wsdl, 'sendConfirmation', $params);

        if ($result['RESCODE'] == -1)
            throw new  BankException('امضا دیچیتال نا معتبر است', -1);
        elseif ($result['RESCODE'] == -2)
            throw new  BankException('آدرس IP پذیرنده نا معتبر است', -2);
        elseif (!in_array($result['RESCODE'], [0, 101]))
            throw new BankException(null, $result['RESCODE']); // خطاهای تعریف شده دیگر


        $data = $result['RESCODE'] . $result['REPETETIVE'] . $result['AMOUNT'] . $result['DATE'] . $result['TIME'] . $result['TRN'] . $result['STAN'];

        // State whether signature is okay or not
        $verify_result = openssl_verify($data, base64_decode($result['SIGNATURE']), $this->key_resource);

        if ($verify_result == 0)
            throw new BankException('خطای امضای دیجیتال');
        elseif ($verify_result != 1)
            throw new BankException('عدم تطبیق امضا دیجیتال بانک');

        /**
         * Result Webservice Array
         * if you need special design for your website, please modify following codes
         */
        if (!empty($result['RESCODE'])) {
            // success
            if (($result['RESCODE'] == '00') && ($result['successful'] == true)) {
                return new SaderatResult(
                    true, // success
                    $this->getInvoiceNumber(),
                    $this->getBankReceiptNumber(),
                    false // repetitive
                );
            } // cancel
            elseif ($result['RESCODE'] == 101) {
                throw new RepetitiveException(
                    $this->getInvoiceNumber(),
                    $this->getBankReceiptNumber()
                );
            } // cancel
            elseif ($result['RESCODE'] == 200) {
                throw new BankException('تراکنش توسط کاربر کنسل شده است');
            } // cancel
            elseif ($result['RESCODE'] == 107) {
                throw new BankException(null, 107);
            } // other problem
            elseif (!empty($result['description']))
                throw new BankException($result['description']);

        } else
            throw new BankException('درخواست نامعتبر است');

    }


    private function forwarder($token)
    {
        return view("saderat::forwarder")->with(compact('token'));
    }

    /**
     * مقادیر ورودی را با توجه به امضاء تولید شده کدگذاری میکند
     * @param $data
     *
     * @return string
     */
    function enc($data)
    {
        openssl_public_encrypt($data, $crypt_text, $this->key_resource);
        return base64_encode($crypt_text);
    }

    /**
     * Generate signature from given data
     * @return string
     */
    private function generate_signature()
    {
        $phrase = implode('', func_get_args());
        /**
         * Sign data and make final signature
         */
        $signature = '';
        $private_key_id = openssl_pkey_get_private($this->private_key);
        if (!openssl_sign($phrase, $signature, $private_key_id, OPENSSL_ALGO_SHA1)) {
            throw new BankException('OPEN SSL SIGN ERROR');
        }

        return $signature;
    }

    /**
     * Soap Call
     *
     * @param $serverURL
     * @param $method
     * @param array $parameters
     *
     * @return mixed
     * @throws \Exception
     */
    private function call($serverURL, $method, $parameters = [])
    {
        try {
            $attempt = 1;
            while ($attempt <= $this->max_request_attempts) {
                try {
                    $client = new \nusoap_client($serverURL, 'wsdl');
                    if ($client->getError())
                        throw new \Exception('خطای ارتباط با بانک');
                    $result = $client->call($method, $parameters);
                    break;
                } catch (\Exception $e) {
                    if ($attempt == $this->max_request_attempts)
                        throw $e;
                }
                $attempt++;
                sleep(1); // wait 1 second and request server again
            }
            return $result['return'];
        } catch (\Exception $e) {
            throw new BankException($e->getMessage());
        }
    }

    function __destruct()
    {
        openssl_free_key($this->key_resource);
    }
}


if (!function_exists('url_modify')) {
    /**
     * manipulate the Current/Given URL with the given parameters
     *
     * @param $changes
     * @param  $url
     *
     * @return string
     */
    function url_modify($changes, $url = false)
    {
        // If $url wasn't passed in, use the current url
        if ($url == false) {
            $scheme = $_SERVER['SERVER_PORT'] == 80 ? 'http' : 'https';
            $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        // Parse the url into pieces
        $url_array = parse_url($url);
        // The original URL had a query string, modify it.
        if (!empty($url_array['query'])) {
            parse_str($url_array['query'], $query_array);
            $query_array = array_merge($query_array, $changes);
        } // The original URL didn't have a query string, add it.
        else {
            $query_array = $changes;
        }

        return (!empty($url_array['scheme']) ? $url_array['scheme'] . '://' : null) .
        (!empty($url_array['host']) ? $url_array['host'] : null) .
        (!empty($url_array['port']) ? ':' . $url_array['port'] : null) .
        $url_array['path'] . '?' . http_build_query($query_array);
    }
}