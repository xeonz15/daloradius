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

    isset($_REQUEST['name']) ? $name = $_REQUEST['name'] : $name = "";
    isset($_REQUEST['macaddress']) ? $macaddress = $_REQUEST['macaddress'] : $macaddress = "";
    isset($_REQUEST['geocode']) ? $geocode = $_REQUEST['geocode'] : $geocode = "";
    isset($_REQUEST['owner']) ? $owner = $_REQUEST['owner'] : $owner = "";
    isset($_REQUEST['email_owner']) ? $email_owner = $_REQUEST['email_owner'] : $email_owner = "";
    isset($_REQUEST['manager']) ? $manager = $_REQUEST['manager'] : $manager = "";
    isset($_REQUEST['email_manager']) ? $email_manager = $_REQUEST['email_manager'] : $email_manager = "";
    isset($_REQUEST['address']) ? $address = $_REQUEST['address'] : $address = "";
    isset($_REQUEST['company']) ? $company = $_REQUEST['company'] : $company = "";
    isset($_REQUEST['phone1']) ? $phone1 = $_REQUEST['phone1'] : $phone1 = "";
    isset($_REQUEST['phone2']) ? $phone2 = $_REQUEST['phone2'] : $phone2 = "";
    isset($_REQUEST['hotspot_type']) ? $hotspot_type = $_REQUEST['hotspot_type'] : $hotspot_type = "";
    isset($_REQUEST['companywebsite']) ? $companywebsite = $_REQUEST['companywebsite'] : $companywebsite = "";
    isset($_REQUEST['companyphone']) ? $companyphone = $_REQUEST['companyphone'] : $companyphone = "";
    isset($_REQUEST['companyemail']) ? $companyemail = $_REQUEST['companyemail'] : $companyemail = "";
    isset($_REQUEST['companycontact']) ? $companycontact = $_REQUEST['companycontact'] : $companycontact = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST["submit"])) {
        $name = $_REQUEST['name'];
        $macaddress = $_REQUEST['macaddress'];
        $geocode = $_REQUEST['geocode'];
        
        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='".$dbSocket->escapeSimple($name)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {
            if (trim($name) != "" and trim($macaddress) != "") {

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                // insert hotspot info
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
                " (id, name, mac, geocode, owner, email_owner, manager, email_manager, address, company, ".
                "  phone1, phone2, type, companywebsite, companyemail, companycontact, companyphone, ".
                "  creationdate, creationby, updatedate, updateby) ".
                " VALUES (0, '".$dbSocket->escapeSimple($name)."', '".
                $dbSocket->escapeSimple($macaddress)."', '".
                $dbSocket->escapeSimple($geocode)."','".$dbSocket->escapeSimple($owner)."','".
                $dbSocket->escapeSimple($email_owner)."','".$dbSocket->escapeSimple($manager)."','".
                $dbSocket->escapeSimple($email_manager)."','".
                $dbSocket->escapeSimple($address)."','".$dbSocket->escapeSimple($company)."','".
                $dbSocket->escapeSimple($phone1)."','".$dbSocket->escapeSimple($phone2)."','".
                $dbSocket->escapeSimple($hotspot_type)."','".$dbSocket->escapeSimple($companywebsite)."','".
                $dbSocket->escapeSimple($companyemail)."','".
                $dbSocket->escapeSimple($companycontact)."','".
                $dbSocket->escapeSimple($companyphone).    "', ".
                " '$currDate', '$currBy', NULL, NULL)";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                $successMsg = "Added to database new hotspot: <b>$name</b>";
                $logAction .= "Successfully added new hotspot [$name] on page: ";
            } else {
                $failureMsg = "you must provide atleast a hotspot name and mac-address";    
                $logAction .= "Failed adding new hotspot [$name] on page: ";    
            }
        } else { 
            $failureMsg = "You have tried to add a hotspot that already exist in the database: $name";    
            $logAction .= "Failed adding new hotspot already in database [$name] on page: ";        
        }
    
        include 'library/closedb.php';

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
        "library/javascript/pages_common.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );

    $title = t('Intro','mnghsnew.php');
    $help = t('helpPage','mnghsnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-hs.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    // set navbar stuff
    $navbuttons = array(
                          'HotspotInfo-tab' => t('title','HotspotInfo'),
                          'ContactInfo-tab' => t('title','ContactInfo'),
                       );

    print_tab_navbuttons($navbuttons);

?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <div id="HotspotInfo-tab" class="tabcontent" style="display: block">

    <fieldset>

        <h302> <?php echo t('title','HotspotInfo'); ?> </h302>
        <br/>

        <ul>

        <li class='fieldset'>
        <label for='name' class='form'><?php echo t('all','HotSpotName') ?></label>
        <input name='name' type='text' id='name' value='' tabindex=100 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('hotspotNameTooltip')" /> 
        
        <div id='hotspotNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','hotspotNameTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='macaddress' class='form'><?php echo t('all','MACAddress') ?></label>
        <input name='macaddress' type='text' id='macaddress' value='' tabindex=101 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('hotspotMacaddressTooltip')" /> 
        
        <div id='hotspotMacaddressTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','hotspotMacaddressTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='geocode' class='form'><?php echo t('all','Geocode') ?></label>
        <input name='geocode' type='text' id='geocode' value='' tabindex=102 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('geocodeTooltip')" /> 
        
        <div id='geocodeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','geocodeTooltip') ?>
        </div>
        </li>
    
        <li class='fieldset'>
        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
        </li>

        </ul>
    </fieldset>

    </div>


    <div id="ContactInfo-tab" class="tabcontent">

<?php
        include_once('include/management/contactinfo.php');
?>

    </div><!-- #ContactInfo-tab -->

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
