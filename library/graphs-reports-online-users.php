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
 * Description:    this extension creates a pie chart of online users
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('checklogin.php');

    include('opendb.php');
    include('libchart/classes/libchart.php');

    $chart = new VerticalBarChart(800, 600);
    $dataSet = new XYDataSet();

    // getting total users
    $sql = sprintf("SELECT DISTINCT(username) FROM %s", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql);
    $totalUsers = $res->numRows();

    // get total users online
    $sql = sprintf("SELECT DISTINCT(username)
                    FROM %s
                    WHERE AcctStopTime IS NULL
                       OR AcctStopTime = '0000-00-00 00:00:00'", $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);
    $totalUsersOnline = $res->numRows();

    // get total nas online
    $sql = "SELECT count(UserName) FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (AcctStopTime is NULL OR AcctStopTime = '0000-00-00 00:00:00')";
    $res = $dbSocket->query($sql);
    $totalNasOnline = $res->fetchRow()[0];


    include('closedb.php');

    if ($totalUsers > 0) {
        $totalUsersOffline = $totalUsers - $totalUsersOnline;
        
        $label1 = "Offline Users";
        $value1 = intval($totalUsersOffline);
        $point1 = new Point($label1, $value1);
        $dataSet->addPoint($point1);
        
        if ($totalUsersOnline > 0) {
            $label2 = "Online Users";
            $value2 = intval($totalUsersOnline);
            $point2 = new Point($label2, $value2);
            $dataSet->addPoint($point2);
        }
        if ($totalNasOnline > 0) {
            $label3 = "NAS Connections";
            $value3 = intval($totalNasOnline);
            $point3 = new Point($label3, $value3);
            $dataSet->addPoint($point3);
        }

    }

    header("Content-type: image/png");
    $chart->setTitle("online/offline users");
    $chart->setDataSet($dataSet);
    $chart->render();
?>
