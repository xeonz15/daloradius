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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');


    include 'library/opendb.php';

        isset($_REQUEST['paymentname']) ? $paymentname = $_REQUEST['paymentname'] : $paymentname = "";
        isset($_REQUEST['paymentnotes']) ? $paymentnotes = $_REQUEST['paymentnotes'] : $paymentnotes = "";

    $edit_paymentname = $paymentname; //feed the sidebar variables    

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST['submit'])) {

                $paymentname = $_POST['paymentname'];
                $paymentnotes = $_POST['paymentnotes'];

        if (trim($paymentname) != "") {

            $currDate = date('Y-m-d H:i:s');
            $currBy = $_SESSION['operator_user'];

            $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." SET ".
            " value='".$dbSocket->escapeSimple($paymentname)."', ".
            " notes='".$dbSocket->escapeSimple($paymentnotes).    "', ".
            " updatedate='$currDate', updateby='$currBy' ".
            " WHERE value='".$dbSocket->escapeSimple($paymentname)."'";
            $res = $dbSocket->query($sql);
            $logDebugSQL = "";
            $logDebugSQL .= $sql . "\n";
            
            $successMsg = "Updated payment type: <b> $paymentname </b>";
            $logAction .= "Successfully updated payment type [$paymentname] on page: ";
            
        } else {
            $failureMsg = "no payment type was entered, please specify a payment type to edit.";
            $logAction .= "Failed updating payment type [$paymentname] on page: ";
        }
        
    }
    

    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." WHERE value='".$dbSocket->escapeSimple($paymentname)."'";
    $res = $dbSocket->query($sql);
    $logDebugSQL .= $sql . "\n";

    $row = $res->fetchRow();
    $paymenttype = $row[1];
    $paymentnotes = $row[2];
    $creationdate = $row[3];
    $creationby = $row[4];
    $updatedate = $row[5];
    $updateby = $row[6];

    include 'library/closedb.php';


    if (trim($paymentname) == "") {
        $failureMsg = "no payment type was entered or found in database, please specify a payment type to edit";
    }


    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        "library/javascript/ajaxGeneric.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','paymenttypesedit.php');
    $help = t('helpPage','paymenttypesedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    if (isset($paymentname)) {
        $title .= ":: $paymentname";
    } 

    include("menu-bill-payments.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    $input_descriptors2 = array();
    $input_descriptors2[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
    $input_descriptors2[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
    $input_descriptors2[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
    $input_descriptors2[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );
    
    // set navbar stuff
    $navbuttons = array(
                          'PayTypeInfo-tab' => t('title','PayTypeInfo'),
                          'Optional-tab' => t('title','Optional'),
                       );

    print_tab_navbuttons($navbuttons);
    
    include_once('include/management/actionMessages.php');
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="tabcontent" id="PayTypeInfo-tab" style="display: block">


        <fieldset>

            <h302> <?php echo t('title','PayTypeInfo'); ?> </h302>
            <br/>

            <ul>

                <li class='fieldset'>
                <label for='paymentname' class='form'><?php echo t('all','PayTypeName') ?></label>
                <input disabled name='paymentname' type='text' id='paymentname' value='<?php echo $paymentname ?>' tabindex=100 />
                </li>

                <li class='fieldset'>
                <label for='paymentnotes' class='form'><?php echo t('all','PayTypeNotes') ?></label>

                        <input class='text' name='paymentnotes' type='text' id='paymentnotes' value='<?php echo $paymentnotes ?>' tabindex=101 />

                </li>

                

            </ul>

        </fieldset>

        <input type="hidden" value="<?php echo $paymentname ?>" name="paymentname"/>

    </div>

    <div class="tabcontent" id="Optional-tab">
        <fieldset>

            <h302> Optional </h302>
            <h301> Other </h301>
            
            <ul style="margin: 30px auto">

<?php
                foreach ($input_descriptors2 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>
        </fieldset>
    </div>
    
    <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
</form>

        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>
