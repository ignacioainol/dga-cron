<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$file_handle = fopen($_SERVER['DOCUMENT_ROOT'] . '/dga/inserted_logs.txt', 'a');
$lastSamples = json_decode(file_get_contents('http://localhost:3002/api/streams/getLastSamples'));

//Fecha
$datetime = new DateTime('now');
$datetime->setTimezone(new DateTimeZone('UTC'));
$ndate = $datetime->format('Y-m-d H:i:s');
$today = date("d-m-Y", strtotime("$ndate - 4 hours"));
$currentHour = date("H:i:s", strtotime("$ndate - 4 hours"));

$url = "https://snia.mop.gob.cl/controlextraccion/datosExtraccion/SendDataExtraccionService";

foreach ($lastSamples as $val) {
    //if ($val->codigoObra != 'OB-0601-507') continue;
    $ISOdate = $val->createdAt;
    $codObra = $val->codigoObra;
    $totalizer = $val->totalizer1;
    $absoluteVolumenflow = $val->absoluteVolumenflow;
    $level = $val->level;
    
    $datePart = explode(':',$ISOdate);
    $ISOFormated = "{$datePart[0]}:{$datePart[1]}:00Z";
    //echo $val->codigoObra .' '. $ISOFormated . "<br>";

    //echo "isoDate: $ISOdate, codObra: $codObra, totalizer: $totalizer, volumenFLow: $absoluteVolumenflow, nivel: $level<br>";
   $xmldata = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:aut1="http://www.mop.cl/controlextraccion/xsd/datosExtraccion/AuthSendDataExtraccionRequest">
                    <x:Header>
                        <aut1:authSendDataExtraccionTraza>
                            <aut1:codigoDeLaObra>'.$codObra.'</aut1:codigoDeLaObra>
                            <aut1:timeStampOrigen>'.$ISOFormated.'</aut1:timeStampOrigen>
                        </aut1:authSendDataExtraccionTraza>
                    </x:Header>
                    <x:Body>
                        <aut1:authSendDataExtraccionRequest>
                            <aut1:authDataUsuario>
                                <aut1:idUsuario>
                                    <aut1:rut>17404493-7</aut1:rut>
                                </aut1:idUsuario>
                                <aut1:password>HytWkUdCAy</aut1:password>
                            </aut1:authDataUsuario>
                            <aut1:authDataExtraccionSubterranea>
                                <aut1:fechaMedicion>'.$today.'</aut1:fechaMedicion>
                                <aut1:horaMedicion>'.$currentHour.'</aut1:horaMedicion>
                                <aut1:totalizador>'.$totalizer.'</aut1:totalizador>
                                <aut1:caudal>'.$absoluteVolumenflow.'</aut1:caudal>
                                <aut1:nivelFreaticoDelPozo>'.$level.'</aut1:nivelFreaticoDelPozo>
                            </aut1:authDataExtraccionSubterranea>
                        </aut1:authSendDataExtraccionRequest>
                    </x:Body>
                </x:Envelope>';

            $ch = curl_init();
            if (!$ch) {
                die("Couldn't initialize a cURL handle");
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch); // execute
            echo $result;             //show response
            curl_close($ch);

	    sleep(1);

}

?>
