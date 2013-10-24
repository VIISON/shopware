<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @group disable
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Frontend_AccountTest extends Enlight_Components_Test_Controller_TestCase
{

    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Partner').'Partner.xml');
    }

    /**
     * test testPartnerStatistic controller action
     *
     * @return array|int|string $id
     */
    public function testPartnerStatistic()
    {
        //Login to the frontend
        $this->Request()
                ->setMethod('POST')
                ->setPost('email', 'test@example.com')
                ->setPost('password', 'shopware');
        $this->dispatch('/account/login');
        $this->assertTrue($this->Response()->isRedirect());
        $this->reset();

        //setting date range
        $params["fromDate"] = "01.01.2000";
        $params["toDate"] = "01.01.2222";
        $this->Request()->setParams($params);
        Shopware()->Session()->partnerId = 1;

        $this->dispatch('/account/partnerStatistic');
        $this->assertEquals("01.01.2000", $this->View()->partnerStatisticFromDate);
        $this->assertEquals("01.01.2222", $this->View()->partnerStatisticToDate);
        $chartData = $this->View()->sPartnerOrderChartData[0];

        $this->assertTrue(($chartData["date"] instanceof \DateTime));
        $this->assertTrue(!empty($chartData["timeScale"]));
        $this->assertTrue(!empty($chartData["netTurnOver"]));
        $this->assertTrue(!empty($chartData["provision"]));
    }
}
