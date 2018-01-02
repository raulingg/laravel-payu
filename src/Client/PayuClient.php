<?php

namespace Raulingg\LaravelPayU\Client;

use PayU;
use Exception;
use PayUPayments;
use PayUException;
use PayUParameters;
use Environment as PayUEnvironment;
use Raulingg\LaravelPayU\Contracts\PayuClientInterface;

class PayuClient implements PayuClientInterface
{
    const API_KEY = 'apiKey';
    const API_LOGIN = 'apiLogin';
    const MERCHANT_ID = 'merchantId';
    const ON_TESTING = 'isTest';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected static $accountId;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected static $country;


    /**
     * The current currency.
     *
     * @var string
     */
    public static $currency;

    /**
     * The current currency symbol.
     *
     * @var string
     */
    public static $currencySymbol = '$';

    /**
     * The custom currency formatter.
     *
     * @var callable
     */
    protected static $formatCurrencyUsing;


    /**
     *
     * @param array $environmentParams
     */
    public function __construct($environmentParams)
    {
        static::setEnvironment($environmentParams);
    }

     /**
     * Set Payu Environment for the account.
     *
     * @param array $params
     * @return void
     */
    public static function setEnvironment($environmentParams)
    {
        $environmentParamNames = PayuClient::getEnvironmentParamsNames();

        foreach ($environmentParams as $paramName => $paramValue) {
            if (! in_array($paramName, $environmentParamNames)) {
                continue;
            }

            if ($paramName == PayUParameters::ACCOUNT_ID || $paramName == PayUParameters::COUNTRY) {
                PayuClient::${$paramName} = $paramValue;
            } else {
                PayU::${$paramName} = $paramValue;
            }
        }

        if (PayU::$isTest) {
            PayUEnvironment::setPaymentsCustomUrl(
                'https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi'
            );
            PayUEnvironment::setReportsCustomUrl(
                'https://sandbox.api.payulatam.com/reports-api/4.0/service.cgi'
            );
            PayUEnvironment::setSubscriptionsCustomUrl(
                'https://sandbox.api.payulatam.com/payments-api/rest/v4.3/'
            );
        } else {
            PayUEnvironment::setPaymentsCustomUrl(
                'https://api.payulatam.com/payments-api/4.0/service.cgi'
            );
            PayUEnvironment::setReportsCustomUrl(
                'https://api.payulatam.com/reports-api/4.0/service.cgi'
            );
            PayUEnvironment::setSubscriptionsCustomUrl(
                'https://api.payulatam.com/payments-api/rest/v4.3/'
            );
        }
    }

    public static function getEnvironmentParamsNames()
    {
        return [
            PayuClient::API_KEY,
            PayuClient::API_LOGIN,
            PayuClient::MERCHANT_ID,
            PayuClient::ON_TESTING,
            PayUParameters::ACCOUNT_ID,
            PayUParameters::COUNTRY,
        ];
    }


    /**
     * Set the currency to be used when billing users.
     *
     * @param  string  $currency
     * @param  string|null  $symbol
     * @return void
     */
    public static function useCurrency($currency, $symbol = null)
    {
        static::$currency = $currency;

        static::useCurrencySymbol($symbol ?: static::guessCurrencySymbol($currency));
    }

    /**
     * Guess the currency symbol for the given currency.
     *
     * @param  string  $currency
     * @return string
     */
    protected static function guessCurrencySymbol($currency)
    {
        switch (strtolower($currency)) {
        case 'usd':
        case 'clp':
        case 'cop':
        case 'mxn':
            return '$';
        case 'ars':
            return '$a';
        case 'pen':
            return 'S/';
        case 'brl':
            return 'R$';
        default:
            throw new Exception('Unable to guess symbol for currency. Please explicitly specify it.');
        }
    }

    /**
     * Get the currency currently in use.
     *
     * @return string
     */
    public static function usesCurrency()
    {
        return static::$currency;
    }

    /**
     * Set the currency symbol to be used when formatting currency.
     *
     * @param  string  $symbol
     * @return void
     */
    public static function useCurrencySymbol($symbol)
    {
        static::$currencySymbol = $symbol;
    }

    /**
     * Set the custom currency formatter.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function formatCurrencyUsing($callback)
    {
        static::$formatCurrencyUsing = $callback;
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    public static function formatAmount($amount)
    {
        if (static::$formatCurrencyUsing) {
            return call_user_func(static::$formatCurrencyUsing, $amount);
        }

        $amount = number_format($amount / 100, 2);

        if (starts_with($amount, '-')) {
            return '-'.static::usesCurrencySymbol().ltrim($amount, '-');
        }

        return static::usesCurrencySymbol().$amount;
    }

    /** @inheritDoc */
    public function doPing($onSuccess, $onError)
    {
        try {
            $response = PayUPayments::doPing();
            if ($response) {
                $onSuccess($response);
            }
        } catch (PayUException $exc) {
            $onError($exc);
        }
    }

    /** @inheritDoc */
    public function pay($params, $onSuccess, $onError)
    {
        $params[PayUParameters::ACCOUNT_ID] = static::$accountId;
        $params[PayUParameters::COUNTRY] = static::$country;

        try {
            $response = PayUPayments::doAuthorizationAndCapture($params);

            if ($response) {
                $onSuccess($response, $this);
            }
        } catch (PayUException $exc) {
            $onError($exc);
        } catch (InvalidArgumentException $exc) {
            $onError($exc);
        }
    }

    /** @inheritDoc */
    public function authorize($params, $onSuccess, $onError)
    {
        $params[PayUParameters::ACCOUNT_ID] = static::$accountId;
        $params[PayUParameters::COUNTRY] = static::$country;

        try {
            $response = PayUPayments::doAuthorization($params);

            if ($response) {
                $onSuccess($response, $this);
            }
        } catch (PayUException $exc) {
            $onError($exc);
        } catch (InvalidArgumentException $exc) {
            $onError($exc);
        }
    }

    /** @inheritDoc */
    public function capture($params, $onSuccess, $onError)
    {
        $params[PayUParameters::ACCOUNT_ID] = static::$accountId;
        $params[PayUParameters::COUNTRY] = static::$country;

        try {
            $response = PayUPayments::doCapture($params);

            if (! is_null($response)) {
                $onSuccess($response, $this);
            }
        } catch (PayUException $exc) {
            $onError($exc);
        } catch (InvalidArgumentException $exc) {
            $onError($exc);
        }
    }
}
