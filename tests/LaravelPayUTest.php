<?php

use Fakes\User;
use Fakes\Order;
use Carbon\Carbon;
use Raulingg\LaravelPayU\LaravelPayU;

class LaravelPayUTest extends PHPUnit_Framework_TestCase
{
    protected $approvedOrder;

    public static function setUpBeforeClass()
    {
        if (file_exists(__DIR__.'/../.env.TMPL')) {
            $dotenv = new Dotenv\Dotenv(__DIR__.'/../', '.env.TMPL');
            $dotenv->load();
        }

        date_default_timezone_set('America/Lima');
    }

    // public function testCreditCardPayment()
    // {
    //     $user = $this->getUser();
    //     $order = $this->getOrder();

    //     $session = md5('myecommercewebsite.com');
    //     $data = [
    //         \PayUParameters::DESCRIPTION => 'Payment cc test',
    //         \PayUParameters::IP_ADDRESS => '127.0.0.1',
    //         \PayUParameters::CURRENCY => 'PEN',
    //         \PayUParameters::CREDIT_CARD_NUMBER => '378282246310005',
    //         \PayUParameters::CREDIT_CARD_EXPIRATION_DATE => '2017/02',
    //         \PayUParameters::CREDIT_CARD_SECURITY_CODE => '1234',
    //         \PayUParameters::INSTALLMENTS_NUMBER => 1,
    //         \PayUParameters::DEVICE_SESSION_ID => session_id($session),
    //         \PayUParameters::PAYMENT_METHOD => 'AMEX',
    //         \PayUParameters::PAYER_NAME => 'APPROVED',
    //         \PayUParameters::PAYER_DNI => $user->identification,
    //         \PayUParameters::REFERENCE_CODE => $order->reference,
    //         \PayUParameters::USER_AGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    //         \PayUParameters::VALUE => $order->value
    //     ];

    //     $order->payWith($data, function($response, $order) {
    //         if ($response->code == 'SUCCESS') {
    //             // ... check transactionResponse object and do what you need
    //             $order->update([
    //                 'payu_order_id' => $response->transactionResponse->orderId,
    //                 'transaction_id' => $response->transactionResponse->transactionId
    //             ]);

    //             $this->assertEquals($response->transactionResponse->state, 'APPROVED');
    //         } else {
    //             //... something went wrong
    //         }
    //     }, function($error) {
    //         // ... handle PayUException, InvalidArgument or another error
    //     });

    //     return $order;
    // }

    // public function testCashPayment()
    // {
    //     $user = $this->getUser();
    //     $order = $this->getOrder();

    //     // Method only used for testing, because cash payments can't use
    //     // account testing enviroment equals true
    //     LaravelPayU::setAccountOnTesting(false);

    //     $now = Carbon::now();
    //     $nextWeek = $now->addDays(8);
    //     $data = [
    //         \PayUParameters::DESCRIPTION => 'Payment cash test',
    //         \PayUParameters::IP_ADDRESS => '127.0.0.1',
    //         \PayUParameters::CURRENCY => 'COP',
    //         \PayUParameters::EXPIRATION_DATE => $nextWeek->format('Y-m-d\TH:i:s'),
    //         \PayUParameters::PAYMENT_METHOD => 'BALOTO',
    //         \PayUParameters::BUYER_EMAIL => 'buyeremail@test.com',
    //         \PayUParameters::PAYER_NAME => 'APPROVED',
    //         \PayUParameters::PAYER_DNI => $user->identification,
    //         \PayUParameters::REFERENCE_CODE => $order->reference,
    //         \PayUParameters::VALUE => $order->value
    //     ];

    //     $order->payWith($data, function($response) {
    //         if ($response->code == 'SUCCESS') {
    //             // ... check transactionResponse object and do what you need
    //             $this->assertEquals($response->transactionResponse->state, 'PENDING');
    //         } else {
    //             //... something went wrong
    //         }
    //     }, function($error) {
    //         // ... handle PayUException, InvalidArgument or another error
    //     });
    // }

    // /**
    //  * @depends testCreditCardPayment
    //  */
    // public function testSearchOrderById($order)
    // {
    //     $order->searchById(function($response) {
    //         // ... check response and use the order data to update or something
    //         $this->assertEquals($response->status, 'CAPTURED');
    //     }, function($error) {
    //         // ... handle PayUException, InvalidArgument or another error
    //     });
    // }

    // /**
    //  * @depends testCreditCardPayment
    //  */
    // public function testSearchOrderByReference($order)
    // {
    //     $order->searchByReference(function($response) {
    //         // ... check response array list and use the order data to update or something
    //         $this->assertEquals($response[0]->status, 'CAPTURED');
    //     }, function($error) {
    //         // ... handle PayUException, InvalidArgument or another error
    //     });
    // }

    // /**
    //  * @depends testCreditCardPayment
    //  */
    // public function testSearchTransactionResponse($order)
    // {
    //     $order->searchByTransaction(function($response) {
    //         // ... check response array list and use the order data to update or something
    //         $this->assertEquals($response->state, 'APPROVED');
    //     }, function($error) {
    //         // ... handle PayUException, InvalidArgument or another error
    //     });
    // }

    private function getUser()
    {
        return new User([
            'name' => 'Taylor Otwell',
            'email' => 'user@tests.com',
            'identification' => '1000100100'
        ]);
    }

    private function getOrder()
    {
        return new Order([
            'payu_order_id' => null,
            'transaction_id' => null,
            'reference' => uniqid(time()),
            'value' => 20000,
            'user_id' => 1
        ]);
    }
}
