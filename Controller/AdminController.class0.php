<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class AdminController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isNavs = 'Admin';
		$this->model = M("Admin");
		$this->rank = M("rank");
	}
	
	

	public function index()
	{   
		$data = $this->model->order("sort asc")->select();
		foreach($data as $key=>$val){
			    $data[$key]['name'] = $this->rank->where(array("id"=>$val['level_id']))->getField('name');	
				$data[$key]['cid'] = $this->rank->where(array("id"=>$val['level_id']))->getField('id');
				$data[$key]['pid'] = $this->rank->where(array("id"=>$val['level_id']))->getField('pid');
				$data[$key]['username'] = M("Member")->where(array("id"=>$val['member_id']))->getField('username');	
		 }
		 $this->ch_data = Data::tree($data, "name", "cid", "pid");
		 $admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
		 $genjin = M("rank")->where(array("pid"=>$admin_names['id']))->select();
		 foreach($genjin as $kb=>$vb){
			$genjins[$kb] =$vb['id'];
		 }
		 $genjins = implode(",",$genjins);
		 $genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
		 $genjin = array_merge($genjin, $genjins);
		 foreach($genjin as $k=>$v){
			$ayp[$k] = $v['id'];
		 }
		 $this->ayp = $ayp;
		 $this->display();
	}

	public function getBranch()
	{
		$id = I("get.id", 0, "intval");
		$srt = $this->model->getField('member_id',true);
		//$srt = implode(",",$srt);
		$data = M("Member")->where(array("branch_id" => $id))->field("id,username")->select();
		foreach($data as $key=>$val){
			//echo $val['branch_id'];
			//print_r($val);
			 if(!in_array($val['id'],$srt)){
				$datas[$key] = $val;
			} 
		}
		array_merge($datas);
		//print_r($data);exit;
		$this->ajaxReturn($datas);
	}
	
		// 添加
	public function addindex()
	{
		if(IS_POST)
		{
			$data = I("post.");
			
		

			$iid = I("post.iid", 0, "intval");
			
			if($data['password']){
			$data['passw'] = $data['password'];	
			$data['password'] = MD5($data['password']);
			}
			if($arr=$this->model->find($iid))
			{ 
              if($data['shift_id']){	
			  //echo 2;exit;
		     /*  $rank_name = M("rank")->where(array("id"=>$arr['level_id']))->getField("name"); */
			 // echo $rank_name;exit;
			 /*  if($rank_name == '业务员' || $rank_name == '业务经理'){ */
				  M("Uclient")->where(array("admin_id"=>$arr['id']))->save(array("admin_id"=>$data['shift_id']));
			  /* }else if($rank_name == '渠道部'){
				  M("Uclient")->where(array("rank_admin_id"=>$arr['id']))->save(array("rank_admin_id"=>$data['shift_id']));
			  }else if($rank_name == '财务部'){
				  M("Uclient")->where(array("finance_admin_id"=>$arr['id']))->save(array("finance_admin_id"=>$data['shift_id']));
			  } */
			  }	
			  
			  //添加业务人员
				$admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
			    $admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
		        $genjin = M("rank")->where(array("pid"=>$admin_name))->select();
				foreach($genjin as $kb=>$vb){
					$genjins[$kb] =$vb['id'];
				}
				$genjins = implode(",",$genjins);
				$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
				$genjin = array_merge($genjin, $genjins);
				foreach($genjin as $k=>$v){
					$genjin[$k] =$v['id'];
				}
				if($data['type'] != 1){ //可分配
				if(in_array($data['level_id'],$genjin)){
				if(!$srt=M("admin_sms")->where(array("admin_id"=>$arr['id']))->find()){	
				$kun['admin_id'] = $iid;
				$member = M("member")->where(array("id"=>$data['member_id']))->field("id,phone")->find();
				$kun['phone'] = $member['phone'];
				$kun['member_id'] = $member['id'];
				$sun = M("admin_sms")->field("sun")->order("sun desc")->find();
				$kun['sun'] = $sun['sun'];
				M("admin_sms")->add($kun);	
				} 
				}
			    }else{
				M("admin_sms")->where(array("admin_id" => $iid))->delete();	
				}
		        
				$this->model->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{   
		        //添加业务人员
				$admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
			    $admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
		        $genjin = M("rank")->where(array("pid"=>$admin_name))->select();
				foreach($genjin as $kb=>$vb){
					$genjins[$kb] =$vb['id'];
				}
				$genjins = implode(",",$genjins);
				$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
				$genjin = array_merge($genjin, $genjins);
				foreach($genjin as $k=>$v){
					$genjin[$k] =$v['id'];
				}
		        $data['addtime'] = time();
				$arr = $this->model->add($data);
				if($data['type'] != 1){
				if($arr){
				if(in_array($data['level_id'],$genjin)){
				 $kun['admin_id'] = $arr;
				$member = M("member")->where(array("id"=>$data['member_id']))->field("id,phone")->find();
				$kun['phone'] = $member['phone'];
				$kun['member_id'] = $member['id'];
				$sun = M("admin_sms")->field("sun")->order("sun desc")->find();
				$kun['sun'] = $sun['sun'];
				 M("admin_sms")->add($kun);	
				}
				}
				}
				echo 1;
			}
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
				$this->assign("data", $data);
				
				$columnss = M("Member")->where(array("branch_id"=>$data['branch_id']))->select();	
			    $this->assign("columnss", $columnss);

				$admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
				$genjin = M("rank")->where(array("pid"=>$admin_names['id']))->select();
			//print_r($admin_names);exit;
				foreach($genjin as $kb=>$vb){
					$genjins[$kb] =$vb['id'];
				}
				
				$genjins = implode(",",$genjins);
				//print_r($genjins);exit;
				$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
				//print_r($genjins);exit;
				$genjin = array_merge($genjin, $genjins);
				foreach($genjin as $k=>$v){
					$ayp[$k] = $v['id'];
				}
				$this->ayp = $ayp;
			//	print_r($ayp);exit;
				$genjin[] = $admin_names;
				
				$genjin = Data::tree($genjin, "name", "id", "pid");
				$this->assign("srt", $genjin);
				/* $srt = M("admin")->where(array("level_id"=>$data['level_id'],"id"=>array("neq",$data['id'])))->field("id,member_id")->select();
				foreach($srt as $ke=>$va){
					$srt[$ke]['username'] = M("Member")->where(array("id"=>$va['member_id']))->getField("username");
				}
				
				$this->assign("srt", $srt); */
				//print_r($genjin);exit;
			}
			
			//部门
			$columns = M("branch")->select();	
			$this->assign("columns", $columns);
		    //权限
			$level=$this->rank->order("addtime asc")->select();	
            $this->level = Data::tree($level, "name", "id", "pid");			
			// print_r($this->level);exit;
			$this->display();
		}
	}
	
	public function getshift_ids(){
		$id = I("id");
		$srt = M("rank")->where(array("id"=>$id))->field("pid,id")->find();
		if($srt['pid'] == 0){
			echo 3;exit;
		}else{
			
			$arr = M("admin")->where(array("level_id"=>$srt['id']))->field("member_id,id")->select();
			foreach($arr as $key=>$val){
				$arr[$key]['username'] = M("Member")->where(array("id"=>$val['member_id']))->getField('username');
			}
			$this->ajaxReturn($arr);
		}
		
		
	}
	
	//角色管理
	public function rank(){
	  
     $data=$this->rank->order("addtime asc")->select();	  
	 $data = Data::tree($data, "name", "id", "pid");
	 $this->assign("data", $data);  	
	 $this->display();
	}

    //角色管理添加
	public function addrank(){
	if(IS_POST)
		{   // echo 1;exit;
			$data = I("post.");
			
			$iid = I("post.iid", 0, "intval");
			$data['level'] = implode(",",$data['level']);
			//print_r($data['level']);exit;
			if($arr=$this->rank->find($iid))
			{   
		        $data['savetime'] = time();
				$this->rank->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{   
		        $data['addtime'] = time();
				$data['savetime'] = time();
				$this->rank->add($data);
				echo 1;
			}
		}else
		{   
			$id = I("get.id", 0, "intval");
			if($data = $this->rank->find($id))
			{   $columns = $this->rank->select();
				$this->columns = $this->get_choose($this->rank, $columns, $id);
				$data['level'] = explode(",",$data['level']);
				//print_r($this->columns);exit;
				$this->assign("data", $data);
			}else{
				$columns = $this->rank->select();
				$this->columns = Data::tree($columns, "name", "id", "pid");
			}
			
			
			
			$this->display();
		}

	}
	
		// 删除
	public function delById()
	{
		$id = I("post.id");
		if($m = ucfirst(I("post.m", "")))
		{
			$model = M("$m");

			if($arr=$model->find($id))
			{   
		        
				//查找管理员有没有此人
         		$srt = M("uclient")->where(array("admin_id"=>$id,'_logic'=>'or',"finance_admin_id"=>$id,"rank_admin_id"=>$id))->find();
				if(!$srt){
				$model->where(array("id" => $id))->delete();
				M("admin_sms")->where(array("admin_id" => $id))->delete();
				echo 1;
				}else{
				echo 2;	
				}
				exit;
			}
		}
	}

}


 ?>