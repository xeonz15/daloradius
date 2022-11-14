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
    $logDebugSQL = "";

    isset($_REQUEST['poolname']) ? $poolname = $_REQUEST['poolname'] : $poolname = "";
    isset($_REQUEST['ipaddress']) ? $ipaddress = $_REQUEST['ipaddress'] : $ipaddress = "";

    $showRemoveDiv = "block";

    if (isset($_POST['poolname'])) {

        $allPoolNames = "";
        $allIPAddresses = "";

        /* since the foreach loop will report an error/notice of undefined variable $value because
        it is possible that the $poolname is not an array, but rather a simple GET request
        with just some value, in this case we check if it's not an array and convert it to one with
        a NULL 2nd element
        */

        if (is_array($poolname)) {
            $itemsArray = $poolname;
        } else {
            $itemsArray = array($poolname."||".$ipaddress);
        }

        foreach ($itemsArray as $value) {

            list($poolnamex, $ipaddress) = preg_split('/\|\|/', $value);

            if ( (trim($poolnamex) != "") && (trim($ipaddress) != "") ) {

                include 'library/opendb.php';

                $allPoolNames .= $poolnamex . ", ";
                $allIPAddresses .= $ipaddress .", ";

                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADIPPOOL']." WHERE ".
                    " pool_name='".$dbSocket->escapeSimple($poolnamex)."' AND".
                    " framedipaddress='".$dbSocket->escapeSimple($ipaddress)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                $successMsg .= "Deleted IP Address <b>$ipaddress</b> for Pool Name <b>$poolnamex</b> from database <br>";
                $logAction .= "Successfully deleted IP Address [$ipaddress] for Pool name [$poolnamex] on page: ";

                include 'library/closedb.php';

            }  else {
                $failureMsg = "No IPAddress/Pool Name was entered, please specify an IPAddress/Pool Name to remove from database";
                $logAction .= "Failed deleting empty IP Address/Pool Name on page: ";
            } //if trim

        } //foreach

        $showRemoveDiv = "none";
    }

    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    
    $title = t('Intro','mngradippooldel.php');
    $help = t('helpPage','mngradippooldel');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-rad-ippool.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

    <div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

            <h302> <?php echo t('title','IPPoolInfo') ?> </h302>
            <br/>

            <label for='poolname' class='form'><?php echo t('all','PoolName') ?></label>
            <input name='poolname' type='text' id='poolname' value='<?php echo $poolname ?>' tabindex=100 />
            <br />

            <label for='ipaddress' class='form'><?php echo t('all','IPAddress') ?></label>
            <input name='ipaddress' type='text' id='ipaddress' value='<?php echo $ipaddress ?>' tabindex=101 />
            <br />

            <br/><br/>
            <hr><br/>

            <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>


        </form>
    </div>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
