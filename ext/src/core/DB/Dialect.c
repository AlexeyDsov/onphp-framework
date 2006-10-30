/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"
#include "onphp_core.h"
#include "onphp_util.h"

#include "ext/standard/php_string.h"
#include "zend_globals.h"
#include "zend_exceptions.h"
#include "zend_interfaces.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"
#include "core/Exceptions.h"

PHPAPI zend_class_entry *onphp_ce_Dialect;

ONPHP_METHOD(Dialect, quoteValue)
{
	zval *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	// don't know, how to replicate original voodoo
	if (Z_TYPE_P(value) == IS_LONG) {
		RETURN_LONG(Z_LVAL_P(value));
	} else {
		smart_str string = {0};
		char *slashed;
		int length = 0;
		
		if (Z_TYPE_P(value) == IS_STRING) {
			slashed = estrndup(Z_STRVAL_P(value), Z_STRLEN_P(value));
		} else {
			zval *copy;
			
			MAKE_STD_ZVAL(copy);
			ZVAL_ZVAL(copy, value, 1, 0);
			
			convert_to_string(copy);
			
			slashed = estrndup(Z_STRVAL_P(copy), Z_STRLEN_P(copy));
		}
		
		length = strlen(slashed);
		
		slashed =
			php_addslashes(
				slashed,
				length,
				&length,
				0 TSRMLS_CC
			);
		
		smart_str_appends(&string, "'");
		smart_str_appends(&string, slashed);
		smart_str_appends(&string, "'");
		smart_str_0(&string);
		
		efree(slashed);
		
		RETURN_STRINGL(string.c, string.len, 0);
	}
}

ONPHP_METHOD(Dialect, quoteField)
{
	zval *field;
	smart_str string = {0};

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &field) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	smart_str_appends(&string, "\"");
	onphp_append_zval_to_smart_string(&string, field);
	smart_str_appends(&string, "\"");
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, quoteTable)
{
	zval *table;
	smart_str string = {0};

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &table) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	smart_str_appends(&string, "\"");
	onphp_append_zval_to_smart_string(&string, table);
	smart_str_appends(&string, "\"");
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, toCasted)
{
	zval *field, *type;
	smart_str string = {0};
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zz", &field, &type) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	smart_str_appends(&string, "CAST (");
	onphp_append_zval_to_smart_string(&string, field);
	smart_str_appends(&string, " AS ");
	onphp_append_zval_to_smart_string(&string, type);
	smart_str_appends(&string, ")");
	smart_str_0(&string);

	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, timeZone)
{
	unsigned char argc = ZEND_NUM_ARGS();

	if (argc) {
		zend_bool exist = 0;
		
		zend_parse_parameters(argc TSRMLS_CC, "b", &exist);
	
		if (exist) {
			RETURN_STRING(" WITH TIME ZONE", 1);
		}
	}
	
	RETURN_STRING(" WITHOUT TIME ZONE", 1);
}

ONPHP_METHOD(Dialect, dropTableMode)
{
	unsigned char argc = ZEND_NUM_ARGS();
	
	if (argc) {
		zend_bool cascade = 0;
		
		zend_parse_parameters(argc TSRMLS_CC, "b", &cascade);
	
		if (cascade) {
			RETURN_STRING(" CASCADE", 1);
		}
	}
	
	RETURN_STRING(" RESTRICT", 1);
}

ONPHP_METHOD(Dialect, fieldToString)
{
	zval *field, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &field) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	if (
		Z_TYPE_P(field) == IS_OBJECT
		&& instanceof_function(Z_OBJCE_P(field), onphp_ce_DialectString TSRMLS_CC)
	) {
		zend_call_method_with_1_params(
			&field,
			Z_OBJCE_P(field),
			NULL,
			"todialectstring",
			&out,
			getThis()
		);
	} else {
		zend_call_method_with_1_params(
			&getThis(),
			Z_OBJCE_P(getThis()),
			NULL,
			"quotefield",
			&out,
			field
		);
	}
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Dialect, valueToString)
{
	zval *value, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	if (
		Z_TYPE_P(value) == IS_OBJECT
		&& instanceof_function(Z_OBJCE_P(value), onphp_ce_DBValue TSRMLS_CC)
	) {
		zend_call_method_with_1_params(
			&getThis(),
			Z_OBJCE_P(getThis()),
			NULL,
			"quotevalue",
			&out,
			value
		);
		
		if (EG(exception)) {
			return;
		}
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Dialect, fullTextSearch)
{
	zend_throw_exception_ex(
		onphp_ce_UnimplementedFeatureException,
		0 TSRMLS_CC,
		"Implement me first"
	);
}

ONPHP_METHOD(Dialect, fullTextRank)
{
	zend_throw_exception_ex(
		onphp_ce_UnimplementedFeatureException,
		0 TSRMLS_CC,
		"Implement me first"
	);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_THREE;
static ONPHP_ARGINFO_DBCOLUMN;

zend_function_entry onphp_funcs_Dialect[] = {
	ONPHP_ABSTRACT_ME(Dialect, preAutoincrement, arginfo_dbcolumn, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Dialect, postAutoincrement, arginfo_dbcolumn, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Dialect, hasTruncate, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Dialect, hasMultipleTruncate, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, quoteValue, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteField, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteTable, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, toCasted, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, timeZone, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, dropTableMode, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, fieldToString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, valueToString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, fullTextSearch, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, fullTextRank, arginfo_three, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
