<?php


function get_remote_file()
{
    include 'remote_login.php';

    $ch = curl_init();

// set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $curl_database);
    curl_setopt($ch, CURLOPT_USERPWD, $curl_username . ":" . $curl_password);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// grab URL and pass it to the browser

    $curl_output = curl_exec($ch);
    curl_close($ch);

    //$curl_output_replaced = str_replace('\n', ' ', $curl_output);

    $decode_curl = json_decode($curl_output);

    $filename = 'remote.json';

    file_put_contents($filename, $curl_output);

    $result = array();

    foreach ($decode_curl->rows as $row) :
        if ($row->value->molis != 'Molis') :
            $defaultValues = array(
                "Molis" => $row->value->molis
                //"Befundname" => $row->value->title
                //"Ext.\nLabor" => $row
            );

            array_push($result, $defaultValues);

        endif;
    endforeach;

    return $result;
}

function get_local_file()
{
    $result = array();

    $fileData = fopen("LADR.csv", "r");

    while ($row = fgetcsv($fileData)) {
        // can parse further $row by usingstr_getcsv

        for ($i = 0; $i < count($row); $i +=1) {
            $row[$i] = str_replace('\n', ' ', $row[$i]);
        }


        if ($row[0] != 'Molis') :
            $defaultValues = array(
                "Molis" => "$row[0]",
                "Sekt." => "$row[1]",
                "Gruppe" => "$row[2]",
                "Befund" => "$row[3]",
                "Verteilcode" => "$row[4]",
                "Ext. Labor" => "$row[5]",
                "Befundname" => "$row[6]",
                "Identifikator" => "$row[7]",
                "Bereich" => "$row[8]",
                "LVZ" => "$row[9]",
                "CREATDT" => "$row[10]",
                "Einheit" => "$row[11]",
                "Referenzwerte" => "$row[12]",
                "Variable Referenzwerte" => "$row[13]",
                "Lab.-sekt." => "$row[14]",
                "Aktiv St" => "$row[15]",
                "VALN2" => "$row[16]",
                "ANA_SEL4" => "$row[17]",
                "Benutzerdefinierbares Selektionsfeld 1" => "$row[18]",
                "Benutzerdefinierbares Selektionsfeld 2" => "$row[19]",
                "ANA_SEL3" => "$row[20]",
                "ELV LOEM?" => "$row[21]",
                "Material ELV" => "$row[22]",
                "Material Formblatt QF191" => "$row[23]",
                "-18\u00b0C" => "$row[24]",
                "4-8\u00b0C" => "$row[25]",
                "9-25\u00b0C" => "$row[26]"
            );

            array_push($result, $defaultValues);

        endif;

    }
    fclose($fileData);

    $json = json_encode($result);

    $filename = 'local.json';
    file_put_contents($filename, $json);

    //var_dump($result[0]);
    return $result;
}

//var_dump(get_local_file());

function write_data($filename, $data)
{
    $allValues = array("Molis", "Sekt.", "Gruppe", "Befund", "Verteilcode", "Ext. Labor", "Befundname", "Identifikator", "Bereich", "LVZ", "CREATDT", "Einheit", "Referenzwerte", "Variable Referenzwerte", "Lab.-sekt.", "Aktiv St.", "VALN2", "ANA_SEL4", "Benutzerdefinierbares Selektionsfeld 1", "Benutzerdefinierbares Selektionsfeld 2", "ANA_SEL3", "ELV LOEM?", "Material ELV", "Material Formblatt QF191", " -18\u00b0C", " 4-8\u00b0C", " 9-25\u00b0C");
    $output = fopen($filename, 'w') or die("Can't open file");
    header("Content-Type:application/csv");
    header("Content-Disposition:attachment;filename=$filename.csv");
    fputcsv($output, $allValues);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output) or die("Can't close file");
}

function compare_files()
{
    $databaseObject = get_remote_file();
    $localObject = get_local_file();
    $foundInDatabase = array();
    $notFoundInDatabase = array();
    $notFoundLocally = array();

    write_data('localObject.csv', $localObject);

    foreach ($localObject as $localdata) :
        $found = false;
        foreach ($databaseObject as $databaseEntry) :
            if ($localdata['Molis'] == $databaseEntry['Molis']) :
                array_push($foundInDatabase, $localdata);
                $found = true;
                break;
            endif;
        endforeach;
        if (!$found) :
            array_push($notFoundInDatabase, $localdata);
        endif;
    endforeach;

    write_data('notfound.csv', $notFoundInDatabase);
    write_data('found.csv', $foundInDatabase);
}

compare_files();
