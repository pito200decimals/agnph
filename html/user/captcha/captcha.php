<?php
	session_start();
	include("./phptextClass.php");	
	
	/*create class object*/
	$phptextObj = new phptextClass();	
	/*phptext function to genrate image with text*/
	$phptextObj->phpcaptcha('#fff',240,60,10,25);	
 ?>