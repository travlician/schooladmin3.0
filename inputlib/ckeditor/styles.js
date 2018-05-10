/**
 * Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

// This file contains style definitions that can be used by CKEditor plugins.
//
// The most common use for it is the "stylescombo" plugin, which shows a combo
// in the editor toolbar, containing all styles. Other plugins instead, like
// the div plugin, use a subset of the styles on their feature.
//
// If you don't have plugins that depend on this file, you can simply ignore it.
// Otherwise it is strongly recommended to customize this file to match your
// website requirements and design properly.

CKEDITOR.stylesSet.add( 'default', [
	/* Block Styles */


	{ name: 'Titel cursief',		element: 'h2', styles: { 'font-style': 'italic' } },
	{ name: 'Subtitel',			element: 'h3', styles: { 'color': '#aaa', 'font-style': 'italic' } },
	{
		name: 'Speciald Container',
		element: 'div',
		styles: {
			padding: '5px 10px',
			background: '#eee',
			border: '1px solid #ccc'
		}
	},

	{ name: 'Wit',	element: 'span', styles: { 'Color' : 'White' } },
	{ name: 'Groen',	element: 'span', styles: { 'Color' : 'Lime' } },
	{ name: 'Rood',	element: 'span', styles: { 'Color' : 'Red' } },
	{ name: 'Blauw',	element: 'span', styles: { 'Color' : 'Blue' } },
	{ name: 'Marker: Geel',	element: 'span', styles: { 'background-color': 'Yellow' , 'color' : 'Black'} },
	{ name: 'Marker: Groen',	element: 'span', styles: { 'background-color': 'Lime', 'Color' : 'Black' } },
	{ name: 'Marker: Rood',	element: 'span', styles: { 'background-color': 'Red', 'Color' : 'Black' } },
	{ name: 'Marker: Blauw',	element: 'span', styles: { 'background-color': 'Blue', 'Color' : 'Black' } },

	{ name: 'Groot',			element: 'big' },
	{ name: 'Klein',			element: 'small' },
	{ name: 'Typemachine',		element: 'tt' },


	/* Object Styles */

	{
		name: 'Styled image (left)',
		element: 'img',
		attributes: { 'class': 'left' }
	},

	{
		name: 'Styled image (right)',
		element: 'img',
		attributes: { 'class': 'right' }
	},

	{
		name: 'Compact table',
		element: 'table',
		attributes: {
			cellpadding: '5',
			cellspacing: '0',
			border: '1',
			bordercolor: '#ccc'
		},
		styles: {
			'border-collapse': 'collapse'
		}
	},

	{ name: 'Borderless Table',		element: 'table',	styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } },
	{ name: 'Square Bulleted List',	element: 'ul',		styles: { 'list-style-type': 'square' } }
]);

