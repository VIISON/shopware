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

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware Backend - User list
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/view/user/list"}
Ext.define('Shopware.apps.UserManager.view.user.List', {
	extend: 'Ext.grid.Panel',
	alias: 'widget.usermanager-user-list',
    height: '100%',
    region: 'center',
	autoScroll: true,
	// Event listeners
	listeners: {
		scope: this,

		// Sample event listener which will be fired when the user has edited a grid row
		edit: function(editor) {
			editor.grid.setLoading(true);
			window.setTimeout(function() {
				editor.store.sync();
				editor.grid.setLoading(false);
			}, 500);
		}
	},

    /**
     * Initialize the view components
     *
     * @return void
     */
	initComponent: function() {
		var me = this;
        me.registerEvents();
        me.store = me.userStore;

        this.selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange: function(sm, selections) {
                    var owner = this.view.ownerCt,
                        btn = owner.down('button[action=deleteUsers]');

                    btn.setDisabled(selections.length == 0);
                }
            }
        });

		var buttons = [];

        /* {if {acl_is_allowed privilege=delete}} */
		buttons.push({
			iconCls: 'sprite-minus-circle',
			action: 'deleteUser',
			cls: 'delete',
			tooltip: '{s name="list_users/deletetooltip"}Delete this user{/s}',
			handler:function (view, rowIndex, colIndex, item) {
				me.fireEvent('deleteUser', view, rowIndex, colIndex, item);
			}
		});
        /* {/if} */

        /* {if {acl_is_allowed privilege=update}} */
		buttons.push({
			iconCls: 'sprite-user--pencil',
			cls: 'editBtn',
			action: 'editUser',
			tooltip: '{s name="list_users/edittooltip"}Edit this user{/s}',
			handler:function (view, rowIndex, colIndex, item) {
				me.fireEvent('editUser', view, rowIndex, colIndex, item);
		}});
        /* {/if} */

        me.dockedItems = this.createDockedToolBar();

		// Define the columns and renderers
		this.columns = [
		{
			header: '{s name="list_users/username"}Username{/s}',
			dataIndex: 'username',
			width: 100,
			renderer: this.nameColumn
		}, {
			header: '{s name="list_users/realname"}Name{/s}',
			dataIndex: 'name',
			flex: 1
		},
        {
   			header: '{s name="list_users/lastlogin"}Last login{/s}',
   			dataIndex: 'lastLogin',
            xtype: 'datecolumn',
   			flex: 1
   		}
        , {
			header: '{s name="list_users/email"}eMail Address{/s}',
			dataIndex: 'email',
			flex: 1,
			renderer: this.emailColumn
		}, {
			xtype: 'actioncolumn',
            header: '{s name="list_users/options"}Options{/s}',
			flex: 1,
			items: buttons
		}];

		var tbButtons = [];

		/* {if {acl_is_allowed privilege=create}} */
		tbButtons.push({
			iconCls: 'sprite-plus-circle',
			text: '{s name="list_users/adduser"}Add user{/s}',
			action: 'addUser'
		});
		/* {/if} */

		/* {if {acl_is_allowed privilege=delete}} */
		tbButtons.push({
			iconCls: 'sprite-minus-circle',
			text: '{s name="list_users/deleteusers"}Delete selected users{/s}',
			disabled: true,
			action: 'deleteUsers'
		});
		/* {/if} */

		tbButtons.push('->',
		{
			xtype:'textfield',
			name:'searchfield',
			action:'searchUser',
			width:170,
			cls: 'searchfield',
			enableKeyEvents:true,
			checkChangeBuffer: 500,
			emptyText:'{s name=list_users/field/search}Search...{/s}'
		});

		// Row grouping
		this.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
		    groupHeaderTpl: '{literal}Gruppe: {name} ({rows.length}){/literal}'
		});
		this.features = [ this.groupingFeature ];

		// Toolbar
		this.toolbar = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'top',
            ui: 'shopware-ui',
		    items: tbButtons
		});

		me.dockedItems = Ext.clone(this.dockedItems);
        me.dockedItems.push(this.toolbar);

		this.callParent();
	},
    registerEvents:function () {
            this.addEvents(

                    /**
                     * Event will be fired when the user clicks the delete icon in the
                     * action column
                     *
                     * @event deleteColumn
                     * @param [object] View - Associated Ext.view.Table
                     * @param [integer] rowIndex - Row index
                     * @param [integer] colIndex - Column index
                     * @param [object] item - Associated HTML DOM node
                     */
                    'deleteUser',

                    /**
                     * Event will be fired when the user clicks the delete icon in the
                     * action column
                     *
                     * @event deleteColumn
                     * @param [object] View - Associated Ext.view.Table
                     * @param [integer] rowIndex - Row index
                     * @param [integer] colIndex - Column index
                     * @param [object] item - Associated HTML DOM node
                     */
                    'editUser'
            );

            return true;
    },
    /**
     * Create paging toolbar for grid view
     * @return [Array]
     */
    createDockedToolBar: function(){
      return  [{
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: this.userStore
      }];
    },
	/**
	 * Formats the name column
     *
	 * @param [string] value
     * @return [string]
	 */
	nameColumn: function(value,x,row) {
        if (!row.data.active){
            return Ext.String.format('{literal}<strong style="font-weight: 700;color:#F00;text-decoration: line-through">{0}</strong>{/literal}', value);
        }
		return Ext.String.format('{literal}<strong style="font-weight: 700">{0}</strong>{/literal}', value);
	},

	/**
	 * Formats the email column
     *
	 * @param [string] value
     * @return [string]
	 */
	emailColumn: function(value) {
		return Ext.String.format('{literal}<a href="mailto:{0}">{1}</a>{/literal}', value, value);
	},

	/**
	 * Formats the admin (group) column
     *
	 * @param [string] value
     * @return [string]
	 */
	adminColumn: function(value) {
		return value;
	},

	/**
	 * Performs a filtering in the grid
     *
	 * @param [object]
     * @return [boolean]
	 */
	onSearchField: function(field) {

		store = this.store;

		if(field.value.length == 0) {
			store.clearFilter();
			return false;
		}

		store.filter('username', field.getValue());
		return true;
	}
});
//{/block}