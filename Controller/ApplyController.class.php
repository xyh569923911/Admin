<?php 
namespace Admin\Controller;
use Think\Controller;
use \Org\Util\Data;

class ApplyController extends CommonController{


	protected $model;

	
	public function _initialize()
	{   
		$this->huanc = $this->getRealSize($this->getDirSize('./Application/Runtime'));
		$level = M("admin")->where(array("id"=>session("aid")))->find();
		$this->admin_level = $level;
		$level = M("rank")->where(array("id"=>$level['level_id']))->getField("level");
		$this->levels = explode(",",$level);
		$this->level_y = $this->admin_arr();
		$this->base = M("base")->where(array("id"=>3))->field("time")->find();

		// $this->isUclient = "Monetary";
		$this->isUclient = "Apply";

	}

	public function index(){
		
		$this->display();
	}

	//获取业务员
	public function memberss(){
		$rid= I("post.rid");

		$arr=M("admin")->alias("a")->field("m.username,a.id")->join("member m ON a.member_id=m.id")->where("a.level_id ={$rid}")->select();
		
		echo  $this->ajaxReturn($arr);

	}

	public function add(){
			if(!empty($_FILES)){
				$upload=new \Think\Upload();
				$upload->maxSize   =     10000000;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
		    	$upload->rootPath  =     './Uploads/'; 
		   		$upload->savePath  =     "Files_photo/";
			}
			$info = $upload->upload();
			if(!$info) {  
				    $this->error($upload->getError());  
				}else{
				    foreach($info as $file){  
				        $adata['photo'] = './Uploads/'.$file['savepath'].$file['savename'];
				        $_POST['photo'] = './Uploads/'.$file['savepath'].$file['savename'];
				    }

				$_POST['state_id']=17;
				$_POST['money']=$_POST['money']*10000;
				$_POST['addtime']=time();
				$ids=M("applys")->add($_POST);

				if($ids>0){
					$files['aid']=$ids;
					M("cashfiles")->add($files);
					// echo "<script>alert('添加资料成功')</script>";
					$this->success('添加资料成功...','http://47.92.119.237/Admin/Monetary/ipadSystem');
				}else{
					// echo "<script>alert('添加资料失败')</script>";
					$this->error('添加资料失败...', U('Apply/index'));
				}
			} 
	}


	public function selectphone(){
		$phone=I('get.phone');
		
		$num=M('Uclient')->where("phone={$phone}")->select();

		if($num){
			echo 1;
		}
	}

	public function upd(){
		$data=I('post.');
		$id=I('post.id');
		$data['money']=$data['money']*10000;
		
		if($_FILES['photo']['name']){
			$upload=new \Think\Upload();
			$upload->maxSize   =     10000000;
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_photo/{$id}/";
			$info = $upload->upload();
			if(!$info) {  
				    $this->error($upload->getError());  
				}else{
				    foreach($info as $file){  
				        $data['photo'] = './Uploads/'.$file['savepath'].$file['savename']; 
				    } 
				    unlink($data['oldphoto']);
				}
		}else{
			unset($data['photo']);
		}

		$nums=M("Applys")->where("id={$id}")->save($data);
		if($num>0 || $nums>0){
			if($_POST['t']){
					$this->success('修改成功...', U('/Admin/Monetary/listCustomer/t/1'));
				}else{
					$this->success('修改成功...', U('/Admin/Monetary/listCustomer'));
				}
		}else{
			if($_POST['t']){
					$this->error('修改失败...', U('/Admin/Monetary/listCustomer/t/1'));
				}else{
					$this->error('修改失败...', U('/Admin/Monetary/listCustomer'));
				}
		}
	}

	public function yewuall(){
		$this->display();
	}


	public function yewuone(){
		
		if($_FILES){
			$this->assign('ziduan',$_POST['zd']);
			if($_FILES["{$_POST['zd']}"]['name']){
				$id=$_POST['id'];
				unset($_POST['id']);
				$upload=new \Think\Upload();
				$upload->maxSize   =     10000000;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
		    	$upload->rootPath  =     './Uploads/'; 
		   		$upload->savePath  =     "Files_xjzlqr/{$id}/";
				$info = $upload->upload();
				if(!$info) {  
					    $this->error($upload->getError());  
					}else{
					    foreach($info as $file){  
					        $datas["{$_POST['zd']}"] = './Uploads/'.$file['savepath'].$file['savename']; 
					    } 
					}
				//追加
				$zd=M("cashfiles")->where("aid={$id}")->getField("{$_POST['zd']}");
				if($zd){
					$datas["{$_POST['zd']}"]=$zd.';'.$datas["{$_POST['zd']}"];
				}
				$row=M("cashfiles")->where("aid={$id}")->save($datas);
				if($row){
					if($_POST['t']){
							echo "<script>alert('资料保存成功');window.location.href='/Admin/Apply/yewuone/t/1?id={$id}'</script>";
						}else{
							echo "<script>alert('资料保存成功');window.location.href='/Admin/Apply/yewuone?id={$id}'</script>";
						}
				}else{
					if($_POST['t']){
							echo "<script>alert('资料保存失败');window.location.href='/Admin/Apply/yewuone/t/1?id={$id}'</script>";
						}else{
							echo "<script>alert('资料保存失败');window.location.href='/Admin/Apply/yewuone?id={$id}'</script>";
						}
				}
			}else{
				$this->error("请上传资料");
			}
		}

		$datass=M("cashfiles")->where("aid={$_GET['id']}")->Field("qg_law,sz_law,qcc,qxb,house_monitor,sl,lj,lyj,folk_inquiries")->find();
		$data=M("cashfiles")->where("aid={$_GET['id']}")->find();
		foreach ($datass as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}
		$this->assign('data',$data);
		$this->assign('str',rtrim($str,','));
		$sid=M('applys')->where('id='.$_GET['id'])->getField('state_id');
		$this->assign('sid',$sid);
		$this->assign('id',$_GET['id']);
		if($_GET['t']){
			$this->display("Apply/yewuones");
		}else{
			$this->display();
		}
	}
	//预览5ac470326a963
	public function preview(){
		$id=$_GET['id'];
		$zd=$_GET['zd'];
		//删除图片
		if(IS_POST){
			// var_dump($_POST);exit();
			$id=$_POST['id'];
			$zd=$_POST['zd'];
			$cloumnVal=$_POST['cloumnVal'];
			$arrs=explode(';', $cloumnVal);
			foreach ($_POST['imgs'] as $val) {
				unlink($arrs[$val]);
				unset($arrs[$val]);
			}
			$arrs=implode($arrs,';');
			$num=M("cashfiles")->where("aid={$id}")->save(array("{$zd}"=>$arrs));
			if($num){
				echo "<script>alert('删除成功')</script>";
			}else{
				echo "<script>alert('删除失败')</script>";
			}
		}
		
		$arr=M("cashfiles")->where("aid={$id}")->Field("{$zd}")->find();
		if(strstr($arr["{$zd}"],'.docx') || strstr($arr["{$zd}"],'.doc') || strstr($arr["{$zd}"],'.xlsx') || strstr($arr["{$zd}"],'.xls') || strstr($arr["{$zd}"],'.xlt') || strstr($arr["{$zd}"],'.xlsm') || strstr($arr["{$zd}"],'.csv') || strstr($arr["{$zd}"],'.rar') || strstr($arr["{$zd}"],'.zip') || strstr($arr["{$zd}"],'.tar') || strstr($arr["{$zd}"],'.cab') || strstr($arr["{$zd}"],'.7z')){

			$url="http://47.92.119.237".$arr["{$zd}"];
			echo "<script>window.location.href='".$url."';setTimeout(function(){window.close();},5000)</script>";
			exit();
		}
		if($arr["{$zd}"]){
			$arrs=explode(';', $arr["{$zd}"]);
			$array=[];
			foreach ($arrs as $v) {
				array_push($array, ltrim($v,'.'));
			}
		}else{
			echo "<script>alert('没有找到你要的资料');window.close();</script>";
		}
		$this->assign('num',count($array));
		$this->assign('arrs',$array);
		$this->assign('zd',$zd);
		$this->assign('id',$id);
		$this->assign('cloumnVal',$arr["{$zd}"]);
		$this->display();				
	}

	public function yewuoneadd(){
		if(IS_POST){
			$id=$_POST['id'];
			unset($_POST['id']);
			// var_dump($_POST);exit();
			$row=M("cashfiles")->where("aid={$id}")->save($_POST);
			if($row){
				if($_POST['t']){
							echo "<script>alert('资料保存成功');window.location.href='/Admin/Apply/yewuone/t/1?id={$id}'</script>";
						}else{
							echo "<script>alert('资料保存成功');window.location.href='/Admin/Apply/yewuone?id={$id}'</script>";
						}
				}else{
					if($_POST['t']){
							echo "<script>alert('资料保存失败');window.location.href='/Admin/Apply/yewuone/t/1?id={$id}'</script>";
						}else{
							echo "<script>alert('资料保存失败');window.location.href='/Admin/Apply/yewuone?id={$id}'</script>";
						}
			}
		}
	}

	public function downloadpic(){
		$id=$_GET['id'];
		$row=M('cashfiles')->where("aid={$id}")->Field('qg_law,sz_law,qcc,qxb,house_monitor,sl,lj,lyj')->find();
		
		// $pic ='http://www.crm.com/'.ltrim($row['photo'],'./');
		// $pic="http://www.jiakaodashi.com/ocr/images/hero-intro-pic-ocr.png";
		// $pic1="http://www.jiakaodashi.com/ocr/images/hero-intro-pic-ocr.png";
		// $arr=array($pic,$pic1);
		foreach ($row as $val) {
			if($val){
				$vals='http://47.92.119.237'.$val;
				$img.="<img src=".$vals.">";
			}
		}
				$filename=iconv('utf-8','gb2312',$id);
				header('pragma:public');  
				header('Content-type:application/vnd.ms-word;charset=utf-8;name="'.$filename.'".doc');  
				header("Content-Disposition:attachment;filename=$filename.doc");
				$html = '<html xmlns:o="urn:schemas-microsoft-com:office:office"  
				xmlns:w="urn:schemas-microsoft-com:office:word"  
				xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>"'.$img.'"';
				echo $html.'</html>';
	} 

	public function over(){
		$_GET['lxtime']=time();
		$num=M('applys')->save($_GET);
		if($num){
			if($_GET['t']){
				$this->success("已完成。。。", U('/Admin/Monetary/listCustomer/t/1'));
			}else{
				$this->success("已完成。。。", U('/Admin/Monetary/listCustomer'));
			}
		}else{
			if($_GET['t']){
				$this->success("未完成。。。", U('/Admin/Monetary/listCustomer/t/1'));
			}else{
				$this->success("未完成。。。", U('/Admin/Monetary/listCustomer'));
			}
		}
	}

	public function yewutwo(){
		$cid=$_GET['id'];
		$data=M("cashfiles")->where("aid={$cid}")->find();
		// var_dump($data);
		$datass=M("cashfiles")->where("aid={$_GET['id']}")->Field("id_card,yz_card,poids_cards,house_certificate,credit,flow,b_license")->find();
		
		foreach ($datass as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}
		
		$this->assign('str',rtrim($str,','));
		$this->assign('data',$data);
		$sid=M('applys')->where('id='.$_GET['id'])->getField('state_id');
		$this->assign('sid',$sid);
		$this->assign('id',$_GET['id']);
		if($_GET['t']){
			$this->display("Apply/yewutwos");
		}else{
			$this->display();
		}
		
	}

	public function qr_info(){
		
		$str=implode(',',$_POST['certificates']);
		$data['certificates']=$str;
		$cid=$_POST['id'];
		foreach ($_POST as $key => $val) {
			if($key != 'id' && $key != 'certificates'){
				$data[$key]=$val;
			}
		}
		if(!empty($_FILES)){
			$upload=new \Think\Upload();
			$upload->maxSize   =     20000000;
			$upload->exts      =     '';
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_xjzlqr/{$_POST['id']}/";
	    	
		    foreach($_FILES as $key=>$value){
	          if(count($_FILES[$key]) == count($_FILES[$key],1)){
	          	if(strstr($key,'credit')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['credit'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if($key=="flow"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['flow']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="housing"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['housing']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="liabilities"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['liabilities']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if(strstr($key,'house_certificate')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['house_certificate'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'id_card')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['id_card'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'poids_cards')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['poids_cards'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if($key=="b_license"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['b_license']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="yz_card"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['yz_card']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	            
	          }
	        }
		}
		unset($data['t']);
		// foreach ($data as $key => $value) {
		foreach ($_FILES as $key => $value) {
			if($data[$key]){
				$data[$key]=rtrim($data[$key],';');
				$zd=M("cashfiles")->where("aid={$cid}")->getField("{$key}");
				if($zd){
					$data[$key]=$zd.';'.$data[$key];
				}
			}			
		}
		$row=M("cashfiles")->where("aid={$cid}")->save($data);
		if($row){
			if($_POST['t']){
				$this->error("资料保存成功","/Admin/Apply/yewutwo/t/1?id={$cid}");
			}else{
				$this->error("资料保存成功","/Admin/Apply/yewutwo?id={$cid}");
			}
			
		}else{
			if($_POST['t']){
				$this->error("资料保存失败","/Admin/Apply/yewutwo/t/1?id={$cid}");
			}else{
				$this->error("资料保存失败","/Admin/Apply/yewutwo?id={$cid}");
			}
		}
	}

	public function yewuthree(){
		$cid=$_GET['id'];
		$data=M("cashfiles")->where("aid={$cid}")->find();
		$this->assign('data',$data);
		$sid=M('applys')->where('id='.$_GET['id'])->getField('state_id');
		$this->assign('sid',$sid);
		$this->assign('id',$_GET['id']);
		if($_GET['t']){
			$this->display("Apply/yewuthrees");
		}else{
			$this->display();
		}
	}

	public function qrfy(){
		$row=M("cashfiles")->where("aid={$_POST['cid']}")->save($_POST);
		if($row){
			if($_POST['t']){
				$this->error("资料保存成功","/Admin/Apply/yewuthree/t/1?id={$_POST['cid']}");
			}else{
				$this->error("资料保存成功","/Admin/Apply/yewuthree?id={$_POST['cid']}");
			}			
		}else{
			if($_POST['t']){
				$this->error("资料保存失败","/Admin/Apply/yewuthree/t/1?id={$_POST['cid']}");
			}else{
				$this->error("资料保存失败","/Admin/Apply/yewuthree?id={$_POST['cid']}");
			}	
		}
	}

	public function yewufour(){
		$id=$_GET['id'];
		$this->assign('id',$id);
		$this->display();
	}

	public function administrate(){
		$id=$_GET['id'];
		$data=M("cash")->where("aid={$id} and state=1")->select();
		$yjq=M("cash")->where("aid={$id} and state=2")->select();
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

		//index开始
		foreach ($data as $key => $val) {
			
			//滞纳金
			$latefee=M("Cash")->Field('money,returnlaf')->find($data[$key]['id']);
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']} and repayment=1")->order('id asc')->Field('id,endtime')->select();	
			$sum=0;
			foreach ($arr as $k => $val) {
				$stime=strtotime($arr[$k]['endtime']);
				$etime=time();
				if(strtotime($arr[$k+1]['endtime'])>0){
					if(strtotime($arr[$k+1]['endtime'])>$etime){
						if($stime>0){
							if($etime>$stime){
								$timediff = $etime-$stime;
								$days = intval($timediff/86400);
							}else{
								 $days=0;
							}
						}else{
							$days=0;
						}
					}else{
						$timediff = strtotime($arr[$k+1]['endtime'])-$stime;
						$days = intval($timediff/86400);
					}
				}else{
					if($etime>$stime){
						$timediff = $etime-$stime;
						$days = intval($timediff/86400);
					}else{
						 $days=0;
					}
				}
			
				$sum+=($days*$latefee['money']*0.003);

			}
			//滞纳金暂时关闭
			// $dataznj['Latefee']=$sum-$latefee['returnlaf'];
			$dataznj['Latefee']=0;

			M("Cash")->where("id={$data[$key]['id']}")->save($dataznj);

			//滞纳金暂时关闭
			// $data[$key]['latefee']=$sum-$latefee['returnlaf'];
			$data[$key]['latefee']=0;
			//滞纳金暂时关闭
			//逾期天数
			// $data[$key]['days']=$days;
			$data[$key]['days']=0;
			//应还利息跟未还利息
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']}")->order('id asc')->limit(1)->Field('id,endtime,returninterest,nowinterest')->select();
			// var_dump($arrs[1]);
			$time=date("Y-m-d",time());
			$times=explode('-', $time);
			// var_dump($times[1]);
			foreach ($arr as $k => $v) {
				$arrs=explode('-', $v['endtime']);
				if($arrs[1]==$times[1]){
					$data[$key]['whlx']=$v['nowinterest']-$v['returninterest'];
					$data[$key]['yhlx']=$v['returninterest'];
					break;
				}
			}
		}
		
		//统计数据
		foreach ($data as $k => $v) {
			$twhlx+=$v['whlx'];
			$tyhlx+=$v['yhlx'];
			$tmoney+=$v['money'];
			$tfee+=$v['fee'];
			$tmargin+=$v['margin'];
			$taccumulative+=$v['accumulative'];
			$tlatefee+=$v['latefee'];
		}
		//index结束
		if(empty($data) && !empty($yjq)){
			$_GET['state_id']=25;
			$_GET['lxtime']=time();
			M('applys')->save($_GET);
		}
		
		$this->assign("twhlx",$twhlx);
		$this->assign("tyhlx",$tyhlx);
		$this->assign("tmoney",$tmoney);
		$this->assign("tfee",$tfee);
		$this->assign("tmargin",$tmargin);
		$this->assign("taccumulative",$taccumulative);
		$this->assign("tlatefee",$tlatefee);
		$this->assign('data',$data);
		$this->display();
	}

	public function settlement(){
		$id=$_GET['id'];
		$dhgl=M("cash")->where("aid={$id} and state=1")->select();
		$data=M("cash")->where("aid={$id} and state=2")->select();
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
			$_GET['state_id']=25;
			$_GET['lxtime']=time();
			M('applys')->save($_GET);
		}else if($data){
			$_GET['lxtime']=time();
			$_GET['state_id']=24;
			M('applys')->save($_GET);
		}

		$this->assign('data',$data);
		$this->display();
	}

	public function personInfo(){
		if(IS_POST){
			$flag=false;
			$phone=$_POST['phone'];
			//车贷
			$cid=M('carloan')->where("phone={$phone}")->Field('id,username')->find();
			if($cid){
				$carlist=M("carinfo")->where("uid={$cid['id']}")->select();
				if($carlist){
					$flag=true;
					foreach ($carlist as $key => $value) {
							$carlist[$key]['username']=$cid['username'];
					}
					$this->assign('carlist',$carlist);
				}
			}

			//现金
			$aid=M('applys')->where("phone={$phone}")->Field('id,name')->find();
			if($aid){
				$moneylist=M("cash")->where("aid={$aid['id']}")->select();
				if($moneylist){
					$flag=true;
					foreach ($moneylist as $key => $value) {
							$moneylist[$key]['username']=$aid['name'];
					}
					$this->assign('moneylist',$moneylist);
				}
			}
			if(!$flag){
				$info="该手机号没有相关的贷款信息";
				$this->assign("info",$info);
			}
		}
		$this->display();
	}

	public function persondetails(){
		$id=$_POST['id'];
		$type=$_POST['type'];
		if($type>0){
			$result=M("cashdetails")->where("uid={$id}")->select();
			foreach ($result as $key => $value) {
				if($value['repayment']==1){
					$result[$key]['repayment']='未还';
				}else{
					$result[$key]['repayment']='已还';
				}
			}
		}else{
			$result=M("cardetails")->where("cid={$id}")->select();
			foreach ($result as $key => $value) {
				$result[$key]['starttime']=date("Y-m-d",$result[$key]['starttime']);
				$result[$key]['endtime']=date("Y-m-d",$result[$key]['endtime']);

				if($value['sf_payment']==1){
					$result[$key]['sf_payment']='未还';
				}else{
					$result[$key]['sf_payment']='已还';
				}
			}
		}
		// var_dump($result);
		$this->ajaxReturn($result);
	}


	public function investigate(){
		if(IS_POST){
			if(!empty($_FILES)){
			$upload=new \Think\Upload();
			$upload->maxSize   =     10000000;
			$upload->exts      =     '';//不限制类型
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_kczl/{$_POST['id']}/";
	    	
		    foreach($_FILES as $key=>$value){
	          if(count($_FILES[$key]) == count($_FILES[$key],1)){
	          	if(strstr($key,'house_photo')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['house_photo1'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if($key=="data_supplement"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['data_supplement']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if(strstr($key,'company_photo')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['company_photo'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	            
	          }
	        }
			}

			$aid=$_POST['id'];
			unset($_POST['id']);
			if($_POST['house_photo1']){
				$_POST['house_photo1']=rtrim($_POST['house_photo1'],';');
			}
			if($_POST['company_photo']){
				$_POST['company_photo']=rtrim($_POST['company_photo'],';');
			}
			$row=M("cashfiles")->where("aid={$aid}")->save($_POST);
			if($row){
				if($_POST['t']){
						$this->error("资料保存成功","/Admin/Apply/investigate/t/1?id={$aid}");
					}else{
						$this->error("资料保存成功","/Admin/Apply/investigate?id={$aid}");
					}
			}else{
				if($_POST['t']){
						$this->error("资料保存失败","/Admin/Apply/investigate/t/1?id={$aid}");
					}else{
						$this->error("资料保存失败","/Admin/Apply/investigate?id={$aid}");
					}
			}
		}else{

			$datass=M("cashfiles")->where("aid={$_GET['id']}")->Field("house_photo1,data_supplement,company_photo")->find();

			$data=M("cashfiles")->where("aid={$_GET['id']}")->find();
			if($data['salesmans']){
				$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where("a.id=".$data['salesmans'])->Field('m.username')->find();
				$data['salesmanss']=$arrs['username'];
			}

			foreach ($datass as $k => $val) {
				if($val){
					$str .=$k.',';
				}
			}

			$this->assign('str',rtrim($str,','));
			$this->assign('data',$data);

		}

		$sid=M('applys')->where('id='.$_GET['id'])->getField('state_id');
		$id=$_GET['id'];
		$this->assign('sid',$sid);
		$this->assign('id',$id);
		if($_GET['t']){
			$this->display("Apply/investigates");
		}else{
			$this->display();
		}
	}

	public function loan_approval(){
		if(IS_POST){
			// var_dump($_POST);
			$aid=$_POST['id'];
			unset($_POST['id']);
			$row=M("cashfiles")->where("aid={$aid}")->save($_POST);
			if($row){
				if($_POST['t']){
					$this->error("资料保存成功","/Admin/Apply/loan_approval/t/1?id={$aid}");
				}else{
					$this->error("资料保存成功","/Admin/Apply/loan_approval?id={$aid}");
				}
			}else{
				if($_POST['t']){
					$this->error("资料保存失败","/Admin/Apply/loan_approval/t/1?id={$aid}");
				}else{
					$this->error("资料保存失败","/Admin/Apply/loan_approval?id={$aid}");
				}
			}
		}else{
			$data=M("cashfiles")->where("aid={$_GET['id']}")->find();
			$this->assign('data',$data);
		}

		$sid=M('applys')->where('id='.$_GET['id'])->getField('state_id');
		$this->assign('sid',$sid);
		$this->assign('id',$_GET['id']);
		if($_GET['t']){
			$this->display("Apply/loan_approvals");
		}else{
			$this->display();
		}
	}

	public function sign(){
		if(IS_POST){
			if(!empty($_FILES)){
			$upload=new \Think\Upload();
			$upload->maxSize   =     10000000;
			$upload->exts      =     '';//不限制类型
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_sign/{$_POST['id']}/";
	    	
		    foreach($_FILES as $key=>$value){
	          if(count($_FILES[$key]) == count($_FILES[$key],1)){
	          	if(strstr($key,'loan_contract')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['loan_contract'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'iou')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['iou'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'receipt')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['receipt'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'letter_commitment')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['letter_commitment'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'mail_list')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['mail_list'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'sign_scene')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$_POST['sign_scene'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	            
	          }
	        }
			}
			$aid=$_POST['id'];
			unset($_POST['id']);
			foreach ($_POST as $key => $value) {
				$_POST[$key]=rtrim($value,';');
			}
			$row=M("cashfiles")->where("aid={$aid}")->save($_POST);
			if($row){
				if($_POST['t']){
					$this->error("资料保存成功","/Admin/Apply/sign/t/1?id={$aid}");
				}else{
					$this->error("资料保存成功","/Admin/Apply/sign?id={$aid}");
				}
			}else{
				if($_POST['t']){
					$this->error("资料保存失败","/Admin/Apply/sign/t/1?id={$aid}");
				}else{
					$this->error("资料保存失败","/Admin/Apply/sign?id={$aid}");
				}
			}
		}else{

			$datass=M("cashfiles")->where("aid={$_GET['id']}")->Field("loan_contract,iou,receipt,letter_commitment,mail_list,sign_scene")->find();

			$data=M("cashfiles")->where("aid={$_GET['id']}")->find();

			foreach ($datass as $k => $val) {
				if($val){
					$str .=$k.',';
				}
			}

			$this->assign('str',rtrim($str,','));
			$this->assign('data',$data);
		}
		
		$sid=M('applys')->where('id='.$_GET['id'])->getField('state_id');
		$this->assign('sid',$sid);
		$this->assign('id',$_GET['id']);
		if($_GET['t']){
			$this->display("Apply/signs");
		}else{
			$this->display();
		}
	}

	public function fxzs(){
		$id=$_GET['id'];
		$risk=$_GET['risk'];
		$data=M("applys")->Field("name,cardid,phone,sex,money")->find($id);
		$data['risk']=$risk;
		// $message='';
		// if($risk<25) {
		// 	$message="可以放款";
		// }else if(25<$risk<=50){
		// 	$message="可以适当放款";
		// }
		
		// $data['message']=$message;

		$this->assign('data',$data);
		$this->display();
	}

	public function wecatinfo(){
		$this->display();
	}

}

 ?>