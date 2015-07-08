<?php

namespace Brick\Money\Tests;

use Brick\Money\Money;
use Brick\Money\MoneyBag;

/**
 * Base class for money tests.
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expectedAmount   The expected decimal amount.
     * @param string $expectedCurrency The expected currency code.
     * @param Money  $actual           The money to test.
     */
    final protected function assertMoneyEquals($expectedAmount, $expectedCurrency, $actual)
    {
        $this->assertInstanceOf(Money::class, $actual);
        $this->assertSame($expectedCurrency, (string) $actual->getCurrency());
        $this->assertSame($expectedAmount, (string) $actual->getAmount());
    }

    /**
     * @param Money|string $expected The expected money, or its string representation.
     * @param Money        $actual   The money to test.
     */
    final protected function assertMoneyIs($expected, $actual)
    {
        $this->assertInstanceOf(Money::class, $actual);
        $this->assertSame((string) $expected, (string) $actual);
    }

    /**
     * @param array    $expectedMonies
     * @param MoneyBag $moneyBag
     */
    final protected function assertMoneyBagContains(array $expectedMonies, $moneyBag)
    {
        $this->assertInstanceOf(MoneyBag::class, $moneyBag);

        // Test get() on each Money
        foreach ($expectedMonies as $money) {
            $money = Money::parse($money);
            $this->assertMoneyIs($money, $moneyBag->get($money->getCurrency()));
        }

        $actualMonies = $moneyBag->getMonies();

        foreach ($actualMonies as & $money) {
            $money = (string) $money;
        }

        sort($expectedMonies);
        sort($actualMonies);

        // Test getMonies()
        $this->assertSame($expectedMonies, $actualMonies);
    }
}