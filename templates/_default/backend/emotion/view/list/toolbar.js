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
 *
 * @category   Shopware
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/list/toolbar}

/**
 * Shopware UI - Emotion Toolbar
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/list/toolbar"}
Ext.define('Shopware.apps.Emotion.view.list.Toolbar', {
	extend: 'Ext.toolbar.Toolbar',
    ui: 'shopware-ui',
    alias: 'widget.emotion-list-toolbar',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            emptyText: '{s name=toolbar/search_emotion}Search emotion...{/s}',
            cls: 'searchfield',
            width: 200,
            enableKeyEvents:true,
            checkChangeBuffer:500,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchEmotions', value);
                }
            }
        });

        me.items = [{
            text: '{s name=toolbar/add_emotion}Add emotion{/s}',
            iconCls: 'sprite-plus-circle',
            action: 'emotion-list-toolbar-add'
        }, {
            text: '{s name=toolbar/delete_selected_emotion}Delete selected emotions{/s}',
            iconCls: 'sprite-minus-circle',
            action: 'emotion-list-toolbar-delete',
            disabled: true,
            handler: function() {
                me.fireEvent('removeEmotions');
            }
        }, '->', me.searchField, {
            xtype: 'tbspacer',
            width: 6
        }];
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
    	this.addEvents(
    		/**
    		 * Event will be fired when the user insert a value into the search field
    		 *
    		 * @event
    		 * @param [string] The inserted value
    		 */
    		'searchEmotions',
            /**
             * Event will be fired when the user clicks the "remove all selected" button
             *
             * @event
             */
             'removeEmotions'
    	);
    }

});
//{/block}