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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Log list backend module
 *
 * Main controller of the log module.
 * It only creates the main-window.
 */

//{block name="backend/log/controller/log"}
Ext.define('Shopware.apps.Log.controller.Main', {
    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',

    requires: [ 'Shopware.apps.Log.controller.log.Backend' ],

    /**
     * Init-function to create the main-window and assign the paymentStore
     */
    init: function() {
        var me = this;
		me.subApplication.backendLogStore = me.subApplication.getStore('logs.Backend');
		me.subApplication.backendLogStore.load();
        me.subApplication.coreLogStore = me.subApplication.getStore('logs.Core');
        me.subApplication.coreLogStore.load();
        me.subApplication.pluginLogStore = me.subApplication.getStore('logs.Plugin');
        me.subApplication.pluginLogStore.load();

        me.mainWindow = me.getView('main.Window').create({
            backendLogStore: me.subApplication.backendLogStore,
            coreLogStore: me.subApplication.coreLogStore,
            pluginLogStore: me.subApplication.pluginLogStore
        });

        this.callParent(arguments);
    }
});
//{/block}
