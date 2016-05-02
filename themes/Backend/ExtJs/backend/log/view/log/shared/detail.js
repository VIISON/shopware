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
 * @author shopware AG
 */

//{namespace name=backend/log/shared}

//{block name="backend/log/view/log/shared/detail"}
Ext.define('Shopware.apps.Log.view.log.shared.Detail', {
    extend: 'Enlight.app.Window',
    alias: 'widget.log-shared-detail-window',
    cls: Ext.baseCSSPrefix + 'log-shared-detail',
    border: false,
    autoShow: true,
    layout: 'fit',
    width: '90%',
    height: '90%',
    record: null,

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.title + ' - ' + Ext.Date.format(me.record.get('timestamp'), 'Y-m-d H:i:s');
        me.items = [{
            xtype: 'panel',
            layout: 'anchor',
            border: false,
            bodyPadding: 10,
            defaults: {
                anchor: '100% 50%',
                layout: 'anchor'
            },
            items: me.createPanelItems()
        }];
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                '->',
                {
                    text: '{s name=detail/toolbar/button/close}Close{/s}',
                    cls: 'secondary',
                    scope: me,
                    handler: me.destroy
                }
            ]
        }];

        me.callParent(arguments);
    },

    /**
     * @return Ext.Component[]
     */
    createPanelItems: function () {
        var me = this;

        var items = [
            Ext.create('Ext.form.FieldSet', {
                title: '{s name=detail/field_set/log}Log{/s}',
                defaults: {
                    anchor: '100%',
                },
                items: me.createMainFieldSetItems()
            })
        ];

        if (me.record.get('file') && me.record.get('file').length > 0) {
            // Add fields for exception info
            items.push(Ext.create('Ext.form.FieldSet', {
                title: '{s name=detail/field_set/exception}Exception{/s}',
                defaults: {
                    anchor: '100%',
                },
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: '{s name=model/field/code}Error code{/s}',
                    value: me.record.get('code')
                }, {
                    xtype: 'displayfield',
                    fieldLabel: '{s name=model/field/file}File{/s}',
                    value: me.record.get('file')
                }, {
                    xtype: 'displayfield',
                    fieldLabel: '{s name=model/field/line}Line{/s}',
                    value: me.record.get('line')
                }, {
                    xtype: 'textareafield',
                    fieldLabel: '{s name=model/field/trace}Trace{/s}',
                    value: me.record.get('trace'),
                    anchor: '100% -75',
                    selectOnFocus: true,
                    readOnly: true
                }]
            }));
        }

        return items;
    },

    /**
     * @return Ext.Component[]
     */
    createMainFieldSetItems: function() {
        var me = this;

        return [{
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/timestamp}Date{/s}',
            value: Ext.Date.format(me.record.get('timestamp'), 'Y-m-d H:i:s')
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/level}Level{/s}',
            value: me.record.get('level')
        }, {
            xtype: 'textareafield',
            fieldLabel: '{s name=model/field/message}Message{/s}',
            value: me.record.get('message'),
            selectOnFocus: true,
            readOnly: true
        }, {
            xtype: 'textareafield',
            fieldLabel: '{s name=model/field/context}Context{/s}',
            value: me.record.get('context'),
            anchor: '100% -115',
            selectOnFocus: true,
            readOnly: true
        }];
    }
});
//{/block}
