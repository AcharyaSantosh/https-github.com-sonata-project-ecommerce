<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\Component\Tests\Payment;

use Buzz\Browser;
use Buzz\Client\ClientInterface;
use PHPUnit\Framework\TestCase;
use Sonata\Component\Basket\Basket;
use Sonata\Component\Payment\PassPayment;
use Sonata\Component\Payment\TransactionInterface;
use Sonata\Component\Product\ProductInterface;
use Sonata\OrderBundle\Entity\BaseOrder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class PassPaymentTest_Order extends BaseOrder
{
    /**
     * @return int the order id
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }
}
class PassPaymentTest extends TestCase
{
    /**
     * useless test ....
     */
    public function testPassPayment(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->exactly(2))->method('generate')->will($this->returnValue('http://foo.bar/ok-url'));

        $client = $this->createMock(ClientInterface::class);

        $browser = new Browser($client);
        $payment = new PassPayment($router, $browser);
        $payment->setCode('free_1');

        $basket = $this->createMock(Basket::class);
        $product = $this->createMock(ProductInterface::class);

        $transaction = $this->createMock(TransactionInterface::class);
        $transaction->expects($this->exactly(2))->method('get')->will($this->returnCallback([$this, 'callback']));
        $transaction->expects($this->once())->method('setTransactionId');

        $date = new \DateTime();
        $date->setTimeStamp(strtotime('11/30/1981'));
        $date->setTimezone(new \DateTimeZone('Europe/Paris'));

        $order = new PassPaymentTest_Order();
        $order->setCreatedAt($date);

        $this->assertEquals('free_1', $payment->getCode(), 'Pass Payment return the correct code');
        $this->assertTrue($payment->isAddableProduct($basket, $product));
        $this->assertTrue($payment->isBasketValid($basket));
        $this->assertTrue($payment->isRequestValid($transaction));

        $this->assertFalse($payment->isCallbackValid($transaction));
        $this->assertFalse($payment->sendConfirmationReceipt($transaction));

        $transaction->expects($this->any())->method('getOrder')->will($this->returnValue($order));

        $this->assertTrue($payment->isCallbackValid($transaction));
        $this->assertInstanceOf(Response::class, $payment->handleError($transaction));

        $this->assertInstanceOf(Response::class, $payment->sendConfirmationReceipt($transaction));

        $response = $payment->sendbank($order);

        $this->assertTrue($response->headers->has('Location'));
        $this->assertEquals('http://foo.bar/ok-url', $response->headers->get('Location'));
        $this->assertFalse($response->isCacheable());

        $this->assertEquals($payment->getOrderReference($transaction), '0001231');

        $payment->applyTransactionId($transaction);
    }

    public static function callback($name)
    {
        if ('reference' == $name) {
            return '0001231';
        }

        if ('transaction_id' == $name) {
            return 1;
        }

        if ('check' == $name) {
            return '0df8a3065a433ffbd907b2e6450199fb25e7902f';
        }
    }
}
