<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class BusinessController extends CommonController{


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
	    $id = I("id");
		$sou = I("sou");
		if($id == 1){
		$where = array("admin_id"=>$sou,"state_id"=>8);
		}else if($id == 2){
		$where = array("admin_id"=>$sou,"state_id"=>10);	
		}else if($id == 3){
		$where = array("admin_id"=>$sou,"state_id"=>15);	
		}
		if($_GET['sou']){
		$co_arr = M("uclienttime")->where($where)->getField("u_id",true);
		$co_arr = implode(",",$co_arr);
		if($co_arr){
		$lohb = $this->model->where(array("id"=>array("in",$co_arr)))->select();
		}
		}
		if($id == 4){
		$wheres = array("admin_id"=>$sou,"state_id"=>15,"finance_state"=>1);	
		$lohb = $this->model->where($wheres)->select();
		//echo $this->model->getLastSql();exit;
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
			//ÊÖ»úºÅ¼ÓÃÜ****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$this->assign("ch_data",$ch_datat);
		$this->display();
	}
    
	public function channel()
	{  
	    $id = I("id");
		$sou = I("sou");
		if($id == 1){
		$where = array("admin_id"=>$sou,"state_id"=>12);
		}else if($id == 2){
		$where = array("admin_id"=>$sou,"state_id"=>11);	
		}else if($id == 3){
		$where = array("admin_id"=>$sou,"state_id"=>13);	
		}else if($id == 4){
		$where = array("admin_id"=>$sou,"state_id"=>14);	
		}else if($id == 5){
		$where = array("admin_id"=>$sou,"state_id"=>15);	
		}
		if($_GET['sou']){
		$co_arr = M("uclienttime")->where($where)->getField("u_id",true);
		$co_arr = implode(",",$co_arr);
		if($co_arr){
		$lohb = $this->model->where(array("id"=>array("in",$co_arr)))->select();
		}
		}
		if($id == 4){
		$wheres = array("admin_id"=>$sou,"state_id"=>15,"finance_state"=>1);	
		$lohb = $this->model->where($wheres)->select();
		//echo $this->model->getLastSql();exit;
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
			//ÊÖ»úºÅ¼ÓÃÜ****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		$this->assign("ch_data",$ch_datat);
		$this->display('index');
	}
	
}


 ?>