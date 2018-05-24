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

	//列表查询条件
	public function conditions(){
		if(in_array('1001',$_SESSION['level'])){
		  $kun =  array('id'=>array('gt','0'));

		}else if(in_array('1002',$_SESSION['level'])){
		  
		  $id=M("rank")->where("name='{$_SESSION['rank_name']}'")->getField("id");
		  $ids=M("rank")->where("pid={$id}")->getField("id",true);
		  $ids[]=$id;
		  $u_id=M("admin")->where(array("level_id"=>array("in",$ids)))->getField("id",true);
		  $kun = array("salesman"=>array("in",$u_id));

		}else{
			$kun= array("salesman"=>$_SESSION['aid']);
		}

		return $kun;
	}
	//速分贷
	public function index(){
		$kun=$this->conditions();
		$where=array("type"=>1,"state"=>1);
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
				$uid=M("cash")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("cash")->where(array("cardid"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['Salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["id"]=array("in",$cardid);
					}
				$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['stime'] && $post['endtime'] && $post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	
			  	$where['Salesman']=array("in",$adimds);
			  	$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));

			  }elseif($post['stime'] && $post['endtime']){
			  	$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$where['Salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("cash")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("cash")->where(array("cardid"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['Salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["id"]=array("in",$cardid);
					}else{
							$where["id"]=array("elt",0);
					}
					// $where[]=$kun;
			 }elseif($post['yqkh']){
			 	 $where[]=array('Latefee'=>array("gt",0));
			 }

		}
		//添加搜索条件结束
		
		$where[] = $kun;
		// var_dump($where);exit();
		$data=M("Cash")->where($where)->order("id desc")->select();
		// var_dump($data);
		// echo M("Cash")->getlastsql();
		foreach ($data as $key => $val) {
			//业务员
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
			$data[$key]['salesman']=$arrs['0']['username'];
			
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
		
		$count=count($data);
        $Page= $this->getPage($count,20);
        $this->show= $Page->show();
        $datas = array_slice($data,$Page->firstRow,$Page->listRows);

        $this->assign("count",$count);
		$this->assign("cashinfo",$datas);
		$this->assign("twhlx",$twhlx);
		$this->assign("tyhlx",$tyhlx);
		$this->assign("tmoney",$tmoney);
		$this->assign("tfee",$tfee);
		$this->assign("tmargin",$tmargin);
		$this->assign("taccumulative",$taccumulative);
		$this->assign("tlatefee",$tlatefee);
		$this->display();
	}

	//速贷
	public function indexsd(){
		// var_dump($_SESSION);
		$kun=$this->conditions();
		$where[]=array("type"=>2,"state"=>1);

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
				$uid=M("cash")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("cash")->where(array("cardid"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['Salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["id"]=array("in",$cardid);
					}
				$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['stime'] && $post['endtime'] && $post['branch']){

			  	if($post['branch']==33){
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$where['Salesman']=array("in",$adimds);
			  	$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));

			  }elseif($post['stime'] && $post['endtime']){
			  	$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$where['Salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("cash")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("cash")->where(array("cardid"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['Salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["id"]=array("in",$cardid);
					}else{
							$where["id"]=array("elt",0);
					}
					// $where[]=$kun;
			  }elseif($post['yqkh']){
			 	 $where[]=array('Latefee'=>array("gt",0));
			  }

		}
		//添加搜索条件结束

		$where[] = $kun;
		$data=M("Cash")->where($where)->order("id desc")->select();
		foreach ($data as $key => $val) {
			//业务员
			$arr=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
			$data[$key]['salesman']=$arr['0']['username'];

			//滞纳金
			$latefee=M("Cash")->Field('money,returnlaf')->find($data[$key]['id']);
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']} and repayment=1")->order('id asc')->Field('id,starttime')->select();
					
			$sum=0;
			foreach ($arr as $k => $val) {
				$stime=strtotime($arr[$k]['starttime']);
				$etime=time();
				if(strtotime($arr[$k+1]['starttime'])>0){
					if(strtotime($arr[$k+1]['starttime'])>$etime){
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
						$timediff = strtotime($arr[$k+1]['starttime'])-$stime;
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
			//暂时关闭滞纳金计算
			// $dataznj['Latefee']=$sum-$latefee['returnlaf'];
			$dataznj['Latefee']=0;
			M("Cash")->where("id={$data[$key]['id']}")->save($dataznj);
			//暂时关闭滞纳金计算
			// $data[$key]['latefee']=$sum-$latefee['returnlaf'];
			$data[$key]['latefee']=0;
			//逾期天数
			//暂时关闭滞纳金计算
			// $data[$key]['days']=$days;
			$data[$key]['days']=0;
			//应还利息跟未还利息
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']}")->order('id asc')->limit(1)->Field('id,starttime,returninterest,nowinterest')->select();
			// var_dump($arrs[1]);
			$time=date("Y-m-d",time());
			$times=explode('-', $time);
			// var_dump($times[1]);
			foreach ($arr as $k => $v) {
				$arrs=explode('-', $v['starttime']);
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

		// var_dump($data);
		$count=count($data);
        $Page= $this->getPage($count,20);
        $this->show= $Page->show();
        $datas = array_slice($data,$Page->firstRow,$Page->listRows);

        $this->assign("count",$count);
		$this->assign("cashinfo",$datas);
		$this->assign("twhlx",$twhlx);
		$this->assign("tyhlx",$tyhlx);
		$this->assign("tmoney",$tmoney);
		$this->assign("tfee",$tfee);
		$this->assign("tmargin",$tmargin);
		$this->assign("taccumulative",$taccumulative);
		$this->assign("tlatefee",$tlatefee);
		$this->display();
	}

	public function yewu(){
		
		$this->display();
	}

	public function edit(){
		$id=$_GET['id'];
		$data=M("Cashdetails")->where("uid={$id}")->select();
		//还款详情
		$infos=M("Cashinfo")->where("uid={$id}")->select();
		$i=0;
		foreach ($infos as $k => $val){
				$info[$k]=$val;
				$info[$k]['time']=date("Y-m-d H:i:s",$val['time']);
				$i++;
				$info[$k]['bs']=$i;
		}
		
		//滞纳金
		
		$latefee=M("Cash")->Field('money,returnlaf,name,aid,dkbh')->find($id);

		$card=M("cash")->alias("c")->join("applys a on a.id=c.aid")->where("c.aid={$latefee['aid']}")->Field("a.cardid")->find();

		$arr=M("Cashdetails")->where("uid={$id} and repayment=1")->order('id asc')->Field('id,endtime')->select();
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
			foreach ($data as $key => $v) {
				if($data[$key]['id']==$arr[$k]['id']){
					//滞纳金计算暂时关闭
					// $data[$key]['yqday']=$days;
					// $data[$key]['yqmoney']=$days*$latefee['money']*0.003;
					$data[$key]['yqday']=0;
					$data[$key]['yqmoney']=0;

					// $data[$key]['whznj']=($days*$latefee['money']*0.003)-$latefee['returnlaf'];
				}
			}
			//滞纳金计算暂时关闭
			// $cbinfo['yqday']=$days;
			// $cbinfo['yqmoney']=$days*$latefee['money']*0.003;
			$cbinfo['yqday']=0;
			$cbinfo['yqmoney']=0;
			M("cashdetails")->where("id={$arr[$k]['id']}")->save($cbinfo);
		}
		$datainfo=M("Cashdetails")->where("uid={$id} and repayment=1")->select();
		foreach ($datainfo as $key => $value) {
			 $zsum+=$value['yqmoney'];
		}
		foreach ($data as $key => $value) {
			 $zyqd+=$value['yqday'];
			 $zyqm+=$value['yqmoney'];
		}
		$sum=$zsum-$latefee['returnlaf'];
		$dataznj['Latefee']=$sum;
		M("Cash")->where("id={$id}")->save($dataznj);
		//跟进信息
		$records=M("xj_record")->where("c_id={$id}")->order('addtime desc')->select();
		$count=count($records);//得到数组元素个数
		 $Page= $this->getPages($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		 $records = array_slice($records,$Page->firstRow,$Page->listRows);
		 $this->show= $Page->show();

		$this->assign("ids",$id);
		$this->assign("details",$data);
		$this->assign("info",$info);
		$this->assign("znj",$sum);
		$this->assign("zyqd",$zyqd);
		$this->assign("zyqm",$zyqm);
		$this->assign("records",$records);
		$this->assign("name",$latefee['name']);
		$this->assign("cardid",$card['cardid']);
		$this->assign("dkbh",$latefee['dkbh']);
		$this->display();
	}

	public function editsd(){
		$id=$_GET['id'];
		$data=M("Cashdetails")->where("uid={$id}")->select();
		//还款详情
		$infos=M("Cashinfo")->where("uid={$id}")->select();
		$i=0;
		foreach ($infos as $k => $val){
				$info[$k]=$val;
				$info[$k]['time']=date("Y-m-d H:i:s",$val['time']);
				$i++;
				$info[$k]['bs']=$i;
		}
		// var_dump($info);
		//滞纳金
		$latefee=M("Cash")->Field('money,returnlaf,name,aid,dkbh')->find($id);
		$card=M("cash")->alias("c")->join("applys a on a.id=c.aid")->where("c.aid={$latefee['aid']}")->Field("a.cardid")->find();
		$arr=M("Cashdetails")->where("uid={$id} and repayment=1")->order('id asc')->Field('id,starttime')->select();
		foreach ($arr as $k => $val) {
			$stime=strtotime($arr[$k]['starttime']);
			$etime=time();
			if(strtotime($arr[$k+1]['starttime'])>0){
				if(strtotime($arr[$k+1]['starttime'])>$etime){
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
					$timediff = strtotime($arr[$k+1]['starttime'])-$stime;
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
			foreach ($data as $key => $v) {
				if($data[$key]['id']==$arr[$k]['id']){
					//滞纳金计算暂时关闭
					// $data[$key]['yqday']=$days;
					// $data[$key]['yqmoney']=$days*$latefee['money']*0.003;
					$data[$key]['yqday']=0;
					$data[$key]['yqmoney']=0;
					// $data[$key]['whznj']=($days*$latefee['money']*0.003)-$latefee['returnlaf'];
				}
			}
			//滞纳金计算暂时关闭
			// $cbinfo['yqday']=$days;
			// $cbinfo['yqmoney']=$days*$latefee['money']*0.003;
			$cbinfo['yqday']=0;
			$cbinfo['yqmoney']=0;
			M("cashdetails")->where("id={$arr[$k]['id']}")->save($cbinfo);
		}
		$datainfo=M("Cashdetails")->where("uid={$id} and repayment=1")->select();
		foreach ($datainfo as $key => $value) {
			 $zsum+=$value['yqmoney'];
		}
		foreach ($data as $key => $value) {
			 $zyqd+=$value['yqday'];
			 $zyqm+=$value['yqmoney'];
		}
		$sum=$zsum-$latefee['returnlaf'];
		$dataznj['Latefee']=$sum;
		M("Cash")->where("id={$id}")->save($dataznj);

		//跟进信息
		$records=M("xj_record")->where("c_id={$id}")->order('addtime desc')->select();
		$count=count($records);//得到数组元素个数
		 $Page= $this->getPages($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		 $records = array_slice($records,$Page->firstRow,$Page->listRows);
		 $this->show= $Page->show();
		
		$this->assign("ids",$id);
		$this->assign("details",$data);
		$this->assign("info",$info);
		$this->assign("znj",$sum);
		$this->assign("zyqd",$zyqd);
		$this->assign("zyqm",$zyqm);
		$this->assign("records",$records);
		$this->assign("name",$latefee['name']);
		$this->assign("cardid",$card['cardid']);
		$this->assign("dkbh",$latefee['dkbh']);
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
		$data=M("applys")->alias("a")->join("state s on a.state_id=s.id")->where(array("a.state_id"=>array("in",$arrs)))->Field('s.id as sid,s.name as sname,s.addtime as saddtime,s.*,a.*')->order("a.addtime desc")->select();
		foreach ($data as $k => $val) {
			$data[$k]=$val;
			$data[$k]['addtime']=date("Y-m-d H:i:s",$val['addtime']);
			if($val['lxtime']){
			$data[$k]['lxtime']=date("Y-m-d H:i:s",$val['lxtime']);
			}
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$k]['salesman']))->Field('m.username')->find();
				$data[$k]['admin_id']=$arrs['username'];
		}
		$this->assign("data",$data);
		$this->assign("count",count($data));
		$this->display();
	}

	public function listqysystem(){
		$arr=M("qysystem")->select();
		foreach ($arr as $k => $v) {
			$data[$k]=$v;
			$data[$k]['time']=date('Y-m-d',$v['time']);
			$admins=M('admin')->field('member_id')->find($v['salesman']);
			$admin=M('member')->field('username')->find($admins['member_id']);
			$data[$k]['username']=$admin['username'];
		}
		$this->assign("data",$data);
		$this->assign("count",count($data));
		$this->display();
	}

	public function delsystem(){
		$id=$_GET['id'];

		$num=M("qysystem")->delete($id);
		if($num){
				$this->success('删除成功',U('Monetary/listqysystem'));
			}else{
				$this->error('删除失败',U('Monetary/listqysystem'));
			}
	}


	public function addindex(){
		
		$_GET["starttime"]=strtotime($_GET["starttime"]." 00:00:00");
		$_GET["endtime"]=strtotime($_GET["endtime"]." 20:00:00");
		$_GET["caddtime"]=time();
		$times['lxtime']=time();
		// M("Applys")->where("id={$_GET['aid']}")->save($times);
		$id=M("Cash")->add($_GET);
		if($id){
			// M("applys")->where("id={$_GET['aid']}")->save(array("state_id"=>23));
			for($i=1;$i<=$_GET["num"];$i++){
				$data["uid"]=$id;
				$data["num"]=$i;
				$data["Principal"]=$_GET["money"];
				$data["rate"]=$_GET["rate"];
				if($i==1){
					$stime=date('Y-m-d',$_GET["starttime"]);
					$data["starttime"]= $stime;
				}else{
					$data["starttime"]=$cftime;
				}
			
		$arr=explode("-", $data["starttime"]);
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

				
				//速贷速分贷月本金根止息时间
				if($_GET['leixing']=='sd' && ($i==$_GET["num"])){
					$data["endtime"]=$endtime;
					$data["nowpri"]=$_GET["money"];
				}elseif ($_GET['leixing']=='sd') {
					$data["endtime"]='';
					$data["nowpri"]=0;
				}else{
					$data["nowpri"]=$_GET["money"]/$_GET["num"];
					$data["endtime"]=$endtime;
				}

				$data["nowinterest"]=$_GET["accumulative"]/$_GET["num"];
				$data["returnpri"]=0;
				$data["returninterest"]=0;
				$data["Arrears"]=$data["nowpri"]+$data["nowinterest"];
				$data["repayment"]=1;
				
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
			if($Cashdetailsid>0){
				
				if($_GET['leixing']=='sd'){
					$this->error("添加成功",U("Monetary/indexsd"));
					// $this->redirect("Monetary/indexsd");
				}else{
					// $this->redirect("Monetary/index");
					$this->error("添加成功",U("Monetary/index"));
				}
			}
						
		}

			$this->display();
	}

	//获取业务员
	public function memberss(){
		$rid= I("post.rid");

		$arr=M("admin")->alias("a")->field("m.username,a.id")->join("member m ON a.member_id=m.id")->where("a.level_id ={$rid}")->select();
		
		echo  $this->ajaxReturn($arr);

	}


	public function upd(){

		$arr=M("cashdetails")->field("id,nowpri,nowinterest,returnpri,returninterest,arrears,principal")->where("uid={$_POST['id']} and repayment=1")->select();

		if($arr){
			$znjs=M("cash")->field("Latefee,money,returnlaf")->find($_POST['id']);
			$znj=$znjs['latefee'];
			$ylx=$arr[0]['nowinterest'];
			$ybj=$arr[0]['nowpri'];
			$hkje=$_POST["hkje"];
			foreach ($arr as $key => $val) {
				$arrs[]=$val['id'];
				$total+=$val['arrears'];
			}

			$data["returnpri"]=$ybj;
			$data["returninterest"]=$ylx;
			$data['Arrears']=0;
			$data['repayment']=2;
			$data['Principal']=0;
			$infos=array();
			$infos["uid"]=$_POST['id'];
			$infos["time"]=time();
			

			if($hkje==($total+$znj)){
				// $i=0;
				foreach ($arrs as $val) {
					// $data['Principal']=$arr[0]['Principal']-$ybj;
					// $i++;
					$num=M("cashdetails")->where("id={$val}")->save($data);
				}
				if($num){
					$cah['Latefee']=0;
					$cah['returnlaf']=0;
					M("Cash")->where("id={$_POST['id']}")->save($cah);
					//插入明细表
					$infos["sum"]=$hkje;
					$infos["returnlf"]=$znj;
					M("cashinfo")->add($infos);
					echo  "还款成功";
				}

			}elseif($hkje<=$znj){
				
				$cah["returnlaf"]=$hkje+$znjs['returnlaf'];
				$num=M("Cash")->where("id={$_POST['id']}")->save($cah);
				if($num){
					//插入明细表
					$infos["sum"]=$hkje;
					$infos["returnlf"]=$hkje;
					M("cashinfo")->add($infos);
					echo "还款成功";
				}

			}elseif($hkje>$znj){

				//插入明细表
				$infos["sum"]=$hkje;
				$infos["returnlf"]=$znj;
				M("cashinfo")->add($infos);

				//循环还款期数
				for($i=0; $i<count($arr); $i++){
					if($i==0){
						if($hkje < ($arr[$i]['arrears']+$znj)){

							if($znj > 0){
								$cah["returnlaf"]=$znj+$znjs['returnlaf'];
							}
							$num=M("Cash")->where("id={$_POST['id']}")->save($cah);
							//判断repayment=1的第一个是否有还利息
							  if($arr[$i]['returninterest']>0){
								if((($hkje+$arr[$i]['returninterest'])-($znj+$ylx))>0){
									$detail['returninterest']=$ylx;
									//判断repayment=1的第一个是否有还本金；
									if($arr[$i]['returnpri']>0){
									  $detail['returnpri']=(($hkje+$arr[$i]['returninterest'])-($znj+$ylx))+$arr[$i]['returnpri'];
									  $bjd=$hkje-$znj;
									}else{
									  $detail['returnpri']=($hkje)-(($znj+$ylx)-$arr[$i]['returninterest']);
									  $bjd=$detail['returnpri'];
									}
									
									$detail['Arrears']=($ylx+$ybj)-($detail['returninterest']+$detail['returnpri']);
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

									//计算本金余额
									$bj['Principal']=$arr[$i]['principal']-$bjd;
									$bjs=$bj['Principal'];
									foreach ($arrs as $val) {
											M("cashdetails")->where("id=({$val}+1)")->save($bj);
										}
									// for ($i=0; $i < count($arrs); $i++) { 
										 
									// }

								}else{
									$detail['returninterest']=$hkje-$znj+$arr[$i]['returninterest'];
									$detail['returnpri']=0;
									$detail['Arrears']=($ylx+$ybj)-$detail['returninterest'];
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

								}

							  }else{

							  	if(($hkje-$znj-$ylx)>0){
									$detail['returninterest']=$ylx;
									$detail['returnpri']=$hkje-$znj-$ylx;
									$detail['Arrears']=($ylx+$ybj)-($detail['returninterest']+$detail['returnpri']);
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

									//计算本金余额
									$bj['Principal']=$arr[$i]['principal']-$detail['returnpri'];
									$bjs=$bj['Principal'];
									foreach ($arrs as $val) {
											M("cashdetails")->where("id=({$val}+1)")->save($bj);
										}

								}else{
									$detail['returninterest']=$hkje-$znj;
									$detail['returnpri']=0;
									$detail['Arrears']=($ylx+$ybj)-$detail['returninterest'];
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);
									
								}
							  }
							  
							break;
						}else{
							//整条插入改变repayment的值
							$cah["returnlaf"]=0;
							$num=M("Cash")->where("id={$_POST['id']}")->save($cah);

							$detail['returninterest']=$ylx;
							$detail['returnpri']=$ybj;
							$detail['repayment']=2;
							$detail['Arrears']=0;
							M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

							if($arr[$i]['returnpri']>0){
									  $bjd=$ybj-$arr[$i]['returnpri'];
									}else{
									  $bjd=$detail['returnpri'];
									}
							//计算本金余额
							$bj['Principal']=$arr[$i]['principal']-$bjd;
							$bjs=$bj['Principal'];
							// echo $bj['Principal'].'aaa';
							foreach ($arrs as $val) {
									M("cashdetails")->where("id=({$val}+1)")->save($bj);
								}
								
							$hkje=$hkje-($znj+$arr[$i]['arrears']);
							
						}
					}else{
						
						if($hkje < $arr[$i]['arrears']){
							
							if($hkje){
								if(($hkje-$ylx)>0){
									$detail['returninterest']=$ylx;
									$detail['returnpri']=$hkje-$ylx;
									$detail['repayment']=1;
									$detail['Arrears']=($ylx+$ybj)-($detail['returninterest']+$detail['returnpri']);
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

									// 计算本金余额
									// echo $arr[$i]['returnpri'];
									if($arr[$i]['returnpri']>0){
									  $bjd=$ybj-$arr[$i]['returnpri'];
									}else{
									  $bjd=$detail['returnpri'];
									}
									
									$bj['Principal']=$bjs-$bjd;
									$bjs=$bj['Principal'];
									$array=array_slice($arrs,$i);
									foreach ($array as $val) {
											M("cashdetails")->where("id=({$val}+1)")->save($bj);
										}

								}else{
									$details['returninterest']=$hkje;
									$details['returnpri']=0;
									$details['repayment']=1;
									$details['Arrears']=($ylx+$ybj)-$hkje;
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($details);
									
								}
							}
							break;
						}else{
							
							$detaila['returninterest']=$ylx;
							$detaila['returnpri']=$ybj;
							$detaila['repayment']=2;
							$detaila['Arrears']=0;
							M("cashdetails")->where("id={$arr[$i]['id']}")->save($detaila);

							//计算本金余额
							$bj['Principal']=$bjs-$ybj;
							$bjs=$bj['Principal'];
							$array=array_slice($arrs,$i);
							foreach ($array as $val) {
									M("cashdetails")->where("id=({$val}+1)")->save($bj);
								}
							$hkje=$hkje-$arr[$i]['arrears'];
						}
					}
				}
				echo "还款成功";
			}

		}else{
			echo "没有需要还的贷款";
		}
	}


	public function updsd(){

		$arr=M("cashdetails")->field("id,nowinterest,returnpri,returninterest,arrears,principal")->where("uid={$_POST['id']} and repayment=1")->select();

		if($arr){

			$znjs=M("cash")->field("Latefee,returnlaf")->find($_POST['id']);
			$znj=$znjs['latefee'];
			$ylx=$arr[0]['nowinterest'];
			$bj=$arr[0]['principal'];
			$hkje=$_POST["hkje"];
			foreach ($arr as $key => $val) {
				$arrs[]=$val['id'];
				$total+=$val['arrears'];
			}
			
			$data["returnpri"]=0;
			$data["returninterest"]=$ylx;
			$data['Arrears']=0;
			$data['repayment']=2;
			$data['Principal']=$bj;
			$infos=array();
			$infos["uid"]=$_POST['id'];
			$infos["time"]=time();
			
			// echo $hkje.'ss';
			// echo $total+$znj.'ss'.$hkje; exit();
			if($hkje==($total+$znj)){
				
				$i=0;
				foreach ($arrs as $val) {
					$i++;
					if($i==count($arrs)){
						$data["returnpri"]=$bj;
						$data['Principal']=0;
					}
					$num=M("cashdetails")->where("id={$val}")->save($data);
				}
				if($num){
					$cah['Latefee']=0;
					$cah['returnlaf']=0;
					M("Cash")->where("id={$_POST['id']}")->save($cah);
					//插入明细表
					$infos["sum"]=$hkje;
					$infos["returnlf"]=$znj;
					M("cashinfo")->add($infos);
					echo  "还款成功";
				}

			}elseif($hkje<=$znj){
				
				$cah["returnlaf"]=$hkje+$znjs['returnlaf'];
				$num=M("Cash")->where("id={$_POST['id']}")->save($cah);
				if($num){
					//插入明细表
					$infos["sum"]=$hkje;
					$infos["returnlf"]=$hkje;
					M("cashinfo")->add($infos);
					echo "还款成功";
				}

			}elseif($hkje>$znj){
				// echo $hkje; exit();
				//插入明细表
				$infos["sum"]=$hkje;
				$infos["returnlf"]=$znj;
				M("cashinfo")->add($infos);
				
				//循环还款期数
				for($i=0; $i<count($arr); $i++){

					if($i==0){
						// echo 'sss';
						// echo $arr[$i]['arrears']+$znj;exit();
						if($hkje < ($arr[$i]['arrears']+$znj)){

							if($znj > 0){
								$cah["returnlaf"]=$znj+$znjs['returnlaf'];
							}
							$num=M("Cash")->where("id={$_POST['id']}")->save($cah);
							//判断repayment=1的第一个是否有还利息
							  if($arr[$i]['returninterest']>0){

								if((($hkje+$arr[$i]['returninterest'])-($znj+$ylx))>0){

									$detail['returninterest']=$ylx;
									 //判断是否最后一次
									if(count($arr)==1){
										//判断repayment=1的第一个是否有还本金；
										if($arr[$i]['returnpri']>0){
										  $detail['returnpri']=(($hkje+$arr[$i]['returninterest'])-($znj+$ylx))+$arr[$i]['returnpri'];
										  // $bjd=$hkje-$znj;
										}else{
										  $detail['returnpri']=$hkje-(($znj+$ylx)-$arr[$i]['returninterest']);
										  // $bjd=$detail['returnpri'];
										}
										$detail['Arrears']=($ylx+$bj)-($detail['returninterest']+$detail['returnpri']);
										// $detail['Principal']=$bj-$detail['returnpri'];
									}else{
										
										$detail['returnpri']=0;
										$detail['Arrears']=($ylx+$ybj)-($detail['returninterest']+$detail['returnpri']);
									}
									
									// $detail['Arrears']=($ylx+$ybj)-($detail['returninterest']+$detail['returnpri']);
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

								}else{
									$detail['returninterest']=$hkje-$znj+$arr[$i]['returninterest'];
									$detail['returnpri']=0;
									//是否最后一次
									if(count($arr)==1){
										$detail['Arrears']=($ylx+$bj)-$detail['returninterest'];
									}else{
										$detail['Arrears']=$ylx-$detail['returninterest'];
									}
									
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

								}

							  }else{

							  	if(($hkje-$znj-$ylx)>0){
									$detail['returninterest']=$ylx;
									//是否最后一次
									if(count($arr)==1){
										$detail['returnpri']=$hkje-$znj-$ylx;
										$detail['Arrears']=($ylx+$bj)-($detail['returninterest']+$detail['returnpri']);
										// $detail['Principal']=$bj-$detail['returnpri'];
									}else{
										$detail['returnpri']=0;
										$detail['Arrears']=$ylx-($detail['returninterest']+$detail['returnpri']);
									}
									
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

								}else{
									$detail['returninterest']=$hkje-$znj;
									$detail['returnpri']=0;
									if(count($arr)==1){
										$detail['Arrears']=($ylx+$bj)-$detail['returninterest'];
									}else{
										$detail['Arrears']=($ylx+$ybj)-$detail['returninterest'];
									}
									
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);
									
								}
							  }
							  
							break;
						}else{
							//整条插入改变repayment的值
							// echo $hkje; exit();
							$cah["returnlaf"]=0;
							$num=M("Cash")->where("id={$_POST['id']}")->save($cah);

							$detail['returninterest']=$ylx;
							//判断最后一次
							if(count($arr)==1){
								$detail['returnpri']=$bj;
								// $detail['Principal']=$bj-$detail['returnpri'];
							}else{
								$detail['returnpri']=0;
							}
							// echo $detail['returnpri'];exit();

							$detail['repayment']=2;
							$detail['Arrears']=0;
							M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

							$hkje=$hkje-($znj+$arr[$i]['arrears']);
							// echo $hkje; exit();
						}
					}else{
						
						if($hkje < $arr[$i]['arrears']){
							
							if($hkje){
								if(($hkje-$ylx)>0){
									$detail['returninterest']=$ylx;
									//判断最后一次
									if($i==(count($arr)-1)){
											$detail['returnpri']=$hkje-$ylx;
											$detail['Arrears']=($ylx+$bj)-($detail['returninterest']+$detail['returnpri']);
											// $detail['Principal']=$bj-$detail['returnpri'];
										}else{
											$detail['returnpri']=0;
											$detail['Arrears']=($ylx+$ybj)-($detail['returninterest']+$detail['returnpri']);
										}

									$detail['repayment']=1;
									
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($detail);

								}else{
									$details['returninterest']=$hkje;
									$details['returnpri']=0;
									$details['repayment']=1;
									//判断最后一次
									if($i==(count($arr)-1)){
											$details['Arrears']=($ylx+$bj)-$hkje;
										}else{
											$details['Arrears']=$ylx-$hkje;
										}
									// $details['Arrears']=($ylx+$ybj)-$hkje;
									M("cashdetails")->where("id={$arr[$i]['id']}")->save($details);
									
								}
							}
							break;
						}else{
							
							$detaila['returninterest']=$ylx;

							//判断最后一次
							if($i==(count($arr)-1)){
									$detaila['returnpri']=$bj;
									// $detaila['Principal']=$bj-$detaila['returnpri'];
								}else{
									$detaila['returnpri']=0;
								}
							
							$detaila['repayment']=2;
							$detaila['Arrears']=0;
							M("cashdetails")->where("id={$arr[$i]['id']}")->save($detaila);

							$hkje=$hkje-$arr[$i]['arrears'];
						}
					}
				}
				echo "还款成功";
			}

		}else{
			echo "没有需要还的贷款";
		}
	}

	public function dels(){

		$id=I('get.id');
		$arr=M('cash')->where("id={$id}")->getField("type", true);
		$type=$arr['0'];
		$num=M("cash")->where("id={$id}")->delete();
		if($num){
			M("cashinfo")->where("uid={$id}")->delete();
			$nums=M("cashdetails")->where("uid={$id}")->delete();
			if($nums){
					if($type==1){
						$this->success('删除成功',U('Monetary/index'));
					}else{
						$this->success('删除成功',U('Monetary/indexsd'));
					}
				}else{
					if($type==1){
						$this->success('删除失败',U('Monetary/index'));
					}else{
						$this->success('删除失败',U('Monetary/indexsd'));
					}
			}
		}
	}

	public function del(){
		
		$id=$_GET['id'];
		$photo=M("applys")->where("id={$id}")->Field('photo')->find();
		$f=$photo['photo'];
		$num=M("applys")->where("id={$id}")->delete();
		if($num){
				unlink($f);
				M("cashfiles")->where("aid={$id}")->delete();
				$this->success('删除成功',U('Monetary/listCustomer'));
			}else{
				$this->error('删除失败',U('Monetary/listCustomer'));
			}
	}

	public function infos(){

		$id=$_GET['id'];
		$data=M("applys")->where("id={$id}")->find();
		$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data['salesman']))->Field('m.username')->find();
			
		$data['admin_name']=$arrs['username'];
		$data['money']=$data['money']/10000;
		// var_dump($data);
		$this->assign('data',$data);
		$this->display();
	}

	public function infosystem(){

		$id=$_GET['id'];
		$data=M("qysystem")->find($id);
		$admins=M('admin')->field('member_id')->find($data['salesman']);
		$admin=M('member')->field('username')->find($admins['member_id']);
		$data['username']=$admin['username'];
		$this->assign('data',$data);
		$this->display();
	}

	public function modify(){
		$id=$_GET['id'];
		$t=$_GET['t'];
		$data=M("cash")->find($id);
		$info=M("cashinfo")->where("uid={$id}")->select();
		// echo count($info);
		foreach ($data as $k => $v) {
			$datas[$k]=$v;
			$datas['starttime']=date('Y-m-d',$data['starttime']);
			$datas['endtime']=date('Y-m-d',$data['endtime']);

			$arr=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data['salesman']))->Field('m.username')->select();
			$datas['admin']=$arr['0']['username'];
		}
		$this->assign('datas',$datas);
		$this->assign('nums',count($info));
		// var_dump($datas);
		if($t=='sd'){
			$this->display("Monetary/modifysd");
		}else{
			$this->display();
		}
	}

	public function sdupd(){
		$id=$_GET['id'];
		$_GET["starttime"]=strtotime($_GET["starttime"]." 00:00:00");
		$_GET["endtime"]=strtotime($_GET["endtime"]." 20:00:00");
		$t =$_GET['leixing'];
		$_GET['state']=1;
		$no=M("cash")->save($_GET);
		if($no>0){
			$num=M("cashdetails")->where("uid={$id}")->delete();
		
			for($i=1;$i<=$_GET["num"];$i++){
				$data["uid"]=$id;
				$data["num"]=$i;
				$data["Principal"]=$_GET["money"];
				$data["rate"]=$_GET["rate"];
				if($i==1){
					$stime=date('Y-m-d',$_GET["starttime"]);
					$data["starttime"]= $stime;
				}else{
					$data["starttime"]=$cftime;
				}
			
		$arr=explode("-", $data["starttime"]);
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

				
				//速贷速分贷月本金根止息时间
				if($_GET['leixing']=='sd' && ($i==$_GET["num"])){
					$data["endtime"]=$endtime;
					$data["nowpri"]=$_GET["money"];
				}elseif ($_GET['leixing']=='sd') {
					$data["endtime"]='';
					$data["nowpri"]=0;
				}else{
					$data["nowpri"]=$_GET["money"]/$_GET["num"];
					$data["endtime"]=$endtime;
				}

				$data["nowinterest"]=$_GET["accumulative"]/$_GET["num"];
				$data["returnpri"]=0;
				$data["returninterest"]=0;
				$data["Arrears"]=$data["nowpri"]+$data["nowinterest"];
				$data["repayment"]=1;
				
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
			if($Cashdetailsid>0){
				if($_GET['leixing']=='sd'){
					$this->success('更新成功',U('Monetary/indexsd'));
					// $this->redirect("Monetary/indexsd");
				}else{
					// $this->redirect("Monetary/index");
					$this->success('更新成功',U('Monetary/index'));
				}
			}
						
		


		}else{
			if($t=='sd'){
				$this->error('更新失败',U('Monetary/indexsd'));
			}else{
				$this->error('更新失败',U('Monetary/index'));
			}
			
		}
		
	}


	public function updstate(){
		$id= I("post.id");
		$arr=M("cashdetails")->where("uid={$id} and repayment=1")->select();
		if($arr){
			echo 3;
			exit();
		}
		$data['state']=2;
		$num=M('cash')->where("id={$id}")->save($data);
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
				$uid=M("cash")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("cash")->where(array("cardid"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['Salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["id"]=array("in",$cardid);
					}
				$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['stime'] && $post['endtime'] && $post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	
			  	$where['Salesman']=array("in",$adimds);
			  	$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));

			  }elseif($post['stime'] && $post['endtime']){
			  	$where[]=array("starttime"=>array(array("gt",$stime),array('elt',$endtime)));
				// $where[]=$kun;
			  }elseif($post['branch']){
			  	if($post['branch']==33){
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$where['Salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("cash")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("cash")->where(array("cardid"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
							$where['Salesman']=array("in",$aids);
					}elseif(count($uid)){
							$where["id"]=array("in",$uid);
					}elseif(count($cardid)){
							$where["id"]=array("in",$cardid);
					}else{
							$where["id"]=array("elt",0);
					}
					// $where[]=$kun;
			  }

		}
		//添加搜索条件结束
		
		$where[] = $kun;
		$data=M("Cash")->where($where)->order("id desc")->select();
		foreach ($data as $key => $val) {
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
			$data[$key]['salesman']=$arrs['0']['username'];
		}
		$this->assign("cashinfo",$data);

		$this->display();
	}

	public function record(){
		$data['c_id']=I('get.id');
		$data['content']=I('get.content');
		$data['admin_name']=session("aname");
		$data['addtime']=time();
		$num=M('xj_record')->add($data);
		if($num){
			echo 1;
		}else{
			echo 2;
		}
	}

	public function addsd(){
		$id=$_GET['id'];
		$data=M("applys")->where("id={$id}")->find();
		// var_dump($data);
		$data['money']=$data['money']*10000;
		$this->assign('data',$data);
		$this->display('Monetary/addindexsd');
	}
	
	public function addsfd(){
		$id=$_GET['id'];
		$data=M("applys")->where("id={$id}")->find();
		// var_dump($data);
		$data['money']=$data['money']*10000;
		$this->assign('data',$data);
		$this->display('Monetary/addindex');
	}
		
}

 ?>