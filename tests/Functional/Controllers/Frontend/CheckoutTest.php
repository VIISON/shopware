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
     * @return String | session id
     */
    private function addBasketArticle($userAgent)
    {
        $this->reset();
        $this->Request()->setHeader('User-Agent', $userAgent);
        $this->dispatch('/checkout/addArticle/sAdd/'.self::ARTICLE_NUMBER);
        return Shopware()->Container()->get('SessionID');
    }

    public function loginFrontendUser()
    {
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
        $user = Shopware()->Container()->get('dbal_connection')->fetchAssoc(
            'SELECT id, email, password, subshopID, language FROM s_user WHERE id = :userId',
            [
                ':userId' => 1
            ]
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

    private function addBasketArticleWithQuantity($quantity = 1)
    {
        $this->reset();
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setParam('sQuantity', $quantity);
        $this->dispatch('/checkout/addArticle/sAdd/'.self::ARTICLE_NUMBER);
        return Shopware()->Container()->get('SessionID');
    }

    /**
     * @group dev_thomas
     */
    public function testCheckoutForNetOrders() {
        $net = true;
        $this->runCheckoutTest($net);
        for($i=19;$i<30; $i++) {
            $this->runCheckoutTest($net, $i);
        }
    }

    /**
     * @group dev_thomas
     */
    public function testCheckoutForGrossOrders() {
        $net = false;
        $this->runCheckoutTest($net);

    }

    public function runCheckoutTest($net = false, $quantity=1) {
        $this->loginFrontendUser();

        $tax = $net == true ? 0 : 1;

        // Set net customer group
        $defaultShop = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class)->find(1);
        $previousCustomerGroup = $defaultShop->getCustomerGroup()->getKey();
        $netCustomerGroup = Shopware()->Models()->getRepository(\Shopware\Models\Customer\Group::class)->findOneBy(['tax' => $tax])->getKey();
        $this->assertNotEmpty($netCustomerGroup);
        Shopware()->Db()->query(
            "UPDATE s_user SET customergroup = ? WHERE id = 1",
            array($netCustomerGroup)
        );

        // Add article to basket
        $this->addBasketArticleWithQuantity($quantity);

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
            "UPDATE s_user SET customergroup = ? WHERE id = 1",
            array($previousCustomerGroup)
        );

        // Fetch created order
        $orderId = Shopware()->Db()->fetchOne(
            "SELECT id FROM s_order ORDER BY ID DESC LIMIT 1"
        );
        /** @var \Shopware\Models\Order\Order $order */
        $order = Shopware()->Models()->getRepository(\Shopware\Models\Order\Order::class)->find($orderId);

        $previousInvoiceAmount = $order->getInvoiceAmount();
        $previousInvoiceAmountNet = $order->getInvoiceAmountNet();

        // Simulate backend order save
        $order->calculateInvoiceAmount();

        // Test that sBasket calculation matches calculateInvoiceAmount
        $this->assertEquals(round($order->getInvoiceAmount(), 2), round($previousInvoiceAmount, 2), 'InvoiceAmount');
        $this->assertEquals(round($order->getInvoiceAmountNet(), 2), round($previousInvoiceAmountNet, 2), 'InvoiceAmountnet');

        Shopware()->Modules()->Admin()->logout();
    }
}
