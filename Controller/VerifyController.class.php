<?php
namespace Admin\Controller;
use Think\Controller;
Class VerifyController extends Controller{
	public function index()
	{
		$Verify = new \Think\Verify();
		$Verify->fontSize = 24;
		$Verify->length   = 4;
		$Verify->useNoise = false;
		$Verify->entry();
	}
}
 ?>