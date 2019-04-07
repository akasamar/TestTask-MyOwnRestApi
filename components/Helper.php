<?php 

class Helper
{
	public static function makeRandom($count = 10, $email)
	{
		$outstr = '';
		$i = -1;
		$str = "abcdefghijklmnopqrstuvwxyz1234567890";
		while (++$i < $count)
			$outstr .= $str{rand(0, 35)};
		return base64_encode($outstr.$email);
	}
}
?>
