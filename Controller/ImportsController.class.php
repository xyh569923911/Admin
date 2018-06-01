<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class ImportsController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isUclient = "Imports";
	}

	public function index(){
		// var_dump($_FILES);
		if($_FILES){
			$upload=new \Think\Upload();
			$upload->maxSize   =     20000000;
			$upload->exts      =     '';
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Excel/";
	   		$info   =   $upload->upload();

	   		vendor("PHPExcel.PHPExcel");
	        $files="./Uploads/".$info['excels']['savepath'].$info['excels']['savename'];
	        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
	        $objPHPExcel = $objReader->load($files,$encode='utf-8');
	        $sheet = $objPHPExcel->getSheet(0);
	        $highestRow = $sheet->getHighestRow(); // 取得总行数
	        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
	        $time=-30;
	        for($i=2;$i<=$highestRow;$i++)
	        {  
	          $data[$i]['username']= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue(); 
	          $data[$i]['phone']  = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
	          $data[$i]['saleman'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
	          $data[$i]['member_id']=M("member")->where("username=".'"'.$data[$i]['saleman'].'"')->getField('id');
	          if(!$data[$i]['member_id']){
	          	 unlink($files);
	          	$this->error("错误：excel表格中的业务员名称--{$data[$i]['saleman']}--不对",U('/Admin/Imports/index'),5);
	          	return false;
	          }
	          $arr=M("admin")->where("member_id=".$data[$i]['member_id'])->Field("id,level_id")->find();
	          $data[$i]['admin_id']=$arr['id'];
	          $data[$i]['admin_ids']=$arr['level_id'];
	          $data[$i]['state']=1;
	          $data[$i]['zd_state']=1;
	          $data[$i]['state_id']=5;
	          $data[$i]['type_id']=14;
	          $t=$time-$i;
	          $data[$i]['addtime']=strtotime("$t day");
	          $data[$i]['lxtime']=time();
	        } 
	        // echo "<pre>";
	        // var_dump($data);exit();
	        foreach ($data as $k => $val) {
	        	$uid=M("Uclient")->add($val);
	        	if($uid<1){
	        		 unlink($files);
	        		$this->error("错误：{$val['username']}这条客户信息有误,这条信息及后面的数据要重新上传",U('/Admin/Imports/index'),5);
	          		return false;
	        	}
	        	$str['u_id']=$uid;
	        	$str['time']=$val['addtime'];
	        	$str['admin_id']=$val['admin_id'];
	        	$str['state_id']=$val['state_id'];
	        	$ids=M("Uclienttime")->add($str);
	        }
	         unlink($files);
			 $this->assign('data',$data); 
			 echo "<script>alert('上传成功')</script>";
		}
		
		$this->display();
	}

}


 ?>