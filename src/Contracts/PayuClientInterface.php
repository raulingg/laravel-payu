<?php

namespace Raulingg\LaravelPayU\Contracts;


interface PayuClientInterface
{
    /**
     * Check if available PayU platform is.
     *
     * @param callabe $onSuccess
     * @param callabe $onError
     * @return void
     */
    public function doPing($onSuccess, $onError);

    /**
     * Make a "one off" payment on the given order.
     *
     * @param array $params
     * @param callable $onSuccess
     * @param callable $onError
     * @return void
     */
    public function pay($params, $onSuccess, $onError);

    /**
     * Check authorization for payment on the given order.
     *
     * @param array $params
     * @param callable $onSuccess
     * @param callable $onError
     * @return void
     */
    public function authorize($params, $onSuccess, $onError);

    /**
     * Capture payment data
     *
     * @param array $params
     * @param callable $onSuccess
     * @param callable $onError
     * @return void
     */
    public function capture($params, $onSuccess, $onError);
}

