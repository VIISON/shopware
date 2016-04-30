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

//{namespace name=backend/log/core}

/**
 * TODO
 */
//{block name="backend/log/view/log/core/list"}
Ext.define('Shopware.apps.Log.view.log.core.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.log-core-main-list',
    ui: 'shopware-ui',
    border: 0,
    autoScroll: true,

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.columns = me.getColumns();
        me.dockedItems = [{
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];

        me.callParent(arguments);
    },

    /**
     *  @return Ext.grid.Column[]
     */
    getColumns: function(){
        var me = this;

        var columns = [{
            xtype: 'datecolumn',
            header: 'Date',
            dataIndex: 'timestamp',
            width: 150,
            format: 'Y-m-d H:i:s'
        }, {
            header: 'Level',
            dataIndex: 'level',
            width: 100,
            sortable: false
        }, {
            header: 'Message',
            dataIndex: 'message',
            flex: 1,
            sortable: false
        }, {
            header: 'Error code',
            dataIndex: 'code',
            width: 75,
            sortable: false
        }, {
            xtype: 'actioncolumn',
            width: 30,
            items: [{
                iconCls: 'sprite-magnifier',
                action: 'openLog',
                tooltip: 'Open log',
                handler: function(view, rowIndex, colIndex, item, event, record) {
                    me.fireEvent('openLog', record);
                }
            }]
        }];

        return columns;
    }
});
//{/block}
