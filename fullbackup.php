<?php
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
session_start();

global $login_qualify;
$login_qualify = 'A';
require_once('./schooladminfunctions.php');
include ("schooladminconstants.php");

require('./export_sql.php');


/**
 * Increase time limit for script execution and initializes some variables
 */
@set_time_limit(300);



$crlf = SA_whichCrlf();

$output_charset_conversion = FALSE;

// Use on fly compression?
$onfly_compression = FALSE;

// Generate filename and mime type if needed
    $SA_uri_parts = parse_url($livesite);
    $filename = 'schooladmin';

    // convert filename to iso-8859-1, it is safer

    // Generate basic dump extension
    $filename  .= '.sql';
    $mime_type = (SA_USR_BROWSER_AGENT == 'IE' || SA_USR_BROWSER_AGENT == 'OPERA')
                   ? 'application/octetstream'
                   : 'application/octet-stream';
    


/**
 * Send headers depending on whether the user chose to download a dump file
 * or not
 */
        // Download
        header('Content-Type: ' . $mime_type);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        // lem9 & loic1: IE need specific headers
        if (SA_USR_BROWSER_AGENT == 'IE') {
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
        }


/**
 * Builds the dump
 */
// Gets the number of tables if a dump of a database has been required
/**
 * Gets the databases list - if it has not been built yet
 */

SA_exportDBHeader($databasename);
$tables     = mysql_list_tables($databasename);
$num_tables = ($tables) ? @mysql_numrows($tables) : 0;
$i = 0;
while ($i < $num_tables)
{
    $table = mysql_tablename($tables, $i);
    $local_query  = 'SELECT * FROM ' . SA_backquote($databasename) . '.' . SA_backquote($table);
    SA_exportStructure($databasename, $table, $crlf);
    SA_exportData($databasename, $table, $crlf, $local_query);
    $i++;
}
SA_exportDBFooter($databasename);
echo("# END of backup");

?>
