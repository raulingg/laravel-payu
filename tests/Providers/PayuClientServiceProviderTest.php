<?php

namespace Raulingg\LaravelPayU\Tests\Providers;

use Closure;
use Mockery;
use ArrayAccess;
use Mockery\Mock;
use PayUParameters;
use ReflectionClass;
use Raulingg\LaravelPayU\Client\PayuClient;
use Raulingg\LaravelPayU\Tests\BaseTestCase;
use Raulingg\LaravelPayU\Contracts\PayuClientInterface;
use Raulingg\LaravelPayU\Providers\PayuClientServiceProvider;
use Illuminate\Contracts\Foundation\Application as ApplicationInterface;

class PayuClientServiceProviderTest extends BaseTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $configMock;

    /**
     * @var \Mockery\MockInterface
     */
    protected $applicationMock;

    /**
     * @var \Raulingg\LaravelPayU\Providers\PayuClientServiceProvider
     */
    protected $serviceProvider;


    protected function setUp()
    {
        parent::setUp();

        $this->setUpMocks();
        $this->serviceProvider = new PayuClientServiceProvider($this->applicationMock);
    }

    protected function setUpMocks()
    {
        $this->configMock = Mockery::mock();
        $this->applicationMock = Mockery::mock(ArrayAccess::class);
        $this->applicationMock->shouldReceive('offsetGet')
            ->zeroOrMoreTimes()
            ->with('path.config')
            ->andReturn('config');
        $this->applicationMock->shouldReceive('make')
            ->zeroOrMoreTimes()
            ->with('config')
            ->andReturn($this->configMock);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(PayuClientServiceProvider::class, $this->serviceProvider);
    }

    /**
     * @test
     */
    public function it_does_nothing_in_the_register_method()
    {
         /** @noinspection PhpMethodParametersCountMismatchInspection */
         $this->configMock->shouldReceive('get')->withAnyArgs()->once()->andReturn([]);
         /** @noinspection PhpMethodParametersCountMismatchInspection */
         $this->configMock->shouldReceive('set')->withAnyArgs()->once()->andReturnUndefined();

        $this->applicationMock->shouldReceive('singleton')->withAnyArgs()->once()->andReturnUndefined();

        $this->assertNull($this->serviceProvider->register());
    }

    /**
     * @test
     */
    public function it_performs_a_boot_method()
    {
        $this->serviceProvider->boot();

        $this->assertContains(PayuClientServiceProvider::class, PayuClientServiceProvider::publishableProviders());
        $this->assertContains(
            'config' . DIRECTORY_SEPARATOR . PayuClientServiceProvider::CONFIG_FILE_NAME_PAYU . '.php',
            array_values(PayuClientServiceProvider::pathsToPublish(
                PayuClientServiceProvider::class
            ))
        );
    }

     /**
     * @test
     */
    public function it_performs_a_create_payu_client_closure_method()
    {
        $method  = self::getMethod('getCreatePayuClientClosure');
        /** @var Closure $closure */
        $closure = $method->invokeArgs($this->serviceProvider, []);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->configMock
            ->shouldReceive('get')
            ->withArgs([PayuClientServiceProvider::CONFIG_FILE_NAME_PAYU, []])
            ->once()
            ->andReturn([
                PayuClient::API_KEY => '',
                PayuClient::API_LOGIN => '',
                PayuClient::MERCHANT_ID => '',
                PayuClient::ON_TESTING => true,
                PayUParameters::ACCOUNT_ID => '',
                PayUParameters::COUNTRY => 'PE',
            ]);
        $this->assertInstanceOf(Closure::class, $closure);
        $this->assertInstanceOf(PayuClientInterface::class, $closure());
    }

    /**
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class  = new ReflectionClass(PayuClientServiceProvider::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
