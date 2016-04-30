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
 *
 * @category   Shopware
 * @package    Log
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/log/plugin}

/**
 * TODO
 */
//{block name="backend/log/view/log/plugin/list"}
Ext.define('Shopware.apps.Log.view.log.plugin.List', {
    extend: 'Shopware.apps.Log.view.log.core.List',
    alias: 'widget.log-plugin-main-list',

    /**
     *  @return Ext.grid.Column[]
     */
    getColumns: function(){
        var me = this;

        var columns = me.callParent(arguments);
        columns.splice(2, 0, {
            header: 'Plugin',
            dataIndex: 'plugin',
            width: 200
        });

        return columns;
    }
});
//{/block}
