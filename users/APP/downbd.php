<?php


if (isset($_POST['Submit'])) {
	
	 
  $ts2 = isset($_REQUEST["date2"]) ? $_REQUEST["date2"] : ""; 
		$ts1 = isset($_REQUEST["date1"]) ? $_REQUEST["date1"] : ""; 

// http://stackoverflow.com/questions/125113/php-code-to-convert-a-mysql-query-to-csv

include "conn.php";  
 $check = mysql_query("SELECT * FROM agent_cust_transaction WHERE STR_TO_DATE(date, '%Y-%m-%d') BETWEEN '$ts1' AND '$ts2'  ") or die('Query failed: ' . mysql_error());

// These functions setup the headers:


function setExcelContentType() {
    if(headers_sent())
        return false;

    header('Content-type: application/vnd.ms-excel');
    return true;
}

function setDownloadAsHeader($filename) {
    if(headers_sent())
        return false;

    header('Content-disposition: attachment; filename=' . $filename);
    return true;
}
// This one sends a CSV to a stream using a mysql result

function csvFromResult($stream, $result, $showColumnHeaders = true) {
    if($showColumnHeaders) {
        $columnHeaders = array();
        $nfields = mysql_num_fields($result);
        for($i = 0; $i < $nfields; $i++) {
            $field = mysql_fetch_field($result, $i);
            $columnHeaders[] = $field->name;
        }
        fputcsv($stream, $columnHeaders);
    }

    $nrows = 0;
    while($row = mysql_fetch_row($result)) {
        fputcsv($stream, $row);
        $nrows++;
    }

    return $nrows;
}

// This one uses the above function to write a CSV to a file, given by $filename

function csvFileFromResult($filename, $result, $showColumnHeaders = true) {
    $fp = fopen($filename, 'w');
    $rc = csvFromResult($fp, $result, $showColumnHeaders);
    fclose($fp);
    return $rc;
}

// And this is where the magic happens ;)

function csvToExcelDownloadFromResult($result, $showColumnHeaders = true, $asFilename = 'data.csv') {
    setExcelContentType();
    setDownloadAsHeader($asFilename);
    return csvFileFromResult('php://output', $result, $showColumnHeaders);
}

// For example:

//$result = mysql_query("SELECT foo, bar, shazbot FROM baz WHERE boo = 'foo'");




$tableName="download_agent_clear_trans" ;
mysql_query("DROP TABLE IF EXISTS `kunzle`.`$tableName`") or die(mysql_error());

//Create a New table
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
  `id` int(11) NOT NULL AUTO_INCREMENT, `Date` Date NOT NULL, `Receipt_No` varchar(60) NOT NULL, `Sender` varchar(60) NOT NULL,
  `Receiver` varchar(60) NOT NULL,`Transfer_Mode` varchar(62) NOT NULL,  `Amount_Sending` double NOT NULL,`Local_Pay_N` double NOT NULL,
`Daph_Com` double NOT NULL,`Agent_Com` double NOT NULL,`Total_Paying` double NOT NULL,`status` varchar(56) NOT NULL,
  PRIMARY KEY (`id`),KEY `date` (`date`),KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=261 ");
// Transfer new request of transaction to the new created table database				
mysql_query ( "INSERT $tableName(id,Date,Receipt_No,Sender,Receiver,Transfer_Mode,Amount_sending,Local_Pay_N,Daph_Com,Agent_Com,Total_Paying,status)

SELECT id,date,receiptno,sender_name,r_name,r_transfer,amt_send,amt_local,com_d,com_a,total,status FROM agent_cust_transaction WHERE status='PAID'ORDER BY DATE DESC");


$result = mysql_query("SELECT * FROM $tableName") or die('Query failed: ' . mysql_error());}

csvToExcelDownloadFromResult($result);
?>