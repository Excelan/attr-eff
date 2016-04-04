<?php

class RoboPaymentGateway
{

	static function result()
	{
		$mrh_pass2 = payment_robo_mrh_pass2;
		$tm=getdate(time()+9*3600); // ?????/ USED?
		$date="$tm[year]-$tm[mon]-$tm[mday] $tm[hours]:$tm[minutes]:$tm[seconds]";
		$out_summ = $_REQUEST["OutSum"];
		$inv_id = $_REQUEST["InvId"];
		$shp_item = $_REQUEST["Shp_item"];
		$crc = $_REQUEST["SignatureValue"];
		$crc = strtoupper($crc);
		$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2:Shp_item=$shp_item"));
		if ($my_crc !=$crc)
		{
		  echo "bad sign\n";
		  //exit();
		  return false;
		}
		echo "OK$inv_id\n";
		return array('total'=>$out_summ,'uuid'=>$inv_id);
	}
	
	static function success()
	{
		$mrh_pass1 = payment_robo_mrh_pass1;
		$out_summ = $_REQUEST["OutSum"];
		$inv_id = $_REQUEST["InvId"];
		$shp_item = $_REQUEST["Shp_item"];
		$crc = $_REQUEST["SignatureValue"];
		$crc = strtoupper($crc);
		$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1:Shp_item=$shp_item"));
		if ($my_crc !=$crc)
		{
		  echo "bad sign\n";
		  return false;
		}
		echo "OK$inv_id\n";
		return true;
	}
	
	static function fail()
	{
		$inv_id = (int) $_REQUEST["InvId"];
		return $inv_id;	
	}

}

?>