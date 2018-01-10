<?php
namespace Raulingg\LaravelPayU\Tests;

use PayU;
use Exception;
use PayUPayments;
use Carbon\Carbon;
use PayUCountries;
use PayUParameters;
use PHPUnit\Framework\TestCase;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Request;
use Raulingg\LaravelPayU\Tests\Fakes\User;
use Raulingg\LaravelPayU\Client\PayuClient;
use Raulingg\LaravelPayU\Tests\Fakes\Order;
use Raulingg\LaravelPayU\Tests\Fakes\CreditCard;
use Raulingg\LaravelPayU\Contracts\PayuClientInterface;

class PayuClientTest extends TestCase
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

    /**
     * @test
     */
    public function it_do_ping_successfuly()
    {
        $response = $this->payuClient->doPing(function($response) {
            $this->assertAttributeEquals('SUCCESS', 'code', $response);
        }, function($error) {
            $this->assertTrue(false, $error->getCode() . ' - ' . $error->getMessage());
        });
    }

    /**
     * @test
     */
    public function it_make_payment_with_credit_card_successfuly()
    {
        $this->makePaymentWithCreditCard();

        $this->assertNotNull($this->order->payu_order_id);
    }

    /**
     * @test
     */
    public function it_search_successful_order_detail_by_payu_order_id()
    {
        $this->makePaymentWithCreditCard();

        $payuOrderId = $this->order->payu_order_id;

        $this->payuClient->searchById($payuOrderId, function($order) {
            $this->assertContains($order->status, ['CAPTURED', 'IN_PROGRESS']);
        }, function($error) {
            $this->assertTrue(false, $error->getCode() . ' - ' . $error->getMessage());
        });
    }

    /**
     * @test
     */
    public function it_search_successful_order_detail_by_its_reference_code()
    {
        $this->makePaymentWithCreditCard();
        $referenceCode = $this->order->reference_code;

        $this->payuClient->searchByReference($referenceCode , function($response) {
            $this->assertContains($response[0]->status, ['CAPTURED', 'IN_PROGRESS']);
        }, function($error) {
            $this->assertTrue(false, $error->getCode() . ' - ' . $error->getMessage());
        });
    }

    /**
     * @test
     */
    public function it_search_order_transaction_detail_successful_by_payu_transaction_id()
    {
        $this->makePaymentWithCreditCard();

        $payuTransactionId = $this->order->transaction_id;

        $this->payuClient->searchByTransaction($payuTransactionId, function($transaction) {
            $this->assertEquals($transaction->state, 'APPROVED');
        }, function($error) {
            $this->assertTrue(false, $error->getCode() . ' - ' . $error->getMessage());
        });
    }

    /**
     * Make a payment using the Payu Client
     *
     * @param Closure $onSuccessClosure
     * @return void
     */
    protected function makePaymentWithCreditCard()
    {
        $creditCard = $this->getCreditCard();
        $data = [
            PayUParameters::VALUE => $this->order->value,
            PayUParameters::DESCRIPTION => 'Payment cc test',
            PayUParameters::REFERENCE_CODE => $this->order->reference_code,

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

        $this->payuClient->pay($data, function($response) {
            if ($response->code != 'SUCCESS') {
                throw new Exception($response->code);
            }

            if ($response->transactionResponse->state != 'APPROVED') {
                throw new Exception($response->code);
            }

            $this->order->payu_order_id = $response->transactionResponse->orderId;
            $this->order->transaction_id = $response->transactionResponse->transactionId;
        }, function($error) {
            throw $error;
        });
    }

    protected function getUser()
    {
        return new User([
            'name' => 'Taylor Otwell',
            'email' => 'user@tests.com',
            'identification' => '1000100100'
        ]);
    }

    protected function getOrder()
    {
        return new Order([
            'payu_order_id' => null,
            'transaction_id' => null,
            'reference_code' => uniqid(time()),
            'value' => 100,
            'user_id' => 1
        ]);
    }

    protected function getCreditCard()
    {
        $creditCard = new CreditCard($this->faker->creditCardDetails);
        $creditCard->cvv = $this->faker->numberBetween(199, 399);
        $creditCard->name = 'APPROVED';
        $creditCard->expirationDate = Carbon::createFromFormat('m/y', $creditCard->expirationDate)->format('Y/m');

        return $creditCard;
    }
}
