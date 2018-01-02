<?php

namespace Raulingg\LaravelPayU\Tests\Fakes;

use Raulingg\LaravelPayU\Payable;
use Raulingg\LaravelPayU\Searchable;

class Order extends FakeObject
{
    public $payu_order_id = null;
    public $transaction_id = null;
    public $state = 1;
    public $reference = '';
    public $value = null;
    protected $user_id = null;

    public function update($values)
    {
        $this->updateValues($values);
    }
}
