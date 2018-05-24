<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class SearchController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->model = M("Uclient");
	}
	
	public function adminMember(){
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
		  $tab_rank = M("state")->where(array("state"=>1))->getField("id",true);
		  $tab_rank = implode(",", $tab_rank);
		  $where = array("state_id"=>array("in",$tab_rank));
          $as_name = M("Admin")->where(array("level_id"=>9))->getField("id", true);
		  $as_name = implode(",", $as_name);
		  $kun = array(array("state_id"=>array("in",$tab_rank)),array(array('finance_admin_id'=>session('aid'),'_logic'=>'or',"rank_admin_id"=>array("in",$as_name)),"state"=>1));
		}else{
		  $kun = array(array('admin_id'=>session('aid'),'_logic'=>'or',"rank_admin_id"=>session('aid')),"state"=>1);	
		}
		
		return $kun;
		
	}
	
	public function index()
	{  
	    $day = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
	    $id = I("id");
		if($_GET['u_id']){
		$u_id = I("u_id");	
		$lohb = $this->model->where(array("id"=>$u_id))->select();
		}else{
		$state_id = I("state_id");
		$where = array("state_id"=>$state_id,"admin_id"=>$id);
		$where[] = $day;
	    $lohb = M("uclienttime")->where($where)->getField("u_id",true);//约见客户
        $lohb = implode(",",$lohb);
		$lohb = $this->model->where(array("id"=>array("in",$lohb)))->order("addtime desc")->select();
		}
		
		foreach($lohb as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$this->assign("ch_data",$ch_datat);
		$this->display();
	}
    
	public function today(){
		/**今日时间**/
		$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		$stimes = strtotime(date('Y-m-d 24:0:0'));
		$weeks = array('addtime' => array(array('gt',$stime),array("elt",$stimes)));
		if($_GET['id'] == 1){$weeks[] = array("allot"=>1);}else if($_GET['id'] == 2){$weeks[] = array("allot"=>0);}
		$weeks[] = $this->adminMember();
		$weeks[] = $this->statewc();
	    $ch_datats = $this->model->where($weeks)->select(); //本日累计
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function month(){
		/**本月时间**/
		$weeks = array("addtime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
		if($_GET['id'] == 1){
			$weeks[] = array("allot"=>1);
			}else if($_GET['id'] == 2){
			$weeks[] = array("allot"=>0);
		}
		$weeks[] = $this->adminMember();
		$weeks[] = $this->statewc();
	    $ch_datats = $this->model->where($weeks)->order("addtime desc")->select(); //本日累计
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function total(){
		/**总计**/
		if($_GET['id'] == 1){
			$weeks = array("allot"=>1);
			}else if($_GET['id'] == 2){
			$weeks = array("allot"=>0);
		}
		$weeks[] = $this->adminMember();
		$weeks[] = $this->statewc();
	    $ch_datats = $this->model->where($weeks)->order("addtime desc")->select(); //本日累计
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
     
    
	public function accumul(){
		/**累计签约**/
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>10);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>10);
		    }else{
			$weeks = array("state_id"=>10);	
			}
		//$weeks[] = 
		//print_r($weeks);exit;
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = $this->adminMember();
	    $ch_datats = $this->model->where($acl)->order("addtime desc")->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function lender(){
		/*累计放款*/
		
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
		
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('fitime' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>15);
			}else if($_GET['id'] == 2){
			$weeks = array("fitime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>15);
		    }else{
			$weeks = array("state_id"=>15);
			}
			$weeks[] = $kun;
			$ch_datats = $this->model->where($weeks)->select();
			//echo $this->model->getLastSql();exit;
		    foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
	    $this->display('index');	
	}
	
		public function mulated(){
		/*累计回款*/
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
		
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('ctime' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>15,"finance_state"=>1);
			}else if($_GET['id'] == 2){
			$weeks = array("ctime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>15,"finance_state"=>1);
		    }else{
			$weeks = array("state_id"=>15,"finance_state"=>1);
			}
			$weeks[] = $kun;
			$ch_datats = $this->model->where($weeks)->select();
			//echo $this->model->getLastSql();exit;
		    foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
	    $this->display('index');	
	}
	
	public function intention(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>5);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>5);
		    }else{
			$weeks = array("state_id"=>5);	
			}
		//$weeks[] = $kun;
		$weeks[] = $this->adminMember();
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//意向客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
	    $ch_datats = $this->model->where(array("id"=>array("in",$Sign)))->select(); //本日累计
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	
	public function Meet(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>8);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>8);
		    }else{
			$weeks = array("state_id"=>8);	
			}
		//$weeks[] = $kun;
		$weeks[] = $this->adminMember();
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//意向客户
		//echo M("uclienttime")->getLastSql();exit;
		if($Sign){
		$Sign = implode(",",$Sign);
	    $ch_datats = $this->model->where(array("id"=>array("in",$Sign)))->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function accumuls(){
		/**累计签约**/
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>10);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>10);
		    }else{
			$weeks = array("state_id"=>10);	
			}
		//$weeks[] = 
		//print_r($weeks);exit;
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = array("rank_admin_id"=>session("aid"));
	    $ch_datats = $this->model->where($acl)->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function approval(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>12);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>12);
		    }else{
			$weeks = array("state_id"=>12);	
			}
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = array("rank_admin_id"=>session("aid"));
	    $ch_datats = $this->model->where($acl)->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function giveup(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>11);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>11);
		    }else{
			$weeks = array("state_id"=>11);	
			}
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = array("rank_admin_id"=>session("aid"));
	    $ch_datats = $this->model->where($acl)->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function Approved(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>13);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>13);
		    }else{
			$weeks = array("state_id"=>13);	
			}
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = array("rank_admin_id"=>session("aid"));
	    $ch_datats = $this->model->where($acl)->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function veto(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>14);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>14);
		    }else{
			$weeks = array("state_id"=>14);	
			}
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = array("rank_admin_id"=>session("aid"));
	    $ch_datats = $this->model->where($acl)->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
	public function sumnay(){
		if($_GET['id'] == 1){
			$stime = strtotime(date('Y-m-d 0:0:0'));//今天时间
		    $stimes = strtotime(date('Y-m-d 24:0:0'));
		    $weeks = array('time' => array(array('gt',$stime),array("elt",$stimes)));
			$weeks[] = array("state_id"=>15);
			}else if($_GET['id'] == 2){
			$weeks = array("time"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
			$weeks[] = array("state_id"=>15);
		    }else{
			$weeks = array("state_id"=>15);	
			}
		$Sign = M("uclienttime")->where($weeks)->getField("u_id",true);//签约客户
		//echo M("uclienttime")->getLastSql();exit;
		$Sign = implode(",",$Sign);
		if($Sign){
		$acl = array("id"=>array("in",$Sign));
		$acl[] = array("rank_admin_id"=>session("aid"));
	    $ch_datats = $this->model->where($acl)->select(); //本日累计
		}
		foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
	
			}
			if($val['sign_state'] == 1){
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			$ch_datat[$key]['rank_admin_username'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			if($val['rank_admin_id']){
				$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id');
			    $ch_datat[$key]['rank_admin_user'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
}


 ?>