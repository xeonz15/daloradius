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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // declaring variables
        $username = (array_key_exists('username', $_POST) && isset($_POST['username']))
                  ? trim(str_replace("%", "", $_POST['username'])) : "";
        $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

        $groupname = (array_key_exists('group', $_POST) && isset($_POST['group']))
                   ? trim(str_replace("%", "", $_POST['group'])) : "";
        $groupname_enc = (!empty($groupname)) ? htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') : "";

        $groupnameOld = (array_key_exists('groupOld', $_POST) && isset($_POST['groupOld']))
                      ? trim(str_replace("%", "", $_POST['groupOld'])) : "";
        $groupnameOld_enc = (!empty($groupnameOld)) ? htmlspecialchars($groupnameOld, ENT_QUOTES, 'UTF-8') : "";

        $priority = (array_key_exists('priority', $_POST) && isset($_POST['priority']) &&
                     intval(trim($_POST['priority'])) >= 0) ? intval(trim($_POST['priority'])) : 1;
    } else {
        // declaring variables
        $username = (array_key_exists('username', $_REQUEST) && isset($_REQUEST['username']))
                  ? trim(str_replace("%", "", $_REQUEST['username'])) : "";
        $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

        $groupnameOld = (array_key_exists('group', $_REQUEST) && isset($_REQUEST['group']))
                      ? trim(str_replace("%", "", $_REQUEST['group'])) : "";
        $groupnameOld_enc = (!empty($groupnameOld)) ? htmlspecialchars($groupnameOld, ENT_QUOTES, 'UTF-8') : "";
    }

    // feed the sidebar
    $usernameList = $username_enc;

    include('library/opendb.php');

    $mapping_check_format = "SELECT COUNT(*) FROM %s WHERE username='%s' AND groupname='%s'";

    // check if the old mapping is already in place
    $sql = sprintf($mapping_check_format, $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                          $dbSocket->escapeSimple($username),
                                          $dbSocket->escapeSimple($groupnameOld));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $old_mapping_inplace = intval($res->fetchrow()[0]) > 0;
    
    if (!$old_mapping_inplace) {
        // if the mapping is not in place we reset user and group
        $username = "";
        $groupnameOld = "";
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($username) || empty($groupname) || empty($groupnameOld)) {
            // username and groupname are required
            $failureMsg = "Username and groupname are required.";
            $logAction .= "Failed updating user-group mapping (username and/or groupname missing or invalid): ";
        } else {
            // check if the new mapping is already in place
            $sql = sprintf($mapping_check_format, $configValues['CONFIG_DB_TBL_RADUSERGROUP'],
                                                  $dbSocket->escapeSimple($username),
                                                  $dbSocket->escapeSimple($groupname));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";

            $new_mapping_inplace = intval($res->fetchrow()[0]) > 0;
            
            if ($new_mapping_inplace) {
                // error
                $failureMsg = "The chosen user mapping ($username_enc - $groupname_enc) is already in place.";
                $logAction .= "Failed updating user-group mapping [$username - $groupname already in place]: ";
            } else {
                $sql = sprintf("UPDATE %s SET groupname='%s', priority=%s WHERE username='%s' AND groupname='%s'",
                               $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($groupname),
                               $dbSocket->escapeSimple($priority), $dbSocket->escapeSimple($username),
                               $dbSocket->escapeSimple($groupnameOld));
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    $successMsg = "Updated user-group mapping [$username_enc, from $groupnameOld_enc to $groupname_enc]";
                    $logAction .= "Updated user-group mapping [$username, from $groupnameOld to $groupname]: ";
                } else {
                    $failureMsg = "DB Error when updating the chosen user mapping ($username_enc, from $groupnameOld_enc to $groupname_enc)";
                    $logAction .= "Failed updating user-group mapping [$username, from $groupnameOld to $groupname, db error]: ";
                }
            }
        }
    }
    
    if (empty($username) || empty($groupnameOld)) {
        $failureMsg = "the user-group you have specified is empty or invalid";
        $logAction .= "Failed updating user-group [empty or invalid user-group] on page: ";
    } else {
        // retrieve mapping from database
        $sql = sprintf("SELECT username, groupname, priority FROM %s WHERE username='%s' AND groupname='%s'",
                       $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username),
                       $dbSocket->escapeSimple($groupnameOld));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        list($this_username, $this_groupname, $this_priority) = $res->fetchRow();
    }

    include('library/closedb.php');

    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/productive_funcs.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','mngradusergroupedit');
    $help = t('helpPage','mngradusergroupedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    if (!empty($username_enc)) {
        $title .= " $username_enc";
    }
    
    include("menu-mng-rad-usergroup.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    include_once('include/management/actionMessages.php');
    
    if (!empty($username) && !empty($groupnameOld)) {
        include_once('include/management/populate_selectbox.php');

        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "username-presentation",
                                        "caption" => t('all','Username'),
                                        "type" => "text",
                                        "value" => $this_username,
                                        "tooltipText" => t('Tooltip','usernameTooltip'),
                                        "disabled" => true,
                                     );

        $input_descriptors1[] = array(
                                        "name" => "username",
                                        "type" => "hidden",
                                        "value" => $this_username,
                                     );
        
        $input_descriptors1[] = array(
                                        "name" => "groupname-presentation",
                                        "caption" => (t('all','Groupname') . " (current)"),
                                        "type" => "text",
                                        "value" => $this_groupname,
                                        "disabled" => true,
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "groupOld",
                                        "type" => "hidden",
                                        "value" => $this_groupname,
                                     );

        $options = get_groups();
        $input_descriptors1[] = array(
                                        "id" => "group",
                                        "name" => "group",
                                        "caption" => (t('all','Groupname') . " (new)"),
                                        "type" => "select",
                                        "options" => $options,
                                        "selected_value" => $this_groupname,
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );
                                     
        $input_descriptors1[] = array(
                                        "id" => "priority",
                                        "name" => "priority",
                                        "caption" => t('all','Priority'),
                                        "type" => "number",
                                        "min" => "1",
                                        "value" => $this_priority,
                                     );

        $input_descriptors1[] = array(
                                        'type' => 'submit',
                                        'name' => 'submit',
                                        'value' => t('buttons','apply')
                                     );
?>
            <form name="newusergroup" method="POST">
                <fieldset>
                    <h302><?= t('title','GroupInfo') ?></h302>

                    <ul>
                    
<?php
                        foreach ($input_descriptors1 as $input_descriptor) {
                            print_form_component($input_descriptor);
                        }
?>

                    </ul>
                </fieldset>
            </form>
<?php
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
