<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class IntermediaryController extends CommonController {


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
	   $this->isUclient = "Intermediary";
	}

	//列表查询条件
	public function conditions(){
		if(in_array('1001',$_SESSION['level'])){
		  $kun =  array('id'=>array('gt','0'));
		  
		}else if(in_array('1002',$_SESSION['level'])){
		  //加审批权限的pid
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
				$uid=M("id_apply")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("id_apply")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
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
					$uid=M("id_apply")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("id_apply")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
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
			 }elseif($post['state_id']){
			 	 $where[]=array('state_id'=>$post['state_id']);
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

		$arr=M("id_state")->where("type=1 or type=2")->Field("id")->select();
		foreach ($arr as $k => $v) {
			$arrs[]=$v['id'];
		}
		
		$where[]=array("a.state_id"=>array("in",$arrs));

		$data=M("id_apply")->alias("a")->join("id_state s on a.state_id=s.id")->where($where)->Field('s.id as sid,s.name as sname,s.*,a.*')->order("a.addtime desc")->select();
		foreach ($data as $k => $val) {
			$data[$k]=$val;
			$data[$k]['addtime']=date("Y-m-d H:i:s",$val['addtime']);
			if($val['lxtime']){
			$data[$k]['lxtime']=date("Y-m-d H:i:s",$val['lxtime']);
			}
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$k]['salesman']))->Field('m.username')->find();
				$data[$k]['admin_id']=$arrs['username'];
		}
		
		$this->assign("count",count($data));
		$page=$this->getPage(count($data),20);
		$datas=array_slice($data, $page->firstRow,$page->listRows);
		$this->show=$page->show();
		$this->assign("data",$datas);
		if($_GET['t']){
			$this->display('Intermediary/index_m');
		}else{
			$this->display();
		}
	}

	public function credit(){
		$times=M("id_credit_files")->where("aid=".$_GET['id'])->Field("onetime,twotime,threetime,fourtime,fivetime,sixtime")->find();
		foreach ($times as  $k=>$v) {
			if($v){
				$timess[$k]=date('Y-m-d',$v);
			}
		}
		$this->assign("times",$timess);
		$this->display();
	}
	public function credit_m(){
		$times=M("id_credit_files")->where("aid=".$_GET['id'])->Field("onetime,twotime,threetime,fourtime,fivetime,sixtime")->find();
		foreach ($times as  $k=>$v) {
			if($v){
				$timess[$k]=date('Y-m-d',$v);
			}
		}
		$this->assign("times",$timess);
		$this->display();
	}
	public function mort(){
		$times=M("id_mort_files")->where("aid=".$_GET['id'])->Field("onetime,twotime,threetime,fourtime,fivetime,sixtime,seventime,eighttime,ninetime,tentime,eleventime")->find();
		foreach ($times as  $k=>$v) {
			if($v){
				$timess[$k]=date('Y-m-d',$v);
			}
		}
		$this->assign("times",$timess);
		$this->display();
	}
	public function mort_m(){
		$times=M("id_mort_files")->where("aid=".$_GET['id'])->Field("onetime,twotime,threetime,fourtime,fivetime,sixtime,seventime,eighttime,ninetime,tentime,eleventime")->find();
		foreach ($times as  $k=>$v) {
			if($v){
				$timess[$k]=date('Y-m-d',$v);
			}
		}
		$this->assign("times",$timess);
		$this->display();
	}
	public function ipadSystem(){
		$this->display();
	}

	public function application(){
		if($_GET['id']){
			$id=$_GET['id'];
			$data=M("id_apply")->where("id={$id}")->find();
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data['salesman']))->Field('m.username')->find();
				
			$data['admin_name']=$arrs['username'];
			$data['money']=$data['money']/10000;
			$this->assign('data',$data);
		}

		if(I('get.t')){
			$this->display("Intermediary/application_m");
		}else{
			$this->display();
		}
		
	}

	public function add(){
		// var_dump($_POST);exit();
			if(!empty($_FILES)){
				$upload=new \Think\Upload();
				$upload->maxSize   =     10000000;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
		    	$upload->rootPath  =     './Uploads/'; 
		   		$upload->savePath  =     "Files_photo_jj/";
			}
			$info = $upload->upload();
			if(!$info) {  
				    $this->error($upload->getError());  
				}else{
				    foreach($info as $file){
				        $_POST['photo'] = './Uploads/'.$file['savepath'].$file['savename'];
				    }
			if($_POST['type']==1){
				$_POST['state_id']=2;
			}else{
				$_POST['state_id']=21;
			}
				
			$_POST['money']=$_POST['money']*10000;
			$_POST['addtime']=time();
			$_POST['lxtime']=time();

			$ids=M("id_apply")->add($_POST);

			if($ids>0){
				$files['aid']=$ids;
				$files['onetime']=time();
				if($_POST['type']==1){
					M("id_credit_files")->add($files);
				}else{
					M("id_mort_files")->add($files);
				}
				
				// echo "<script>alert('添加资料成功')</script>";
				$this->success('添加资料成功...', U('Intermediary/index?t=2'));
			}else{
				// echo "<script>alert('添加资料失败')</script>";
				$this->error('添加资料失败...', U('Intermediary/application'));
			}
		} 
	}

	//修改
	public function upd(){
		$data=I('post.');
		$id=I('post.id');
		$data['money']=$data['money']*10000;
		
		if($_FILES['photo']['name']){
			$upload=new \Think\Upload();
			$upload->maxSize   =     10000000;
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_photo_jj/";
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

		$nums=M("id_apply")->where("id={$id}")->save($data);
		if($nums>0){
			if(I('post.t')){
				$this->success('修改成功...', U('/Admin/Intermediary/index/t/2'));
			}else{
				$this->success('修改成功...', U('/Admin/Intermediary/index'));
			}
			
		}else{
			if(I('post.t')){
				$this->error('修改失败...', U('/Admin/Intermediary/index/t/2'));
			}else{
				$this->error('修改失败...', U('/Admin/Intermediary/index'));	
			}
		}
	}

	//删除
	public function del(){
		
		$id=$_GET['id'];
		$photo=M("id_apply")->where("id={$id}")->Field('photo,type')->find();
		$f=$photo['photo'];
		$num=M("id_apply")->where("id={$id}")->delete();
		if($num){
			unlink($f);
			if($photo['type']==1){
				M("id_credit_files")->where("aid={$id}")->delete();
			}else{
				M("id_mort_files")->where("aid={$id}")->delete();
			}
			
			$this->success('删除成功',U('Intermediary/index'));
		}else{
			$this->error('删除失败',U('Intermediary/index'));
		}
	}

	//信用贷贷前资料收集
	public function CreditTwo(){
		$cid=$_GET['id'];
		$data=M("id_credit_files")->where("aid={$cid}")->find();
		
		$datass=M("id_credit_files")->where("aid={$cid}")->Field("id_card,residence_booklet,marriage_certificate,house_certificate,work_proof,flow,b_license,policy,residence_permit,lease_contract,mortgage_contract,social_certificate,hydropower,rent_certificate,inspection_certificate,gjj_certificate")->find();
		
		foreach ($datass as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}

		$sid=M('id_apply')->where('id='.$cid)->getField('state_id');
		$this->assign('str',rtrim($str,','));
		$this->assign('data',$data);
		$this->assign('sid',$sid);
		$this->assign('id',$cid);

		if(I('get.t')){
			$this->display("Intermediary/CreditTwo_m");
		}else{
			$this->display();
		}
	}

	public function qr_info(){
		$cid=$_POST['id'];
		unset($_POST['id']);
		$data=$_POST;
		if($_POST['types']){
			$model=M("id_mort_files");
			if($_POST['t']){
				$types="MortTwo/t/2";
			}else{
				$types="MortTwo";
			}
		}else{
			$model=M("id_credit_files");
			if($_POST['t']){
				$types="CreditTwo/t/2";
			}else{
				$types="CreditTwo";
			}
		}
		if(!empty($_FILES)){
			$upload=new \Think\Upload();
			$upload->maxSize   =     20000000;
			$upload->exts      =     '';
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_dqsj/{$cid}/";
	    	
		    foreach($_FILES as $key=>$value){
	          if(count($_FILES[$key]) == count($_FILES[$key],1)){
	          	if(strstr($key,'id_card')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['id_card'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if($key=="residence_booklet"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['residence_booklet']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if(strstr($key,'marriage_certificate')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['marriage_certificate'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'house_certificate')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['house_certificate'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if(strstr($key,'work_proof')){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['work_proof'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	          		}
	          	}
	          	if($key=="flow"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['flow']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="b_license"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['b_license']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="policy"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['policy']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="residence_permit"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['residence_permit']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="lease_contract"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['lease_contract']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="mortgage_contract"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['mortgage_contract']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="social_certificate"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['social_certificate']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="hydropower"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['hydropower']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="rent_certificate"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['rent_certificate']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="inspection_certificate"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['inspection_certificate']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	          	if($key=="gjj_certificate"){
	          		$info = $upload->uploadOne($_FILES[$key]);
	          		if($info){
	          			$data['gjj_certificate']='./Uploads/'.$info['savepath'].$info['savename'];
	          		}
	          	}
	            
	          }
	        }
		}
		// unset($data['t']);
		//判断当前字段是否有数据，有数据再追加
		foreach ($_FILES as $key => $value) {
			if($data[$key]){
				$data[$key]=rtrim($data[$key],';');
				$zd=$model->where("aid={$cid}")->getField("{$key}");
				if($zd){
					$data[$key]=$zd.';'.$data[$key];
				}
			}
		}
		// var_dump($data);exit();
		$row=$model->where("aid={$cid}")->save($data);
		if($row){
			$this->error("资料保存成功","/Admin/Intermediary/{$types}?id={$cid}");
		}else{
			$this->error("资料保存失败","/Admin/Intermediary/{$types}?id={$cid}");
		}
	}

	public function publicMethod(){
		$cid=$_GET['id'];
		$s=$_GET['f'];
		$data=M("id_credit_files")->where("aid={$cid}")->find();
		$sid=M('id_apply')->where('id='.$cid)->getField('state_id');
		$this->assign('data',$data);
		$this->assign('sid',$sid);
		$this->assign('id',$cid);
		$this->display("Intermediary/{$s}");
	}

	public function public_fun(){
		if(IS_POST){
			$cid=$_POST['id'];
			$step=$_POST['step'];
			unset($_POST['id']);
			unset($_POST['step']);

			$row=M("id_credit_files")->where("aid={$cid}")->save($_POST);
			// var_dump(M("id_credit_files")->getlastsql());echo $row;exit();
			if($row){
				$this->error("资料保存成功","/Admin/Intermediary/publicMethod?f={$step}&id={$cid}");
			}else{
				$this->error("资料保存失败","/Admin/Intermediary/publicMethod?f={$step}&id={$cid}");
			}
		}
	}

	//预览
	public function preview(){
		$id=$_GET['id'];
		$zd=$_GET['zd'];
		if($_GET['t']==1 || $_POST['t']==1){
			$model=M("id_credit_files");
		}else{
			$model=M("id_mort_files");
		}
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
			$num=$model->where("aid={$id}")->save(array("{$zd}"=>$arrs));
			if($num){
				echo "<script>alert('删除成功')</script>";
			}else{
				echo "<script>alert('删除失败')</script>";
			}
		}
		
		$arr=$model->where("aid={$id}")->Field("{$zd}")->find();
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
		$this->assign('t',$_GET['t']);
		$this->assign('cloumnVal',$arr["{$zd}"]);
		$this->display();				
	}

	public function over(){
		$_GET['lxtime']=time();
		$num=M('id_apply')->save($_GET);
		if($_GET['state_id']<15){
			$data[$_GET['s']]=time();
			M('id_credit_files')->where("aid=".$_GET['id'])->save($data);
		}else{
			$data[$_GET['s']]=time();
			M('id_mort_files')->where("aid=".$_GET['id'])->save($data);
		}
		if($_GET['t']){
			$t="?t=2";
		}
		if($num){
			$this->success("已完成。。。", U("/Admin/Intermediary/index{$t}"));
		}else{
			$this->success("未完成。。。", U("/Admin/Intermediary/index{$t}"));
		}
	}


	//抵押贷前资料收集
	public function MortTwo(){
		$cid=$_GET['id'];
		$data=M("id_mort_files")->where("aid={$cid}")->find();
		
		$datass=M("id_mort_files")->where("aid={$cid}")->Field("id_card,residence_booklet,marriage_certificate,house_certificate,work_proof,flow,b_license,policy,residence_permit,lease_contract,mortgage_contract,social_certificate,hydropower,rent_certificate,inspection_certificate,gjj_certificate")->find();
		
		foreach ($datass as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}
		
		$sid=M('id_apply')->where('id='.$cid)->getField('state_id');
		$this->assign('str',rtrim($str,','));
		$this->assign('data',$data);
		$this->assign('sid',$sid);
		$this->assign('id',$cid);
		if(I('get.t')){
			$this->display("Intermediary/MortTwo_m");
		}else{
			$this->display();
		}
	}

	// public function M_info(){
	// 	$cid=$_POST['id'];
	// 	unset($_POST['id']);
	// 	$data=$_POST;
		
	// 	if(!empty($_FILES)){
	// 		$upload=new \Think\Upload();
	// 		$upload->maxSize   =     20000000;
	// 		$upload->exts      =     '';
	//     	$upload->rootPath  =     './Uploads/'; 
	//    		$upload->savePath  =     "Files_dqsj/{$cid}/";
	    	
	// 	    foreach($_FILES as $key=>$value){
	//           if(count($_FILES[$key]) == count($_FILES[$key],1)){
	//           	if(strstr($key,'m_card')){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['m_card'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	//           		}
	//           	}
	//           	if($key=="yz_card"){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['yz_card']='./Uploads/'.$info['savepath'].$info['savename'];
	//           		}
	//           	}
	//           	if(strstr($key,'spouse_card')){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['spouse_card']='./Uploads/'.$info['savepath'].$info['savename'].';';
	//           		}
	//           	}
	//           	if(strstr($key,'house_certificate')){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['house_certificate']='./Uploads/'.$info['savepath'].$info['savename'].';';
	//           		}
	//           	}
	//           	if(strstr($key,'credit_file')){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['credit_file'] .='./Uploads/'.$info['savepath'].$info['savename'].';';
	//           		}
	//           	}
	//           	if($key=="flow"){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['flow']='./Uploads/'.$info['savepath'].$info['savename'];
	//           		}
	//           	}
	//           	if($key=="b_license"){
	//           		$info = $upload->uploadOne($_FILES[$key]);
	//           		if($info){
	//           			$data['b_license']='./Uploads/'.$info['savepath'].$info['savename'];
	//           		}
	//           	}	            
	//           }
	//         }
	// 	}
	// 	// unset($data['t']);
	// 	//判断当前字段是否有数据，有数据再追加
	// 	foreach ($_FILES as $key => $value) {
	// 		if($data[$key]){
	// 			$data[$key]=rtrim($data[$key],';');
	// 			$zd=M("id_mort_files")->where("aid={$cid}")->getField("{$key}");
	// 			if($zd){
	// 				$data[$key]=$zd.';'.$data[$key];
	// 			}
	// 		}			
	// 	}
	// 	$row=M("id_mort_files")->where("aid={$cid}")->save($data);
	// 	if($row){
	// 		$this->error("资料保存成功","/Admin/Intermediary/MortTwo?id={$cid}");
	// 	}else{
	// 		$this->error("资料保存失败","/Admin/Intermediary/MortTwo?id={$cid}");
	// 	}
	// }

	//抵押
	public function mPublicMethod(){
		$cid=$_GET['id'];
		$s=$_GET['t'];
		$data=M("id_mort_files")->where("aid={$cid}")->find();
		$sid=M('id_apply')->where('id='.$cid)->getField('state_id');
		$strings=M("id_mort_files")->where("aid={$cid}")->Field("sl_form,authentic_act,repayment_certificate,cancellation_letter,letter_mortgage,lending_water,batch_letter")->find();
		foreach ($strings as $k => $val) {
			if($val){
				$str .=$k.',';
			}
		}
		$this->assign('str',rtrim($str,','));
		$this->assign('data',$data);
		$this->assign('sid',$sid);
		$this->assign('id',$cid);
		$this->display("Intermediary/{$s}");
	}

	//抵押
	public function mPublicFun(){
		if(IS_POST || $_FILES){
			//six
			if($_POST['fair_information']){
				foreach ($_POST['fair_information'] as $val) {
					$str .=$val.',';
				}
				$_POST['fair_information'] = rtrim($str,',');
			}
			//seven
			if($_POST['sl_information']){
				foreach ($_POST['sl_information'] as $val) {
					$string .=$val.',';
				}
				$_POST['sl_information'] = rtrim($string,',');
			}
			//eight
			if($_POST['preparation_information']){
				foreach ($_POST['preparation_information'] as $val) {
					$strs .=$val.',';
				}
				$_POST['preparation_information'] = rtrim($strs,',');
			}
			//nine
			if($_POST['mortgage_information']){
				foreach ($_POST['mortgage_information'] as $val) {
					$strss .=$val.',';
				}
				$_POST['mortgage_information'] = rtrim($strss,',');
			}
			//上传图片
			if($_FILES['sl_form'] || $_FILES['authentic_act'] || $_FILES['repayment_certificate'] || $_FILES['cancellation_letter'] || $_FILES['letter_mortgage'] || $_FILES['lending_water'] || $_FILES['batch_letter']){
				foreach ($_FILES as $k => $v) {
					$zd=$k;
				}
				$_POST["{$zd}"]=$this->upImg($zd,$_POST['id']);
			}
			$cid=$_POST['id'];
			$step=$_POST['step'];
			unset($_POST['id']);
			unset($_POST['step']);
			$row=M("id_mort_files")->where("aid={$cid}")->save($_POST);
			if($row){
				$this->error("资料保存成功","/Admin/Intermediary/mPublicMethod?t={$step}&id={$cid}");
			}else{
				$this->error("资料保存失败","/Admin/Intermediary/mPublicMethod?t={$step}&id={$cid}");
			}
		}
	}

	public function upImg($zd,$id){
		$upload=new \Think\Upload();
		$upload->maxSize   =     10000000;
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
    	$upload->rootPath  =     './Uploads/'; 
   		$upload->savePath  =     "Files_dyd/{$id}/";
		$info = $upload->upload();
		if(!$info) {  
			   return false;
			}else{
			    foreach($info as $file){  
			        $imgUrl = './Uploads/'.$file['savepath'].$file['savename']; 
			    } 
			}
		$zd=M("id_mort_files")->where("aid={$id}")->getField("{$zd}");
		if($zd){
			$imgUrl=$zd.';'.$imgUrl;
		}
		return 	$imgUrl;
	}
	
}