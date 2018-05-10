<?php
// vim: expandtab sw=4 ts=4 sts=4:
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
error_reporting(E_ALL);
/**
 * Set of functions used to build SQL dumps of tables
 */

/**
 * Returns $table's field types
 *
 * This function exists because mysql_field_type() returns 'blob'
 * even for 'text' fields.
 */
function SA_fieldTypes($db, $table) 
{
    mysql_select_db($db);
    $table_def = mysql_query('SHOW FIELDS FROM ' . SA_backquote($db) . '.' . SA_backquote($table));
    while($row = mysql_fetch_array($table_def))
        $types[SA_backquote($row['Field'])] = ereg_replace('\\(.*', '', $row['Type']);
    return $types;
}

/**
 * Outputs comment
 */
function SA_exportComment($text) 
{
    echo('# ' . $text . $GLOBALS['crlf']);
}


/**
 * Outputs database header
 *
 */
function SA_exportDBHeader($db)
{
    global $crlf;
    $head = '# SQL Dump for database: ' . $db . $crlf
          . '# ' . $crlf;
    echo($head);
}

/**
 * Outputs database footer
 *
 */
function SA_exportDBFooter($db) {
    if (isset($GLOBALS['sql_constraints']))
      echo($GLOBALS['sql_constraints']);
    return TRUE;
}

/**
 * Returns $table's CREATE definition
 *
 * @param   string   the database name
 * @param   string   the table name
 * @param   string   the end of line sequence
 *
 * @return  string   resulting schema
 *
 * @global  boolean  whether to add 'drop' statements or not
 * @global  boolean  whether to use backquotes to allow the use of special
 *                   characters in database, table and fields names or not
 *
 * @access  public
 */
function SA_getTableDef($db, $table, $crlf)
{

    $schema_create = '';
    $auto_increment = '';
    $new_crlf = $crlf;


    $result = mysql_query('SHOW TABLE STATUS FROM ' . SA_backquote($db) . ' LIKE \'' . SA_sqlAddslashes($table) . '\'');
    if ($result != FALSE)
    {
        if (mysql_num_rows($result) > 0)
        {
            $tmpres        = mysql_fetch_array($result);
            if (!empty($tmpres['Auto_increment'])) 
                $auto_increment .= ' AUTO_INCREMENT=' . $tmpres['Auto_increment'] . ' ';

        mysql_free_result($result);
        }
    }

    $schema_create .= $new_crlf;

    $schema_create .= 'DROP TABLE IF EXISTS ' . SA_backquote($table) . ';' . $crlf;

    // Steve Alberty's patch for complete table dump,
    // Whether to quote table and fields names or not
    mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
    $result = mysql_query('SHOW CREATE TABLE ' . SA_backquote($db) . '.' . SA_backquote($table));
    if ($result != FALSE && mysql_num_rows($result) > 0)
    {
        $tmpres        = mysql_fetch_array($result);
        // Fix for case problems with winwin, thanks to
        // Pawe Szczepañski <pauluz at users.sourceforge.net>
        $pos           = strpos($tmpres[1], ' (');

        // Fix a problem with older versions of mysql
        // Find the first opening parenthesys, i.e. that after the name
        // of the table
        $pos2          = strpos($tmpres[1], '(');
        // Old mysql did not insert a space after table name
        // in query "show create table ..."!
        if ($pos2 != $pos + 1)
        {
            // This is the real position of the first character after
            // the name of the table
            $pos = $pos2;
            // Old mysql did not even put newlines and indentation...
            $tmpres[1] = str_replace(",", ",\n     ", $tmpres[1]);
        }

        $tmpres[1]     = substr($tmpres[1], 0, 13)
                       . (SA_backquote($tmpres[0]))
                       . substr($tmpres[1], $pos);
        $tmpres[1]     = str_replace("\n", $crlf, $tmpres[1]);
        $schema_create .= $tmpres[1];
    }

    $schema_create .= $auto_increment;


    mysql_free_result($result);
    return $schema_create;
} // end of the 'SA_getTableDef()' function


/**
 * Outputs table's structure
 *
 * @param   string   the database name
 * @param   string   the table name
 * @param   string   the end of line sequence
 *
 * @access  public
 */
function SA_exportStructure($db, $table, $crlf)
{
    $formatted_table_name = SA_backquote($table);
    $dump = $crlf
          . '# --------------------------------------------------------' . $crlf
          .  $crlf . '#' . $crlf
          .  '#  Structure for table' . $formatted_table_name . $crlf
          .  '#' . $crlf
          .  SA_getTableDef($db, $table, $crlf) . ';' . $crlf;


    echo($dump);
}

/**
 *
 * @param   string      the database name
 * @param   string      the table name
 * @param   string      the end of line sequence
 * @param   string      SQL query for obtaining data
 *
 * @return  bool        Whether it suceeded
 *
 * @global  boolean  whether to use backquotes to allow the use of special
 *                   characters in database, table and fields names or not
 * @global  integer  the number of records
 * @global  integer  the current record position
 *
 * @access  public
 *
 */
function SA_exportData($db, $table, $crlf, $sql_query)
{
    global $rows_cnt;
    global $current_row;

    $formatted_table_name = SA_backquote($table);
    $head = $crlf
          . '#' . $crlf
          . '# Data for table' . $formatted_table_name . $crlf
          . '#' . $crlf .$crlf;

    echo($head);

    $buffer = '';

    $result = mysql_query($sql_query) or SA_mysqlDie('', $sql_query, '', '');
    if ($result != FALSE)
    {
        $fields_cnt = mysql_num_fields($result);
        $rows_cnt   = mysql_num_rows($result);

        // get the real types of the table's fields (in an array)
        // the key of the array is the backquoted field name
        $field_types = SA_fieldTypes($db,$table);

        // analyze the query to get the true column names, not the aliases
        // (this fixes an undefined index, also if Complete inserts
        //  are used, we did not get the true column name in case of aliases)

        // Checks whether the field is an integer or not
        for ($j = 0; $j < $fields_cnt; $j++)
        {
            $field_set[$j] = SA_backquote(mysql_field_name($result, $j));

            $type = $field_types[$field_set[$j]];

            if ($type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'int' ||
                $type == 'bigint'  || (SA_MYSQL_INT_VERSION < 40100 && $type == 'timestamp'))
                $field_num[$j] = TRUE;
            else
                $field_num[$j] = FALSE;

            // blob
            if ($type == 'blob' || $type == 'mediumblob' || $type == 'longblob' || $type == 'tinyblob')
                $field_blob[$j] = TRUE;
            else
                $field_blob[$j] = FALSE;
        } // end for

        $sql_command    = 'INSERT';

        // Sets the scheme
        $schema_insert = $sql_command .' INTO ' . SA_backquote($table)
                           . ' VALUES (';

        $search       = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
        $replace      = array('\0', '\n', '\r', '\Z');
        $current_row  = 0;

        while ($row = mysql_fetch_array($result,MYSQL_NUM))
        {
            $current_row++;
            for ($j = 0; $j < $fields_cnt; $j++)
            {
                if (!isset($row[$j]))
                    $values[]     = 'NULL';
                else if ($row[$j] == '0' || $row[$j] != '')
                {
                    // a number
                    if ($field_num[$j]) {
                        $values[] = $row[$j];
                    // a not empty blob
                    } else if ($field_blob[$j] && !empty($row[$j])) {
                        $values[] = '0x' . bin2hex($row[$j]);
                    // a string
                    } else {
                        $values[] = "'" . str_replace($search, $replace, SA_sqlAddslashes($row[$j])) . "'";
                    }
                } 
                else
                    $values[]     = "''";
            } // end for


            $insert_line      = $schema_insert . implode(', ', $values) . ')';
            unset($values);

            echo($insert_line . ';' . $crlf);

        } // end while
    } // end if ($result != FALSE)
    mysql_free_result($result);

} // end of the 'SA_exportData()' function
?>
