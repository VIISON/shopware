<?php

class Migrations_Migration778 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add a new 'customer_group_id' field to 's_user'
        $sql = <<<SQL
ALTER TABLE `s_user`
    ADD `customer_group_id` INT(11) NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        // Fill the new 's_user.customer_group_id' field with the 's_core_customergroups.id'
        // respective to the current value of 's_user.customergroup'
        $sql = <<<SQL
UPDATE `s_user`
LEFT JOIN `s_core_customergroups`
    ON `s_user`.`customergroup` = `s_core_customergroups`.`groupkey`
SET `s_user`.`customer_group_id` = `s_core_customergroups`.`id`;
SQL;
        $this->addSql($sql);

        // Remove the old 's_user.customergroup' field
        $sql = <<<SQL
ALTER TABLE `s_user`
    DROP `customergroup`;
SQL;
        $this->addSql($sql);
    }
}
