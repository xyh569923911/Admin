<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class MonetaryController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isUclient = "Uclient";
		$this->model = M("Uclient");
	}
	
	public function index(){
		$data=M("Cash")->where("type=1")->order("id desc")->select();
		foreach ($data as $key => $val) {
			$arr=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
			$data[$key]['salesman']=$arr['0']['username'];
		}
		$count=count($data);
        $Page= $this->getPage($count,20);
        $this->show= $Page->show();
        $datas = array_slice($data,$Page->firstRow,$Page->listRows);

        $this->assign("count",$count);
		$this->assign("cashinfo",$datas);

		$this->display();
	}
	public function indexsd(){
		$data=M("Cash")->where("type=2")->order("id desc")->select();
		foreach ($data as $key => $val) {
			$arr=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
			$data[$key]['salesman']=$arr['0']['username'];
		}
		$count=count($data);
        $Page= $this->getPage($count,20);
        $this->show= $Page->show();
        $datas = array_slice($data,$Page->firstRow,$Page->listRows);

        $this->assign("count",$count);
		$this->assign("cashinfo",$datas);

		$this->display();
	}

	public function yewu(){
		$id=I('get.id');
		$array=M('applys')->where("uid={$id}")->find();
		// var_dump($array);
		$this->assign('data',$array);
		$this->assign('id',$id);
		$this->display();
	}

	public function edit(){
		$id=$_GET['id'];
		$data=M("Cashdetails")->where("uid={$id}")->select();
		// var_dump($data);
		$numss=count($data);
		$this->assign("details",$data);
		$this->assign("numss",$numss);
		$this->display();
	}

	public function apply(){
		$this->display();
	}

	public function listCustomer(){
		$arr=M("state")->where("type=4")->Field("id")->select();
		foreach ($arr as $k => $v) {
			$arrs[]=$v['id'];
		}
		$data=$this->model->alias("u")->join("state s on u.state_id=s.id")->where(array("u.state_id"=>array("in",$arrs)))->Field('s.id as sid,s.name as sname,s.addtime as saddtime,s.*,u.*')->select();
		foreach ($data as $k => $val) {
			$data[$k]=$val;
			$data[$k]['addtime']=date("Y-m-d H:i:s",$val['addtime']);
			if($val['lxtime']){
			$data[$k]['lxtime']=date("Y-m-d H:i:s",$val['lxtime']);
			}
			$array=M('member')->Field('username')->find($data[$k]['member_id']);
			$data[$k]['admin_id']=$array['username'];
		}
		$this->assign("data",$data);
		$this->assign("count",count($data));
		$this->display();
	}

	public function addindex(){
		// $_GET['accumulative']=$_GET['money']*$_GET['num']*$_GET['rate'];
		$id=M("Cash")->add($_GET);
		if($id){
			for($i=1;$i<=$_GET["num"];$i++){
				$data["uid"]=$id;
				$data["num"]=$i;
				$data["Principal"]=$_GET["money"];
				$data["rate"]=$_GET["rate"];
				if($i==1){
					$data["starttime"]= $_GET["starttime"];
				}else{
					$data["starttime"]=$cftime;
				}
			
		$arr=explode("-", $data["starttime"]);
		if($arr['1']==4 || $arr['1']==6 ||$arr['1']==9 || $arr['1']==11){
			if($arr['2']>29){
				if(($arr['1']+1)>12){
					$arr['1']=1;
					$arr['0']++;
				}else{
					$arr['1']++;
				}
				$arr['2']=31;
			}else{
				$arr['1']++;
				if(($arr['2']-1)==0){
					$arr['1']--;
					$arr['2']=30;
				}else{
					$arr['2']--;
				}
			}
		}else{
			if($arr['1']==1 && $arr['0']%4==0 && $arr['2']>29){
				if(($arr['1']+1)>12){
					$arr['1']=1;
					$arr['0']++;
				}else{
					$arr['1']++;
				}
				$arr['2']=29;
			}else if($arr['1']==1 &&  $arr['2']>29){
				if(($arr['1']+1)>12){
					$arr['1']=1;
					$arr['0']++;
				}else{
					$arr['1']++;
				}
				$arr['2']=28;
			}else if($arr['2']>30){
				if(($arr['1']+1)>12){
					$arr['1']=1;
					$arr['0']++;
				}else{
					$arr['1']++;
				}
				$arr['2']=30;
			}else{

				if(($arr['2']-1)==0  &&  $arr['1']==2 && $arr['0']%4==0){
					$arr['1']--;
					$arr['2']=29;
				}else if(($arr['2']-1)==0  &&  $arr['1']==2){
					$arr['1']--;
					$arr['2']=28;
				}else if(($arr['2']-1)==0  &&  $arr['1']==12){
					$arr['1']==12;
					$arr['2']=31;
				}else if(($arr['1']+1)>12){
					$arr['1']=1;
					$arr['0']++;
					$arr['2']--;
				}else{
					$arr['2']--;
					$arr['1']++;
				}
			}
		}
		$arr['1']=$arr['1']<10?'0'.$arr['1']:$arr['1'];
		$arr['2']=$arr['2']<10?'0'.$arr['2']:$arr['2'];
		$endtime=implode($arr, "-");

				$data["endtime"]=$endtime;
				if($_GET["num"]>4){
					$data["nowpri"]=$_GET["money"]/$_GET["num"];
				}elseif ($_GET["num"]<4 && ($i==$_GET["num"])) {
					$data["nowpri"]=$_GET["money"];
				}else{
					$data["nowpri"]=0;
				}

				$data["nowinterest"]=$_GET["accumulative"]/$_GET["num"];
				$data["returnpri"]=0;
				$data["returninterest"]=0;
				if($i==1){
					$data["Arrears"]=$data["nowpri"]+$data["nowinterest"];
				}else{
					$data["Arrears"]=0;
				}
				
				
				
				$Cashdetailsid=M("Cashdetails")->add($data);
				
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
			}





			if($Cashdetailsid<0){
				M("Cash")->delete($id);
			}

			$this->redirect("Monetary/index");
		}

			$this->display();
	}


	public function qrinfo(){
		
		foreach ($_POST as $key => $value) {
			if($key != 'id'){
				$data[$key]=$value;
			}
		}
		if(!empty($_FILES)){
			$upload=new \Think\Upload();
			$upload->maxSize   =     5200000;
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg', 'rar', 'zip');
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_xx/{$_POST['id']}/";
	    	// $info = $upload->upload();

		    foreach($_FILES as $key=>$value){
	          if(count($_FILES[$key]) == count($_FILES[$key],1)){
	            $info = $upload->uploadOne($_FILES[$key]);
	          }
	        }

	         if(count($_FILES)){
	          $info = $upload->upload();//如果是二维数组，使用批量上传文件的方法
	          // var_dump($info);
	          $img_url = './Uploads/'.$info[0]['savepath'].$info[0]['savename'];
	          $img_url1 = './Uploads/'.$info[1]['savepath'].$info[1]['savename'];
	          if(count($info) !=2){
	          	  unlink($img_url1);
	          	  unlink($img_url);
	              $this->error($upload->getError());
	              exit();
	            }
	          $data['housing']=$img_url;
	          $data['liabilities']=$img_url1;
	          $res = array('imgPath1'=>$img_url,code=>$img_url,'msg'=>$info);
	        }
		}
		// var_dump($data);exit();
		$num=M('applys')->where("uid={$_POST['id']}")->save($data);

		$arrs=array("lxtime"=>time(),"state_id"=>18);
		$this->model->where("id={$_POST['id']}")->save($arrs);

		if($num){
			$this->success('确认信息成功',U('Monetary/listCustomer'));
		}else{
			$this->error('确认信息失败',U('Monetary/listCustomer'));
		}

	}

	public function qrzl(){
		// var_dump($_POST);
		$str=implode(',',$_POST['certificates']);
		$data['certificates']=$str;

		foreach ($_POST as $key => $val) {
			if($key != 'id' && $key != 'certificates'){
				$data[$key]=$val;
			}
		}

		if(!empty($_FILES)){
			$upload=new \Think\Upload();
			$upload->maxSize   =     5200000;
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg', 'rar', 'zip');
	    	$upload->rootPath  =     './Uploads/'; 
	   		$upload->savePath  =     "Files_zl/{$_POST['id']}/";
	    	// $info = $upload->upload();

		    foreach($_FILES as $key=>$value){
	          if(count($_FILES[$key]) == count($_FILES[$key],1)){
	            $info = $upload->uploadOne($_FILES[$key]);
	          }
	        }

	         if(count($_FILES)){
	          $info = $upload->upload();//如果是二维数组，使用批量上传文件的方法
	          // var_dump($info);
	          $img_url = './Uploads/'.$info[0]['savepath'].$info[0]['savename'];
	          $img_url1 = './Uploads/'.$info[1]['savepath'].$info[1]['savename'];
	          if(count($info) !=2){
	          	  unlink($img_url1);
	          	  unlink($img_url);
	              $this->error($upload->getError());
	              exit();
	            }
	          $data['rehousing']=$img_url;
	          $data['reliabilities']=$img_url1;
	          $res = array('imgPath1'=>$img_url,code=>$img_url,'msg'=>$info);
	        }
		}

		$num=M('applys')->where("uid={$_POST['id']}")->save($data);
		$arrs=array("lxtime"=>time(),"state_id"=>19);
		$this->model->where("id={$_POST['id']}")->save($arrs);

		if($num){
			$this->success('确认资料成功',U('Monetary/listCustomer'));
		}else{
			$this->error('确认资料失败',U('Monetary/listCustomer'));
		}

		
	}

	public function qrfy(){
		foreach ($_POST as $key => $val) {
			if($key != 'id'){
				$data[$key]=$val;
			}
		}
		$num=M('applys')->where("uid={$_POST['id']}")->save($data);
		$arr=array("lxtime"=>time(),"state_id"=>20);
		$this->model->where("id={$_POST['id']}")->save($arr);
		if($num){
			$this->success('确认费用成功',U('Monetary/listCustomer'));
		}else{
			$this->error('确认费用失败',U('Monetary/listCustomer'));
		}

	}

	//获取业务员
	public function memberss(){
		$rid= I("post.rid");

		$arr=M("admin")->alias("a")->field("m.username,a.id")->join("member m ON a.member_id=m.id")->where("a.level_id ={$rid}")->select();
		
		echo  $this->ajaxReturn($arr);

	}

	public function upd(){

		$data['Arrears']=($_POST['nowpri']+$_POST['nowinterest'])-($_POST['returnpri']+$_POST['returninterest']);
		$data['returnpri']=$_POST['returnpri'];
		$data['returninterest']=$_POST['returninterest'];
		$nums=M("cashdetails")->where("id={$_POST['id']}")->save($data);
	
		if($nums){
			$uids=M("cashdetails")->field("uid,num")->where("id={$_POST['id']}")->find();
			$where=array('uid'=>$uids['uid'],'num'=>array('gt',$uids['num']));
			$datas['Principal']=$_POST['Principal']-$_POST['returnpri'];
			$line=M("cashdetails")->where($where)->save($datas);
			echo 1;
		}else{
			echo 2;
		}
	}

	public function del(){

		$id=$_GET['id'];
		$num=M("cash")->delete($id);
		if($num){
			$numss=M("cashdetails")->where("uid={$id}")->delete();
			if($numss){
					$this->success('删除成功',U('Monetary/index'));
				}else{
					$this->error('删除失败',U('Monetary/index'));
			}
		}

	}



}

 ?>