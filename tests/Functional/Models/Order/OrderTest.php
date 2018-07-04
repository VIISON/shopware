<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Models_Order_OrderTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var Shopware\Components\Model\ModelManager
     */
    protected $em;

    /**
     * @var Shopware\Models\User\Repository
     */
    protected $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');

        Shopware()->Container()->set('Auth', new ZendAuthMock());
    }

    public function testUpdateOrderHistory()
    {
        $order = $this->createOrder();

        $previousPaymentStatus = $order->getPaymentStatus();
        $previousOrderStatus = $order->getOrderStatus();
        $order->calculateInvoiceAmount();

        $this->orderIsSaved($order);

        $history = $this->thenRetrieveHistoryOf($order);
        $this->assertCount(0, $history);

        $paymentStatusInProgress = $this->em->getReference('\Shopware\Models\Order\Status', 1);
        $orderStatusReserved = $this->em->getReference('\Shopware\Models\Order\Status', 18);

        $order->setPaymentStatus($paymentStatusInProgress);
        $order->setOrderStatus($orderStatusReserved);
        $this->em->flush($order);

        /** @var \Shopware\Models\Order\History[] $history */
        $history = $this->em->getRepository('\Shopware\Models\Order\History')->findBy(['order' => $order->getId()]);

        $this->assertCount(1, $history);

        $this->assertSame($paymentStatusInProgress, $history[0]->getPaymentStatus());
        $this->assertSame($previousPaymentStatus, $history[0]->getPreviousPaymentStatus());

        $this->assertSame($orderStatusReserved, $history[0]->getOrderStatus());
        $this->assertSame($previousOrderStatus, $history[0]->getPreviousOrderStatus());
    }

    public function createOrder($taxFree = false, $net = false)
    {
        $paymentStatusOpen = $this->em->getReference('\Shopware\Models\Order\Status', 17);
        $orderStatusOpen = $this->em->getReference('\Shopware\Models\Order\Status', 0);
        $paymentDebit = $this->em->getReference('\Shopware\Models\Payment\Payment', 2);
        $dispatchDefault = $this->em->getReference('\Shopware\Models\Dispatch\Dispatch', 9);
        $defaultShop = $this->em->getReference('\Shopware\Models\Shop\Shop', 1);

        $partner = new \Shopware\Models\Partner\Partner();
        $partner->setCompany('Dummy');
        $partner->setIdCode('Dummy');
        $partner->setDate(new \DateTime());
        $partner->setContact('Dummy');
        $partner->setStreet('Dummy');
        $partner->setZipCode('Dummy');
        $partner->setCity('Dummy');
        $partner->setPhone('Dummy');
        $partner->setFax('Dummy');
        $partner->setCountryName('Dummy');
        $partner->setEmail('Dummy');
        $partner->setWeb('Dummy');
        $partner->setProfile('Dummy');

        $this->em->persist($partner);

        $order = new \Shopware\Models\Order\Order();
        $order->setNumber('abc');
        $order->setPaymentStatus($paymentStatusOpen);
        $order->setOrderStatus($orderStatusOpen);
        $order->setPayment($paymentDebit);
        $order->setDispatch($dispatchDefault);
        $order->setPartner($partner);
        $order->setShop($defaultShop);
        $order->setInvoiceAmount(0);
        $order->setInvoiceAmountNet(0);
        $order->setInvoiceShipping(0);
        $order->setInvoiceShippingNet(0);
        $order->setTransactionId('');
        $order->setComment('Dummy');
        $order->setCustomerComment('Dummy');
        $order->setInternalComment('Dummy');
        $order->setNet($net);
        $order->setTaxFree($taxFree);
        $order->setTemporaryId(5);
        $order->setReferer('Dummy');
        $order->setTrackingCode('Dummy');
        $order->setLanguageIso('Dummy');
        $order->setCurrency('EUR');
        $order->setCurrencyFactor(1);
        $order->setRemoteAddress('127.0.0.1');
        return $order;
    }

    private function orderIsSaved($order)
    {
        $this->em->persist($order);
        $this->em->flush($order);
    }

    private function thenRetrieveHistoryOf($order)
    {
        return $this->em->getRepository('\Shopware\Models\Order\History')->findBy(['order' => $order->getId()]);
    }

    /**
     * Assert that tax is added to a single article
     *
     * Assert:	TAX_AMOUNT = NET * TAX_PERCENT
     * 			GROSS = round(NET + TAX_AMOUNT, 2)
     *
     * NET = 5.30
     * GROSS = round(NET + TAX_AMOUNT, 2)
     *       = round(5.30 + 1.007, 2)
     *       = round(6.307, 2)
     *       = 6.31
     *
     * @group POSApp
     */
    function testCalculationWithTax()
    {
        $articlePrice = 5.30;
        $quantity = 1;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(5.30, $order->getInvoiceAmountNet());
        $this->assertEquals(6.31, $order->getInvoiceAmount());
    }

    /**
     *  Asserting that the price of an Article is rounded before the quantity is multiplied with the price
     *
     *  rounded_price = round(22.445, 2) = 22.45
     *  quantity = 3
     *
     *  total = rounded_price * quantity = 22.45 * 3 = 67.35
     *
     *  @group POSApp
     */
    function testSinglePositionRounding()
    {
        $netAmount = 22.445;

        $net = true;
        $taxFree = false;

        $quantity = 3;
        $taxRate = "0";
        $detailAmount = 1;
        $order = $this->createTaxTestOrder($netAmount, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(67.35, $order->getInvoiceAmountNet());
        $this->assertEquals(67.35, $order->getInvoiceAmount());
    }

    /**

     *  @group POSApp
     */
    function testTaxCalculationInNetMode()
    {
        $netAmount = 22.445;

        $net = true;
        $taxFree = false;

        $quantity = 3;
        $taxRate = "19";
        $detailAmount = 1;
        $order = $this->createTaxTestOrder($netAmount, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(67.35, $order->getInvoiceAmountNet());
        $this->assertEquals(80.15, $order->getInvoiceAmount());
    }



    /**
     *  Asserting that the price of an Article is rounded before the quantity is multiplied with the price
     *
     *  rounded_price = round(22.445, 2) = 22.45
     *  quantity = 3
     *
     *  total = rounded_price * quantity = 22.45 * 3 = 67.35
     *
     *  @group POSApp
     */
    function testSinglePositionRoundingInNetMode()
    {
        $netAmount = 22.445;

        $net = true;
        $taxFree = false;

        $quantity = 3;
        $taxRate = "0";
        $detailAmount = 1;
        $order = $this->createTaxTestOrder($netAmount, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(67.35, $order->getInvoiceAmountNet());
        $this->assertEquals(80.15, $order->getInvoiceAmount());
    }

    /**
     * Assert that price is rounded after tax calculation and before multiplying with quantity
     *
     * Assert:	TAX_AMOUNT = NET * TAX_PERCENT
     * 			GROSS = round(NET + TAX_AMOUNT, 2) * QUANTITY
     *
     * QUANTITY = 5
     * NET = 5.30
     * GROSS = round(NET + TAX_AMOUNT, 2) * QUANTITY
     *       = round((5.30 + 1.007) * 5, 2)
     *       = round(6.307, 2) * 50
     *       = 6.31 * 50
     *       = 315.50
     *
     *  @group POSApp
     */
    function testTaxCalculationIsRoundedBeforeApplyingQuantity()
    {
        $grossAmount = 6.307; // net = 5.30

        $net = false;
        $taxFree = false;

        $quantity = 50;
        $taxRate = "19";
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($grossAmount, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(265.13, $order->getInvoiceAmountNet());
        $this->assertEquals(315.50, $order->getInvoiceAmount());
    }

    /**
     * Taken from a customer report
     *
     *  @group POSApp
     */
    function testStory1() {
        $grossAmount = 0.13; // net = 0.109243697479

        $net = false;
        $taxFree = false;

        $quantity = 175;
        $taxRate = "19";
        $detailAmount = 1;
        $order = $this->createTaxTestOrder($grossAmount, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(19.12, $order->getInvoiceAmountNet());
        $this->assertEquals(22.75, $order->getInvoiceAmount());
    }

    /**
     *  @group POSApp
     */
    function testStory2() {
        $grossAmount = 0.49; // net = 0.4117647059

        $net = false;
        $taxFree = false;

        $quantity = 100;
        $taxRate = "19";
        $detailAmount = 1;
        $order = $this->createTaxTestOrder($grossAmount, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(41.18, $order->getInvoiceAmountNet());
        $this->assertEquals(49.00, $order->getInvoiceAmount());
    }










    /**
     * Test that no tax is added to tax-free orders.
     *
     * @group new
     */
    function testCalculationTaxFree()
    {
        $articlePrice = 5.3;
        $quantity = 1;
        $taxRate = "19";
        $taxFree = true;
        $net = true;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(5.30, $order->getInvoiceAmount());
        $this->assertEquals(5.30, $order->getInvoiceAmountNet());
    }

    /**
     * Assert that rounding after tax calculation before multiplying is not implemented
     *
     * ASSERT THAT:
     * TAX_AMOUNT = NET * TAX_PERCENT
     * GROSS != round(NET + TAX_AMOUNT, 2) * QUANTITY
     *
     * QUANTITY = 5
     * NET = 5.30
     * GROSS = round(NET + TAX_AMOUNT, 2) * QUANTITY
     *       = round(5.30 + 1.007, 2) * 5
     *       = round(6.307, 2) * 5
     *       = 6.31 * 5
     *       = 31.55
     *
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @group HowItShouldBeButIsNot
     */
    function testRoundingAcrossTaxCalculation() {
        $netPrice = 4.4537815126; // 5.30 / 1.19
        $quantity = 50;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($netPrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(265.00, $order->getInvoiceAmountNet());
        $this->assertEquals(315.50, $order->getInvoiceAmount());
    }

    /**
     * Assert that rounding after tax calculation before multiplying is not implemented
     *
     * ASSERT THAT:
     * TAX_AMOUNT = NET * TAX_PERCENT
     * GROSS != round(NET + TAX_AMOUNT, 2) * QUANTITY
     *
     * QUANTITY = 5
     * NET = 5.30
     * GROSS = round(NET + TAX_AMOUNT, 2) * QUANTITY
     *       = round(5.30 + 1.007, 2) * 5
     *       = round(6.307, 2) * 5
     *       = 6.31 * 5
     *       = 31.55
     *
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @group HowItShouldBeButIsNot
     * @group now2
     */
    function testRoundingAcrossTaxCalculationWithNet() {
        $articlePrice = 5.30;
        $quantity = 50;
        $taxRate = "19";
        $taxFree = false;
        $net = false;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(265.00, $order->getInvoiceAmountNet());
        $this->assertEquals(315.50, $order->getInvoiceAmount());
    }

    /**
     * Assert that price is rounded after tax calculation and multiplying with quantity
     *
     * ASSERT THAT:
     * TAX_AMOUNT = NET * TAX_PERCENT
     * GROSS = round(NET + TAX_AMOUNT * QUANTITY, 2)
     *
     * QUANTITY = 5
     * NET = 5.30
     * GROSS = round(NET + TAX_AMOUNT * QUANTITY, 2)
     *       = round((5.30 + 1.007) * 5, 2)
     *       = round(6.307 * 5, 2)
     *       = round(31.535, 2)
     *       = 31.54
     *
     * @group HowItIs
     * @group bb2
     */
    function testAssertsNoRoundingAcrossTaxCalculation() {
        $netPrice = 5.30;
        $quantity = 50;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($netPrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(265.00, $order->getInvoiceAmountNet());
        $this->assertEquals(315.50, $order->getInvoiceAmount());
    }

    /**
     * Tests that values taxes are added to net orders.
     *      `net = true` refers to order details prices are net (values are stored as net)
     *
     * @group new
     */
    function testCalculationWithTaxAndNet() {
        $netPrice = 5.30;
        $quantity = 1;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($netPrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(5.30, $order->getInvoiceAmountNet());
        $this->assertEquals(6.31, $order->getInvoiceAmount());
    }

    /**
     *
     * @group new
     */
    function testAssertsThatLinesAreRoundedBeforeSummingUp() {
        $netPrice = 5.30;
        $quantity = 1;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 5;

        $order = $this->createTaxTestOrder($netPrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(26.50, $order->getInvoiceAmountNet());
        $this->assertEquals(31.55, $order->getInvoiceAmount());
    }

    /**
     * This test was created under the assumption that a price is rounded with
     * round(price, 3) and later with round(price, 2).
     * Behaviour was observed in sBasket
     *
     * Reason for this test:
     *   round(round(5.3449, 3), 2) != round(5.3449, 2)
     *
     * @group new
     */
    function testPricesAreOnlyRoundedToTwoDecimalPlaces()
    {
        // Net price from the database with superflous precision
        // to test if price is rounded before multiplied with quantity.
        $articlePrice = 5.3449;
        $quantity = 1;
        $taxRate = "19";
        $taxFree = false;
        $net = false;
        $detailAmount = 1;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals(5.34, $order->getInvoiceAmount());
    }

    function createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net) {
        $order = $this->createOrder($taxFree, $net);

        $details = [];
        for ($i=0; $i<$detailAmount; $i++) {
            $detail = new \Shopware\Models\Order\Detail();
            $detail->setQuantity($quantity);
            $detail->setNumber("sw-dummy-" . $i);
            $detail->setPrice($articlePrice);
            $detail->setTaxRate($taxRate);
            $details[] = $detail;
        }

        $order->setDetails($details);
        return $order;
    }


}

class ZendAuthMock
{
    public function getIdentity()
    {
        return null;
    }
}
