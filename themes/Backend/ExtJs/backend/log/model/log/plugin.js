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
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware - Plugin log model
 */
//{block name="backend/log/model/log/plugin"}
Ext.define('Shopware.apps.Log.model.log.Plugin', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/log/model/log/plugin/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'timestamp', type: 'date' },
        { name: 'level', type: 'string' },
        { name: 'message', type: 'string' },
        { name: 'context', type: 'string' },
        { name: 'plugin', type: 'string' },
        // Exception
        { name: 'code', type: 'int' },
        { name: 'file', type: 'string' },
        { name: 'line', type: 'int' },
        { name: 'trace', type: 'string' }
    ],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller=log action=getPluginLogs}',
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
