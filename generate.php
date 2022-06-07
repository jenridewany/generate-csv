<?php

/*
    Generate csv for blastika (added salut, split loan number, payment due date str)
    on php 7.0
    Jeje - 2022
    
    ** Format Header rawCSV
    no,Bot Name,Name,Gender,Phone,Loan Number,paymentTotal,Create Date ,Due Date 
*/

$dir = "rawCsv/";
        
$files = scan_dir($dir);
$tmp = [];
foreach($files as $file){
    $fileName = $dir.$file;
    # read files
    $data = array_map('str_getcsv', file($fileName));

    //delete header
    unset($data[0]);
    $tmp =[];
    foreach($data as $val){
        //set salut
        if(strtolower($val[3]) == "female"){
            $salut = ["ibu"];
        }else{
            $salut = ["bapak"];
        }

        //replace 62 to 0
        $checkphone = $val[4];
        $checkphone = str_replace([' ', '.', '(', ')'], '', $checkphone);
        $phone = null;
        if(
            (in_array(substr($checkphone, 0, 2), ['08','62','01','02','05']) && is_numeric($checkphone) && strlen($checkphone) > 8 && strlen($checkphone) < 15) ||
            (substr($checkphone, 0, 3) == '+62' && is_numeric(substr($checkphone, 1)) && strlen($checkphone) >= 11 && strlen($checkphone) < 16) ||
            (substr($checkphone, 0, 1) == '8' && is_numeric($checkphone) && strlen($checkphone) > 8 && strlen($checkphone) < 13)
        ){
            if(substr($checkphone, 0, 1) == '8' && is_numeric($checkphone) && strlen($checkphone) > 8 && strlen($checkphone) < 13) $checkphone = '0'.$checkphone;
            
            $phone = str_replace('+','',$checkphone);
            if(substr($phone, 0, 2) == '62') $phone = '0'.substr($phone,2,strlen($phone));
        }
        if(!$phone) continue; 
        $val[4] =$phone;

        //split loan number
        $loanNum = str_split($val[5]);
        $loanNumber = [implode(" ",$loanNum)]; 

        //set payment due date
        $months = array("januari", "febuari", "maret", "april", "mei", "juni", "juli", "agustus", "september", "oktober", "november", "desember");
        $paymentDueDate = strtotime($val[8]);
        $date = date("j", $paymentDueDate);
        $month = $months[date("n", $paymentDueDate)-1];
        $year = date("Y", $paymentDueDate);
        $paymentDueDateStr = ["$date $month $year"];

        //merge data lama & tambahan
        $tmp[] = array_merge($val, $salut, $loanNumber, $paymentDueDateStr);
    }
    $name = "Data Broadcast.csv";
    //create csv
    createCsv($name, $tmp);
}

function scan_dir($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess');

    $files = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    asort($files);
    $files = array_keys($files);

    return ($files) ? $files : false;
}

function createCsv($filename, $data){

    // open csv file for writing
    $folder = 'csv/'; 
    if(!file_exists($folder)) mkdir($folder, 770);
    if (file_exists($folder) && !is_writable($folder)) chmod($folder, 0770);
    // open csv file for writing
    $f = fopen($folder.'/'.$filename, 'w');

    if ($f === false) {
        return false;
    }

    // header name
    fputcsv($f, ['No','Bot Name','Name','Gender','Phone','Loan Number','Payment','Create Date','Due Date', 'salut', 'loanNumber', 'paymentDueDateStr']);
    
    // write each row at a time to a file
    foreach ($data as $row) {
        fputcsv($f, $row);
    }

    // close the file
    fclose($f);
    return true;
}