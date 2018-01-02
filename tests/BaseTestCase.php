<?php

namespace Raulingg\LaravelPayU\Tests;

use Mockery;
use PHPUnit_Framework_TestCase;
/**
 * @package Neomerx\Tests\CorsIlluminate
 */
abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Tear down test.
     *
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }
}
