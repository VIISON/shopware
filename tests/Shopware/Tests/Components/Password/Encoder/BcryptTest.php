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
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Hash_HasherBcryptTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Components\Password\Encoder\Bcrypt
     */
    private $hasher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->hasher = new \Shopware\Components\Password\Encoder\Bcrypt(array(
            // test should run fast, use minimum cost
            'cost' => 4,
        ));

        if (!$this->hasher->isCompatible()) {
            $this->markTestSkipped(
                'Brypt Hasher is not compatible with current system.'
            );
        }

    }

    /**
     * Test case
     */
    public function testIsAvailable()
    {
        $this->assertInstanceOf('\Shopware\Components\Password\Encoder\Bcrypt', $this->hasher);
    }

    /**
     * Test case
     */
    public function testGetNameShouldReturnName()
    {
        $this->assertEquals('Bcrypt', $this->hasher->getName());
    }

    /**
     * Test case
     */
    public function testGenerateShouldReturnString()
    {
        $this->assertInternalType('string', $this->hasher->encodePassword('foobar'));
    }

    /**
     * Test case
     */
    public function testGenerateShouldReturnDifferentHashesForSamePlaintextString()
    {
        $this->assertNotEquals($this->hasher->encodePassword('foobar'), $this->hasher->encodePassword('foobar'));
    }

    /**
     * Test case
     */
    public function testVerifyShouldReturnTrueForMatchingHash()
    {
        $hash = $this->hasher->encodePassword('foobar');

        $this->assertTrue($this->hasher->isPasswordValid('foobar', $hash));
    }

    /**
     * Test case
     */
    public function testVerifyShouldReturnFalseForNotMatchingHash()
    {
        $hash = $this->hasher->encodePassword('foobar');

        $this->assertFalse($this->hasher->isPasswordValid('notfoo', $hash));
    }

    /**
     * Test case
     */
    public function testRehash()
    {
        $hash = $this->hasher->encodePassword('foobar');

        $this->assertFalse($this->hasher->isReencodeNeeded($hash));
    }

    /**
     * Test case
     */
    public function testRehash2()
    {
        $hash = $this->hasher->encodePassword('foobar');
        $this->hasher = new \Shopware\Components\Password\Encoder\Bcrypt(array(
            'cost' => 5,
        ));

        $this->assertTrue($this->hasher->isReencodeNeeded($hash));
    }

    /**
     * Test case
     */
    public function testIfSaltIsGivenHashIsKnown()
    {
        $expectedHash = '$2y$04$T3l1YWl1Ri5CY3c3bU5DYOiIKqbbPpdcIiqpnkLoRwJkELw/uCaUO';

        $this->hasher = new \Shopware\Components\Password\Encoder\Bcrypt(array(
            'cost' => 4,
            'salt' => 'OyuaiuF.Bcw7mNCak/3Au7c=',
        ));

        $hash = $this->hasher->encodePassword('foobar');

        $this->assertEquals($expectedHash, $hash);
    }
}
