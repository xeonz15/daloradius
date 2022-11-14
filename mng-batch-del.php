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

    isset($_GET['batch_id']) ? $batch_id = $_GET['batch_id'] : $batch_id = "";
    isset($_GET['batch_name']) ? $batch_name = $_GET['batch_name'] : $batch_name = "";

    $showRemoveDiv = "block";

    if (
        ( (isset($_GET['batch_id'])) && (!empty($batch_id)) )
             ||
        ( (isset($_GET['batch_name'])) && (!empty($batch_name)) )
        )
            {

        include 'library/opendb.php';
        
        // if batch_name is set then we need to translate that to the batch_id
        if ($batch_name) {
            
            $sql = "SELECT ".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".id,".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name ".
                    " FROM ".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY']." ".
                    " WHERE ".
                    $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].".batch_name = '$batch_name'";
                    
            $res_q_batch = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";
            $row_q_batch = $res_q_batch->fetchRow();
            $batch_id = array($row_q_batch[0]);
                
        }
                
        $allBatches = "";

        /* since the foreach loop will report an error/notice of undefined variable $value because
           it is possible that the $username is not an array, but rather a simple GET request
           with just some value, in this case we check if it's not an array and convert it to one with
           a NULL 2nd element
        */
        if (!is_array($batch_id))
            $batch_id = array($batch_id);

        foreach ($batch_id as $variable=>$value) {
            
            if (trim($variable) != "") {
                                
                $batch = $value;
                $allBatches .= $batch . ", ";
                
                // delete all attributes associated with a username
                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY']." WHERE id='".$dbSocket->escapeSimple($batch)."'";
                $req_q_delete = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                // we grab all users which are associated with this batch_id
                $sql = "SELECT ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".id,".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username ".
                        " FROM ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." ".
                        " WHERE ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".batch_id = $batch ";
                        
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                // setting table-related parameters first                
                switch($configValues['FREERADIUS_VERSION']) {
                    case '1' :
                        $tableSetting['postauth']['user'] = 'user';
                        $tableSetting['postauth']['date'] = 'date';
                        break;
                    case '2' :
                        // down
                    case '3' :
                        // down
                    default  :
                        $tableSetting['postauth']['user'] = 'username';
                        $tableSetting['postauth']['date'] = 'authdate';
                        break;
                }
                                
                // loop through each user and delete it
                while($row = $res->fetchRow()) {
    
                    $username = $row[1];
                    
                    // delete all attributes associated with a username
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE Username='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                    
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADREPLY']." WHERE Username='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
    
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." WHERE Username='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
    
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']." WHERE Username='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
    
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE Username='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                    
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADPOSTAUTH']." WHERE ".
                        $tableSetting['postauth']['user']."='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                    
                    $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE Username='$username'";
                    $res_q = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                    
                }
                

                $successMsg = "Deleted batch(s): <b> $allBatches </b>";
                $logAction .= "Successfully deleted batch(s) [$allBatches] on page: ";

            }  else { 
                $failureMsg = "no batch was entered, please specify a batch name to remove from database";        
                $logAction .= "Failed deleting batch(s) [$allBatches] on page: ";
            }


        $showRemoveDiv = "none";

        } //foreach
        
        include 'library/closedb.php';
        
    } //if

    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngbatchdel.php');
    $help = t('helpPage','mngbatchdel');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-batch.php");
    
    if (!empty($batch_name) && !is_array($batch_name)) {
        $title .= " :: " . htmlspecialchars($batch_name, ENT_QUOTES, 'UTF-8');
    }
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

    <div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    
    <fieldset>

        <h302> <?php echo t('title','BatchRemoval') ?> </h302>
        <br/>

        <label for='batch_name' class='form'><?php echo t('all','BatchName')?></label>
        <input name='batch_name' type='text' id='batch_name' value='' tabindex=100 />

        <br/><br/>
        <hr><br/>
        <input type="submit" name="submit" value="<?php echo t('buttons','apply') ?>" tabindex=1000 
            class='button' />

    </fieldset>
    
    </form>
    </div>

<?php
    include('include/config/logging.php');
    
    include_once("include/management/autocomplete.php");
    
    if ($autoComplete) {
         $inline_extra_js = "
autoComEdit = new DHTMLSuite.autoComplete();
autoComEdit.add('batch_name','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBatchNames');";
    } else {
        $inline_extra_js = "";
    }
    
    print_footer_and_html_epilogue($inline_extra_js);
?>

