<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class BaseController extends CommonController {


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->model = M("uclient");
	}
	
	public function getBase(){
		$id = I('get.id', 0);
		$modal = I('get.modal', 0);
		if($data = M($modal)->find($id)){
			$huiy = M("Admin")->where(array("id"=>$data['member_id']))->field("username")->find();
			$tianj = M("Admin")->where(array("id"=>$data['user_id']))->field("username")->find();
			$data['member_id'] = $huiy['username'];
			$data['user_id'] = $tianj['username'];
			$data['addtime'] = date("Y-m-d h:i",$data['addtime']);
			$data['waddtime'] = date("Y-m-d h:i",$data['waddtime']);
			$this->ajaxReturn($data);
		}else{
			exit;
		}
	}
	
	 public function getStates(){
	    $id = I('get.id', 0);
		$modal = I('get.modal');
		if($data = M($modal)->find($id)){
			M($modal)->where(array('id' => $id))->save(array('state' => 1));
			$this->ajaxReturn($data);
		} 
		
	}

	//arr 数组 , $num 状态 ,$time 时间段 ,$srt 附加值
	public function nums($arr,$num,$time,$srt=false){
		 $where = array("admin_id"=>$arr,"state_id"=>$num);
		 if(!empty($time)){
		  $where[] = $time;
		 }
		 if($srt){
		  $where[] = $srt;
		 }
		 $count = M("uclienttime")->where($where)->count();
		return $count;
	}
	
	//arr 数组 , $num 状态 ,$time 时间段 ,$srt 附加值
	public function num_price($arr,$num,$time,$price="price",$srt=false){
		 $where = array("admin_id"=>$arr,"state_id"=>$num);
		 if(!empty($time)){
		  $where[] = $time;
		 }
		 $count = M("uclienttime")->where($where)->getField("u_id",true);
		 if($count){
		 $sing = implode(",",$count); 
		 $wheres = array("id"=>array("in",$sing));
		 if($srt){
		  $wheres[] = $srt;
		 }
		 $count = $this->model->where($wheres)->sum($price);
		 }else{
		 $count = 0; 
		 }
		return $count;
	}
	
	//arr 数组 , $num 状态 ,$time 时间段 ,$srt 附加值
	public function nums_count($arr,$num,$time,$srt=false){
		 $where = array("admin_id"=>$arr,"state_id"=>$num);
		 if(!empty($time)){
		  $where[] = $time;
		 }
		 $count = M("uclienttime")->where($where)->getField("u_id",true);
		 if($count){
		 $sing = implode(",",$count); 
		 $wheres = array("id"=>array("in",$sing));
		 if($srt){
		 $wheres[] = $srt;
		 }
		 $count = $this->model->where($wheres)->count();
		 }else{
		 $count = 0; 
		 }
		return $count;
	}
    
		public function sun_admin($lohb){
		    $b=array();
			foreach($lohb as $v){
				$b[]=$v['admin_id'];
			}
			$c=array_unique($b);
			foreach($c as $key=>$v){
				$n=0;
				foreach($lohb as $t){
					if($v==$t['admin_id'])
						$n++;
				   }
				$kun[$v] = $n;
			}
			$pos = array_search(max($kun), $kun);
			
			//业务员
			$admin_member_id = $pos;
			foreach($lohb as $ke=>$vl){
				if($vl['admin_id'] == $admin_member_id){
					$admin_arr[] = $vl;
				}
			}
			
			//print_r($admin_arr);exit;
			$member_ids = M("admin")->where(array("id"=>$admin_member_id))->getField("member_id");//跟进人
			$sun_admin['admin_member_id'] = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
			//数量
			$sun_admin['admin_member_sun'] = $kun[$pos];
			$sun_admin['admin_id'] = $admin_member_id;
			$sun_admin['state_id'] = $admin_arr[0]['state_id'];
			
			return $sun_admin;
	}
	
	public function sunprice($lohbss){
		   //print_r($lohbss);exit;
			$newArr = array(); 
			foreach ($lohbss as $v) {
				if (array_key_exists($v['admin_id'], $newArr)) {
					$newArr[$v['admin_id']]['hprice'] += $v['hprice'];
				} else {
					$newArr[$v['admin_id']] = $v;
				}
			}
	    $b = array();
		foreach($newArr as $key=>$value){
		$a=$newArr[$key];
		$b = array_merge($a,$b);
		sort($b);
		}
		foreach($newArr as $kuy=>$vul){
			if($vul['hprice'] == $b[count($b)-1]){
				 $srt[] = $vul;
			}
		}
		//print_r($srt);exit;
		$member_ids = M("admin")->where(array("id"=>$srt[0]['admin_id']))->getField("member_id");//跟进人
		$sun_admin = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
		$arr['admin_member_id'] = $sun_admin;
		$arr['admin_id'] = $srt[0]['admin_id'];
		$arr['hprice'] = $srt[0]['hprice'];
		$arr['u_id'] = $srt[0]['id'];
		return $arr;
	}
	

	
    public function index()
    {   
	     // echo date("Y-m-d H:i:s",'1501683117');EXIT;
	     //龙虎榜  本月
        //约见客户 		
		  $lohb = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $lohb[] = array("state_id"=>8);
		  $lohb = M("uclienttime")->where($lohb)->select();//约见客户
		  $this->admin_member_sun = $this->sun_admin($lohb);
		  
		//签单 		
		  $lohb = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $lohb[] = array("state_id"=>10);
		  $lohb = M("uclienttime")->where($lohb)->select();//约见客户
		  $this->admin_member_dan = $this->sun_admin($lohb); 
        //放款 		
		  $lohb = array("fitime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $lohb[] = array("state_id"=>15);
		  $lohbs = M("uclient")->where($lohb)->select();//约见客户
		  $this->admin_member_dans = $this->sun_admin($lohbs); 
		 //货款额最高的		
		  $lohb = array("ctime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $lohb[] = array("state_id"=>15,"finance_state"=>1);
		  $lohbss = M("uclient")->where($lohb)->field("id,hprice,admin_id")->select();//约见客户
		  
		  $this->admin_member_danss = $this->sunprice($lohbss);  
		
		
	    if(in_array('1001',$_SESSION['level'])){
		  $kun =  array('admin_id'=>array('gt',0));	
		}else if(in_array('1002',$_SESSION['level'])){
		  $admin = M("admin")->where(array("id"=>session("aid")))->find();
		  $level = M("rank")->where(array("id"=>$admin['level_id']))->find();
		  $cids = M("rank")->where(array("pid" => $level['id']))->getField("id", true);
		  $cids[] = $level["id"];
		  $cids = implode(",", $cids);
		  $all_id = M("admin")->where(array('level_id'=>array("in",$cids)))->getField('id', true);
		  $all_id = implode(",", $all_id);
		  $kun = array('admin_id'=>array('in',$all_id));	
		}else if(in_array('1004',$_SESSION['level'])){
		  $kun = array(array('admin_id'=>session('aid'),'_logic'=>'or',"rank_admin_id"=>session('aid'),'_logic'=>'or',"finance_admin_id"=>session('aid')));
		}else{
		  $kun = array(array('admin_id'=>session('aid'),'_logic'=>'or',"rank_admin_id"=>session('aid'),'_logic'=>'or',"finance_admin_id"=>session('aid')));	
		}
		
		/**今日时间结束**/
		//总累计
		$arr[] = $kun;
		$arr[] = $this->statewc();
	    $this->total = $this->model->where($arr)->count(); //总累计
		//本日累计
		/**今日时间**/
		$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		$stimes = strtotime(date('Y-m-d 24:0:0'));
		$weeks = array('addtime' => array(array('gt',$stime),array("elt",$stimes)));
		$weeks[] = $kun;
		$weeks[] = $this->statewc();
	    $this->weeks = $this->model->where($weeks)->count(); //本日累计
		//echo $this->model->getLastSql();exit;
		//系统总分配
		 $systems = $kun;
		 $systems[] = array("allot"=>1);
		 $systems[] = $this->statewc();
		 $this->systems = $this->model->where($systems)->count(); //系统总分配
		 
		 //今日系统分配
		 $tosystem = $kun;
		 $tosystem[] = $this->statewc();
		 $tosystem[] = array("allot"=>1,'addtime' => array(array('gt',$stime),array("elt",$stimes)));
		 $this->tosystem = $this->model->where($tosystem)->count(); //今日系统分配
		 
		 //本月系统分配
		 $mosystem = $kun;
		 $mosystem[] = array("allot"=>1,'addtime' => array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		 $mosystem[] = $this->statewc();
		 $this->mosystem = $this->model->where($mosystem)->count(); //本月系统分配

		//本月累计
		$weekss = array('addtime' => array('gt',strtotime(date('Y-m-01 00:00:01'))));
		$weekss[] = $kun;
		$weekss[] = $this->statewc();
	    $this->weekss = $this->model->where($weekss)->count(); //本月累计

		//本日已完成的
		$completed = array("finance_state"=>1,'ctime' => array('gt',date(mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))));
		$completed[] = $kun;
		$completed[] = $this->statewc();
	    $this->completed = $this->model->where($completed)->count(); //本周已完成的数据
		
		$sty = $this->completed/$this->weeks*100;
		$this->sty = ceil($sty);
		
		
		//本周已成交的 completed
		$Dealdone_id = M("rank")->where(array("name"=>'财务部'))->getField("id"); 
		$Dealdone = array("finance_state"=>0,"finance_id"=>$Dealdone_id,'fitime' => array('gt',date(mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))));
		$Dealdone[] = $kun;
		$Dealdone[] = $this->statewc();
	    $this->Dealdone = $this->model->where($Dealdone)->count(); //本周已成交的数据
		
		/*******************************************************时间********************************************************************/
		
     	
		/******************************************************************列表*******************************************************************/
		
		
		
		if(in_array('1101',$_SESSION['level'])){
            if($_GET['stime'] || $_GET['endtime']){
			$post = I("get.");
			$stime = strtotime($post['stime']." 00:00:00");//开始时间
		    $endtime = strtotime($post['endtime']." 24:00:00");//开始时间
	        if($post['stime'] && $post['endtime']){
				 $wheres = array("time"=>array(array("gt",$stime),array('elt',$endtime)));	
			 }else{
				if($post['stime']){
					$wheres = array("time"=>array(array("gt",$stime)));	
				}else if($post['endtime']){
					$wheres = array("time"=>array(array("gt",$stime)));		
				}else{
				   $wheres = array();
				  }
				}	  
			 }
			$admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
			$admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
			$genjin = M("rank")->where(array("pid"=>$admin_name))->select();
			
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}
			$gen = $genjins;
		    $genjinss = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjinss)))->getField("id",true);
			$genjin = array_merge($gen, $genjins);
			$degen = implode(",",$genjin);
			$srt_arr = M("admin")->where(array("level_id"=>array("in",$degen)))->field("id,member_id")->select();
			$sun_as = 0;$sun_bs = 0;$sun_cs = 0;$sun_ds = 0;$sun_es = 0;$sun_cc = 0;
			foreach($srt_arr as $kv=>$vl){
			   	$srt_arr[$kv]['username'] = M("member")->where(array("id"=>$vl['member_id']))->getField("username");
			    $srt_arr[$kv]['sun_a'] = $this->nums($vl['id'],8,$wheres);//约见客户
				$sun_as += $srt_arr[$kv]['sun_a'];
				$srt_arr[$kv]['sun_b'] = $this->nums($vl['id'],10,$wheres);//签单
				$sun_bs += $srt_arr[$kv]['sun_b'];
				$su_time = array("state_id"=>15,"admin_id"=>$vl['id']);
				$srt_arr[$kv]['sun_c'] = $this->nums($vl['id'],15,$wheres);
				$sun_cs += $srt_arr[$kv]['sun_c'];
				$srt_arr[$kv]['sun_cc'] = $this->num_price($vl['id'],15,$wheres);
				$sun_cc += $srt_arr[$kv]['sun_cc'];
				if($srt_arr[$kv]['sun_cc']){
				$srt_arr[$kv]['sun_cc'] = $srt_arr[$kv]['sun_cc'];
				}else{
				$srt_arr[$kv]['sun_cc'] = 0;	
				}
				$srt_arr[$kv]['sun_d'] = $this->nums_count($vl['id'],15,$wheres,array("finance_state"=>1));
				$sun_ds += $srt_arr[$kv]['sun_d'];
				$srt_arr[$kv]['sun_e'] = $this->num_price($vl['id'],15,$wheres,'hprice',array("finance_state"=>1));//回款金额
				$sun_es += $srt_arr[$kv]['sun_e'];
				if($srt_arr[$kv]['sun_e']){
				$srt_arr[$kv]['sun_e'] = $srt_arr[$kv]['sun_e'];
				}else{
				$srt_arr[$kv]['sun_e'] = 0;	
				}
			}
			$this->sun_as = $sun_as;$this->sun_bs = $sun_bs;$this->sun_cs = $sun_cs;$this->sun_ds = $sun_ds;$this->sun_es = $sun_es;
			$this->sun_cc = $sun_cc;
			//print_r($srt_arr);exit;
			
			if($_GET['sx']){
			if($_GET['sx'] == 1){
				$vas = 'sun_a';
			}else if($_GET['sx'] == 2){
				$vas = 'sun_b';
			}else if($_GET['sx'] == 3){
				$vas = 'sun_c';
			}else if($_GET['sx'] == 4){
				$vas = 'sun_d';
			}	
			foreach ($srt_arr as $keys => $rows) {
				$volume[$keys]  = $rows[$vas];
				//$edition[$keys] = $rows['edition'];
			}
			array_multisort($volume, SORT_DESC, $srt_arr);
			}
			//$this->srt_arr = $srt_arr;
			$count=count($srt_arr);//得到数组元素个数
			$Page= $this->getPage($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
			$srt_arr = array_slice($srt_arr,$Page->firstRow,$Page->listRows);
			$show= $Page->show();// 分页显示输出?
			//print_r($newarray_1);
			$this->assign("srt_arr",$srt_arr);
			$this->assign('show',$show);// 赋值分页输出
			
		}else if(in_array('1102',$_SESSION['level'])){
			if($_GET['stime'] || $_GET['endtime']){
			$post = I("get.");
			$stime = strtotime($post['stime']." 00:00:00");//开始时间
		    $endtime = strtotime($post['endtime']." 24:00:00");//开始时间
	        if($post['stime'] && $post['endtime']){
				 $wheres = array("addtime"=>array(array("gt",$stime),array('elt',$endtime)));	
			 }else{
				if($post['stime']){
					$wheres = array("addtime"=>array(array("gt",$stime)));	
				}else if($post['endtime']){
					$wheres = array("addtime"=>array(array("gt",$stime)));		
				}else{
				   $wheres = array();
				  }
				}	  
			 }
			$yewhere = array("allot"=>1,"state_id"=>0);
			$yewhere[] = 	$kun;
			if(!empty($wheres)){
			$yewhere[] = 	$wheres;
			}
			$srt_arr = $this->model->where($yewhere)->order("addtime")->select();
			foreach($srt_arr as $key=>$val){
				$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			    $srt_arr[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			}
			$this->srt_arr = $srt_arr;
		}else if(in_array('1103',$_SESSION['level'])){ //渠道工作台
			if($_GET['stime'] || $_GET['endtime']){
			$post = I("get.");
			$stime = strtotime($post['stime']." 00:00:00");//开始时间
		    $endtime = strtotime($post['endtime']." 24:00:00");//开始时间
	        if($post['stime'] && $post['endtime']){
				 $wheres = array("time"=>array(array("gt",$stime),array('elt',$endtime)));	
			 }else{
				if($post['stime']){
					$wheres = array("time"=>array(array("gt",$stime)));	
				}else if($post['endtime']){
					$wheres = array("time"=>array(array("gt",$stime)));		
				}else{
				   $wheres = array();
				  }
				}	  
			 }
			$admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
			$admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
			$genjin = M("rank")->where(array("pid"=>$admin_name))->select();
			
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}
			$gen = $genjins;
		    $genjinss = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjinss)))->getField("id",true);
			$genjin = array_merge($gen, $genjins);
			$degen = implode(",",$genjin);
			$srt_arr = M("admin")->where(array("level_id"=>array("in",$degen)))->field("id,member_id")->select();
			$sun_as = 0;$sun_bs = 0;$sun_cs = 0;$sun_ds = 0;$sun_es = 0;$sun_cc = 0;
			foreach($srt_arr as $kv=>$vl){
			   	$srt_arr[$kv]['username'] = M("member")->where(array("id"=>$vl['member_id']))->getField("username");
			    $srt_arr[$kv]['sun_a'] = $this->nums($vl['id'],12,$wheres);//进件审批
				$sun_as += $srt_arr[$kv]['sun_a'];
				
				$srt_arr[$kv]['sun_b'] = $this->nums($vl['id'],11,$wheres);//进件放弃
				$sun_bs += $srt_arr[$kv]['sun_b'];
				

				$srt_arr[$kv]['sun_c'] = $this->nums($vl['id'],13,$wheres);//审批通过
				$sun_cs += $srt_arr[$kv]['sun_c'];
				if($srt_arr[$kv]['sun_c']){
				$srt_arr[$kv]['sun_c'] = $srt_arr[$kv]['sun_c'];
				}else{
				$srt_arr[$kv]['sun_c'] = 0;	
				}
				
				$srt_arr[$kv]['sun_cc'] = $this->nums($vl['id'],14,$wheres);//审批否决
				$sun_cc += $srt_arr[$kv]['sun_cc'];
				if($srt_arr[$kv]['sun_cc']){
				$srt_arr[$kv]['sun_cc'] = $srt_arr[$kv]['sun_cc'];
				}else{
				$srt_arr[$kv]['sun_cc'] = 0;	
				}
				$srt_arr[$kv]['sun_d'] = $this->nums($vl['id'],15,$wheres);
				$sun_ds += $srt_arr[$kv]['sun_d'];
				
				
			}
			$this->sun_as = $sun_as;$this->sun_bs = $sun_bs;$this->sun_cs = $sun_cs;$this->sun_ds = $sun_ds;$this->sun_es = $sun_es;
			$this->sun_cc = $sun_cc;
			
			if($_GET['sm']){
			if($_GET['sm'] == 1){
				$vas = 'sun_a';
			}else if($_GET['sm'] == 2){
				$vas = 'sun_b';
			}else if($_GET['sm'] == 3){
				$vas = 'sun_c';
			}else if($_GET['sm'] == 4){
				$vas = 'sun_cc';
			}else if($_GET['sm'] == 5){
				$vas = 'sun_d';
			}	
			foreach ($srt_arr as $keys => $rows) {
				$volume[$keys]  = $rows[$vas];
				//$edition[$keys] = $rows['edition'];
			}
			array_multisort($volume, SORT_DESC, $srt_arr);
			}
			//$this->srt_arr = $srt_arr;
			$count=count($srt_arr);//得到数组元素个数
			$Page= $this->getPage($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
			$srt_arr = array_slice($srt_arr,$Page->firstRow,$Page->listRows);
			$show= $Page->show();// 分页显示输出?
			//print_r($newarray_1);
			$this->assign("srt_arrs",$srt_arr);
			$this->assign('show',$show);// 赋值分页输出
		}
		
		
	
		
		/****************************************************************列表结束****************************************************************/
	    /***********************************************************业务员跟业务经理项***********************************************************************/ 
		  if(in_array('1101',$_SESSION['level']) || in_array('1102',$_SESSION['level'])){
		  $stime = strtotime(date('Y-m-d 00:00:00'));//今天时间
		  $stimes = strtotime(date('Y-m-d 24:00:00'));
		  //总录入客户 allot=>0表示手动录入 =>1表示系统分配 
		  $allot = array("allot"=>0);
		  $Totals = $allot;
		  $Totals[] = $kun;
		  $Totals[] = $this->statewc();
	      $this->Totals = $this->model->where($Totals)->count();
		  
		  //今日收录客户
		  $comtoday = $allot;
		  $comtoday[] = array('addtime' => array(array('gt',$stime),array("elt",$stimes)));
		  $comtoday[] = $kun;
		  $comtoday[] = $this->statewc();
		  $this->comtoday = $this->model->where($comtoday)->count();
		  
		  //本月录入客户
		  $common = $allot;
		  $common[] = array('addtime' => array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $common[] = $kun;
		  $common[] = $this->statewc();
		  $this->common = $this->model->where($common)->count();
	      $today = array('time' => array(array('gt',$stime),array("elt",$stimes)));//今日时间
		  
		  /*业务员计算总数*/
		  $yew1 = M("admin")->where(array("id"=>session('aid')))->field("level_id")->find();
		  $rank1[0] = M("rank")->where(array("id"=>$yew1['level_id']))->find();
		  if($rank1[0]['pid'] == 0){//echo 2;exit;
			$genjin = M("rank")->where(array("pid"=>$rank1[0]['id']))->select();
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}
		    $genjins = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
			$genjin = array_merge($genjin, $genjins);
			foreach($genjin as $kbs=>$vbs){
			$rank2[$kbs] =$vbs['id'];
		    }
		    $rank2 = implode(",",$rank2);
			if(!in_array('1101',$_SESSION['level'])){
			$admin1 = M("admin")->where(array("level_id"=>array("in",$rank2)))->getField("id",true);	
			}
		    $admin1 = implode(",",$admin1);
		  }else{
			 if(M("rank")->where(array("pid"=>$rank1[0]['id']))->find()){
			 $genjin = M("rank")->where(array("pid"=>$rank1[0]['id']))->select();
			 $genjin = array_merge($genjin, $rank1); 
			 foreach($genjin as $kbs=>$vbs){
			 $rank2[$kbs] =$vbs['id'];
		     }
		     $rank2 = implode(",",$rank2);
		     $admin1 = M("admin")->where(array("level_id"=>array("in",$rank2)))->getField("id",true);
		     $admin1 = implode(",",$admin1);
			 }else{
			  $admin1 = session("aid"); 	 
			 }		
		  }

		  /*业务员的 今天*/
		  $to = $today;	  
		  $to[] = array("state_id"=>5);
		  if(!in_array('1101',$_SESSION['level'])){
		  $to[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->com = M("uclienttime")->where($to)->count();//意向客户
		  
		  $Meet = $today;
		  $Meet[] = array("state_id"=>8);
		  if(!in_array('1101',$_SESSION['level'])){
		  $Meet[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->Meet = M("uclienttime")->where($Meet)->count();//约见客户
		  //$this->Meet = $this->sun($Meet,$kun);
		  
		  $Sign = $today;
		  $Sign[] = array("state_id"=>10);
		  if(!in_array('1101',$_SESSION['level'])){
		  $Sign[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->Sign = M("uclienttime")->where($Sign)->count();//签约客户
		  //$this->Sign = $this->sun($Sign,$kun);
		  
		  /*业务员的 本月*/
		  $month = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $mocom = $month;
		  $mocom[] = array("state_id"=>5);
		  if(!in_array('1101',$_SESSION['level'])){
		  $mocom[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->mocom = M("uclienttime")->where($mocom)->count();//意向客户
		  //$this->mocom = $this->sun($mocom,$kun);
		  
		  $moMeet = $month;
		  $moMeet[] = array("state_id"=>8);
		  if(!in_array('1101',$_SESSION['level'])){
		  $moMeet[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->moMeet = M("uclienttime")->where($moMeet)->count();//约见客户
		  //$this->moMeet = $this->sun($moMeet,$kun);
		  
		  $moSign = $month;
		  $moSign[] = array("state_id"=>10);
		  if(!in_array('1101',$_SESSION['level'])){
		  $moSign[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->moSign = M("uclienttime")->where($moSign)->count();//签约客户
		 // echo M("uclienttime")->getLastSql();exit;
		
		  /*业务员的 总计*/
		  $totalcom = array("state_id"=>5);
		  if(!in_array('1101',$_SESSION['level'])){
		  $totalcom[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->totalcom = M("uclienttime")->where($totalcom)->count();//意向客户
		  //echo  M("uclienttime")->getLastSql();exit;
		  
		  $totalMeet = array("state_id"=>8);
		  if(!in_array('1101',$_SESSION['level'])){
		  $totalMeet[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->totalMeet = M("uclienttime")->where($totalMeet)->count();//约见客户
		  
		  $totalSign = array("state_id"=>10);
		  if(!in_array('1101',$_SESSION['level'])){
		  $totalSign[] = array("admin_id"=>array("in",$admin1));
		  }
		  $this->totalSign = M("uclienttime")->where($totalSign)->count();//签约客户
		  //echo $this->totalSign;exit;
		  //echo  M("uclienttime")->getLastSql();exit;
		  }

	    /**********************************************************************************************************************************/ 
	    /******************************************************渠道****************************************************************************/
		if(in_array('1103',$_SESSION['level'])){
		
		$daytime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//时刻 今日
		//本月
		$month = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		
		$autograph = array("rank_admin_id"=>session("aid"));
		$autograph = M("uclient")->where($autograph)->getField("id",true);
		$autograph = implode(",",$autograph);
			
		//客户签约
		$this->autograph = $this->qudao(array("state_id"=>10),$autograph,$daytime);//今日签约
		$this->monthgraph = $this->qudao(array("state_id"=>10),$autograph,$month);//今日签约
		$this->monthall = $this->qudao(array("state_id"=>10),$autograph);//今日签约

		//进件审批
		$this->Incom = $this->qudao(array("state_id"=>12),$autograph,$daytime);//今日进件审批
		$this->Incomx = $this->qudao(array("state_id"=>12),$autograph,$month);//本月进件审批
		$this->Incomxs = $this->qudao(array("state_id"=>12),$autograph);//总进件审批
		
		//进件审批
		$this->Incoms = $this->qudao(array("state_id"=>11),$autograph,$daytime);//今日进件放弃
		$this->Incomsx = $this->qudao(array("state_id"=>11),$autograph,$month);//本月进件放弃
		$this->Incomsxs = $this->qudao(array("state_id"=>11),$autograph);//总进件放弃
		
		//审批通过
		$this->Approved = $this->qudao(array("state_id"=>13),$autograph,$daytime);//今日审批通过
		$this->Approvedx = $this->qudao(array("state_id"=>13),$autograph,$month);//本月审批通过
		$this->Approvedxs = $this->qudao(array("state_id"=>13),$autograph);//总审批通过
		
		//审批通过
		$this->veto = $this->qudao(array("state_id"=>14),$autograph,$daytime);//今日审批否决
		$this->vetox = $this->qudao(array("state_id"=>14),$autograph,$month);//本月审批否决
		$this->vetoxs = $this->qudao(array("state_id"=>14),$autograph);//总审批否决
		
		//审批通过
		$this->Success = $this->qudao(array("state_id"=>15),$autograph,$daytime);//今日成功放款
		$this->Successx = $this->qudao(array("state_id"=>15),$autograph,$month);//本月成功放款
		$this->Successxs = $this->qudao(array("state_id"=>15),$autograph);//总成功放款
        }
		/**********************************************************************************************************************************/
		/*****************************************************财务*****************************************************************************/
		if(in_array('1101',$_SESSION['level']) || in_array('1104',$_SESSION['level'])){
		$daytime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//时刻
		$monthtime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//月时刻
		//财务员

		//本月
		$month = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		
		$Loansun = array('fitime' => array(array('gt',$stime),array("elt",$stimes)));;
		$Loansun[] = array("state_id"=>15);
		$Loansun[] = $kun;
		$this->Loansun = M("uclient")->where($Loansun)->count();//今日放款
		
		$monthsun = array("fitime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		$monthsun[] = array("state_id"=>15);
		$monthsun[] = $kun;
		$this->monthsun = M("uclient")->where($monthsun)->count();//本月放款
		
		$Grandsun = $kun;
		$Grandsun[] = array("state_id"=>15);
		$this->Grandsun = M("uclient")->where($Grandsun)->count();//累计放款
		
		
		$Loanprice = array('fitime' => array(array('gt',$stime),array("elt",$stimes)));//时刻
		$Loanprice[] = array("state_id"=>15);
		$Loanprice[] = $kun;
		$this->Loanprice = M("uclient")->where($Loanprice)->sum('price');//本日放款
		
		$monthprice = array("fitime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		$monthprice[] = array("state_id"=>15);
		$monthprice[] = $kun;
		$this->monthprice = M("uclient")->where($monthprice)->sum('price');//本月放款
		
		
		$Grandprice = $kun;
		$Grandprice[] = array("state_id"=>15);
		$Grandprice = M("uclient")->where($Grandprice)->sum("price");//累计放款
		if($Grandprice){
		$this->Grandprice = $Grandprice;
		}else{
		$this->Grandprice = 0;	
		}
		
		//今日回款
		$kuns = $kun;
		$kuns[] = array('ctime' => array(array('gt',$stime),array("elt",$stimes)));
		$kuns[] = array("finance_state"=>1,"state_id"=>15);
		$this->Loansuns = M("uclient")->where(array($kuns))->count();
		
		$monthsuns =  array("ctime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		$monthsuns[] = array("state_id"=>15,"finance_state"=>1);
		$monthsuns[] = $kun;
		$this->monthsuns = M("uclient")->where(array($monthsuns))->count();//本月回款
		
		$Grandsuns = $kun;
		$Grandsuns[] = array("state_id"=>15,"finance_state"=>1);
		$this->Grandsuns = M("uclient")->where($Grandsuns)->count();//累计回款
		
		//今日回款
		$ku = $kun;
		$ku[] = array('ctime' => array(array('gt',$stime),array("elt",$stimes)));;
		$ku[] = array("finance_state"=>1,"state_id"=>15);
		$this->Loansunsx = M("uclient")->where(array($ku))->sum('hprice');//今日回款金额
		
		$kunsx = $kun;
		$kunsx[] = array("ctime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		$kunsx[] = array("state_id"=>15,"finance_state"=>1);
		$this->monthsunsx = M("uclient")->where(array($monthsuns))->sum('hprice');//本月回款金额
		
		$Grandsunsx = $kun;
		$Grandsunsx[] = array("state_id"=>15,"finance_state"=>1);
		$Grandsunsx = M("uclient")->where($Grandsunsx)->sum("hprice");//累计回款金额
		if($Grandsunsx){
		$this->Grandsunsx = $Grandsunsx;
		}else{
		$this->Grandsunsx = 0;	
		}
		
		
		//待回款
		$Loansunss = $daytime;
		$Loansunss[] = array("state_id"=>15);
		$Loansunss = M("uclienttime")->where($Loansunss)->select();
		$kunss = $kun;
		$kunss[] = array("finance_state"=>0);
		$this->Loansunss = $this->sun($Loansuns,$kunss);//今日回款
		
		$monthsunss = $month;
		$monthsunss[] = array("state_id"=>15);
		$monthsunss = M("uclienttime")->where($monthsunss)->select();
		$kunsw = $kun;
		$kunsw[] = array("finance_state"=>0);
		$this->monthsunss = $this->sun($monthsunss,$kunsw);//本月回款
		
		$Grandsunss = $kun;
		$Grandsunss[] = array("state_id"=>15,"finance_state"=>0);
		$this->Grandsunss = M("uclient")->where($Grandsunss)->count();//累计回款
		
		//今日待回款
		$Loansunsxs = $daytime;
		$Loansunsxs[] = array("state_id"=>15);
		$Loansunsxs = M("uclienttime")->where($Loansunsxs)->select();
		$kunsxs = $kun;
		$kunsxs[] = array("finance_state"=>0);
		$this->Loansunsxs = $this->suns($Loansunsxs,$kunsxs);//今日待回款
		
		$monthsunsxs = $month;
		$monthsunsxs[] = array("state_id"=>15);
		$monthsunsxs = M("uclienttime")->where($monthsunsxs)->select();
		$kunsxss = $kun;
		$kunsxss[] = array("finance_state"=>0);
		$this->monthsunsxs = $this->suns($monthsunsxs,$kunsxss);//本月待回款
		
		$Grandsunsxs = $kun;
		$Grandsunsxs[] = array("state_id"=>15,"finance_state"=>0);
		$Grandsunsxs = M("uclient")->where($Grandsunsxs)->sum("hprice");//累计待回款
		if($Grandsunsxs){
		$this->Grandsunsxs = $Grandsunsxs;
		}else{
		$this->Grandsunsxs = 0;	
		}  
		
		}
		/**********************************************************************************************************************************/
    	$this->display();
    }
	
	//计算渠道数
	public function qudao($state_id,$id,$time=false){
		   $where = $state_id;
		   if($id){
		   $where[] = array("u_id"=>array('in',$id));
		   if($time){
			$where[] = $time;
		   }
		   $count = M("uclienttime")->where($where)->count();
		   }else{
		   $count = 0;	   
		   }
		   return  $count;
	}
	
	//算数量
	public function sun($arr,$srt){
		$sunmocom = 0;
		  foreach($arr as $key=>$val){
			 $mys = array(array("id"=>$val['u_id'],"state_id"=>$val["state_id"]),$srt);
			 if($this->model->where($mys)->find()){
				 $sunmocom ++;
			  }
		  }
		return $sunmocom;
	}
	public function suns($arr,$srt){
		$sunmocom = 0;
		  foreach($arr as $key=>$val){
			 $mys = array(array("id"=>$val['u_id'],"state_id"=>$val["state_id"]),$srt);
			 $a = $this->model->where($mys)->find();
			// echo $this->model->getLastSql();exit;
			 if($a){
				 $sunmocom += $a['hprice'];
			  }
		  }
		return $sunmocom;
	}
	public function sunss($arr,$srt){
		$sunmocom = 0;
		  foreach($arr as $key=>$val){
			 $mys = array(array("id"=>$val['u_id'],"state_id"=>$val["state_id"]),$srt);
			 $a = $this->model->where($mys)->find();
			 if($a){
				 $sunmocom += $a['price'];
			  }
		  }
		return $sunmocom;
	}

	
    public function addindex()
    {
    	if(IS_POST)
    	{
    		$data = I("post.");
    		$data['footer'] = $_POST["footer"];
    		$data['contact'] = $_POST["contact"];
    		$data['contact2'] = $_POST["contact2"];
    		$data['contact3'] = $_POST["contact3"];
    		$data['contact4'] = $_POST["contact4"];
    		$data['contact5'] = $_POST["contact5"];
			$data["image"] = I("post.image", "");

    		$lang = $data["lang"];
    		if($arr=$this->model->where(array("lang" => $lang))->find())
    		{   //调用删除图片文件
		        $this->DelImage($arr,$data);
				
    			$this->model->where(array("lang" => $lang))->save($data);
				
    			echo 1;
    		}else
    		{
    			$this->model->add($data);
    			echo 1;
    		}
    	}
    }

    public function test()
    {
        if(IS_POST)
        {
            $oldpass = trim(I("post.oldpass"));
            $newpass = trim(I("post.newpass"));
            $renewpass = trim(I("post.renewpass"));

            if($oldpass == '' || $newpass == '' || $renewpass == '')
            {
                echo 0;
                return;
            }

            if($newpass != $renewpass)
            {
                echo 0;
                return;
            }

            $model = M("Admin");
            $aid = session("aid");
            if($model->where(array("id" => $aid, "password" => md5($oldpass)))->find())
            {
                if($model->where(array("id" => $aid, "password" => md5($oldpass)))->save(array("password" => md5($newpass),"passw" => $newpass)))
                {
                    echo 1;
                    return;
                }
            }
            else{
                echo 0;
                return;
            }
        }else
        {
            $this->display();
        }
    }
    
    public function clearHtml()
    {
        if(IS_POST)
        {
            /* $temp_path = APP_PATH . "Html/";
            $files = glob($temp_path."*.html");
            foreach ($files as $key => $value) {
                if(is_file($value))
                {
                    unlink($value);
                }
            } */
			
			$files = APP_PATH . "Runtime/";//要删除的目录
		    $this->delDirAndFile($files,1);
            echo 1;
            return;
        }
    }
	
	public function delDirAndFile($path, $delDir = FALSE) {
		$handle = opendir($path);
		if ($handle) {
			while (false !== ( $item = readdir($handle) )) {
				if ($item != "." && $item != "..")
					is_dir("$path/$item") ? $this->delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
			}
			closedir($handle);
			if ($delDir)
				return rmdir($path);
		}else {
			if (file_exists($path)) {
				return unlink($path);
			} else {
				return FALSE;
			}
		}
   }
}