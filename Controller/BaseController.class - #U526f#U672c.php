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

	

    public function index()
    {   
	   //测试协议
		/* $param['name']       = 'get_arr_list';
        $param['key']      = 'e10adc3949ba59abbe56e057f20f883e';
		$postUrl = 'http://ylypsb.com/httppost.html';
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
		$data = json_decode($data,true); */
	//	print_r($data['params']);exit;
	 //  exit;
	
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
		  //print_r($kun);exit;
		}else if(in_array('1004',$_SESSION['level'])){
		  /* $tab_rank = M("state")->where(array("state"=>1))->getField("id",true);
		  $tab_rank = implode(",", $tab_rank);
		  $where = array("state_id"=>array("in",$tab_rank));	
		  $kun = array("state_id"=>array("in",$tab_rank)); */
		  $kun = array(array('admin_id'=>session('aid'),'_logic'=>'or',"rank_admin_id"=>session('aid'),'_logic'=>'or',"finance_admin_id"=>session('aid')));
		}else{//echo 1;exit;
		  $kun = array(array('admin_id'=>session('aid'),'_logic'=>'or',"rank_admin_id"=>session('aid'),'_logic'=>'or',"finance_admin_id"=>session('aid')));	
		}
		
		//最高管理员
		$zadmin = M("admin")->where(array("id"=>session("aid")))->field("level_id")->find();
		$zadmin_name = M('rank')->where(array("id"=>$zadmin['level_id']))->field("name")->find();
		if($zadmin_name['name'] == '最高管理员'){
			$this->admin_level = 1;
		}else if($zadmin_name['name'] == '业务经理' || $zadmin_name['name'] == '业务员'){
			$this->admin_level = 2;
		}else if($zadmin_name['name'] == '渠道部'){
			$this->admin_level = 3;
		}else if($zadmin_name['name'] == '财务部'){
			$this->admin_level = 4;
		}
		
		
		/**今日时间结束**/
		//总累计
		//$arr = array('ctime' => array('gt',date(mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))));
		$arr[] = $kun;
	    $this->total = $this->model->where($arr)->count(); //总累计
		//本日累计
		/**今日时间**/
		$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		$stimes = strtotime(date('Y-m-d 24:0:0'));
		$weeks = array('addtime' => array(array('gt',$stime),array("elt",$stimes)));
		$weeks[] = $kun;
	    $this->weeks = $this->model->where($weeks)->count(); //本日累计
		//还有外部对接过来的-------
		
		/*暂时没做*/
		
		//还有外部对接过来的-------
        //echo  $this->weeks;exit;
		//本月累计
		$weekss = array('addtime' => array('gt',strtotime(date('Y-m-01 01:00:00'))));
		$weekss[] = $kun;
	    $this->weekss = $this->model->where($weekss)->count(); //本月累计

		//本日已完成的
		$completed = array("finance_state"=>1,'ctime' => array('gt',date(mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))));
		$completed[] = $kun;
	    $this->completed = $this->model->where($completed)->count(); //本周已完成的数据
		
		$sty = $this->completed/$this->weeks*100;
		$this->sty = ceil($sty);
	    //echo $sty ;exit;
		
		
		//本周已成交的 completed
		$Dealdone_id = M("rank")->where(array("name"=>'财务部'))->getField("id"); 
		$Dealdone = array("finance_state"=>0,"finance_id"=>$Dealdone_id,'fitime' => array('gt',date(mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))));
		$Dealdone[] = $kun;
	    $this->Dealdone = $this->model->where($Dealdone)->count(); //本周已成交的数据
		
		
		
		//本周未完成数据
		//$Nottraded_id = M("rank")->where(array("name"=>'财务部'))->getField("id"); 
		$Nottraded = array("rank_id"=>array('eq',''),'lxtime' => array('gt',date(mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))));
		$Nottraded[] = $kun;
	    $this->Nottraded = $this->model->where($Nottraded)->count(); //本周未完成数据
		
		
		//百分比
		
		
		//本月已完成的
		$completeds = array("finance_state"=>1,'ctime' => array('gt',strtotime(date('Y-m-01 01:00:00'))));
		$completeds[] = $kun;
	    $this->completeds = $this->model->where($completeds)->count(); //本月已完成的数据
		
		$stys = $this->completeds/$this->weekss*100;
		$this->stys = ceil($stys);
		
		//本月已成交的
		$Dealdone_ids = M("rank")->where(array("name"=>'财务部'))->getField("id"); 
		$Dealdones = array("finance_state"=>0,"finance_id"=>$Dealdone_ids,'fitime' => array('gt',strtotime(date('Y-m-01 01:00:00'))));
		$Dealdones[] = $kun;
	    $this->Dealdones = $this->model->where($Dealdones)->count(); //本月已成交的数据
		//本月未完成数据 
		$Nottradeds = array("rank_id"=>array('eq',''),'lxtime' => array('gt',strtotime(date('Y-m-01 01:00:00'))));
		$Nottradeds[] = $kun;
	    $this->Nottradeds = $this->model->where($Nottradeds)->count(); //本月未完成数据
		//echo $this->model->getLastSql();exit;
	    //print_r($completed);exit;
	    
		//总计	

		$common = array("finance_state"=>1);
		$common[] = $kun;
	    $this->common = $this->model->where($common)->count(); //总已完成的数据
	    $styss = $this->common/$this->total*100;
		$this->styss = ceil($styss);
		
		$comm = M("rank")->where(array("name"=>'财务部'))->getField("id"); 
		$commons = array("finance_state"=>0,"finance_id"=>$comm);
		$commons[] = $kun;
	    $this->commons = $this->model->where($commons)->count(); //总已成交的数据
		
		//SELECT * FROM `uclient` WHERE `finance_id` = 0 AND ((`lxtime` > '1497628800' and `state_id` IN ('5','6','7'))) AND ( ( `admin_id` = '5' OR `rank_admin_id` = '5' ) )
		
	    $states = M("state")->where(array('type'=>array("in","2")))->getField('id', true);
		$commonss = array("finance_id"=>0,array("lxtime"=>array("gt",strtotime(date('Y-m-d', strtotime('-7 day')))),"state_id"=>array("in",$states)));
		$commonss[] = $kun;
	    $commonss = $this->model->where($commonss)->count(); //未完成数据
		//echo $this->model->getLastSql();exit;
		$comss = M("state")->where(array('type'=>array("in","0")))->getField('id', true);
		$comss = array("finance_id"=>0,"state_id"=>array("in",$comss));
		$comss[] = $kun;
		$comss = $this->model->where($comss)->count(); //未完成数据
		//echo $this->model->getLastSql();exit;
		$this->commonss = $commonss+$comss;
		//
	    //echo date('Y-m-d h:i', '1497628800');exit;
	 
	    /***********************************************************业务员跟业务经理项***********************************************************************/ 
		  //总录入客户 allot=>0表示手动录入 =>1表示系统分配 
		  $allot = array("allot"=>0);
		  $Totals = $allot;
		  $Totals[] = $kun;
	      $this->Totals = $this->model->where($Totals)->count();
		  
		  //今日收录客户
		  $comtoday = $allot;
		  $comtoday[] = array('addtime' => array(array('gt',$stime),array("elt",$stimes)));
		  $comtoday[] = $kun;
		  $this->comtoday = $this->model->where($comtoday)->count();
		  
		  //本月录入客户
		  $common = $allot;
		  $common[] = array('addtime' => array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $common[] = $kun;
		  $this->common = $this->model->where($common)->count();
		  
		  //echo $this->model->getLastSql();exit;
	      $today = array('time' => array(array('gt',$stime),array("elt",$stimes)));//今日时间
		  /*业务员的 今天*/
		  $to = $today;
		  $to[] = array("state_id"=>5);
		  //$to[] = $kun;
		  $com = M("uclienttime")->where($to)->select();//意向客户
		  
		  $this->com = $this->sun($com,$kun);
		 // print_r($suncom);exit;
		  
		  $Meet = $today;
		  $Meet[] = array("state_id"=>8);
		  //$Meet[] = $kun;
		  $Meet = M("uclienttime")->where($Meet)->select();//约见客户
		  $this->Meet = $this->sun($Meet,$kun);
		  
		  $Sign = $today;
		  //$Sign[] = $kun;
		  $Sign[] = array("state_id"=>10);
		  $Sign = M("uclienttime")->where($Sign)->select();//签约客户
		  
		  $this->Sign = $this->sun($Sign,$kun);
		  
		  /*业务员的 本月*/
		  $month = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		  $mocom = $month;
		  $mocom[] = array("state_id"=>5);
		  //$mocom[] = $kun;
		  $mocom = M("uclienttime")->where($mocom)->select();//意向客户
		 // echo strtotime(date('2017-5-5 12:00:00'));exit;
		  //echo date('Y-m-d H:i:s','1498838399');
		  //echo M("uclienttime")->getLastSql();EXIT;
		  $this->mocom = $this->sun($mocom,$kun);
		  
		  
		  $moMeet = $month;
		  $moMeet[] = array("state_id"=>8);
		  //$moMeet[] = $kun;
		  $moMeet = M("uclienttime")->where($moMeet)->select();//约见客户
		  $this->moMeet = $this->sun($moMeet,$kun);
		  
		  $moSign = $month;
		  $moSign[] = array("state_id"=>10);
		  //$moSign[] = $kun;
		  $moSign = M("uclienttime")->where($moSign)->select();//签约客户
		  $this->moSign = $this->sun($moSign,$kun);
		  
		  /*业务员的 总计*/
		  $totalcom = array("state_id"=>5);
		  $totalcom[] = $kun;
		  $this->totalcom = M("uclient")->where($totalcom)->count();//意向客户
		  $totalMeet = array("state_id"=>8);
		  $totalMeet[] = $kun;
		  $this->totalMeet = M("uclient")->where($totalMeet)->count();//约见客户
		  $totalSign = array("state_id"=>10);
		  $totalSign[] = $kun;
		  $this->totalSign = M("uclient")->where($totalSign)->count();//签约客户
		  
		  /*今天状态*/
		   //外地客户
		   $foreign = $today;
		   $foreign[] = array("state_id"=>1);
		  // $foreign[] = $kun;
		   $foreign = M("uclienttime")->where($foreign)->select();
		   $this->foreign = $this->sun($foreign,$kun);
		   
		   //未接通
		   $connected = $today;
		   $connected[] = array("state_id"=>2);
		   //$connected[] = $kun;
		   $connected = M("uclienttime")->where($connected)->select();
		   $this->connected = $this->sun($connected,$kun);
		   
		   //资质不符
		   $notmatch = $today;
		   $notmatch[] = array("state_id"=>2);
		   //$notmatch[] = $kun;
		   $notmatch = M("uclienttime")->where($notmatch)->select();
		   $this->notmatch = $this->sun($notmatch,$kun);
		   
		   //邀约中
		   $Invitation = $today;
		   $Invitation[] = array("state_id"=>6);
		   //$Invitation[] = $kun;
		   $Invitation = M("uclienttime")->where($Invitation)->select();
		   $this->Invitation = $this->sun($Invitation,$kun);
		   
		   /*今天客户类型*/
		   $todays = array('addtime' => array(array('gt',$stime),array("elt",$stimes)));//今日时间
		   //小额客户
		   $Small = $todays;
		   $Small[] = array("type_id"=>12);
		   $Small[] = $kun;
		   $this->Small = $this->model->where($Small)->count(); //未完成数据
		   
		   //小额客户
		   $Social = $todays;
		   $Social[] = array("type_id"=>9);
		   $Social[] = $kun;
		   $this->Social = $this->model->where($Social)->count(); //未完成数据
		   
		   //保单客户
		   $Policy = $todays;
		   $Policy[] = array("type_id"=>7);
		   $Policy[] = $kun;
		   $this->Policy = $this->model->where($Policy)->count(); //未完成数据
		   
		   //红本客户
		   $RedBook = $todays;
		   $RedBook[] = $kun;
		   $RedBook[] = array("type_id"=>1);
		   $this->RedBook = $this->model->where($RedBook)->count(); //未完成数据
		   
		 //echo M("uclienttime")->getLastSql();exit;
		 // $this->model->where($weeks)->count();//
		
	   // echo date("Y-m-d",'1498469574');exit;
	    /**********************************************************************************************************************************/ 
	    /******************************************************渠道****************************************************************************/
		$daytime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//时刻
		
		$autograph = $daytime;
		$autograph[] = array("state_id"=>10);
		$autograph = M("uclienttime")->where($autograph)->select();
		$this->autograph = $this->sun($autograph,$kun);//今日签约
		
		$monthtime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//月时刻
	    $monthgraph = $monthtime;
		$monthgraph[] = array("state_id"=>10);
		$monthgraph = M("uclienttime")->where($monthgraph)->select();
		$this->monthgraph = $this->sun($monthgraph,$kun);//本月签约 
		
		$monthall = $kun;
		$monthall[] = array("state_id"=>10);
		$this->monthall = M("uclient")->where($monthall)->count();//总签约 

		//今日
		$Incom = $daytime;
		$Incom[] = array("state_id"=>12);
		$Incom = M("uclienttime")->where($Incom)->select();
		$this->Incom = $this->sun($Incom,$kun);//进件审批
		
		$Incoms = $daytime;
		$Incoms[] = array("state_id"=>11);
		$Incoms = M("uclienttime")->where($Incoms)->select();
		$this->Incoms = $this->sun($Incoms,$kun);//进件放弃
		
		$Approved = $daytime;
		$Approved[] = array("state_id"=>13);
		$Approved = M("uclienttime")->where($Approved)->select();
		$this->Approved = $this->sun($Approved,$kun);//审批通过
		
		$veto = $daytime;
		$veto[] = array("state_id"=>14);
		$veto = M("uclienttime")->where($veto)->select();
		$this->veto = $this->sun($veto,$kun);//审批否决
		
		$Success = $daytime;
		$Success[] = array("state_id"=>15);
		$Success = M("uclienttime")->where($Success)->select();
		$this->Success = $this->sun($Success,$kun);//成功放款
		
		//本月
		$Incomx = $monthtime;
		$Incomx[] = array("state_id"=>12);
		$Incomx = M("uclienttime")->where($Incomx)->select();
		$this->Incomx = $this->sun($Incomx,$kun);//进件审批
		
		$Incomsx = $monthtime;
		$Incomsx[] = array("state_id"=>11);
		$Incomsx = M("uclienttime")->where($Incomsx)->select();
		$this->Incomsx = $this->sun($Incomsx,$kun);//进件放弃
		
		$Approvedx = $monthtime;
		$Approvedx[] = array("state_id"=>13);
		$Approvedx = M("uclienttime")->where($Approvedx)->select();
		$this->Approvedx = $this->sun($Approvedx,$kun);//审批通过
		
		$vetox = $monthtime;
		$vetox[] = array("state_id"=>14);
		$vetox = M("uclienttime")->where($vetox)->select();
		$this->vetox = $this->sun($vetox,$kun);//审批否决
		
		$Successx = $monthtime;
		$Successx[] = array("state_id"=>15);
		$Successx = M("uclienttime")->where($Successx)->select();
		$this->Successx = $this->sun($Successx,$kun);//成功放款
		
		//总计
		$Incomxs = $kun;
		$Incomxs[] = array("state_id"=>12);
		$this->Incomxs = M("uclient")->where($Incomxs)->count();//进件审批
		
		$Incomsxs = $kun;
		$Incomsxs[] = array("state_id"=>11);
		$this->Incomsxs = M("uclient")->where($Incomsxs)->count();//进件放弃
		
		$Approvedxs = $kun;
		$Approvedxs[] = array("state_id"=>13);
		$this->Approvedxs = M("uclient")->where($Approvedxs)->count();//审批通过
		
		$vetoxs = $kun;
		$vetoxs[] = array("state_id"=>14);
		$this->vetoxs = M("uclient")->where($vetoxs)->count();//审批否决
		
		$Successxs = $kun;
		$Successxs[] = array("state_id"=>15);
		$this->Successxs = M("uclient")->where($Successxs)->count();//成功放款
		
		/**********************************************************************************************************************************/
		/*****************************************************财务*****************************************************************************/
		$daytime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//时刻
		$monthtime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//月时刻
		
		$Loansun = $daytime;
		$Loansun[] = array("state_id"=>15);
		$Loansun = M("uclienttime")->where($Loansun)->select();
		$this->Loansun = $this->sun($Loansun,$kun);//今日放款
		
		$monthsun = $monthtime;
		$monthsun[] = array("state_id"=>15);
		$monthsun = M("uclienttime")->where($monthsun)->select();
		$this->monthsun = $this->sun($monthsun,$kun);//本月放款
		
		$Grandsun = $kun;
		$Grandsun[] = array("state_id"=>15);
		$this->Grandsun = M("uclient")->where($Grandsun)->count();//累计放款
		
		$pricetime = array('time' => array(array('gt',$stime),array("elt",$stimes)));//时刻
		$Loanprice = $daytime;
		//$Loanprice[] = $kun;
		$Loanprice[] = array("state_id"=>15);
		$Loanprice = M("uclienttime")->where($Loanprice)->select();
		$this->Loanprice = $this->suns($Loanprice,$kun);//本日放款
		
		$monthprice = $monthtime;
		$monthprice[] = array("state_id"=>15);
		$monthprice = M("uclienttime")->where($monthprice)->select();
		$this->monthprice = $this->suns($monthprice,$kun);//本月放款
		
		$Grandprice = $kun;
		$Grandprice[] = array("state_id"=>15);
		$this->Grandprice = M("uclient")->where($Grandprice)->sum("price");//累计放款
		
		//今日回款
		$Loansuns = $daytime;
		$Loansuns[] = array("state_id"=>15);
		$Loansuns = M("uclienttime")->where($Loansuns)->select();
		$kuns = $kun;
		$kuns[] = array("finance_state"=>1);
		$this->Loansuns = $this->sun($Loansuns,$kuns);//今日放款
		
		$monthsuns = $monthtime;
		$monthsuns[] = array("state_id"=>15);
		$monthsuns = M("uclienttime")->where($monthsuns)->select();
		$kuns = $kun;
		$kuns[] = array("finance_state"=>1);
		$this->monthsuns = $this->sun($monthsuns,$kuns);//本月放款
		
		$Grandsuns = $kun;
		$Grandsuns[] = array("state_id"=>15,"finance_state"=>1);
		$this->Grandsuns = M("uclient")->where($Grandsuns)->count();//累计放款
		
		//今日回款
		$Loansunsx = $daytime;
		$Loansunsx[] = array("state_id"=>15);
		$Loansunsx = M("uclienttime")->where($Loansunsx)->select();
		$kunsx = $kun;
		$kunsx[] = array("finance_state"=>1);
		$this->Loansunsx = $this->suns($Loansunsx,$kunsx);//今日放款金额
		
		$monthsunsx = $monthtime;
		$monthsunsx[] = array("state_id"=>15);
		$monthsunsx = M("uclienttime")->where($monthsunsx)->select();
		$kunsx = $kun;
		$kunsx[] = array("finance_state"=>1);
		$this->monthsunsx = $this->suns($monthsunsx,$kunsx);//本月放款金额
		
		$Grandsunsx = $kun;
		$Grandsunsx[] = array("state_id"=>15,"finance_state"=>1);
		$this->Grandsunsx = M("uclient")->where($Grandsunsx)->sum("price");//累计放款金额
		//echo M("uclient")->getLastSql();exit;
		//$this->Loanprice = M("uclient")->where($Loanprice)->sum("price");//今日放款金额
		/**********************************************************************************************************************************/
    	$this->display();
    }
	
	//算数量
	public function sun($arr,$srt){
		//print_r($srt);exit;
		//$mys = $srt;
		$sunmocom = 0;
		  foreach($arr as $key=>$val){
			 $mys = array(array("id"=>$val['u_id'],"state_id"=>$val["state_id"]),$srt);
			 if($this->model->where($mys)->find()){
			//	echo $this->model->getLastSql();exit;
				 $sunmocom ++;
			  }
		  }
		 // echo $sunmocom;exit;
		return $sunmocom;
	}
	public function suns($arr,$srt){
		//print_r($srt);exit;
		//$mys = $srt;
		$sunmocom = 0;
		  foreach($arr as $key=>$val){
			 $mys = array(array("id"=>$val['u_id'],"state_id"=>$val["state_id"]),$srt);
			 $a = $this->model->where($mys)->find();
			// echo $this->model->getLastSql();exit;
			 if($a){
				
				 $sunmocom += $a['price'];
			  }
		  }
		 // echo $sunmocom;exit;
		return $sunmocom;
	}
	public function sunss($arr,$srt){
		//print_r($srt);exit;
		//$mys = $srt;
		$sunmocom = 0;
		  foreach($arr as $key=>$val){
			 $mys = array(array("id"=>$val['u_id'],"state_id"=>$val["state_id"]),$srt);
			 $a = $this->model->where($mys)->find();
			echo $this->model->getLastSql();exit;
			 if($a){
				
				 $sunmocom += $a['price'];
			  }
		  }
		 // echo $sunmocom;exit;
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
                if($model->where(array("id" => $aid, "password" => md5($oldpass)))->save(array("password" => md5($newpass))))
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