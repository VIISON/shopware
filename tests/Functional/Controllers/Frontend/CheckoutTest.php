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
 * @package   Shopware\Tests
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Frontend_CheckoutTest extends Enlight_Components_Test_Plugin_TestCase
{
    const ARTICLE_NUMBER = 'SW10239';
    const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';

    /**
     * reads the user agent black list and test if the bot can add an article
     *
     * @ticket SW-6411
     */
    public function testBotAddBasketArticle()
    {
        $botBlackList = array('digout4u', 'fast-webcrawler', 'googlebot', 'ia_archiver', 'w3m2', 'frooglebot');
        foreach ($botBlackList as $userAgent) {
            if (!empty($userAgent)) {
                $sessionId = $this->addBasketArticle($userAgent);
                $this->assertNotEmpty($sessionId);
                $basketId = Shopware()->Db()->fetchOne(
                    "SELECT id FROM s_order_basket WHERE sessionID = ?",
                    array($sessionId)
                );
                $this->assertEmpty($basketId);
            }
        }

        Shopware()->Modules()->Basket()->sDeleteBasket();
    }

    /**
     * test if an normal user can add an article
     *
     * @ticket SW-6411
     */
    public function testAddBasketArticle()
    {
        $sessionId = $this->addBasketArticle(self::USER_AGENT);
        $this->assertNotEmpty($sessionId);
        $basketId = Shopware()->Db()->fetchOne(
            "SELECT id FROM s_order_basket WHERE sessionID = ?",
            array($sessionId)
        );
        $this->assertNotEmpty($basketId);

        Shopware()->Modules()->Basket()->sDeleteBasket();
    }

    /**
     * fires the add article request with the given user agent
     * @param $userAgent
     * @param $quantity
     * @return String | session id
     */
    private function addBasketArticle($userAgent, $quantity = 1)
    {
        $this->reset();
        $this->Request()->setParam('sQuantity', $quantity);
        $this->Request()->setHeader('User-Agent', $userAgent);
        $this->dispatch('/checkout/addArticle/sAdd/'.self::ARTICLE_NUMBER);
        return Shopware()->Container()->get('SessionID');
    }

    /**
     * Tests that price calculations of the basket do not differ from the price calculation in the invoice document
     */
    public function testsBasketCalculationEqualsInvoiceDocumentCalculationNetMode()
    {
        $net = true;
        $this->runTestBasketCalculationEqualsInvoiceDocumentCalculation($net);
    }

    /**
     * Tests that price calculations of the basket do not differ from the price calculation in the invoice document
     */
    public function testsBasketCalculationEqualsInvoiceDocumentCalculationWithoutNetMode()
    {
        $net = false;
        $this->runTestBasketCalculationEqualsInvoiceDocumentCalculation($net);
    }

    /**
     * @param bool $net
     */
    public function runTestBasketCalculationEqualsInvoiceDocumentCalculation($net = true)
    {
        $tax = $net == true ? 0 : 1;

        // Set net customer group
        $defaultShop = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class)->find(1);
        $previousCustomerGroup = $defaultShop->getCustomerGroup()->getKey();
        $netCustomerGroup = Shopware()->Models()->getRepository(\Shopware\Models\Customer\Group::class)->findOneBy(['tax' => $tax])->getKey();
        $this->assertNotEmpty($netCustomerGroup);

        Shopware()->Db()->query(
            'UPDATE s_user SET customergroup = ? WHERE id = 1',
            array($netCustomerGroup)
        );

        // Simulate checkout in frontend

        // Login
        $this->loginFrontendUser();

        // Add article to basket
        $this->addBasketArticle(self::USER_AGENT, 5);

        // Confirm checkout
        $this->reset();
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->dispatch('/checkout/confirm');

        // Finish checkout
        $this->reset();
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setParam('sAGB', 'on');
        $this->dispatch('/checkout/finish');

        // Revert customer group
        Shopware()->Db()->query(
            'UPDATE s_user SET customergroup = ? WHERE id = 1',
            array($previousCustomerGroup)
        );
        // Logout frontend user
        Shopware()->Modules()->Admin()->logout();

        // Fetch created order
        $orderId = Shopware()->Db()->fetchOne(
            'SELECT id FROM s_order ORDER BY ID DESC LIMIT 1'
        );
        /** @var \Shopware\Models\Order\Order $order */
        $order = Shopware()->Models()->getRepository(\Shopware\Models\Order\Order::class)->find($orderId);

        // Document/Order calculates amount without shipping costs, we remove them here to be able to compare with the document calculation
        // This is not true for shippingCostsAsPosition (But this is not the place to test this method anyway)
        $orderAmount = $order->getInvoiceAmount() - $order->getInvoiceShipping();
        $orderAmountNet = $order->getInvoiceAmountNet() - $order->getInvoiceShippingNet();
        $expectedTax = $orderAmount - $orderAmountNet;

        // Access the document via reflection to test the calculation
        $orderDocument = new Shopware_Models_Document_Order($orderId);
        $orderDocumentReflection = new \ReflectionObject($orderDocument);
        $netProperty = $orderDocumentReflection->getProperty('_amountNetto');
        $netProperty->setAccessible(true);
        $taxProperty = $orderDocumentReflection->getProperty('_tax');
        $taxProperty->setAccessible(true);
        $amountProperty= $orderDocumentReflection->getProperty('_amount');
        $amountProperty->setAccessible(true);

        $orderDocumentAmount = $amountProperty->getValue($orderDocument);
        $orderDocumentAmountNet = $netProperty->getValue($orderDocument);

        // Test that sBasket calculation matches calculateInvoiceAmount
        $this->assertEquals($orderAmount, $orderDocumentAmount, 'amount on invoice');
        $this->assertEquals($orderAmountNet, $orderDocumentAmountNet, 'net amount on invoice');
        $this->assertEquals($expectedTax, array_sum($taxProperty->getValue($orderDocument)), 'Tax on invoice');
    }

    /**
     * Login as a frontend user
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function loginFrontendUser()
    {
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
        $user = Shopware()->Db()->fetchRow(
            'SELECT id, email, password, subshopID, language FROM s_user WHERE id = 1'
        );

        /** @var $repository Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveById($user['language']);

        $shop->registerResources();

        Shopware()->Session()->Admin = true;
        Shopware()->System()->_POST = array(
            'email' => $user['email'],
            'passwordMD5' => $user['password'],
        );
        Shopware()->Modules()->Admin()->sLogin(true);
    }
}
