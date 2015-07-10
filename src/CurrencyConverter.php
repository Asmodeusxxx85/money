<?php

namespace Brick\Money;

use Brick\Money\ExchangeRateProvider;
use Brick\Money\Exception\CurrencyConversionException;

use Brick\Math\RoundingMode;
use Brick\Math\Exception\RoundingNecessaryException;

/**
 * Converts monies into different currencies, using an exchange rate provider.
 */
class CurrencyConverter
{
    /**
     * @var ExchangeRateProvider
     */
    private $exchangeRateProvider;

    /**
     * @var int
     */
    private $roundingMode = RoundingMode::FLOOR;

    /**
     * @param ExchangeRateProvider $exchangeRateProvider The exchange rate provider.
     * @param int                  $roundingMode         The rounding mode to use for conversions.
     */
    public function __construct(ExchangeRateProvider $exchangeRateProvider, $roundingMode = RoundingMode::DOWN)
    {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->roundingMode         = $roundingMode;
    }

    /**
     * @param Money    $money
     * @param Currency $currency
     *
     * @return Money
     *
     * @throws CurrencyConversionException If the exchange rate is not available.
     * @throws RoundingNecessaryException  If rounding was necessary but this converter uses RoundingMode::UNNECESSARY.
     */
    public function convert(Money $money, Currency $currency)
    {
        if ($money->getCurrency()->is($currency)) {
            return $money;
        }

        $exchangeRate = $this->exchangeRateProvider->getExchangeRate($money->getCurrency(), $currency);

        return $money->convertedTo($currency, $exchangeRate, $this->roundingMode);
    }

    /**
     * Compares the given monies.
     *
     * The amount is not rounded before comparison, so the results are more relevant than when using
     * `convert($a, $b->getCurrency())->compareTo($b)`.
     *
     * Note that the comparison is performed by converting A into B's currency.
     * This order is important if the exchange rate provider uses different exchange rates
     * when converting back and forth two currencies.
     *
     * @param Money $a
     * @param Money $b
     *
     * @return int -1, 0 or 1.
     *
     * @throws CurrencyConversionException If the exchange rate is not available.
     */
    public function compare(Money $a, Money $b)
    {
        $aCurrency = $a->getCurrency();
        $bCurrency = $b->getCurrency();

        if ($aCurrency->is($bCurrency)) {
            return $a->compareTo($b);
        }

        $aAmount = $a->getAmount();
        $bAmount = $b->getAmount();

        $exchangeRate = $this->exchangeRateProvider->getExchangeRate($aCurrency, $bCurrency);

        $aAmount = $aAmount->toBigRational()->multipliedBy($exchangeRate);

        return $aAmount->compareTo($bAmount);
    }
}
