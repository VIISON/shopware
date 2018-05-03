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
class Migrations_Migration1402 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Increase the maximum length of description from 30 to 255 to allow for more descriptive price group
        // names. This is particularly important because now they are also shown in the frontend.
        $this->addSQL(
            'ALTER TABLE s_core_pricegroups
             MODIFY COLUMN description
                 VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL'
        );
        $this->addSQL(
            'ALTER TABLE s_core_pricegroups
            ADD crossArticle
                TINYINT(1) DEFAULT 0 NOT NULL'
        );
    }
}
