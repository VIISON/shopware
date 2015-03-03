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
 * @package    Base
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Global Stores and Models
 *
 * The article detail model represents a data row of the s_articles_details or the
 * Shopware\Models\Article\Detail doctrine model.
 */
//{block name="backend/base/model/article_detail"}
Ext.define('Shopware.apps.Base.model.ArticleDetail', {

	/**
	 * Defines an alternate name for this class.
	 */
	alternateClassName:'Shopware.model.ArticleDetail',

	/**
	 * Extends the standard Ext Model
	 * @string
	 */
	extend:'Shopware.data.Model',

	/**
	 * unique id
	 * @int
	 */
	idProperty:'id',

	/**
	 * The fields used for this model
	 * @array
	 */
	fields:[
		//{block name="backend/base/model/article_detail/fields"}{/block}
		{ name:'id', type:'int' },
		{ name:'number', type:'string' },
		{ name:'name', type:'string' },
		{ name:'additionalText', type:'string' },
		{ name:'description', type:'string' },
		{ name:'supplierName', type:'string' },
		{ name:'supplierId', type:'int' },
		{ name:'active', type:'int' },
		{ name:'detailId', type:'int' },
		{ name:'changeTime', type:'date' },
		{ name:'inStock', type:'int' }
	]
});
//{/block}

