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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    // invoice details
    isset($_POST['invoice_status_id']) ? $invoice_status_id = $_POST['invoice_status_id'] : $invoice_status_id = "";
    isset($_POST['invoice_type_id']) ? $invoice_type_id = $_POST['invoice_type_id'] : $invoice_type_id = "";
    isset($_POST['invoice_date']) ? $invoice_date = $_POST['invoice_date'] : $invoice_date = "";
    isset($_POST['invoice_notes']) ? $invoice_notes = $_POST['invoice_notes'] : $invoice_notes = "";
    
    isset($_POST['invoice_items']) ? $invoice_items = $_POST['invoice_items'] : $invoice_items = "";
    
    isset($_GET['user_id']) ? $user_id = $_GET['user_id'] : $user_id = "";
    isset($_GET['username']) ? $username = $_GET['username'] : $username = "";


    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST["submit"])) {
        
        isset($_POST['user_id']) ? $user_id = $_POST['user_id'] : $user_id = "";
        
        include 'library/opendb.php';

        if (trim($user_id) != "") {

            $currDate = date('Y-m-d H:i:s');
            $currBy = $_SESSION['operator_user'];

            if (!$invoice_status_id)
                $invoice_status_id = 1;
            
            $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].
            " (id, user_id, date, status_id, type_id, notes, creationdate, creationby, updatedate, updateby) ".
            " VALUES (0, '".$dbSocket->escapeSimple($user_id)."', '".
            $dbSocket->escapeSimple($invoice_date)."', '".
            $dbSocket->escapeSimple($invoice_status_id)."', '".
            $dbSocket->escapeSimple($invoice_type_id)."', '".
            $dbSocket->escapeSimple($invoice_notes)."', ".
            " '$currDate', '$currBy', NULL, NULL)";
            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";

            if (!PEAR::isError($res)) {

                $invoice_id = $dbSocket->getOne( "SELECT LAST_INSERT_ID() FROM `".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']."`" ); 
                
                // add the invoice items which the user created
                addInvoiceItems($dbSocket, $invoice_id);
                
                $successMsg = "Added to database new invoice: <b>$invoice_id</b>";
                $logAction .= "Successfully added new invoice [$invoice_id] on page: ";
                
            } else {
                
                $failureMsg = "Error in executing invoice INSERT statement";    
                $logAction .= "Failed adding new invoice on page: ";

            }
                
        } else {
            $failureMsg = "you must provide a user id which matches the userbillinfo records";    
            $logAction .= "Failed adding new invoice on page: ";    
        }
    
        include 'library/closedb.php';
    }


    function addInvoiceItems($dbSocket, $invoice_id = '') {

        global $logDebugSQL;
        global $configValues;
    
        $currDate = date('Y-m-d H:i:s');
        $currBy = $_SESSION['operator_user'];
    
        // insert invoice's items
        if (!empty($invoice_id)) {
    
            foreach ($_POST as $itemName => $value) {
    
                if (substr($itemName, 0, 4) == 'item') {
                                    
                    error_log('processing: '.$itemName);
                    
                    $planId = $value['plan'];
                    $amount = $value['amount'];
                    $tax = $value['tax'];
                    $notes = $value['notes'];
    
                    // if no amount is provided just break out
                    if (empty($amount))
                        break;
                    
                    $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].
                            " (id, invoice_id, plan_id, amount, tax_amount, notes, ".
                            " creationdate, creationby, updatedate, updateby) ".
                            " VALUES (0, '".$invoice_id."', '".
                            $dbSocket->escapeSimple($planId)."', '".
                            $dbSocket->escapeSimple($amount)."', '".
                            $dbSocket->escapeSimple($tax)."', '".
                            $dbSocket->escapeSimple($notes)."', ".
                            " '$currDate', '$currBy', NULL, NULL)";
    
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                    
                }
            }
        }
    }
    
    
    include 'library/opendb.php';
    
    // let's try to get the user_id from the username 
    if (empty($user_id)) {
        $sql = "SELECT id FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
        " WHERE username = '".$dbSocket->escapeSimple($username)."'";
        $res = $dbSocket->query($sql);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $user_id = $row['id'];
        $logDebugSQL .= $sql . "\n";
    }
    
    $userInfo = array();
    
    if (isset($user_id) && (!empty($user_id))) {

        $sql = "SELECT id, contactperson, city, state, username FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
        " WHERE id = '".$dbSocket->escapeSimple($user_id)."'";
        $res = $dbSocket->query($sql);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        
        $userInfo['contactperson'] = $row['contactperson'];
        $userInfo['username'] = $row['username'];
        $userInfo['city'] = $row['city'];
        $userInfo['state'] = $row['state'];
        
        $logDebugSQL .= $sql . "\n";
                        
    }
    
    include 'library/closedb.php';
    
    
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
    
    include_once('include/management/populate_selectbox.php');
    $planSelect = populate_plans("","itemXXXXXXX[plan]", "form", "", "", true);

    $inline_extra_js = <<<EOF
var itemCounter = 1;

function addTableRow() {
    itemCounter++;

  	var container = document.getElementById('container');
  	
    var counter = document.getElementById('counter');
    var num = counter.value + 1;
  	counter.value = num;
  	
	var trContainer = document.createElement('tr');
	var trIdName = 'itemsRow' + num;
	trContainer.setAttribute('id', trIdName);

	var plansSelect = "$planSelect";
    
    var td1_name = "item" + itemCounter + "[plan]",
        td2_name = "item" + itemCounter + "[amount]",
        td3_name = "item" + itemCounter + "[tax]",
        td4_name = "item" + itemCounter + "[notes]",
        td5_onclick = "removeTableRow('" + trIdName + "')";
    
    var td1 = document.createElement('td');
    td1.innerHTML = plansSelect.replace("itemXXXXXXX[plan]", td1_name);

	var td2 = document.createElement("td");
    td2.innerHTML = '<input type="text" id="item' + num + '" name="' + td2_name + '">';

	var td3 = document.createElement('td');
	td3.innerHTML = '<input type="text" id="item' + num + '" name="' + td3_name + '">';

	var td4 = document.createElement('td');
	td4.innerHTML = '<input type="text" id="item' + num + '" name="' + td4_name + '">';

	var td5 = document.createElement('td');
	td5.innerHTML = '<input type="button" name="remove" value="Remove" onclick="' + td5_onclick + '" class="button">';

	trContainer.appendChild(td1);
	trContainer.appendChild(td2);
	trContainer.appendChild(td3);
	trContainer.appendChild(td4);
	trContainer.appendChild(td5);
	container.appendChild(trContainer);
	  
}

function removeTableRow(rowCounter) {
	  var container = document.getElementById('container');
	  var trContainer = document.getElementById(rowCounter);
	  container.removeChild(trContainer);
	}    
EOF;

    $title = t('Intro','billinvoicesnew.php');
    $help = t('helpPage','billinvoicesnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js, "", $inline_extra_js);

    include("menu-bill-invoice.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
    
    // set navbar stuff
    $navbuttons = array(
                          'Invoice-tab' => t('title','Invoice'),
                          'Items-tab' => t('title','Items'),
                       );

    print_tab_navbuttons($navbuttons);
?>

<form method="POST">

    <div class="tabcontent" id="Invoice-tab" style="display: block">
        
    <fieldset>

        <h302> <?php echo t('title','Invoice'); ?> </h302>

        <ul>
        
        <?php
        echo 'Customer:<b/><br/>'; 
        echo '<a href="/bill-pos-edit.php?username='.$userInfo['username'].'">'.$userInfo['contactperson'].'</a><br/>'.
            $userInfo['city']. (!empty($userInfo['state']) ? ', '.$userInfo['state'] : '' );
        echo '</b>';
        ?>
        <br/>

        <li class='fieldset'>
        <label for='invoice_status_id' class='form'><?php echo t('all','InvoiceStatus')?></label>
        <?php
                include_once('include/management/populate_selectbox.php');
                populate_invoice_status_id("Select Status", "invoice_status_id", 'form', '', 1);
        ?>
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('invoice_status_id')" />
        <div id='invoiceStatusTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','invoiceStatusTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='invoice_type_id' class='form'><?php echo t('all','InvoiceType')?></label>
        <?php
                include_once('include/management/populate_selectbox.php');
                populate_invoice_type_id("Select Type", "invoice_type_id");
        ?>
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('invoice_type_id')" />
        <div id='invoiceTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','invoiceTypeTooltip') ?>
        </div>
        </li>


        <li class='fieldset'>
        <label for='user_id' class='form'><?php echo t('all','UserId') ?></label>
        <input name='user_id' type='text' id='user_id' value='<?php echo $user_id ?>' tabindex=101 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('user_idTooltip')" /> 
        
        <div id='user_idTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','user_idTooltip') ?>
        </div>
        </li>



        <label for='invoice_date' class='form'><?php echo t('all','Date')?></label>        
        <input value='' id='invoice_date' name='invoice_date'  tabindex=108 />
        <img src="library/js_date/calendar.gif" onclick="showChooser(this, 'invoice_date', 'chooserSpan_invoicedate', 1950, <?php echo date('Y', time());?>, 'Y-m-d H:i:s', true);">
        <br/>

        <label for='invoice_notes' class='form'><?php echo t('ContactInfo','Notes')?></label>
        <textarea class='form' name='invoice_notes' ></textarea>

        
        </ul>
    
    </fieldset>
    <div id="chooserSpan_invoicedate" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
    </div>

    <div class="tabcontent" id="Items-tab">
    <fieldset>

        <h302> <?php echo t('title','Items'); ?> </h302>
        <input type='button' name='addItem' value='Add Item'
            onclick="javascript:addTableRow();" class='button'>
        <br/>


        <input type="hidden" value="0" id="counter" />

        <table BORDER="7" CELLPADDING="10">
        <tbody id="container">
        <tr>
            <th>Plan</th>
            <th>Item Amount</th>
            <th>Item Tax</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
        
        <tr id="itemRowDefault">
        <td>
            <?php
                populate_plans('Select Plan',"itemDefault[plan]", "form", "", '', true); 
            ?>
        </td>
        <td><input type="text" name="itemDefault[amount]" id="itemDefault1"> </td>
        <td><input type="text" name="itemDefault[tax]" id="itemDefault1"> </td>
        <td><input type="text" name="itemDefault[notes]" id="itemDefault1"> </td>
        <td><input type="button" class="button" onclick="javascript:removeTableRow('itemRowDefault');" value="Remove" name="remove"></td>
        </tr>
        
        </tbody>
        </table>
        
    <br/>
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
