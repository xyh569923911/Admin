<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class CarLoanController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isUclient = "CarLoan";
	}
	// public function _initialize()
	// {   
	// 	$this->huanc = $this->getRealSize($this->getDirSize('./Application/Runtime'));
	// 	$level = M("admin")->where(array("id"=>session("aid")))->find();
	// 	$this->admin_level = $level;
	// 	$level = M("rank")->where(array("id"=>$level['level_id']))->getField("level");
	// 	$this->levels = explode(",",$level);
	// 	$this->level_y = $this->admin_arr();
	// 	$this->base = M("base")->where(array("id"=>3))->field("time")->find();

	// 	$this->isUclient = "CarLoan";
	// }

	//列表查询条件
	public function conditions(){
		
		if(in_array('1001',$_SESSION['level'])){
		  $kun =  array('id'=>array('gt','0'));
		}else if(in_array('1002',$_SESSION['level'])){
		   $arr=M("rank")->where("name='{$_SESSION['rank_name']}'")->Field("id,pid")->find();
		  if(strpos($_SESSION['rank_name'],'负责人')){
		  	$ids=M("rank")->where("pid={$arr['id']}")->getField("id",true);
		  	$ywyid=M("rank")->where(array('pid'=>array('in',$ids)))->getField("id",true);
		  	$ids[]=$arr['id'];
		  	foreach ($ywyid as $value) {
		  		array_push($ids, $value);
		  	}
		  }else if(strpos($_SESSION['rank_name'],'总监')){
		  	$ids=M("rank")->where("pid={$arr['id']}")->getField("id",true);
		  	$ids[]=$arr['id'];
		  }else{
		  	$ids=M("rank")->where("pid={$arr['pid']}")->getField("id",true);
		  }
		  
		  $u_id=M("admin")->where(array("level_id"=>array("in",$ids)))->getField("id",true);
		  $kun = array("salesman"=>array("in",$u_id));
		}else{
			$kun= array("salesman"=>$_SESSION['aid']);
		}

		return $kun;
	}


	public function index(){

		$kun=$this->conditions();
		//添加搜索条件
		$post = I('get.');
		$stime = strtotime($post['stime']." 00:00:00");
		$endtime = strtotime($post['endtime']." 24:00:00");
		if(IS_GET){
			if($post['stime'] && $post['endtime'] && $post['sou']){

				$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
				foreach ($aid as $value) {
					$aids[]=$value["id"];
				}
				$uid=M("carloan")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("carloan")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["a.id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["a.id"]=array("in",$cardid);
					}
				$where[]=array("a.addtime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['stime'] && $post['endtime'] && $post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(127,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	
			  	$where['salesman']=array("in",$adimds);
			  	$where[]=array("a.addtime"=>array(array("gt",$stime),array('elt',$endtime)));

			  }elseif($post['stime'] && $post['endtime']){
			  	$where[]=array("a.addtime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(127,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$where['salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("carloan")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("carloan")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["a.id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["a.id"]=array("in",$cardid);
					}else{
							$where["a.id"]=array("elt",0);
					}
					// $where[]=$kun;
			 }elseif($post['yqkh']){
			 	 $where[]=array('Latefee'=>array("gt",0));
			 }
		}
		
		foreach ($kun as $k => $val) {
			if($k=='id'){
				unset($kun['id']);
				$kun['a.id']=$val;
			}
		}
		$where[] = $kun;
		// var_dump($kun);		
		//添加搜索条件结束
		
		$arr=M("state")->where("type=5")->Field("id")->select();
		foreach ($arr as $k => $v) {
			$arrs[]=$v['id'];
		}
		$where[]=array("a.state_id"=>array("in",$arrs));

		$data=M("carloan")->alias("a")->join("state s on a.state_id=s.id")->where($where)->Field('s.id as sid,s.name as sname,s.addtime as saddtime,s.*,a.*')->order("a.addtime desc")->select();

		foreach ($data as $k => $val) {
			$data[$k]=$val;
			$data[$k]['addtime']=date("Y-m-d H:i:s",$val['addtime']);
			$sman[]=$val['salesman'];
		}
		
		if(!empty($sman)){
			$array=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>array('in',$sman)))->Field('m.username,a.id')->select();
		}
		
		foreach ($data as $key => $value) {
			 foreach ($array as $k => $v){
			  	if($value['salesman']==$v['id']){
			 		$data[$key]['salesman']=$v['username'];
			 	}
			  }
		}
		// var_dump($data);
		$this->assign("data",$data);

		$this->assign("count",count($data));
		$this->display();
	}

	
	public function listCustomer(){
		$kun=$this->conditions();
		$wheres[]=array("state"=>1);
		//添加搜索条件
		$post = I('get.');
		$stime = strtotime($post['stime']." 00:00:00");
		$endtime = strtotime($post['endtime']." 24:00:00");
		if(IS_GET){
			if($post['stime'] && $post['endtime'] && $post['sou']){

				$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
				foreach ($aid as $value) {
					$aids[]=$value["id"];
				}
				$uid=M("carloan")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("carloan")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$wheres['salesman']=array("in",$aids);
					}elseif(count($uid)){
							$wheres["uid"]=array("in",$uid);
					}elseif(count($cardid)){
							$wheres["uid"]=array("in",$cardid);
					}
				$wheres[]=array("stime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['stime'] && $post['endtime'] && $post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(127,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	
			  	$wheres['salesman']=array("in",$adimds);
			  	$wheres[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));

			  }elseif($post['stime'] && $post['endtime']){
			  	$wheres[]=array("stime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(127,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$wheres['salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("carloan")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("carloan")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$wheres['salesman']=array("in",$aids);
					}elseif(count($uid)){
							$wheres["uid"]=array("in",$uid);
					}elseif(count($cardid)){
							$wheres["uid"]=array("in",$cardid);
					}else{
							$wheres["id"]=array("elt",0);
					}
					// $where[]=$kun;
			 }elseif($post['yq_state']){
			 	 $wheres[]=array('yq_state'=>2);
			 }

		}
		//添加搜索条件结束
		
		$wheres[] = $kun;
		$data=M("carinfo")->where($wheres)->order("id desc")->select();
		
		foreach ($data as $key => $val) {
			$arr[$key]=$val;
			$arr[$key]['stime']=date('Y-m-d',$val["stime"]);
			$arr[$key]['etime']=date('Y-m-d',$val["etime"]);
			$uids[]=$val['uid'];
			$sman[]=$val['salesman'];
			//获取本金余额id
			$cids[]=$val['id'];
		}
		
		if(!empty($uids) && !empty($sman)){
			$names=M("carloan")->where(array("id"=>array("in",$uids)))->Field("username,id")->select();
			$array=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>array('in',$sman)))->Field('m.username,a.id')->select();
		}
		if($cids){
			$Cardetails=M("Cardetails")->where(array("cid"=>array("in",$cids),'starttime'=>array("elt",time())))->Field("min(residual_principal) as residual_principal,cid")->group('cid')->select();
			$edtimes=M("Cardetails")->where(array("cid"=>array("in",$cids),'sf_payment'=>1))->Field("endtime,cid")->group('cid')->select();
			// echo M("Cardetails")->getlastsql();
			// var_dump($Cardetails);
		}
		
		foreach ($arr as $key => $value) {
			$arrs[$key]=$value;
			 foreach ($names as $k => $val) {
			 	if($value['uid']==$val['id']){
			 		$arrs[$key]['name']=$val['username'];
			 	}
			 }

			 foreach ($array as $k => $v){
			  	if($value['salesman']==$v['id']){
			 		$arrs[$key]['salesman']=$v['username'];
			 	}
			  }

			  foreach ($Cardetails as $k => $v){
			  	if($value['id']==$v['cid']){
			 		$arrs[$key]['residual_principal']=$v['residual_principal'];
			 	}
			  }

			  foreach ($edtimes as $k => $v){
			  	if($value['id']==$v['cid']){
			 		if(time()>$v['endtime']){
			 			$timediff = time()-$v['endtime'];
						$arrs[$key]['yqday'] = intval($timediff/86400);
						M("carinfo")->where("id=".$value['id'])->save(array("yq_state"=>2));
			 		}else{
			 			$arrs[$key]['yqday']=0;
			 			M("carinfo")->where("id=".$value['id'])->save(array("yq_state"=>1));
			 		}
			 	}
			  }
		}

		foreach ($arrs as $k => $val) {
			$jine +=$val['jine'];
			$principal +=$val['residual_principal'];
 		}
 		// var_dump(count($arrs));
 		//排序
 		for ($i=0; $i < count($arrs); $i++) { 
 			for ($j=(count($arrs)-1); $j >$i ; $j--) { 
 				if($arrs[$j]['yqday']>$arrs[$j-1]['yqday']){
 					$temp=$arrs[$j-1];
 					$arrs[$j-1]=$arrs[$j];
 					$arrs[$j]=$temp;
 				}
 			}
 		}
 		// var_dump($arrs(1.1.2.3.5));


		$count=count($arrs);
        $Page= $this->getPage($count,20);
        $this->show= $Page->show();
        $datas = array_slice($arrs,$Page->firstRow,$Page->listRows);

		$this->assign("cashinfo",$datas);
		$this->assign("jine",$jine);
		$this->assign("principal",$principal);
		$this->display();
	}

	public function apply(){

		if($_GET['id']){
			$id=$_GET['id'];
			$arr=M("carloan")->find($id);
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where("a.id=".$arr['salesman'])->Field('m.username')->find();
			$arr['salesmans']=$arrs['username'];

			$this->assign('data',$arr);
		}
		$this->display();

	}

	public function add(){
		$_POST['state_id']=26;
		$_POST['addtime']=time();
		$_POST['cohabitation']=implode(',', $_POST['cohabitation']);
		$num=M("carloan")->add($_POST);
		if($num){
			M("carfiles")->add(array('cid'=>$num));
			$this->success("添加成功......", '/Admin/CarLoan/index');
		}else{
			$this->error("添加失败......", '/Admin/CarLoan/apply');
		}
	}

	public function info_upd(){
		// $id=$_POST['id'];
		$_POST['cohabitation']=implode(',', $_POST['cohabitation']);
		
		$num=M("carloan")->save($_POST);
		if($num){
			$this->success("更新成功......", '/Admin/CarLoan/index');
		}else{
			$this->error("更新失败......", '/Admin/CarLoan/index');
		}
	}

	
	public function carone(){
		
		if($_FILES){
			$this->assign('ziduan',$_POST['zd']);
			if($_FILES["{$_POST['zd']}"]['name']){
				$id=$_POST['id'];
				$upload=new \Think\Upload();
				$upload->maxSize   =     10000000;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
		    	$upload->rootPath  =     './Uploads/'; 
		   		$upload->savePath  =     "Files_car/{$id}/";
				$info = $upload->upload();
				if(!$info) {  
					    $this->error($upload->getError());  
					}else{
					    foreach($info as $file){
					        $datas["{$_POST['zd']}"] = './Uploads/'.$file['savepath'].$file['savename']; 
					    } 
					}
				$row=M("carfiles")->where("cid={$id}")->save($datas);
				if($row){
					echo "<script>alert('资料保存成功');window.location.href='/Admin/CarLoan/carone?id={$id}'</script>";
				}else{
					// $this->error("资料保存成功","/Admin/Apply/yewuone?id={$id}");
					echo "<script>alert('资料保存失败');window.location.href='/Admin/CarLoan/carone?id={$id}'</script>";
				}
			}else{
				$this->error("请上传资料");
			}
		}

		if(IS_POST){
			if($_POST['evaluate'] && $_POST['money']){
				$arr['evaluate']=$_POST['evaluate'];
				$arr['money']=$_POST['money'];
				$id=$_POST['id'];
				$row=M("carfiles")->where("cid={$id}")->save($arr);
				if($row){
						echo "<script>alert('资料保存成功');window.location.href='/Admin/CarLoan/carone?id={$id}'</script>";
					}else{
						// $this->error("资料保存成功","/Admin/Apply/yewuone?id={$id}");
						echo "<script>alert('资料保存失败');window.location.href='/Admin/CarLoan/carone?id={$id}'</script>";
					}
			}
		}

		//显示预览按钮
		$data=M("carfiles")->where("cid={$_GET['id']}")->Field("qxb,chethree,firstcar,evaluate,money")->find();
		foreach ($data as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}
		$this->assign('money',$data['money']);
		$this->assign('evaluate',$data['evaluate']);
		$this->assign('str',rtrim($str,','));
		$this->display();
	}


	public function cartwo(){
		
		if($_FILES){
			$this->assign('ziduan',$_POST['zd']);
			if($_FILES["{$_POST['zd']}"]['name']){
				$id=$_POST['id'];
				$upload=new \Think\Upload();
				$upload->maxSize   =     10000000;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
		    	$upload->rootPath  =     './Uploads/'; 
		   		$upload->savePath  =     "Files_car/{$id}/";
				$info = $upload->upload();
				if(!$info) {  
					    $this->error($upload->getError());  
					}else{
					    foreach($info as $file){
					        $datas["{$_POST['zd']}"] = './Uploads/'.$file['savepath'].$file['savename']; 
					    } 
					}
				$row=M("carfiles")->where("cid={$id}")->save($datas);
				if($row){
					echo "<script>alert('资料保存成功');window.location.href='/Admin/CarLoan/cartwo?id={$id}'</script>";
				}else{
					// $this->error("资料保存成功","/Admin/Apply/yewuone?id={$id}");
					echo "<script>alert('资料保存失败');window.location.href='/Admin/CarLoan/cartwo?id={$id}'</script>";
				}
			}else{
				$this->error("请上传资料");
			}
		}

		
		//显示预览按钮
		$data=M("carfiles")->where("cid={$_GET['id']}")->Field("idcard,hukou,license,bankcard,creditbook,bankwater,incomeprove,jzprove,mcertificate,dlicense,greenbook,policybook,corporate_credit,corporate_water,clicense")->find();
		foreach ($data as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}
		
		$this->assign('str',rtrim($str,','));
		$this->display();
	}

	//预览
	public function preview(){
		$id=$_GET['id'];
		$zd=$_GET['zd'];
		$arr=M("carfiles")->where("cid={$id}")->Field("{$zd}")->find();
		if($arr["{$zd}"]){
			// $imgurl='http://47.92.119.237'.ltrim($arr["{$zd}"],'.');
			$imgurl='http://www.crm.com'.ltrim($arr["{$zd}"],'.');
			echo "<center><img align='left' src=".$imgurl."></center>";
		}else{
			echo "<script>alert('没有找到你要的资料');window.close();</script>";
		}		
	}

	//完成
	public function over(){

		$num=M('carloan')->save($_GET);
		if($num){
			$this->success("已完成。。。", U('/Admin/CarLoan/index'));
		}else{
			$this->success("未完成。。。", U('/Admin/CarLoan/index'));
		}
	}


	public function Carsix(){

		if(IS_POST){
			// var_dump($_POST['cid']);
			$caf['uid']=$_POST['cid'];
			$caf['jine']=$_POST['spje'];
			$caf['znum']=count($_POST['newarr']);
			$caf['stime']=strtotime($_POST['fkrq']." 00:00:00");
			$caf['etime']=strtotime($_POST['jkdq']." 00:00:00");
			$caf['branch']=$_POST['bumen'];
			$caf['salesman']=$_POST['members'];
			$caf['repayment_rate']=$_POST['hklv'];
			$caf['state']=1;
			$caf['cbj_type']=$_POST['cbj_type'];
			$cno=M("carinfo")->add($caf);
			if($cno){
			$data=array();
			$j=0;
			if($_POST['newarr']){
				foreach ($_POST['newarr'] as $key => $val) {
					$data[$key]['cid']=$cno;
					$data[$key]['num']=$val[0];
					$data[$key]['principal']=$val[1];
					if(count($_POST['newarr'])==12){
						$data[$key]['interest']=$val[2]-100;
						$data[$key]['service_charge']=100;
						$data[$key]['bond']=0;
						$data[$key]['platform_fwf']=0;
					}else{
						$data[$key]['interest']=$val[2]-600;
						$data[$key]['service_charge']=100;
						$data[$key]['bond']=300;
						$data[$key]['platform_fwf']=200;
					}
					$data[$key]['repayment']=$val[3];

					if($j==0){
						$data[$key]['residual_principal']=$_POST['fbje'];
					}else{
						$data[$key]['residual_principal']=$data[$j-1]['residual_principal']-$data[$j-1]['principal'];
					}

					$data[$key]['costprice']=$_POST['cbj'];
					$data[$key]['revenue']=$data[$key]['repayment']-$data[$key]['costprice'];
					$data[$key]['bond_money']=$data[$key]['interest']+$data[$key]['service_charge']+$data[$key]['residual_principal']+$_POST['fbje']*0.01;
					
					$j++;
				}

				$i=1;
			foreach ($data as $k => $val) {
				if($i==1){
					// $val["starttime"]= $_POST['fkrq'];
					$val["starttime"]=strtotime($_POST['fkrq']." 00:00:00");
					$starttimes=$_POST['fkrq'];
				}else{
					// $val["starttime"]=$cftime;
					$val["starttime"]=strtotime($cftime." 00:00:00");
					$starttimes=$cftime;
				}
				$arr=explode("-", $starttimes);
				if($arr['1']==4 || $arr['1']==6 ||$arr['1']==9 || $arr['1']==11){
						if(($arr['2']-1)==0){
							//防止月份前多出现0
							$arr['1']=$arr['1']+0;
							$arr['2']=30;
						}else{
							$arr['1']=$arr['1']+1;
							$arr['2']=$arr['2']-1;
						}
					
				}else if($arr['1']==2){
						if(($arr['2']-1)==0  &&  $arr['1']==2 && $arr['0']%4==0){
							//防止月份前多出现0
							$arr['1']=$arr['1']+0;
							$arr['2']=29;
						}else if(($arr['2']-1)==0  &&  $arr['1']==2){
							$arr['1']=$arr['1']+0;
							$arr['2']=28;
						}else{
							$arr['1']=$arr['1']+1;
							$arr['2']=$arr['2']-1;
						}
						
				}else{
					//1,3,5,7,8,10,12
					if($arr['1']==12){
						if(($arr['2']-1)==0){
							// $arr['1']--;
							$arr['1']=$arr['1']+0;
							$arr['2']=31;
						}else{
							$arr['0']=$arr['0']+1;
							$arr['1']=1;
							$arr['2']=$arr['2']-1;
						}
					}else{

						if(($arr['2']-1)==0){
							// $arr['1']--;
							$arr['1']=$arr['1']+0;
							$arr['2']=31;
						}else{
							$arr['1']=$arr['1']+1;
							$arr['2']=$arr['2']-1;
						}

					}
					
				}
				$arr['1']=$arr['1']<10?'0'.$arr['1']:$arr['1'];
				$arr['2']=$arr['2']<10?'0'.$arr['2']:$arr['2'];
				$endtime=implode($arr, "-");

					// $val["endtime"]=$endtime
					$val["endtime"]=strtotime($endtime." 00:00:00");
					$val["sf_payment"]=1;
					$row=M("cardetails")->add($val);
					
					$arrs=explode('-', $endtime);

					$arrs['1']=$arrs['1']<10?$arrs['1']['1']:$arrs['1'];
					$arrs['2']=$arrs['2']<10?$arrs['2']['1']:$arrs['2'];
					
					if($arrs['1']==4 || $arrs['1']==6 ||$arrs['1']==9 || $arrs['1']==11){
						if($arrs['2']==30){
							$arrs['1']++;
							$arrs['2']=1;
						}else{
							$arrs['2']++;
						}
					
					}else if($arrs['1']==1 || $arrs['1']==3 || $arrs['1']==5 || $arrs['1']==7 || $arrs['1']==8 || $arrs['1']==10){
						if($arrs['2']==31){
							$arrs['1']++;
							$arrs['2']=1;
						}else{
							$arrs['2']++;
						}

					}else if($arrs['2']==29 && $arrs['0']%4==0 && $arrs['1']==2){
						$arrs['1']=3;
						$arrs['2']=1;
					}else if($arrs['2']==28 && $arrs['1']==2){
						$arrs['1']=3;
						$arrs['2']=1;
					}else if($arrs['1']==12 && $arrs['2']==31){
						$arrs['0']++;
						$arrs['1']=1;
						$arrs['2']=1;
					}else{
						$arrs['2']++;
					}
					$arrs['1']=$arrs['1']<10?'0'.$arrs['1']:$arrs['1'];
					$arrs['2']=$arrs['2']<10?'0'.$arrs['2']:$arrs['2'];
					$cftime=implode($arrs, "-");

					$i++;
				}

				if($row){
					echo '放款成功';
				}else{
					echo '放款失败';
				}

			}else{
					echo '请先计算';
			}
		  }
		}else{
			$this->display();
		}
		
	}

	public function edit(){
		$id=$_GET['id'];
		$data=M("cardetails")->where("cid={$id}")->select();
		$rement=M("car_reimbursement")->where("cid={$id}")->select();
		$info=M("carloan")->alias("c")->join("carinfo as f on c.id=f.uid ")->where("f.id={$id}")->Field("username,cardnum,repayment_rate,jine")->find();
		//跟进信息
		$records=M("xj_record")->where("c_id={$id} and type = 2")->order('addtime desc')->select();
		$count=count($records);//得到数组元素个数
		 $Page= $this->getPages($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		 $records = array_slice($records,$Page->firstRow,$Page->listRows);
		 $this->show= $Page->show();


		$this->assign("details",$data);
		$this->assign("records",$records);
		$this->assign("rement",$rement);
		$this->assign("ids",$id);
		$this->assign("info",$info);
		$this->display();
	}

	//获取业务员
	public function memberss(){
		$rid= I("post.rid");

		$arr=M("admin")->alias("a")->field("m.username,a.id")->join("member m ON a.member_id=m.id")->where("a.level_id ={$rid}")->select();

		echo  $this->ajaxReturn($arr);

	}

	//还款
	public function upd(){
		
		$arr=M("cardetails")->where("cid={$_POST['id']} and sf_payment=1")->select();
		// var_dump($arr[0]['bond_money']);exit();
		if($arr){
			$yhk=$arr[0]['repayment'];
			$hkje=$_POST["hkje"];
			foreach ($arr as $key => $val) {
				$arrs[]=$val['id'];
			}

			$infos=array();
			$infos["cid"]=$_POST['id'];
			$infos["time"]=time();
			$infos["money"]=$hkje;
			$infos["returnlf"]=0;
			
			if($hkje==$arr[0]['bond_money']){

				$data['sf_payment']=2;
				$num=M("cardetails")->where("cid=".$_POST['id'])->save($data);
				if($num){
					//插入明细表
					M("car_reimbursement")->add($infos);
					echo  "还款成功";
				}

			}elseif($hkje==$yhk){
				
				$cah['sf_payment']=2;
				$num=M("cardetails")->where("id=".$arr[0]['id'])->save($cah);
				if($num){
					//插入明细表
					M("car_reimbursement")->add($infos);
					echo "还款成功";
				}

			}elseif(($hkje/$yhk)>1){

				//插入明细表
				M("car_reimbursement")->add($infos);

				$cars['sf_payment']=2;

				$num=M("cardetails")->where(array('num'=>array('between',array($arr[0]['num'],$arr[0]['num']+($hkje/$yhk)-1)),'cid'=>$_POST['id']))->save($cars);
				echo "还款成功";
			}else{
				echo "请输对金额";
			}

		}else{
			echo "没有需要还的贷款";
		}
	}

	public function updstate(){
		$id= I("post.id");
		$arr=M("cardetails")->where("cid={$id} and sf_payment=1")->select();
		if($arr){
			echo 3;
			exit();
		}
		$data['state']=2;
		$num=M('carinfo')->where("id={$id}")->save($data);
		if($num){
			echo 1;
		}else{
			echo 2;
		}
	}

	//结清页面
	public function settle(){
		$kun=$this->conditions();
		$where=array("state"=>2);
		//添加搜索条件
		$post = I('get.');
		$stime = strtotime($post['stime']." 00:00:00");
		$endtime = strtotime($post['endtime']." 24:00:00");
		if(IS_GET){
			if($post['stime'] && $post['endtime'] && $post['sou']){

				$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
				foreach ($aid as $value) {
					$aids[]=$value["id"];
				}
				$uid=M("carloan")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("carloan")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$wheres['salesman']=array("in",$aids);
					}elseif(count($uid)){
							$wheres["uid"]=array("in",$uid);
					}elseif(count($cardid)){
							$wheres["uid"]=array("in",$cardid);
					}
				$wheres[]=array("stime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['stime'] && $post['endtime'] && $post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(127,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	
			  	$wheres['salesman']=array("in",$adimds);
			  	$wheres[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));

			  }elseif($post['stime'] && $post['endtime']){
			  	$wheres[]=array("stime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(127,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$wheres['salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("carloan")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("carloan")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$wheres['salesman']=array("in",$aids);
					}elseif(count($uid)){
							$wheres["uid"]=array("in",$uid);
					}elseif(count($cardid)){
							$wheres["uid"]=array("in",$cardid);
					}else{
							$wheres["id"]=array("elt",0);
					}
					// $where[]=$kun;
			 }elseif($post['yqkh']){
			 	 $wheres[]=array('Latefee'=>array("gt",0));
			 }

		}
		//添加搜索条件结束
		
		$where[] = $kun;
		// var_dump($where);
		$data=M("carinfo")->where($where)->order("id desc")->select();
		foreach ($data as $key => $val) {
			$arr[$key]=$val;
			$arr[$key]['stime']=date('Y-m-d',$val["stime"]);
			$arr[$key]['etime']=date('Y-m-d',$val["etime"]);
			$uids[]=$val['uid'];
			$sman[]=$val['salesman'];
		}

		if($uids){
				$names=M("carloan")->where(array("id"=>array("in",$uids)))->Field("username,id")->select();
				
				$array=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>array('in',$sman)))->Field('m.username,a.id')->select();
				
				foreach ($arr as $key => $value) {
					$arrs[$key]=$value;
					 foreach ($names as $k => $val) {
					 	if($value['uid']==$val['id']){
					 		$arrs[$key]['name']=$val['username'];
					 	}
					 }
					 foreach ($array as $k => $v){
					  	if($value['salesman']==$v['id']){
					 		$arrs[$key]['salesman']=$v['username'];
					 	}
					  }
				}
			}
		$this->assign("cashinfo",$arrs);
		$this->display();
	}

	
	public function record(){
		$data['c_id']=I('get.id');
		$data['content']=I('get.content');
		$data['admin_name']=session("aname");
		$data['addtime']=time();
		$data['type']=2;
		$num=M('xj_record')->add($data);
		if($num){
			echo 1;
		}else{
			echo 2;
		}
	}

	public function dels(){
		$id=I('get.id');
		$num=M("carinfo")->where("id={$id}")->delete();
		if($num){
			$nums=M("cardetails")->where("cid={$id}")->delete();
			M("xj_record")->where("c_id={$id} and type =2")->delete();
			if($nums){
				$this->success('删除成功',U('CarLoan/listCustomer'));	
			}else{
				$this->error('删除失败',U('CarLoan/listCustomer'));	
			}
		}
	}

	public function del(){
		
		$id=$_GET['id'];
		$num=M("carloan")->where("id={$id}")->delete();
		if($num){
				$ids=M("carinfo")->where("uid={$id}")->Field('id')->find();
				if($ids){
					$carid=$ids['id'];
					M("cardetails")->where("cid={$carid}")->delete();
					M("car_reimbursement")->where("cid={$carid}")->delete();
					M("xj_record")->where("c_id={$carid} and type =2")->delete();
				}
				
				M("carfiles")->where("cid={$id}")->delete();
				M("carinfo")->where("uid={$id}")->delete();
				
				$this->success('删除成功',U('CarLoan/index'));
			}else{
				$this->error('删除失败',U('CarLoan/index'));
			}
	}


	public function settlement(){
		$id=$_GET['id'];
		$dhgl=M("carinfo")->where("uid={$id} and state=1")->select();
		$data=M("carinfo")->where("uid={$id} and state=2")->select();
		$arr=array();
		foreach ($data as $key => $value) {
			array_push($arr, $value['salesman']);
		}
		if($arr){
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>array('in',$arr)))->Field('m.username,a.id')->select();
			
			foreach ($data as $key => $value) {
				
				foreach ($arrs as $k => $v) {
					if($value['salesman']==$v['id']){
						$data[$key]['admins']=$v['username'];
					}
				}
			}
		}
		
		if($data && empty($dhgl)){
			$_GET['state_id']=33;
			M('carinfo')->save($_GET);
		}else if($data){
			$_GET['state_id']=32;
			M('carinfo')->save($_GET);
		}

		$this->assign('data',$data);
		$this->display();
	}

	public function modify(){
		$id=$_GET['id'];
		$data=M('carinfo')->find($id);
		$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where("a.id=".$data['salesman'])->Field('m.username')->find();
		$data['salesmans']=$arrs['username'];
		$data['jine']=round($data['jine'],2);
		// var_dump($data);
		$this->assign('id',$id);
		$this->assign('data',$data);
		$this->display();
	}

	public function modify_upd(){
		$id=$_POST['id'];
		$caf['jine']=$_POST['spje'];
		$caf['znum']=count($_POST['newarr']);
		$caf['stime']=strtotime($_POST['fkrq']." 00:00:00");
		$caf['etime']=strtotime($_POST['jkdq']." 00:00:00");
		$caf['branch']=$_POST['bumen'];
		$caf['salesman']=$_POST['members'];
		$caf['repayment_rate']=$_POST['hklv'];
		$caf['cbj_type']=$_POST['cbj_type'];
		$cno=M("carinfo")->where("id={$id}")->save($caf);
		if($cno){
		$data=array();
		$j=0;
		if($_POST['newarr']){
			M("cardetails")->where("cid={$id}")->delete();
			foreach ($_POST['newarr'] as $key => $val) {
				$data[$key]['cid']=$id;
				$data[$key]['num']=$val[0];
				$data[$key]['principal']=$val[1];
				if(count($_POST['newarr'])==12){
					$data[$key]['interest']=$val[2]-100;
					$data[$key]['service_charge']=100;
					$data[$key]['bond']=0;
					$data[$key]['platform_fwf']=0;
				}else{
					$data[$key]['interest']=$val[2]-600;
					$data[$key]['service_charge']=100;
					$data[$key]['bond']=300;
					$data[$key]['platform_fwf']=200;
				}
				$data[$key]['repayment']=$val[3];

				if($j==0){
					$data[$key]['residual_principal']=$_POST['fbje'];
				}else{
					$data[$key]['residual_principal']=$data[$j-1]['residual_principal']-$data[$j-1]['principal'];
				}

				$data[$key]['costprice']=$_POST['cbj'];
				$data[$key]['revenue']=$data[$key]['repayment']-$data[$key]['costprice'];
				$data[$key]['bond_money']=$data[$key]['interest']+$data[$key]['service_charge']+$data[$key]['residual_principal']+$_POST['fbje']*0.01;
				
				$j++;
			}

			$i=1;
		foreach ($data as $k => $val) {
			if($i==1){
				// $val["starttime"]= $_POST['fkrq'];
				$val["starttime"]=strtotime($_POST['fkrq']." 00:00:00");
				$starttimes=$_POST['fkrq'];
			}else{
				// $val["starttime"]=$cftime;
				$val["starttime"]=strtotime($cftime." 00:00:00");
				$starttimes=$cftime;
			}
			$arr=explode("-", $starttimes);
			if($arr['1']==4 || $arr['1']==6 ||$arr['1']==9 || $arr['1']==11){
					if(($arr['2']-1)==0){
						//防止月份前多出现0
						$arr['1']=$arr['1']+0;
						$arr['2']=30;
					}else{
						$arr['1']=$arr['1']+1;
						$arr['2']=$arr['2']-1;
					}
				
			}else if($arr['1']==2){
					if(($arr['2']-1)==0  &&  $arr['1']==2 && $arr['0']%4==0){
						//防止月份前多出现0
						$arr['1']=$arr['1']+0;
						$arr['2']=29;
					}else if(($arr['2']-1)==0  &&  $arr['1']==2){
						$arr['1']=$arr['1']+0;
						$arr['2']=28;
					}else{
						$arr['1']=$arr['1']+1;
						$arr['2']=$arr['2']-1;
					}
					
			}else{
				//1,3,5,7,8,10,12
				if($arr['1']==12){
					if(($arr['2']-1)==0){
						// $arr['1']--;
						$arr['1']=$arr['1']+0;
						$arr['2']=31;
					}else{
						$arr['0']=$arr['0']+1;
						$arr['1']=1;
						$arr['2']=$arr['2']-1;
					}
				}else{

					if(($arr['2']-1)==0){
						// $arr['1']--;
						$arr['1']=$arr['1']+0;
						$arr['2']=31;
					}else{
						$arr['1']=$arr['1']+1;
						$arr['2']=$arr['2']-1;
					}

				}
				
			}
			$arr['1']=$arr['1']<10?'0'.$arr['1']:$arr['1'];
			$arr['2']=$arr['2']<10?'0'.$arr['2']:$arr['2'];
			$endtime=implode($arr, "-");

				// $val["endtime"]=$endtime
				$val["endtime"]=strtotime($endtime." 00:00:00");
				$val["sf_payment"]=1;
				$row=M("cardetails")->add($val);
				
				$arrs=explode('-', $endtime);

				$arrs['1']=$arrs['1']<10?$arrs['1']['1']:$arrs['1'];
				$arrs['2']=$arrs['2']<10?$arrs['2']['1']:$arrs['2'];
				
				if($arrs['1']==4 || $arrs['1']==6 ||$arrs['1']==9 || $arrs['1']==11){
					if($arrs['2']==30){
						$arrs['1']++;
						$arrs['2']=1;
					}else{
						$arrs['2']++;
					}
				
				}else if($arrs['1']==1 || $arrs['1']==3 || $arrs['1']==5 || $arrs['1']==7 || $arrs['1']==8 || $arrs['1']==10){
					if($arrs['2']==31){
						$arrs['1']++;
						$arrs['2']=1;
					}else{
						$arrs['2']++;
					}

				}else if($arrs['2']==29 && $arrs['0']%4==0 && $arrs['1']==2){
					$arrs['1']=3;
					$arrs['2']=1;
				}else if($arrs['2']==28 && $arrs['1']==2){
					$arrs['1']=3;
					$arrs['2']=1;
				}else if($arrs['1']==12 && $arrs['2']==31){
					$arrs['0']++;
					$arrs['1']=1;
					$arrs['2']=1;
				}else{
					$arrs['2']++;
				}
				$arrs['1']=$arrs['1']<10?'0'.$arrs['1']:$arrs['1'];
				$arrs['2']=$arrs['2']<10?'0'.$arrs['2']:$arrs['2'];
				$cftime=implode($arrs, "-");

				$i++;
			}

			if($row){
				echo '操作成功';
			}else{
				echo '操作失败';
			}

		}else{
				echo '请先计算';
		}
	  }
	}

	public function upd_cbj(){
		$info=I('post.infos');
		$sum=0;
		foreach ($info as $k => $val) {
			$arr=explode('?', $val);
			$num=M('cardetails')->where("cid=".$arr[0])->save(array("costprice"=>$arr[1],"revenue"=>($arr[2]-$arr[1])));
			$sum +=$num;
		}

		if($sum>0){
			echo "成功更新".$sum."条数据";
		}else{
			echo "更新失败";
		}
	}

	
}

 ?>