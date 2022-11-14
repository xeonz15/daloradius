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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/populate_selectbox.php') !== false) {
    header("Location: ../../index.php");
    exit;
}



/*
 * populate_payment_type_id
 * creates a select box and populates it with possible payment type_id options
 *
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_payment_type_id($defaultOption = "Select Payment Type", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "") {

        echo "<select onChange=\"javascript:setStringText(this.id,'populate_payment_type_id')\" id='populate_payment_type_id' $mode
                        name='$elementName' class='$cssClass' />
                        <option value='$defaultOptionValue'>$defaultOption</option>
                        <option value=''></option>";

        include 'library/opendb.php';

        $sql = "(SELECT id, value FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].")";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                echo "
                        <option value='".$row['id']."'>".$row['value']."</option>
                        ";

        }

        include 'library/closedb.php';

        echo "</select>";
}



/*
 * populate_customer_id
 * creates a select box and populates it with customer information from userinfo/userbillinfo tables
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_customer_id($defaultOption = "Select Customer", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'customer_id')\" id='customer_id' $mode
            name='$elementName' class='$cssClass' />
            <option value='$defaultOptionValue'>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        $sql = "(SELECT id, value FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'].")";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                echo "  
                        <option value='".$row['id']."'>".$row['value']."</option>
                        ";

        }

        include 'library/closedb.php';

    echo "</select>";
}




/*
 * populate_invoice_status_id
 * creates a select box and populates it with possible invoice status_id options
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_invoice_status_id($defaultOption = "Select Status", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'invoice_status_id')\" id='invoice_status_id' $mode
            name='$elementName' class='$cssClass' />
            <option value='$defaultOptionValue'>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        $sql = "(SELECT id, value FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'].")";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                echo "  
                        <option value='".$row['id']."'>".$row['value']."</option>
                        ";

        }

        include 'library/closedb.php';

    echo "</select>";
}






/*
 * populate_invoice_type_id
 * creates a select box and populates it with possible invoice type_id options
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_invoice_type_id($defaultOption = "Select Status", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'populate_invoice_type_id')\" id='populate_invoice_type_id' $mode
            name='$elementName' class='$cssClass' />
            <option value='$defaultOptionValue'>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        $sql = "(SELECT id, value FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'].")";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                echo "  
                        <option value='".$row['id']."'>".$row['value']."</option>
                        ";

        }

        include 'library/closedb.php';

    echo "</select>";
}





/*
 * populate_hotspots
 * creates a select box and populates it with all hotspots
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_hotspots($defaultOption = "Select Hotspot", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'hotspot')\" id='hotspot' $mode
            name='$elementName' class='$cssClass' />
            <option value='$defaultOptionValue'>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "(SELECT distinct(id), name FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].")";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                echo "  
                        <option value='".$row['id']."'>".$row['name']."</option>
                        ";

        }

        include 'library/closedb.php';

    echo "</select>";
}

function get_active_plans() {
    global $configValues;

    include('library/opendb.php');

    $sql = sprintf("SELECT DISTINCT(planName) AS planName, id
                      FROM %s
                     WHERE planActive='yes'
                     ORDER BY planName ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']);
    $res = $dbSocket->query($sql);

    $result = array();
    
    while ($row = $res->fetchRow()) {
        list($planName, $id) = $row;
        $result[$id] = $planName;
    }

    include('library/closedb.php');
    
    return $result;
}

/*
 * populate_plans()
 *
 */
function populate_plans($defaultOption = "Select Plan", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "", $valueIsId = false) {

    echo "<select $mode name='$elementName' class='$cssClass' tabindex=105 />".
            "<option value='$defaultOptionValue'>$defaultOption</option>".
            "<option value=''></option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "SELECT distinct(planName), id FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." WHERE planActive = 'yes' ORDER BY planName ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
            

            if ($valueIsId === true)
                $value = $row[1];
            else
                $value = $row[0];
                            
            echo "<option value='$value'> $row[0] </option> ";

        }

        echo "</select>";

        include 'library/closedb.php';
}


function get_users() {
    global $configValues;

    include('library/opendb.php');

    $sql = sprintf("SELECT DISTINCT(username) FROM %s ORDER BY username ASC", $configValues['CONFIG_DB_TBL_RADCHECK']);

    $res = $dbSocket->query($sql);

    $result = array();
    
    while ($row = $res->fetchRow()) {
        $username = $row[0];
        $result[$username] = $username;
    }

    include('library/closedb.php');
    
    return $result;
}


function get_groups() {
    global $configValues;

    include('library/opendb.php');

    $tables = array(
                     $configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                     $configValues['CONFIG_DB_TBL_RADGROUPREPLY'],
                     $configValues['CONFIG_DB_TBL_RADUSERGROUP']
                   );

    $sql_pieces = array();
    
    foreach ($tables as $table) {
        $sql_pieces[] = sprintf("SELECT DISTINCT(groupname) FROM %s", $table);
    }

    $sql = implode(" UNION ", $sql_pieces);
    $res = $dbSocket->query($sql);

    $result = array();
    
    while ($row = $res->fetchRow()) {
        $groupname = $row[0];
        
        if (!array_key_exists($groupname, $result)) {
            $result[$groupname] = $groupname;
        }
    }

    include('library/closedb.php');
    
    return $result;
}

/*
 * populate_groups
 * creates a select box and populates it with all groups from radgroupreply and
 * radgroupcheck
 * 
 * $defaultOption - title for the first/default option in select box
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_groups($defaultOption = "Select Group", $elementName = "", $cssClass = "form", $mode = "", $defaultOptionValue = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'usergroup')\" id='usergroup' $mode
            name='$elementName' class='$cssClass' tabindex=105 />
            <option value='$defaultOptionValue'>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].")".
            "UNION (SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

        include 'library/closedb.php';

    echo "</select>";
}





/*
 * populate_vendors()
 *
 * the populate vendors function returns all the vendors found in the dictionary table in an ascending 
 * alphabetical order
 */
function populate_vendors($defaultOption = "Select Vendor",$elementName = "", $cssClass = "form", $mode = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'group')\" id='usergroup' $mode
            name='$elementName' class='$cssClass' tabindex=105 />
            <option value=''>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

        $sql = "SELECT distinct(Vendor) as Vendor FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." ORDER BY Vendor ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

    echo "</select>";

        include 'library/closedb.php';
}





/*
 * populate_realms()
 *
 * the populate realms function returns all the realms found in the realms table in ascending
 * alphabetical order
 */
function populate_realms($defaultOption = "Select Realm",$elementName = "", $cssClass = "form", $mode = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'realm')\" id='realmlist' $mode
            name='$elementName' class='$cssClass' tabindex=105 />
            <option value=''>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

    $configValues['CONFIG_DB_TBL_DALOREALMS'] = "realms";

        $sql = "SELECT distinct(RealmName) as Realm FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS']." ORDER BY Realm ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

    echo "</select>";

        include 'library/closedb.php';

}







/*
 * populate_proxys()
 *
 * the populate realms function returns all the realms found in the realms table in ascending
 * alphabetical order
 */
function populate_proxys($defaultOption = "Select Proxy",$elementName = "", $cssClass = "form", $mode = "") {

    echo "<select onChange=\"javascript:setStringText(this.id,'proxy')\" id='proxylist' $mode
            name='$elementName' class='$cssClass' tabindex=105 />
            <option value=''>$defaultOption</option>
            <option value=''></option>";

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

    $configValues['CONFIG_DB_TBL_DALOPROXYS'] = "proxys";

        $sql = "SELECT distinct(ProxyName) as Proxy FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS']." ORDER BY Proxy ASC;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "  
                        <option value='$row[0]'> $row[0] </option>
                        ";

        }

    echo "</select>";

        include 'library/closedb.php';

}

/*
 * populate_passwordTypes
 * creates a select box and populates it with all supported password types
 * 
 * $elementName   - the string used for the select element's name='' value
 * $cssClass      - the css/xhtml class name, default is form for displaying on content divs (not sidebar)
 *
 */
function populate_password_types($elementName = "", $cssClass = "form", $mode = "") {

    echo "<select $mode
            name='$elementName' class='$cssClass' tabindex=105 />
            <option value='Cleartext-Password'>Cleartext-Password</option>
            <option value='User-Password'>User-Password</option>
            <option value='Crypt-Password'>Crypt-Password</option>
            <option value='MD5-Password'>MD5-Password</option>
            <option value='SHA1-Password'>SHA1-Password</option>
            <option value='CHAP-Password'>CHAP-Password</option>
            </select>";
}






/*
 * drawTables()
 *
 * an aid function to return the possible options for tables (check or reply)
 */
function drawTables() {

    echo "
        <option value='check'>check</option>
        <option value='reply'>reply</option>
    ";
}






/*
 * drawOptions()
 *
 * an aid function to return the possible options for op (operator) values
 * for attributes
 */
function drawOptions() {

    echo "
                <option value='='>=</option>
                <option value=':='>:=</option>
                <option value='=='>==</option>
                <option value='+='>+=</option>
                <option value='!='>!=</option>
                <option value='>'>></option>
                <option value='>='>>=</option>
                <option value='<'><</option>
                <option value='<='><=</option>
                <option value='=~'>=~</option>
                <option value='!~'>!~</option>
                <option value='=*'>=*</option>
                <option value='!*'>!*</option>

        ";
}





/*
 * drawTypes()
 *
 * an aid function to return the possible attribute types for
 * a given attribute
 */
function drawTypes() {

    echo "
                <option value='string'>string</option>
                <option value='integer'>integer</option>
                <option value='ipaddr'>ipaddr</option>
                <option value='date'>date</option>
                <option value='octets'>octets</option>
                <option value='ipv6addr'>ipv6addr</option>
                <option value='ifid'>ifid</option>
                <option value='abinary'>abinary</option>
        ";
}


/*
 * drawRecommendedHelpers()
 *
 * an aid function to return the possible helper functions for
 * different attributes
 */
function drawRecommendedHelper() {

    echo "
                <option value='date'>date</option>
                <option value='datetime'>datetime</option>
                <option value='authtype'>authtype</option>
                <option value='framedprotocol'>framedprotocol</option>
                <option value='servicetype'>servicetype</option>
                <option value='kbitspersecond'>kbitspersecond</option>
                <option value='bitspersecond'>bitspersecond</option>
                <option value='volumebytes'>volumebytes</option>
                <option value='mikrotikRateLimit'>mikrotikRateLimit</option>
        ";
}





?>
