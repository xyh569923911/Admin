<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class FinanceController extends CommonController{

	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isUclient = "Finance";
	}

	public function index(){
		if($_GET['stime']){
			$st=$_GET['stime'];
			$et=$_GET['endtime'];
			//福永速分贷,速贷,车贷
			$bayb_sfd=$this->xyd(1,1,$st,$et);
			$bayb_sd=$this->xyd(1,2,$st,$et);
			$cars=$this->car(1,$st,$et);
			//西乡速分贷,速贷,车贷
			$baeb_sfd=$this->xyd(11,1,$st,$et);
			$baeb_sd=$this->xyd(11,2,$st,$et);
			$car_e=$this->car(11,$st,$et);
			//科技园速分贷,速贷,车贷
			$xjy_sfd=$this->xyd(19,1,$st,$et);
			$xjy_sd=$this->xyd(19,2,$st,$et);
			$xjy_car=$this->car(19,$st,$et);
			//南山速分贷,速贷,车贷
			$xje_sfd=$this->xyd(10,1,$st,$et);
			$xje_sd=$this->xyd(10,2,$st,$et);
			$xje_car=$this->car(10,$st,$et);
		}else{
			//福永速分贷,速贷,车贷
			$bayb_sfd=$this->xyd(1,1);
			$bayb_sd=$this->xyd(1,2);
			$cars=$this->car(1);
			//西乡速分贷,速贷,车贷
			$baeb_sfd=$this->xyd(11,1);
			$baeb_sd=$this->xyd(11,2);
			$car_e=$this->car(11);
			//科技园速分贷,速贷,车贷
			$xjy_sfd=$this->xyd(19,1);
			$xjy_sd=$this->xyd(19,2);
			$xjy_car=$this->car(19);
			//南山速分贷,速贷,车贷
			$xje_sfd=$this->xyd(10,1);
			$xje_sd=$this->xyd(10,2);
			$xje_car=$this->car(10);
		}

		//宝安一部速分贷，速贷，车贷yslx
		$this->assign("bayb_sfd_k",number_format($bayb_sfd['principal']/10000,2));
		$this->assign("bayb_sfd_l",number_format($bayb_sfd['nowinterest']/10000,2));
		$this->assign("bayb_sfd_y",number_format($bayb_sfd['yflx']/10000,2));
		$this->assign("bayb_sd_k",number_format($bayb_sd['principal']/10000,2));
		$this->assign("bayb_sd_l",number_format($bayb_sd['nowinterest']/10000,2));
		$this->assign("bayb_sd_y",number_format($bayb_sd['yflx']/10000,2));
		$this->assign("bayb_cd_k",number_format($cars['principal']/10000,2));
		$this->assign("bayb_cd_l",number_format($cars['interest']/10000,2));
		$this->assign("bayb_cd_y",number_format($cars['yslx']/10000,2));
		//宝安二部速分贷，速贷，车贷
		$this->assign("baeb_sfd_k",number_format($baeb_sfd['principal']/10000,2));
		$this->assign("baeb_sfd_l",number_format($baeb_sfd['nowinterest']/10000,2));
		$this->assign("baeb_sfd_y",number_format($baeb_sfd['yflx']/10000,2));
		$this->assign("baeb_sd_k",number_format($baeb_sd['principal']/10000,2));
		$this->assign("baeb_sd_l",number_format($baeb_sd['nowinterest']/10000,2));
		$this->assign("baeb_sd_y",number_format($baeb_sd['yflx']/10000,2));
		$this->assign("baeb_cd_k",number_format($car_e['principal']/10000,2));
		$this->assign("baeb_cd_l",number_format($car_e['interest']/10000,2));
		$this->assign("baeb_cd_y",number_format($car_e['yslx']/10000,2));
		//现金一部速分贷,速贷,车贷
		$this->assign("xjy_sfd_k",number_format($xjy_sfd['principal']/10000,2));
		$this->assign("xjy_sfd_l",number_format($xjy_sfd['nowinterest']/10000,2));
		$this->assign("xjy_sfd_y",number_format($xjy_sfd['yflx']/10000,2));
		$this->assign("xjy_sd_k",number_format($xjy_sd['principal']/10000,2));
		$this->assign("xjy_sd_l",number_format($xjy_sd['nowinterest']/10000,2));
		$this->assign("xjy_sd_y",number_format($xjy_sd['yflx']/10000,2));
		$this->assign("xjy_car_k",number_format($xjy_car['principal']/10000,2));
		$this->assign("xjy_car_l",number_format($xjy_car['interest']/10000,2));
		$this->assign("xjy_car_y",number_format($xjy_car['yslx']/10000,2));
		//现金二部速分贷,速贷,车贷
		$this->assign("xje_sfd_k",number_format($xje_sfd['principal']/10000,2));
		$this->assign("xje_sfd_l",number_format($xje_sfd['nowinterest']/10000,2));
		$this->assign("xje_sfd_y",number_format($xje_sfd['yflx']/10000,2));
		$this->assign("xje_sd_k",number_format($xje_sd['principal']/10000,2));
		$this->assign("xje_sd_l",number_format($xje_sd['nowinterest']/10000,2));
		$this->assign("xje_sd_y",number_format($xje_sd['yflx']/10000,2));
		$this->assign("xje_car_k",number_format($xje_car['principal']/10000,2));
		$this->assign("xje_car_l",number_format($xje_car['interest']/10000,2));
		$this->assign("xje_car_y",number_format($xje_car['yslx']/10000,2));
		$this->display();
	}

	//信用贷
	public function xyd($branch,$type,$st='',$et=''){
		$admins=M("admin")->where(array("branch_id=".$branch))->Field("id")->select();
		foreach ($admins as $k => $val) {
			$aid[]=$val['id'];
		}
		if($branch==33){
			$aid[]=65;
			$aid[]=66;
			$aid[]=127;
			$aid[]=128;
			$aid[]=136;
			$aid[]=118;
			$aid[]=147;
			$aid[]=172;
		}
		$where[]=array("Salesman"=>array('in',$aid));
		$where[]=array("type"=>$type,"state"=>1);
		$sfd_id=M("cash")->where($where)->Field("id")->select();
		foreach ($sfd_id as $k => $val) {
			$sfdid[]=$val['id'];
		}

		if($st){
			if($type==1){
				// $where=array("uid"=>array('in',$sfdid),'endtime'=>array(array('egt',$st),array('elt',$et)));
				// $wheres=array("uid"=>array('in',$sfdid),"repayment"=>2,'starttime'=>array(array('egt',$st),array('elt',$et)));
				$wherey=array("uid"=>array('in',$sfdid),'endtime'=>array(array('egt',$st),array('elt',$et)));
			}else{
				// $where=array("uid"=>array('in',$sfdid),'starttime'=>array(array('egt',$st),array('elt',$et)));
				// $wheres=array("uid"=>array('in',$sfdid),"repayment"=>2,'starttime'=>array(array('egt',$st),array('elt',$et)));
				$wherey=array("uid"=>array('in',$sfdid),'starttime'=>array(array('egt',$st),array('elt',$et)));
			}
			
		}else{
			// $where=array("uid"=>array('in',$sfdid));
			// $wheres=array("uid"=>array('in',$sfdid),"repayment"=>2);
			$wherey=array("uid"=>array('in',$sfdid));
		}
		//库存不根据时间改变而改变
		$where=array("uid"=>array('in',$sfdid));

		if($sfdid){
				//统计库存
				$cashdetails=M("cashdetails")->where($where)->Field("id,uid,min(Principal) as principal")->group("uid")->select();
				//统计利息_应收
				$cashdetaily=M("cashdetails")->where($wherey)->Field("id,uid,sum(nowinterest) as nowinterest")->group("uid")->select();
				// var_dump(count($wherey));
				// echo M("cashdetails")->getlastsql();
				//统计利息_已收
				$cashdetail=M("cashdetails")->where($wherey)->Field("id,uid,sum(returninterest) as returninterest")->group("uid")->select();
				// echo M("cashdetails")->getlastsql();				
		}
		
		
		$sfd_info=array();
		foreach ($cashdetails as $key => $val) {
			$principal +=$val['principal'];
		}
		foreach ($cashdetail as $key => $val) {
			$nowinterest +=$val['returninterest'];
		}
		foreach ($cashdetaily as $key => $val) {
			$yflx +=$val['nowinterest'];
		}
		$sfd_info['principal']=$principal;
		$sfd_info['nowinterest']=$nowinterest;
		$sfd_info['yflx']=$yflx;

		return $sfd_info;
	}


	//车贷
	public function car($branch,$st='',$et=''){
		if($st && $et){
			$st=strtotime($st);
			$et=strtotime($et);
		}
		$admins=M("admin")->where(array("branch_id=".$branch))->Field("id")->select();
		foreach ($admins as $k => $val) {
			$aid[]=$val['id'];
		}
		if($branch==33){
			$aid[]=65;
			$aid[]=66;
			$aid[]=127;
			$aid[]=128;
			$aid[]=136;
			$aid[]=118;
			$aid[]=147;
			$aid[]=172;
		}
		$where[]=array("salesman"=>array('in',$aid));
		$where[]=array("state"=>1);
		$cd_id=M("carinfo")->where($where)->Field("id")->select();
		foreach ($cd_id as $k => $val) {
			$cdid[]=$val['id'];
		}

		if($st){
			$where=array("cid"=>array('in',$cdid),"sf_payment"=>1,'starttime'=>array(array('egt',$st),array('elt',$et)));
			$wheres=array("cid"=>array('in',$cdid),"sf_payment"=>2,'starttime'=>array(array('egt',$st),array('elt',$et)));
			$wherey=array("cid"=>array('in',$cdid),'starttime'=>array(array('egt',$st),array('elt',$et)));
		}else{
			$where=array("cid"=>array('in',$cdid),"sf_payment"=>1);
			$wheres=array("cid"=>array('in',$cdid),"sf_payment"=>2);
			$wherey=array("cid"=>array('in',$cdid));
		}


		if($cdid){
				//统计库存
				$cardetails=M("cardetails")->where($where)->Field("id,cid,max(residual_principal) as residual_principal")->group("cid")->select();

				//统计已收利息
				$cardetail=M("cardetails")->where($wheres)->Field("id,cid,sum(interest) as interest")->group("cid")->select();
				//统计应收利息
				$cardetaily=M("cardetails")->where($wherey)->Field("id,cid,sum(interest) as interest")->group("cid")->select();
		}
		
		$car_info=array();
		foreach ($cardetails as $key => $val) {
			$principal +=$val['residual_principal'];
		}
		foreach ($cardetail as $key => $val) {
			$interest +=$val['interest'];
		}
		foreach ($cardetaily as $key => $val) {
			$yslx +=$val['interest'];
		}
		$car_info['principal']=$principal;
		$car_info['interest']=$interest;
		$car_info['yslx']=$yslx;

		return $car_info;
	}

	public function add(){
		$name=$_SESSION['rank_name'];
		if(IS_POST){
			$num=M('profits_new')->add($_POST);
			if($num){
				echo "<script>alert('提交成功')</script>";
			}
		}
		if(strstr($name,'福永')){
			$this->assign('b','1');
		}else if(strstr($name,'南山')){
			$this->assign('b','10');
		}else if(strstr($name,'西乡')){
			$this->assign('b','11');
		}else if(strstr($name,'科技园')){
			$this->assign('b','19');
		}
		$this->display();
	}

	public function branch(){
		$name=$_SESSION['rank_name'];
		if($_GET['y']){
			$year=date("Y");
			$fydata=M('profits_new')->where("branch_id=1 and year={$year}")->select();
			$xxdata=M('profits_new')->where("branch_id=11 and year={$year}")->select();
			$kjydata=M('profits_new')->where("branch_id=19 and year={$year}")->select();
			$nsdata=M('profits_new')->where("branch_id=10 and year={$year}")->select();
			$fy_data=$this->totalCount($fydata);
			$xx_data=$this->totalCount($xxdata);
			$kjy_data=$this->totalCount($kjydata);
			$ns_data=$this->totalCount($nsdata);
		}else{
			$year=date("Y");
			$month=date("m");
			$month=intval($month);
			$fy_data=M('profits_new')->where("branch_id=1 and year={$year} and month={$month}")->find();
			$xx_data=M('profits_new')->where("branch_id=11 and year={$year} and month={$month}")->find();
			$kjy_data=M('profits_new')->where("branch_id=19 and year={$year} and month={$month}")->find();
			$ns_data=M('profits_new')->where("branch_id=10 and year={$year} and month={$month}")->find();
		}

		if(IS_POST){
	        $year=$_POST['year'];
			$bid=$_POST['b'];
			$month=$_POST['month'];
			$bids=explode(',', $bid);
			if(count($bids)>1){
				$fy_data=M('profits_new')->where("branch_id=1 and year={$year} and month={$month}")->find();
				$xx_data=M('profits_new')->where("branch_id=11 and year={$year} and month={$month}")->find();
				$kjy_data=M('profits_new')->where("branch_id=19 and year={$year} and month={$month}")->find();
				$ns_data=M('profits_new')->where("branch_id=10 and year={$year} and month={$month}")->find();

			}else{
				if($bids[0]==1){
					$fy_data=M('profits_new')->where("branch_id={$bids[0]} and year={$year} and month={$month}")->find();
				}else if($bids[0]==11){
					$xx_data=M('profits_new')->where("branch_id={$bids[0]} and year={$year} and month={$month}")->find();
				}else if($bids[0]==19){
					$kjy_data=M('profits_new')->where("branch_id={$bids[0]} and year={$year} and month={$month}")->find();
				}else{
					$ns_data=M('profits_new')->where("branch_id={$bids[0]} and year={$year} and month={$month}")->find();
				}
				
			}
			
		}
		
		$this->assign('fy_data',$fy_data);
		$this->assign('xx_data',$xx_data);
		$this->assign('kjy_data',$kjy_data);
		$this->assign('ns_data',$ns_data);

		if(strstr($name,'福永') && !strstr($name,'福永分部负责人')){
			$this->assign('b','1');
		}
		if(strstr($name,'福永分部负责人') || strstr($name,'最高管理员') || strstr($name,'西乡分部负责人')  || strstr($name,'南山分部负责人')  || strstr($name,'科技园分部负责人')  || strstr($name,'财务部')){
			$this->assign('b','1,10,11,19');
		}
		if(strstr($name,'南山') && !strstr($name,'南山分部负责人')){
			$this->assign('b','10');
		}
		if(strstr($name,'西乡') && !strstr($name,'西乡分部负责人')){
			$this->assign('b','11');
		}
		if(strstr($name,'科技园') && !strstr($name,'科技园分部负责人')){
			$this->assign('b','19');
		}
		$this->assign("month",$_POST['month']);
		$this->display();
	}


	public function totalCount($result){
		$data=array();
		foreach ($result as $k => $val) {
			$data['residence_fee'] +=$val['residence_fee'];
			$data['deposit'] +=$val['deposit'];
			$data['additional_fee'] +=$val['additional_fee'];
			$data['income'] +=$val['income'];
			$data['wages'] +=$val['wages'];
			$data['office_rent'] +=$val['office_rent'];
			$data['other_fee'] +=$val['other_fee'];
			$data['documentary_charges'] +=$val['documentary_charges'];
			$data['residence_total_cost'] +=$val['residence_total_cost'];
			$data['profit'] +=$val['profit'];
			$data['inventory_value'] +=$val['inventory_value'];
			$data['interest_receivable'] +=$val['interest_receivable'];
			$data['interest_rate'] +=$val['interest_rate'];
			$data['commission'] +=$val['commission'];
			$data['penalty'] +=$val['penalty'];
			$data['expense_expenditure'] +=$val['expense_expenditure'];
			$data['net_profit'] +=$val['net_profit'];
			if($data['assignor']){
				$data['assignor'] .=$val['assignor'].';';
			}else{
				$data['assignor']='';
			}
			if($data['xj_assignor']){
				$data['xj_assignor'] .=$val['xj_assignor'].';';
			}else{
				$data['xj_assignor']='';
			}
		}
		return $data;
	}


	function create_xls($data,$filename='simple.xls'){
	    ini_set('max_execution_time', '0');
	    Vendor('PHPExcel.PHPExcel');
	    $filename=str_replace('.xls', '', $filename).'.xls';
	    $phpexcel = new \PHPExcel();
	    $phpexcel->getProperties()
	        ->setCreator("Maarten Balliauw")
	        ->setLastModifiedBy("Maarten Balliauw")
	        ->setTitle("Office 2007 XLSX Test Document")
	        ->setSubject("Office 2007 XLSX Test Document")
	        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
	        ->setKeywords("office 2007 openxml php")
	        ->setCategory("Test result file");
	    $phpexcel->getActiveSheet()->fromArray($data);
	    $phpexcel->getActiveSheet()->setTitle('Sheet1');
	    $phpexcel->setActiveSheetIndex(0);
	    header('Content-Type: application/vnd.ms-excel');
	    header("Content-Disposition: attachment;filename=$filename");
	    header('Cache-Control: max-age=0');
	    header('Cache-Control: max-age=1');
	    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	    header ('Pragma: public'); // HTTP/1.0
	    $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
	    $objwriter->save('php://output');
	    exit;
	}

	public function makexcle(){
		
		$year=$_GET['year'];
		$bid=$_GET['branch'];
		$month=$_GET['month'];
		$data=[];
		$bid=explode(',', $bid);
		$i=0;
		foreach ($bid as $val) {
			$array=array(11=>'西乡分部',10=>'南山分部',19=>'科技园分部',1=>'福永分部');
			$dat=$this->makeExcles($year,$val,$month,$array[$val]);
			foreach ($dat as $k => $val) {
				$data[$i]=$val;
				$i++;
			}
			
		}

		if(count($bid)>1){
			// var_dump($data);exit();
	   		$this->create_xls($data,$filename='所有.xls');
		}else{
			$array=array(11=>'西乡分部',10=>'南山分部',19=>'科技园分部',1=>'福永分部');
	   		$this->create_xls($data,$filename=$array[$bid[0]].'.xls');
		}
	    
	    
	}

	public function makeExcles($year,$bid,$month,$branchName){
		$result=M('profits_new')->where("branch_id={$bid} and year={$year}")->select();
		$data=$this->totalCount($result);
		if($month){
		 $arr=M('profits_new')->where("branch_id={$bid} and year={$year} and month={$month}")->find();
		}

		$datas = array(
		    array($branchName, $year.'年', $month.'月'),
		    array('项目',   '本月金额',   '本年累计'),
		    array('一、居间业务'),
		    array('居间费用收入',   $arr['residence_fee'],  $data['residence_fee']),
		    array('预收客户定金转收入',   $arr['deposit'],  $data['deposit']),
		    array('额外费用收入',   $arr['additional_fee'],  $data['additional_fee']),
		    array('营业收入合计',   $arr['income'],  $data['income']),
		    array('工资及社保',   $arr['wages'],  $data['wages']),
		    array('办公场地租金及水电',   $arr['office_rent'],  $data['office_rent']),
		    array('其他',   $arr['other_fee'],  $data['other_fee']),
		    array('跟单费',   $arr['documentary_charges'],  $data['documentary_charges']),
		    array('居间费用合计',   $arr['residence_total_cost'],  $data['residence_total_cost']),
		    array('利润',   $arr['profit'],  $data['profit']),
		    array('分配人',   $arr['assignor'],  $data['assignor']),
		    array('二、现金业务'),
		    array('库存值',   $arr['inventory_value'],  $data['inventory_value']),
		    array('应收利息',   $arr['interest_receivable'],  $data['interest_receivable']),
		    array('实收利息',   $arr['interest_rate'],  $data['interest_rate']),
		    array('滞纳金、罚息',   $arr['penalty'],  $data['penalty']),
		    array('提成',   $arr['commission'],  $data['commission']),
		    array('费用支出',   $arr['expense_expenditure'],  $data['expense_expenditure']),
		    array('净利润',   $arr['net_profit'],  $data['net_profit']),
		    array('分配人',   $arr['xj_assignor'],  $data['xj_assignor']),
	    );

	    return $datas;
	}

	public function cashBusiness(){
	   $year =I('post.year');
       $branch =I('post.branch');
       $month =I('post.month');
       if(($year%4)>0){
       		//1，3，5，7，8，10，12
       		if($month==1 || $month==3 || $month==5 || $month==7 || $month==8 || $month==10 || $month==12){
       			if($month<10){
			       	$month='0'.$month;
			       }
       			$st = $year.'-'.$month.'-'.'01';
				$et = $year.'-'.$month.'-'.'31';
       		}else if($month==2){
       			$st = $year.'-'.'0'.$month.'-'.'01';
				$et = $year.'-'.'0'.$month.'-'.'28';
       		}else{
       			if($month<10){
			       	$month='0'.$month;
			     }
       			$st = $year.'-'.$month.'-'.'01';
				$et = $year.'-'.$month.'-'.'30';
       		}
       }else{
       		if($month==1 || $month==3 || $month==5 || $month==7 || $month==8 || $month==10 || $month==12){
       			if($month<10){
			       	$month='0'.$month;
			     }
       			$st = $year.'-'.$month.'-'.'01';
				$et = $year.'-'.$month.'-'.'31';
       		}else if($month==2){
       			$st = $year.'-'.'0'.$month.'-'.'01';
				$et = $year.'-'.'0'.$month.'-'.'29';
       		}else{
       			if($month<10){
			       	$month='0'.$month;
			     }
       			$st = $year.'-'.$month.'-'.'01';
				$et = $year.'-'.$month.'-'.'30';
       		}
       }

       $data=$this->xyd($branch,2,$st,$et);
       $this->ajaxReturn($data);
	}

	public function upd(){
		$year=$_GET['year'];
		$month=$_GET['month'];
		$name=$_SESSION['rank_name'];
		if(strstr($name,'福永')){
			$branch=1;
		}else if(strstr($name,'南山')){
			$branch=10;
		}else if(strstr($name,'西乡')){
			$branch=11;
		}else if(strstr($name,'科技园')){
			$branch=19;
		}
		// $branch=11;

		if(IS_POST){
			$num=M('profits_new')->where("id={$_POST['id']}")->save($_POST);
			if($num){
				$this->error("修改成功",'/admin/Finance/branch');
			}else{
				$this->error("修改失败",'/admin/Finance/branch');
			}
		}
		if($branch){
			$arr=M('profits_new')->where("branch_id={$branch} and year={$year} and month={$month}")->find();
		}else{
			echo "<script>alert('你不属于任何分部，无法修改');window.history.back()</script>";
		}
		
		$this->assign('b',$branch);
		$this->assign('month',$_GET['month']);
		$this->assign('arr',$arr);
		$this->display();
	}

	//通过四个部门找业务员
	function memberss(){

		$rid= I("post.rid");
		$arr=M("admin")->alias("a")->field("m.username,a.id")->join("member m ON a.member_id=m.id")->where("a.branch_id ={$rid}")->select();
		echo  $this->ajaxReturn($arr);
	}

	public function statistics(){
		// $_POST['year']$_POST['month']$_POST['branch']$_POST['salesman'];
		if(IS_POST){
			//判断类型
			if($_POST['type']==1){
				$type=1;
				$times='endtime';
			}else{
				$type=2;
				$times='starttime';
			}
			$aids=explode(',', $_POST['ywys']);
			if($_POST['salesman']){
				// $where=array("Salesman"=>$_POST['salesman'],'state'=>1,'type'=>$type);
				$where=array("Salesman"=>$_POST['salesman'],'type'=>$type);
			}else{
				// $where=array("Salesman"=>array('in',$aids),'state'=>1,'type'=>$type);
				$where=array("Salesman"=>array('in',$aids),'type'=>$type);
			}
			$data=M("cash")->where($where)->Field("id,money,name,Salesman")->select();

			foreach ($data as $key => $v) {
				$time=$_POST['year'].'-'.$_POST['month'];
				$where=array("uid"=>$data[$key]['id'],$times=>array('like',"%$time%"));
				$arr=M("Cashdetails")->where($where)->Field('id,endtime,starttime,returninterest,nowinterest,Principal')->find();
				// echo M("Cashdetails")->getlastsql();
				$data[$key]['etime']=$arr["{$times}"];
				$data[$key]['whlx']=$arr['nowinterest'];
				$data[$key]['yhlx']=$arr['returninterest'];
				$data[$key]['s_percentage']=($arr['returninterest']*0.1)*0.7;
				$data[$key]['b_percentage']=($arr['returninterest']*0.1)*0.3;
				//本金余额
				$array=M("Cashdetails")->where("uid={$data[$key]['id']} and repayment=1")->Field('Principal')->select();
				$minpri=array();
				foreach ($array as $k => $v) {
					array_push($minpri, $v['principal']);
				}
				if(min($minpri)){
					$data[$key]['principal']=min($minpri);
				}else{
					$data[$key]['principal']=0;
				}
				//业务员
				$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
				$data[$key]['salesman']=$arrs['0']['username'];
			}
			// echo "<pre>";
			// var_dump(count($data));
			$salesman=[];
			foreach ($data as $k => $val) {
				$salesman["{$val['salesman']}"][]=$data[$k];
			}

			//合计
			$total=[];
			foreach ($salesman as $k => $v) {
				foreach ($v as $key => $val) {
					$total[$k]['tmoney'] +=$val['money'];
					$total[$k]['tprincipal'] +=$val['principal'];
					$total[$k]['twhlx'] +=$val['whlx'];
					$total[$k]['tyhlx'] +=$val['yhlx'];
					$total[$k]['ts_percentage'] +=$val['s_percentage'];
					$total[$k]['tb_percentage'] +=$val['b_percentage'];
				}
			}

			//下载
			if($_POST['down']==1){
				// $datas=[];
				$arr=[];
				foreach ($salesman as $k => $v) {
					array_push($arr,array('','','','',$k));
					array_push($arr,  array(id,'客户姓名','放款金额','本金余额','当月还款时间','应还利息','已还利息','业务员提成','部门提成'));
					foreach ($v as $key => $val) {
				    $info=array($val['id'],$val['name'],$val['money'],$val['principal'],$val['etime'],$val['whlx'],$val['yhlx'],$val['s_percentage'],$val['b_percentage']);
				    array_push($arr,$info);
					}
					$totals=array('合计','',$total[$k]['tmoney'],$total[$k]['tprincipal'],'',$total[$k]['twhlx'],$total[$k]['tyhlx'],$total[$k]['ts_percentage'],$total[$k]['tb_percentage']);
					array_push($arr,$totals);
				}
				// array_push($datas, $arr);
				// echo '<pre>';
				// var_dump($arr);exit();
				$array=array(11=>'西乡分部',10=>'南山分部',19=>'科技园分部',1=>'福永分部');
	   			$this->create_xls($arr,$filename=$array[$_POST['branch']].'.xls');
			}

			$this->assign("salesman",$salesman);
			$this->assign("total",$total);
		}
		$this->display();
	}
	
}

 ?>