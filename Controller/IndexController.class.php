<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class IndexController extends CommonController {

    public function index()
    {  
		if($_SESSION["level"] == 2){
		$this->redirect("/Admin/Coope/carry");	
		}else if($_SESSION["level"] == 3){
		$this->redirect("/Admin/Coope/signed");		
		}else{
		$this->redirect('/Admin/Base/index');
		}
    	//$this->display();
    }
}