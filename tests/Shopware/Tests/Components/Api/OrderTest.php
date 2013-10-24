<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Api_OrderTest extends Shopware_Tests_Components_Api_TestCase
{
    /**
     * @var array
     */
    private $order;

    /**
     * @return \Shopware\Components\Api\Resource\Order
     */
    public function createResource()
    {
        return new \Shopware\Components\Api\Resource\Order();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->order = Shopware()->Db()->fetchRow("SELECT * FROM  `s_order` LIMIT 1");
    }

    public function testGetOneShouldBeSuccessful()
    {
        $order = $this->resource->getOne($this->order['id']);
        $this->assertEquals($this->order['id'], $order['id']);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testGetOneByNumberWithInvalidNumberShouldThrowNotFoundException()
    {
        $this->resource->getOneByNumber(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testGetOneByNumberWithMissinNumberShouldThrowParameterMissingException()
    {
        $this->resource->getOneByNumber('');
    }

    public function testGetOneByNumberShouldBeSuccessful()
    {
        $order = $this->resource->getOneByNumber($this->order['ordernumber']);
        $this->assertEquals($this->order['ordernumber'], $order['number']);
    }


    public function testGetOneShouldBeAbleToReturnObject()
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($this->order['id']);

        $this->assertInstanceOf('\Shopware\Models\Order\Order', $order);
        $this->assertEquals($this->order['id'], $order->getId());
    }

    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);
    }

    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);

        $this->assertInstanceOf('\Shopware\Models\Order\Order', $result['data'][0]);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->update(9999999, array());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', array());
    }
}
