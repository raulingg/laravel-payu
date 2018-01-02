<?php
namespace Raulingg\LaravelPayU\Tests;

use PayU;
use PayUPayments;
use Carbon\Carbon;
use PayUCountries;
use PayUParameters;
use PHPUnit\Framework\TestCase;
use Faker\Factory as FakerFactory;
use Raulingg\LaravelPayU\Tests\Fakes\User;
use Raulingg\LaravelPayU\Tests\Fakes\Order;
use Raulingg\LaravelPayU\Client\PayuClient;
use Raulingg\LaravelPayU\Tests\Fakes\CreditCard;
use Raulingg\LaravelPayU\Contracts\PayuClientInterface;
use Illuminate\Support\Facades\Request;

class LaravelPayUTest extends TestCase
{
    /**
     *
     * @var Order
     */
    protected $order;

    /**
     *
     * @var User
     */
    protected $user;

    /**
     *
     * @var Faker\Generator;
     */
    protected $faker;

    /**
     *
     * @var PayuClientInterface
     */
    protected $payuClient;

    public function setUp()
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
        $this->order = $this->getOrder();
        $this->user = $this->getUser();

        $settings = [
            PayuClient::API_KEY => '4Vj8eK4rloUd272L48hsrarnUA',
            PayuClient::API_LOGIN => 'pRRXKOl8ikMmt9u',
            PayuClient::MERCHANT_ID => '508029',
            PayuClient::ON_TESTING => true,
            PayUParameters::ACCOUNT_ID => 512323,
            PayUParameters::COUNTRY => 'PE'
        ];
        $this->payuClient = new PayuClient($settings);
    }


    public function testDoPing()
    {
        $response = $this->payuClient->doPing(function($response) {
            $this->assertAttributeEquals('SUCCESS', 'code', $response);
        }, function($error) {
            $this->assertTrue(false, $error->getCode() . ' - ' . $error->getMessage());
        });
    }

    public function testCreditCardPayment()
    {
        $creditCard = $this->getCreditCard();
        $data = [
            PayUParameters::VALUE => $this->order->value,
            PayUParameters::DESCRIPTION => 'Payment cc test',
            PayUParameters::REFERENCE_CODE => $this->order->reference,

            PayUParameters::CURRENCY => 'PEN',

            PayUParameters::PAYMENT_METHOD => 'VISA',

            PayUParameters::CREDIT_CARD_NUMBER => '4907840000000005',
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $creditCard->expirationDate,
            PayUParameters::CREDIT_CARD_SECURITY_CODE => $this->faker->numberBetween(199,499),

            PayUParameters::INSTALLMENTS_NUMBER => 1,

            PayUParameters::PAYER_NAME => $creditCard->name,
            PayUParameters::PAYER_DNI => $this->faker->randomNumber(8),

            PayUParameters::DEVICE_SESSION_ID => session_id(),
            PayUParameters::IP_ADDRESS => '127.0.0.1',
            PayUParameters::USER_AGENT => $this->faker->userAgent
        ];

        $this->payuClient->pay($data, function($response, $order) {
            if ($response->code == 'SUCCESS') {
                $this->assertEquals($response->transactionResponse->state, 'APPROVED');
            } else {
                $this->assertTrue(false, print_r($response));
            }
        }, function($error) {
            $this->assertTrue(false, $error->getCode() . ' - ' . $error->getMessage());
        });
    }

    public function testCashPayment()
    {
        $this->markTestSkipped('must be revisited.');

        // Method only used for testing, because cash payments can't use
        // account testing enviroment equals true
        LaravelPayU::setAccountOnTesting(false);

        $now = Carbon::now();
        $nextWeek = $now->addDays(8);
        $data = [
            \PayUParameters::DESCRIPTION => 'Payment cash test',
            \PayUParameters::IP_ADDRESS => '127.0.0.1',
            \PayUParameters::CURRENCY => 'PEN',
            \PayUParameters::EXPIRATION_DATE => $nextWeek->format('Y-m-d\TH:i:s'),
            \PayUParameters::PAYMENT_METHOD => 'BALOTO',
            \PayUParameters::BUYER_EMAIL => 'buyeremail@test.com',
            \PayUParameters::PAYER_NAME => 'APPROVED',
            \PayUParameters::PAYER_DNI => $this->user->identification,
            \PayUParameters::REFERENCE_CODE => $this->order->reference,
            \PayUParameters::VALUE => $this->order->value
        ];

        $this->order->payWith($data, function($response) {
            if ($response->code == 'SUCCESS') {
                // ... check transactionResponse object and do what you need
                $this->assertEquals($response->transactionResponse->state, 'PENDING');
            } else {
                //... something went wrong
            }
        }, function($error) {
            // ... handle PayUException, InvalidArgument or another error
        });
    }

    /**
     * @depends testCreditCardPayment
     */
    public function testSearchOrderById($order)
    {
        $this->markTestSkipped('must be revisited.');

        $this->payuClient->searchById(function($response) {
            // ... check response and use the order data to update or something
            $this->assertEquals($response->status, 'CAPTURED');
        }, function($error) {
            // ... handle PayUException, InvalidArgument or another error
        });
    }

    /**
     * @depends testCreditCardPayment
     */
    public function testSearchOrderByReference($order)
    {
        $this->markTestSkipped('must be revisited.');

        $this->payuClient->searchByReference(function($response) {
            // ... check response array list and use the order data to update or something
            $this->assertEquals($response[0]->status, 'CAPTURED');
        }, function($error) {
            // ... handle PayUException, InvalidArgument or another error
        });
    }

    /**
     * @depends testCreditCardPayment
     */
    public function testSearchTransactionResponse($order)
    {
        $this->markTestSkipped('must be revisited.');

        $this->payuClient->searchByTransaction(function($response) {
            // ... check response array list and use the order data to update or something
            $this->assertEquals($response->state, 'APPROVED');
        }, function($error) {
            // ... handle PayUException, InvalidArgument or another error
        });
    }

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
            'value' => 100,
            'user_id' => 1
        ]);
    }

    private function getCreditCard()
    {
        $creditCard = new CreditCard($this->faker->creditCardDetails);
        $creditCard->cvv = $this->faker->numberBetween(199, 399);
        $creditCard->name = 'APPROVED';
        $creditCard->expirationDate = Carbon::createFromFormat('m/y', $creditCard->expirationDate)->format('Y/m');

        return $creditCard;
    }
}
