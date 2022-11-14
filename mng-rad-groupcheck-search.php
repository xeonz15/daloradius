<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
    
    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";


    //setting values for the order by and order type variables
    isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
    isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";


    isset($_REQUEST['groupname']) ? $groupname = $_REQUEST['groupname'] : $groupname = "%";

    $search_groupname = $groupname; //feed the sidebar variables
    $groupname = str_replace('*', '%', $groupname);

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradgroupchecksearch.php');
    $help = t('helpPage','mngradgroupchecksearch');
    
    print_html_prologue($title, $langCode);

    include("menu-mng-rad-groups.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    
    include 'library/opendb.php';
    include 'include/management/pages_numbering.php';        // must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
    
    //orig: used as method to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT GroupName, Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].
            " WHERE GroupName LIKE '".$dbSocket->escapeSimple($groupname)."%' GROUP BY GroupName";
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    $sql = "SELECT GroupName, Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].
            " WHERE GroupName LIKE '".$dbSocket->escapeSimple($groupname)."%' ".
            " ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
    $res = $dbSocket->query($sql);
    $logDebugSQL = "";
    $logDebugSQL .= $sql . "\n";
    
    /* START - Related to pages_numbering.php */
    $maxPage = ceil($numrows/$rowsPerPage);
    /* END */

    echo "<form name='listgroupcheck' method='post' action='mng-rad-groupcheck-del.php'>";

    echo "<table border='0' class='table1'>\n";
    echo "
        <thead>
            <tr>
            <th colspan='10' align='left'>

            Select:
            <a class=\"table\" href=\"javascript:SetChecked(1,'group[]','listgroupcheck')\">All</a>
            <a class=\"table\" href=\"javascript:SetChecked(0,'group[]','listgroupcheck')\">None</a>
            <br/>
            <input class='button' type='button' value='Delete' onClick='javascript:removeCheckbox(\"listgroupcheck\",\"mng-rad-groupcheck-del.php\")' />
            <br/><br/>
    ";

    if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
        setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

    echo "    </th></tr>
            </thead>
    ";

    if ($orderType == "asc") {
        $orderTypeNextPage = "desc";
    } else  if ($orderType == "desc") {
        $orderTypeNextPage = "asc";
    }

    echo "<thread> <tr>
        <th scope='col'>
        <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=groupname&orderType=$orderTypeNextPage\">
        ".t('all','Groupname')."</a>
        </th>

        <th scope='col'>
        <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=$orderTypeNextPage\">
        ".t('all','Attribute')."</a>
        </th>

        <th scope='col'>
        <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=op&orderType=$orderTypeNextPage\">
        ".t('all','Operator')."</a>
        </th>

        <th scope='col'>
        <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=value&orderType=$orderTypeNextPage\">
        ".t('all','Value')."</a>
        </th>

    </tr> </thread>";
    while($row = $res->fetchRow()) {
        echo "<tr>
                                <td> <input type='checkbox' name='group[]' value='$row[0]||$row[1]||$row[3]'> 
                                        <a class='tablenovisit' href='mng-rad-groupcheck-edit.php?groupname=$row[0]&value=$row[3]'> $row[0] </td>
                                <td> $row[1] </td>
                                <td> $row[2] </td>                                              
                                <td> $row[3] </td>      
        </tr>";
    }

    echo "
        <tfoot>
            <tr>
            <th colspan='10' align='left'>
    ";
    setupLinks($pageNum, $maxPage, $orderBy, $orderType);
    echo "
            </th>
            </tr>
        </tfoot>
    ";

    echo "</table></form>";

    include 'library/closedb.php';

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
