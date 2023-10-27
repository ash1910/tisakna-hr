<?php
require_once ("tcpdf_min/tcpdf_barcodes_2d.php");

$total=$_REQUEST['total'];
$company=$_REQUEST['company'];
$address=$_REQUEST['address'];
$zip=$_REQUEST['zip'];
$city=$_REQUEST['city'];
$company2=$_REQUEST['company2'];
$address2=$_REQUEST['address2'];
$city2=$_REQUEST['city2'];
$iban=$_REQUEST['iban'];
$poziv=$_REQUEST['poziv'];
$desc=$_REQUEST['desc'];

$totalfixed = sprintf("%015d", $total);

$data="HRVHUB30\nEUR\n$totalfixed\n$company\n$address\n$zip $city\n$company2\n$address2\n$city2\n$iban\n00\n$poziv\nCOST\n$desc\n";

$type = "PDF417";
$barcodeobj = new TCPDF2DBarcode($data, $type);
$barcodeobj->getBarcodePNG();
?>