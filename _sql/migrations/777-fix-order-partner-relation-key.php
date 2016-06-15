<?php

class Migrations_Migration777 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add a temporary 'partner_id' field to 's_order'
        $sql = <<<SQL
ALTER TABLE `s_order`
    ADD `partner_id` INT(11) NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        // Fill the temporary 's_order.partner_id' field with the 's_emarketing_partner.id'
        // respective to the current value of 's_order.partnerID'
        $sql = <<<SQL
UPDATE `s_order`
LEFT OUTER JOIN `s_emarketing_partner`
    ON `s_order`.`partnerID` = `s_emarketing_partner`.`idcode`
SET `s_order`.`partner_id` = `s_emarketing_partner`.`id`;
SQL;
        $this->addSql($sql);

        // Remove the old 's_order.partnerID' field
        $sql = <<<SQL
ALTER TABLE `s_order`
    DROP `partnerID`;
SQL;
        $this->addSql($sql);

        // Rename the new 's_order.partner_id' field to 's_order.partnerID'
        $sql = <<<SQL
ALTER TABLE `s_order`
    CHANGE `partner_id` `partnerID` INT(11) NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);
    }
}
