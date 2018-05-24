<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class MonetaryController extends CommonController{


	protected $model;
	public function _initialize()
	{
	   parent::_initialize();
	   $this->isUclient = "Monetary";
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
		
		// echo M("Cash")->getlastsql();
		foreach ($data as $key => $val) {
			//业务员
			$arrs=M("member")->alias("m")->join("admin a on a.member_id=m.id")->where(array("a.id"=>$data[$key]['salesman']))->Field('m.username')->select();
			$data[$key]['salesman']=$arrs['0']['username'];
			
			//滞纳金
			$latefee=M("Cash")->Field('money,returnlaf')->find($data[$key]['id']);
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']} and repayment=1")->order('id asc')->Field('id,endtime')->select();
			
			$sum=0;
			if($arr){
				foreach ($arr as $k => $val) {
					$stime=strtotime($arr[$k]['endtime']);
					// echo $stime.',';
					$etime=time();
					if(strtotime($arr[$k+1]['endtime'])>0){
						if(strtotime($arr[$k+1]['endtime'])>$etime){
							if($stime>0){
								if($etime>$stime){
									$timediff = $etime-$stime;
									$days = intval($timediff/86400);
									$sum+=($days*$latefee['money']*0.003);
									break;
								}else{
									 $days=0;
								}
							}else{
								$days=0;
							}
						}else{
							$timediff = strtotime($arr[$k+1]['endtime'])-$stime;
							$days = intval($timediff/86400);
							$sum+=($days*$latefee['money']*0.003);
							break;
						}
					}else{
						if($etime>$stime){
							$timediff = $etime-$stime;
							$days = intval($timediff/86400);
							$sum+=($days*$latefee['money']*0.003);
							break;
						}else{
							 $days=0;
						}
					}
					
				}
			}else{
				$sum=0;
				$days=0;
			}
			
			// echo $days.',';
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
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']}")->order('id asc')->Field('id,endtime,returninterest,nowinterest,Principal')->select();
			// var_dump($arrs[1]);
			$time=date("Y-m-d",time());
			$times=explode('-', $time);
			
			foreach ($arr as $k => $v) {
				$arrs=explode('-', $v['endtime']);
				if($arrs[1]==$times[1]){
					$data[$key]['whlx']=$v['nowinterest']-$v['returninterest'];
					$data[$key]['yhlx']=$v['returninterest'];
					break;
				}
			}
		//本金余额
			$minpri=array();
			foreach ($arr as $k => $v) {
				array_push($minpri, $v['principal']);
			}
			$data[$key]['principal']=min($minpri);
		}
		
		//统计数据
		foreach ($data as $k => $v) {
			$twhlx+=$v['whlx'];
			$tyhlx+=$v['yhlx'];
			$tmoney+=$v['money'];
			$tprincipal+=$v['principal'];
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
		$this->assign("tprincipal",$tprincipal);
		$this->assign("tfee",$tfee);
		$this->assign("tmargin",$tmargin);
		$this->assign("taccumulative",$taccumulative);
		$this->assign("tlatefee",$tlatefee);
		$this->display();
	}

	//速贷
	public function indexsd(){
		
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
			// echo M("Cashdetails")->getlastsql().'<br>';
			$sum=0;
			foreach ($arr as $k => $val) {
				$stime=strtotime($arr[$k]['starttime']);
				// echo $stime.',';
				$etime=time();
				if(strtotime($arr[$k+1]['starttime'])>0){
					if(strtotime($arr[$k+1]['starttime'])>$etime){
						if($stime>0){
							if($etime>$stime){
								$timediff = $etime-$stime;
								$days = intval($timediff/86400);
								$sum+=($days*$latefee['money']*0.003);
								break;
							}else{
								 $days=0;
							}
						}else{
							$days=0;
						}
					}else{
						$timediff = strtotime($arr[$k+1]['starttime'])-$stime;
						$days = intval($timediff/86400);
						$sum+=($days*$latefee['money']*0.003);
						break;
					}
				}else{
					if($etime>$stime){
						$timediff = $etime-$stime;
						$days = intval($timediff/86400);
						$sum+=($days*$latefee['money']*0.003);
						break;
					}else{
						 $days=0;
					}
				}
				
			}
			// echo $days.',';
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
			$arr=M("Cashdetails")->where("uid={$data[$key]['id']}")->order('id asc')->Field('id,starttime,returninterest,nowinterest,Principal')->select();
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

			//本金余额
			$minpri=array();
			foreach ($arr as $k => $v) {
				array_push($minpri, $v['principal']);
			}
			$data[$key]['principal']=min($minpri);

		}

		//统计数据
		foreach ($data as $k => $v) {
			$twhlx+=$v['whlx'];
			$tyhlx+=$v['yhlx'];
			$tmoney+=$v['money'];
			$tprincipal+=$v['principal'];
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
		$this->assign("tprincipal",$tprincipal);
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
			 //给初始本金复制
			$data[$key]['csbj']=$latefee['money'];
		}

		$sum=$zsum-$latefee['returnlaf'];
		$dataznj['Latefee']=$sum;
		M("Cash")->where("id={$id}")->save($dataznj);
		//跟进信息
		$records=M("xj_record")->where("c_id={$id} and type = 1")->order('addtime desc')->select();
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
		$data=M("Cashdetails")->where("uid={$id}")->order("num asc")->select();
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

		$whqs=array();
		foreach ($data as $key => $value) {
			 $zyqd+=$value['yqday'];
			 $zyqm+=$value['yqmoney'];
			 //给初始本金复制
			$data[$key]['csbj']=$latefee['money'];
			if($value['repayment']==1){
				$whqs[$key]=$value;
			}
		}
		$sum=$zsum-$latefee['returnlaf'];
		$dataznj['Latefee']=$sum;
		M("Cash")->where("id={$id}")->save($dataznj);

		//跟进信息
		$records=M("xj_record")->where("c_id={$id} and type = 1")->order('addtime desc')->select();
		$count=count($records);//得到数组元素个数
		 $Page= $this->getPages($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		 $records = array_slice($records,$Page->firstRow,$Page->listRows);
		 $this->show= $Page->show();
		
		$this->assign("ids",$id);
		$this->assign("details",$data);
		$this->assign("whqs",$whqs);
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
				$uid=M("applys")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
				$cardid=M("applys")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
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
			  		$adimds=array(65,66,127,128,136,118,147,172);
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
			  		$adimds=array(65,66,127,128,136,118,147,172);
			  	}else{
			  		$adimds=M("admin")->where("branch_id={$post['branch']}")->getField("id",true);
			  	}
			  	$where['salesman']=array("in",$adimds);	

			  }elseif($post['sou']){
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>$post['sou']))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("applys")->where(array("name"=>array("like","%{$post['sou']}%")))->getField("id",true);
					$cardid=M("applys")->where(array("phone"=>array("like","%{$post['sou']}%")))->getField("id",true);
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

		$arr=M("state")->where("type=4")->Field("id")->select();
		foreach ($arr as $k => $v) {
			$arrs[]=$v['id'];
		}
		$where[]=array("a.state_id"=>array("in",$arrs));

		$data=M("applys")->alias("a")->join("state s on a.state_id=s.id")->where($where)->Field('s.id as sid,s.name as sname,s.addtime as saddtime,s.*,a.*')->order("a.addtime desc")->select();
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
		if(IS_GET){
			$_GET["starttime"]=strtotime($_GET["starttime"]." 00:00:00");
			$_GET["endtime"]=strtotime($_GET["endtime"]." 20:00:00");
			$_GET["caddtime"]=time();
			$times['lxtime']=time();
			M("Applys")->where("id={$_GET['aid']}")->save($times);
			$id=M("Cash")->add($_GET);
		}
		
		if($id){
			$maxid=M("cashdetails")->Field("max(id) as id")->find();
			M("applys")->where("id={$_GET['aid']}")->save(array("state_id"=>23));
			for($i=1;$i<=$_GET["num"];$i++){
				$data["uid"]=$id;
				$data["num"]=$i;
				$data["Principal"]=$_GET["money"];
				$data["rate"]=$_GET["rate"];
				if($i==1){
					$stime=date('Y-m-d',$_GET["starttime"]);
					$data["starttime"]= $stime;
					$data["id"]=$maxid['id']+1;
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

				//判断是否是一期，获取自定义结束时间
				if($_GET["num"]==1){
					$etime=date('Y-m-d',$_GET["endtime"]);
					$data["endtime"]= $etime;
				}

				$Cashdetailsid=M("Cashdetails")->add($data);
				$data["id"]=$Cashdetailsid+1;
				
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

	public function updsd(){

		$arr=M("cashdetails")->field("id,nowinterest,returnpri,returninterest,arrears,principal")->where("uid={$_POST['id']} and repayment=1")->select();

		$infos=array();
		$infos["uid"]=$_POST['id'];
		$type=$_POST['type'];
		$infos["time"]=time();
		$infos["returnlf"]=0;
		$infos["explain"]='归还利息';

		$ylx=$arr[0]['nowinterest'];
		$arrears=$arr[0]['arrears'];
		$hkje=$_POST["hkje"];
		if(count($arr)>1){
			if($ylx==$hkje){
				$data["returninterest"]=$ylx;
				if($hkje==$arrears){
					$data['repayment']=2;
					$data['Arrears']=0;
				}else{
					$data['repayment']=1;
					$data['Arrears']=$arrears-$hkje;
				}
				$num=M("cashdetails")->where("id=".$arr[0]['id'])->save($data);
				if($num){
					M("cash")->where("id=".$_POST['id'])->save(array('returnlaf'=>0));
					$infos["sum"]=$hkje;
					M("cashinfo")->add($infos);
					echo  "还款成功";
				}
			}else{
				echo  "请输入正确的利息金额";
			}
		}else{
			if($ylx==$hkje){
				if($arr[0]['returninbbterest']==0){
					$data["returninterest"]=$ylx;
					$data['Arrears']=$arr[0]['arrears']-$ylx;
					if($type=='sfd'){
						$data['repayment']=2;
					}
					$num=M("cashdetails")->where("id=".$arr[0]['id'])->save($data);
					if($num){
						$infos["sum"]=$hkje;
						M("cashinfo")->add($infos);
						echo  "还款成功";
					}
				}else{
					echo  "利息已还完,请归还本金";
				}
				
			}else{
				echo  "请输对利息金额";
			}
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
			$maxid=M("cashdetails")->Field("max(id) as id")->find();
			for($i=1;$i<=$_GET["num"];$i++){
				$data["uid"]=$id;
				$data["num"]=$i;
				$data["Principal"]=$_GET["money"];
				$data["rate"]=$_GET["rate"];
				if($i==1){
					$stime=date('Y-m-d',$_GET["starttime"]);
					$data["starttime"]= $stime;
					$data["id"]=$maxid['id']+1;
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

				//判断是否是一期，获取自定义结束时间
				if($_GET["num"]==1){
					$etime=date('Y-m-d',$_GET["endtime"]);
					$data["endtime"]= $etime;
				}
				
				$Cashdetailsid=M("Cashdetails")->add($data);
				$data["id"]=$Cashdetailsid+1;
				
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

	//结清按钮
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

	//提前结清
	public function upd_jq(){
		$id= I("post.id");
		$arr=M("cashdetails")->where("uid={$id} and repayment=1")->select();
		if($arr){
			echo 3;
			exit();
		}
		$data['state']=3;
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
		$where=array("state"=>array('in','2,3'));
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
			if($val['state']==3){
				$array=M("cashdetails")->where("uid=".$data[$key]['id'])->Field("SUM(returnpri),SUM(returninterest)")->select();
				$data[$key]['money']=$array[0]["sum(returnpri)"];
				$data[$key]['accumulative']=$array[0]["sum(returninterest)"];
				// var_dump($array);
			}
		}
		// var_dump($data);
		$this->assign("cashinfo",$data);

		$this->display();
	}

	public function record(){
		$data['c_id']=I('get.id');
		$data['content']=I('get.content');
		$data['admin_name']=session("aname");
		$data['addtime']=time();
		$data['type']=1;
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
		$data['money']=$data['money'];
		$this->assign('data',$data);
		$this->display('Monetary/addindexsd');
	}
	
	public function addsfd(){
		$id=$_GET['id'];
		$data=M("applys")->where("id={$id}")->find();
		// var_dump($data);
		$data['money']=$data['money'];
		$this->assign('data',$data);
		$this->display('Monetary/addindex');
	}

	// public function upd_money(){
	// 	//no是期数
	// 	$no=I('post.no');
	// 	$bjye=I('post.bjye');
	// 	$yll=I('post.yll');
	// 	$hkje=I('post.hkje');
	// 	$csbj=I('post.csbj');
	// 	$ids=I('post.ids');
	// 	//归还本金是否是最小一期
	// 	$nums=M("cashdetails")->where(array('repayment'=>1,'uid'=>$ids))->Field("min(num) as num")->find();
	// 	if($no==$nums['num']){
	// 		//修改还款金额
	// 		$ghbj=M("cashdetails")->where(array('num'=>$no,'uid'=>$ids))->Field("returnpri")->find();
	// 		M("cashdetails")->where(array('num'=>$no,'uid'=>$ids))->save(array("returnpri"=>($hkje+$ghbj['returnpri'])));
	// 		$cashdetails=M("cashdetails")->where("uid={$ids}")->select();
	// 		$cids=array();
	// 		foreach ($cashdetails as $value) {
	// 			//获取已经还过的本金
	// 			$yhbjs +=$value['returnpri'];
	// 			array_push($cids, $value['id']);
	// 			if($value['num']>=$no){
	// 				$principal+=$value['returnpri'];
	// 			}
	// 			if($value['num']==($no-1)){
	// 				//上月本金
	// 				$sybj=$value['principal'];
	// 			}
	// 		}
	// 		// 本金余额
	// 		if($sybj){
	// 			$datayll['Principal']=$sybj-$principal;
	// 		}else{
	// 			$datayll['Principal']=$bjye-$principal;
	// 		}
	// 		$data['nowpri']=0;

	// 		$datayll['nowinterest']=$datayll['Principal']*$yll;
	// 		$datayll['Arrears']=$datayll['nowinterest'];

	// 		$where[]=array('num'=>array('egt',$no));
	// 		$where[]=array('num'=>array('lt',count($cashdetails)));
	// 		$where[]=array("uid"=>$ids);
	// 		//利息从下一期开始
	// 		$wheres[]=array('num'=>array('gt',$no));
	// 		$wheres[]=array("uid"=>$ids);
	// 		// echo $yhbjs.'++'.$csbj;
	// 		if(($hkje+$yhbjs) < $csbj){
				
	// 			$row=M("cashdetails")->where($wheres)->save($datayll);
	// 			$row=M("cashdetails")->where($where)->save($data);
				
	// 			//最后一期的月还款
	// 			$data['nowpri']=$bjye-$principal;
	// 			$nowinterest=M("cashdetails")->where("id=".max($cids))->Field("nowinterest,returninterest")->find();
	// 			$data['Arrears']=$nowinterest['nowinterest']-$nowinterest['returninterest']+$data['nowpri'];
	// 			$num=M("cashdetails")->where("id=".max($cids))->save($data);

	// 		}else{
	// 			// echo 111;
	// 			$data['repayment']=2;
	// 			$row=M("cashdetails")->where($wheres)->save($datayll);
	// 			$row=M("cashdetails")->where($where)->save($data);
				
	// 			//最后一期的月还款
	// 			$data['nowpri']=$bjye-$principal;
	// 			$nowinterest=M("cashdetails")->where("id=".max($cids))->Field("nowinterest,returninterest")->find();
	// 			$data['Arrears']=$nowinterest['nowinterest']-$nowinterest['returninterest']+$data['nowpri'];
	// 			$num=M("cashdetails")->where("id=".max($cids))->save($data);
	// 		}
			
	// 		$ljlx=M("cashdetails")->where("uid={$ids}")->field("SUM(nowinterest) as totallx")->find();
	// 		M("cash")->where("id={$ids}")->save(array("accumulative"=>$ljlx['totallx']));
	// 		if($num){
	// 				$infos["time"]=time();
	// 				$infos["uid"]=$ids;
	// 				$infos["explain"]='归还本金';
	// 				$infos["sum"]=$hkje;
	// 				$infos["returnlf"]=0;
	// 				M("cashinfo")->add($infos);
	// 				echo "还款成功";
	// 		}else{
	// 			echo "还款失败";
	// 		}
	// 	}else{
	// 		echo "必须先还上面未还期数";
	// 	}
	// }

	//归还利息之前归还本金，当期利息改变
	public function before_money(){
		//no是期数
		//no是期数
		$yhlx=I('post.yhlx');
		$no=I('post.no');
		$bjye=I('post.bjye');
		$yll=I('post.yll');
		$hkje=I('post.hkje');
		$csbj=I('post.csbj');
		$ids=I('post.ids');
		//归还本金是否是最小一期
		$nums=M("cashdetails")->where(array('repayment'=>1,'uid'=>$ids))->Field("min(num) as num")->find();
		if($no==$nums['num']){
			//修改还款金额
			$ghbj=M("cashdetails")->where(array('num'=>$no,'uid'=>$ids))->Field("returnpri")->find();
			M("cashdetails")->where(array('num'=>$no,'uid'=>$ids))->save(array("returnpri"=>($hkje+$ghbj['returnpri'])));
			$cashdetails=M("cashdetails")->where("uid={$ids}")->select();
			$cids=array();
			foreach ($cashdetails as $value) {
				//获取已经还过的本金
				$yhbjs +=$value['returnpri'];
				array_push($cids, $value['id']);
				if($value['num']>=$no){
					$principal+=$value['returnpri'];
				}
				if($value['num']==($no-1)){
					//上月本金
					$sybj=$value['principal'];
				}
			}
			// 本金余额
			if($sybj){
				$datayll['Principal']=$sybj-$principal;
			}else{
				$datayll['Principal']=$csbj-$yhbjs;
			}
			$datayll['nowpri']=0;

			$datayll['nowinterest']=$datayll['Principal']*$yll;
			$datayll['Arrears']=$datayll['nowinterest'];
			//判断是否是当前利息
			if($yhlx>0){
				//利息从下一期开始
				$where[]=array('num'=>array('gt',$no));
			}else{
				$where[]=array('num'=>array('egt',$no));
				// $where[]=array('num'=>array('lt',count($cashdetails)));
			}
			$where[]=array("uid"=>$ids);

			// echo $csbj.'++'.$yhbjs.'++'.$hkje;
			if($yhbjs < $csbj){
				$row=M("cashdetails")->where($where)->save($datayll);
				//最后一期的月还款
				// $data['nowpri']=$bjye-$principal;
				$nowinterest=M("cashdetails")->where("id=".max($cids))->Field("nowinterest,returninterest")->find();
				$data['Principal']=$csbj-$yhbjs;
				$data['nowpri']=$data['Principal'];
				$data['Arrears']=$nowinterest['nowinterest']-$nowinterest['returninterest']+$data['nowpri'];
				$num=M("cashdetails")->where("id=".max($cids))->save($data);

			}else{
				$datayll['repayment']=2;
				$data['repayment']=2;
				$row=M("cashdetails")->where($where)->save($datayll);
				//最后一期的月还款
				// $data['nowpri']=$bjye-$principal;
				$nowinterest=M("cashdetails")->where("id=".max($cids))->Field("nowinterest,returninterest")->find();
				$data['Principal']=$csbj-$yhbjs;
				$data['nowpri']=$data['Principal'];
				$data['Arrears']=$nowinterest['nowinterest']-$nowinterest['returninterest']+$data['nowpri'];
				$num=M("cashdetails")->where("id=".max($cids))->save($data);

			}
			
			$ljlx=M("cashdetails")->where("uid={$ids}")->field("SUM(nowinterest) as totallx")->find();
			M("cash")->where("id={$ids}")->save(array("accumulative"=>$ljlx['totallx']));
			if($num || $row){
					$infos["time"]=time();
					$infos["uid"]=$ids;
					$infos["explain"]='归还本金';
					$infos["sum"]=$hkje;
					$infos["returnlf"]=0;
					M("cashinfo")->add($infos);
					echo "还款成功";
			}else{
				echo "还款失败";
			}
		}else{
			echo "必须先还上面未还期数";
		}
	}

	public function upd_fj(){
		$hkje=I('post.hkje');
		$ids=I('post.ids');
		$row=M("Cash")->where("id=".$ids)->save(array("fine"=>$hkje));
		if($row){
			M("cashinfo")->add(array("uid"=>$ids,'time'=>time(),'sum'=>$hkje,'returnlf'=>0,'explain'=>'归还罚息金额'));
			echo "还款成功";
		}else{
			echo "还款失败";
		}
	}

	public function upd_znj(){
		$hkje=I('post.hkje');
		$ids=I('post.ids');
		$znj=I('post.znj');
		if($hkje<=$znj){
			$znjs=M("cash")->field("returnlaf")->find($ids);
			$cah["returnlaf"]=$hkje+$znjs['returnlaf'];
					$num=M("Cash")->where("id={$ids}")->save($cah);
					if($num){
						//插入明细表
						$infos["time"]=time();
						$infos["uid"]=$ids;
						$infos["explain"]='归还滞纳金';
						$infos["sum"]=$hkje;
						$infos["returnlf"]=$hkje;
						M("cashinfo")->add($infos);
						echo "还款成功";
					}
			}else{
				echo "请输对滞纳金金额";
			}
	}

	//获取每期开始时间
	public function starttimes($endtime){
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
					$arrs['2']=$arrs['2']+1;
				}
				$arrs['1']=$arrs['1']<10?'0'.$arrs['1']:$arrs['1'];
				$arrs['2']=$arrs['2']<10?'0'.$arrs['2']:$arrs['2'];
				$cftime=implode($arrs, "-");
				
				return $cftime;
	}

	//获取每期结束时间
	public function endtimes($arr){
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
		return $endtime;
	}

	public function newsd(){
		$id=$_GET['id'];
		$data=M("cashdetails")->where("uid={$id}")->order("num asc")->select();
		$flag=false;
		foreach ($data as $key => $value) {
			if($value['repayment']==1){
				$flag=true;
			}
		}
		if($flag){
			$max_id=M("cashdetails")->Field("Max(id) as id")->find();
			$mid=$max_id['id']+1;
			$ids=$data[count($data)-1]['id'];
			$arrs=$data[count($data)-1]['endtime'];
			$st=$this->starttimes($arrs);
			$arr=explode('-',$st);
			$et=$this->endtimes($arr);
			$endqs['id']=$mid;
			$endqs['Principal']=$data[count($data)-1]['nowpri'];
			$endqs['nowpri']=$endqs['Principal'];
			$endqs['nowinterest']=$endqs['nowpri']*$data[count($data)-1]['rate'];
			$endqs['returnpri']=0;
			$endqs['returninterest']=0;
			$endqs['returninterest']=0;
			$endqs['Arrears']=$endqs['nowpri']+$endqs['nowinterest'];
			$endqs['starttime']=$st;
			$endqs['endtime']=$et;
			$endqs['num']=count($data)+1;
			$row=M("cashdetails")->where("id={$ids}")->save($endqs);
			if($row){
				//修改cash期数
				M("cash")->where("id={$id}")->save(array("num"=>$endqs['num'],"endtime"=>strtotime($et."00:00:00")));
				//新增一期
				$array=$data[count($data)-2];
				unset($array['id']);
				$array['Principal']=$data[count($data)-1]['principal'];
				$array['num']=count($data);
				$array['id']=$data[count($data)-1]['id'];
				$array['uid']=$data[count($data)-1]['uid'];
				$array['rate']=$data[count($data)-1]['rate'];
				$array['yqday']=0;
				$array['yqmoney']=0;
				$array['returnpri']=$data[count($data)-1]['returnpri'];
				$array['returninterest']=$data[count($data)-1]['returninterest'];
				$array['nowpri']=0;
				$array['starttime']=$data[count($data)-1]['starttime'];
				$array['nowinterest']=$data[count($data)-1]['nowinterest'];
				if($array['nowinterest']==$array['returninterest']){
					$array['repayment']=2;
					$array['Arrears']=0;
				}else{
					$array['repayment']=1;
					$array['Arrears']=$array['nowinterest'];
				}
				
				$num=M("cashdetails")->add($array);
				if($num){
					$this->success("成功加入一期", "/Admin/Monetary/editsd/id/{$id}");
				}else{
					$this->error("新增失败", "/Admin/Monetary/editsd/id/{$id}");
				}
				
			}else{
				$this->error("新增失败", "/Admin/Monetary/editsd/id/{$id}");
			}
			
		}else{
			$this->error("贷款已还完，不能再新增", "/Admin/Monetary/editsd/id/{$id}");
		}
		
	}

	public function before_sfd(){
		$id=$_POST['id'];
		$hkje=$_POST['hkje'];
		$data=M("cashdetails")->where("uid={$id} and repayment=1")->order("num asc")->select();
		// var_dump($data[0]['principal']);
		$arr=M("cashdetails")->where("uid={$id}")->select();

		if($hkje==$data[0]['principal'] && $data[0]['num'] != count($arr)){
			$upd['Principal']=0;
			$upd['nowpri']=0;
			$upd['nowinterest']=0;
			$upd['Arrears']=0;
			$upd['repayment']=2;
			$where=array("uid"=>$id,"num"=>array(array("egt",$data[0]['num']),array("elt",count($arr))));

			$row=M("cashdetails")->where($where)->save($upd);
			$upds['returnpri']=$hkje+$data[0]['returnpri'];
			M("cashdetails")->where(array("num"=>$data[0]['num'],"uid"=>$id))->save($upds);
			//插入明细表
			if($row){
				$array['uid']=$id;
				$array['time']=time();
				$array['sum']=$hkje;
				$array['returnlf']=0;
				$array['explain']='提前归还本金';
				M('cashinfo')->add($array);
				echo "还款成功";
			}else{
				echo "还款失败";
			}
		}elseif($hkje<=$data[0]['principal']){
			//现在还的本金加上已经还的本金
			if(($hkje+$data[0]['returnpri'])<=$data[0]['nowpri']){
				$datas['Principal']=$data[0]['principal']-$hkje;
				$data['returnpri']=$hkje+$data[0]['returnpri'];
				$data['Arrears']=$data[0]['arrears']-$hkje;
				if($data['Arrears']>0){
					$data['repayment']=1;
				}else{
					$data['repayment']=2;
				}

				$where=array("uid"=>$id,"num"=>array(array("egt",$data[0]['num']),array("elt",count($arr))));
					$row=M("cashdetails")->where($where)->save($datas);
				if($row){
					$wheres=array("uid"=>$id,"num"=>$data[0]['num']);
					$no=M("cashdetails")->where($wheres)->save($data);
					if($no){
						$array['uid']=$id;
						$array['time']=time();
						$array['sum']=$hkje;
						$array['returnlf']=0;
						$array['explain']='归还本金';
						M('cashinfo')->add($array);
						echo "还款成功";
					}else{
						echo "还款失败";
					}
				}else{
					echo "还款失败";
				}
			}else{
				echo "请确认还款余额，重新输入";
			}
		}else{
			echo "请确认还款余额，重新输入";
		}
	}

		
}

 ?>