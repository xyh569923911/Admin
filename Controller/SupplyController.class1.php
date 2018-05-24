<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class SupplyController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isUclient = "Supply";
		$this->model = M("Uclient");
	}
    //公海列表
	public function index()
	{   //header("Content-type: text/html; charset=utf-8"); 
	   if(in_array('1001',$_SESSION['level'])){
		  $kun =  array('admin_id'=>array('gt',0));	
		}else if(in_array('1002',$_SESSION['level'])){
		
		  $kun = array("state"=>1);	
		//  print_r($kun);exit;
		}else if(in_array('1004',$_SESSION['level'])){
		  $tab_rank = M("state")->where(array("state"=>1))->getField("id",true);
		  $tab_rank = implode(",", $tab_rank);
		  $where = array("state_id"=>array("in",$tab_rank));	
		  $kun = array("state_id"=>array("in",$tab_rank));
		}else{
		  $kun = array("state"=>1);	
		}
	
	//	echo $this->model->getLastSql();exit;
		//15天不联系投入公海数据
        $end_time = strtotime(date('Y-m-d h:i', strtotime('-15 day')));
		//echo $end_time;exit;
		$states = M("state")->where(array('type'=>array("in","1")))->getField('id', true);
		$statess = M("state")->where(array('type'=>array("in","2")))->getField('id', true);
	   
		$states = implode(",",$states);
		$statess = implode(",",$statess);
		$post = I("get.");
		$stime = strtotime($post['stime']." 00:00:00");//开始时间
		$endtime = strtotime($post['endtime']." 24:00:00");//开始时间
		//	print_r($statess);exit;
		if(IS_GET){
			if($post['stime'] && $post['endtime']){
				if($post['types']){
				 $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array('elt',$endtime),array(array("elt",$end_time),'_logic'=>'or',"state_id"=>array("in",$statess))));
				 $wheres[] = $kun;
				}else{
				 
				 $wheres[] = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array('elt',$endtime)));
				 $wheres['_logic'] = 'or';
				 $wheres[] =  array("lxtime"=>array(array("gt",$stime),array('elt',$end_time)),"state_id"=>array("in",$statess));
				// $wheres['_complex'] = $kun;
				 //echo 1;exit;
				}
			 }else{
				if($post['stime']){
					if($post['types']){
					$wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array(array("elt",$end_time),'_logic'=>'or',"state_id"=>array("in",$statess))));
					$wheres[] = $kun;
					}else{
					
                     $wheres[] = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array('elt',$endtime)));
					 $wheres['_logic'] = 'or';
					 $wheres[] =  array("lxtime"=>array(array("gt",$stime),array('elt',$end_time)),"state_id"=>array("in",$statess));
					
					}
				}else if($post['endtime']){
					if($post['types']){
					$wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array(array("elt",$end_time),'_logic'=>'or',"state_id"=>array("in",$statess))));
					$wheres[] = $kun;
					}else{
					 $wheres[] = array("state_id"=>array("in",$states),"lxtime"=>array(array('elt',$endtime)));
					 $wheres['_logic'] = 'or';
					 $wheres[] =  array("lxtime"=>array(array('elt',$end_time)),"state_id"=>array("in",$statess));
					// echo 1;exit;
					}
				}else{
				  if($post['types']){	
				  $wheres = array("types"=>$post['types'],array(array("lxtime"=>array("elt",$end_time),"state_id"=>array("in",$statess)),'_logic'=>'or',"state_id"=>array("in",$states)));	  
				  }else{
                  if($post['sou']){
					 
					 $sou_member=M("member")->where(array("username"=>$post['sou']))->find();
					 if($sou_member){
					 $sou_member=M("admin")->where(array("member_id"=>$sou_member['id']))->getField("id");	 
					 }
					 if($sou_member){
			
					 $wheresx[] = array("state_id"=>array("in",$states));
				     $wheresx['_logic'] = 'or';
				     $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>array("in",$statess));
					 $wheres[] = array("admin_id"=>$sou_member);  
				     $wheres['_complex'] = $wheresx;	 
								 
					 }else{
					 $sou_state = M("state")->where(array("name"=>$post['sou']))->getField("id");					 
					 if($sou_state || $post['sou'] == "未联系"){
					 if($post['sou'] == "未联系"){
					//	 echo 11;exit;
					 $wheresx[] = array("state_id"=>'');
				     $wheresx['_logic'] = 'or';
				     $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>'');
				     $wheres['_complex'] = $wheresx; 	  	 
					 }else{
					 $arr_state = explode(",",$states);	
					 $arr_states = explode(",",$statess);
					 //echo $sou_state;exit;
                     if(in_array($sou_state,$arr_state)){
					    // echo $sou_state;exit;
                         $wheresx[] = array("state_id"=>$sou_state);
				         $wheresx['_logic'] = 'or';
				         $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>$sou_state);
				         $wheres['_complex'] = $wheresx; 
						 
					 }else if(in_array($sou_state,$arr_states)){
						 //$wheresx[] = array("state_id"=>$sou_state);
				        // $wheresx['_logic'] = 'or';
				         $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>$sou_state);
				         $wheres['_complex'] = $wheresx; 
						 
					 }else{
						 $wheres[] = array("state_id"=>100); 
                         $wheresx[] = array("state_id"=>$sou_state);
				         $wheresx['_logic'] = 'or';
				         $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>100);
				         $wheres['_complex'] = $wheresx; 
					 }					 
					 }	 
					 }else{	 
					 
					 $wheresx[] = array("state_id"=>array("in",$states));
				     $wheresx['_logic'] = 'or';
				     $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>array("in",$statess));
				     $wheres['_complex'] = $wheresx;
					 $wheres[]["username|phone"] = $post['sou'];
					 }
					 $wheres[] = $kun;
					 }
				   }else{
				  $wheresx[] = array("state_id"=>array("in",$states));
				  $wheresx['_logic'] = 'or';
				  $wheresx[] = array("lxtime"=>array("elt",$end_time),"state_id"=>array("in",$statess));
				  $wheres['_complex'] = $wheresx;
				  $wheres[] = $kun;
				   }
				  }
				}	  
			 }
		}else{
			
			$wheres = array("state_id"=>array("in",$states));
			$wheres[] = $kun;
			
		}
	
		$ch_datatss = $this->model->where($wheres)->order("addtime desc")->field("id,username,sex,phone,lxtime,type_id,state_id,admin_id,types,state,sign_state,rank_admin_id,allot,content,source,sources,promotion")->select();
	 /*  if(in_array('1001',$_SESSION['level'])){
			  echo $this->model->getLastSql();exit; 
		// print_r($ch_datatss);exit;
		} */ 
		//echo $this->model->getLastSql();exit;
        foreach($ch_datatss as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id');
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			if($val['state'] == 0){
			
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
			
			}
			//手机号加密****
			$ch_datat[$key]['phones'] = preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	

			//print_r($ch_datat[$key]['phones']);exit;
		}
		
		$this->admin_level = M("admin")->where(array("id"=>session("aid")))->find();
		//业务跟最高可以提取数据
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
				$typ[$k] =$v['id'];
	    }
		$this->typ = $typ;
		
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,45);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display();
	}

	//预览
	public function viewindex(){
		$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
			
			if(in_array('1001',$_SESSION['level'])){
			$kun =  array('admin_id'=>array('gt',0));	
			}else if(in_array('1002',$_SESSION['level'])){

			$kun = array("state"=>1);	
			//  print_r($kun);exit;
			}else if(in_array('1004',$_SESSION['level'])){
			$tab_rank = M("state")->where(array("state"=>1))->getField("id",true);
			$tab_rank = implode(",", $tab_rank);
			$where = array("state_id"=>array("in",$tab_rank));	
			$kun = array("state_id"=>array("in",$tab_rank));
			}else{
			$kun = array("state"=>1);	
			}
			$state = M("state")->where(array('type'=>array("in","0")))->getField('id', true);//未完成客户数据
			$state[] = 0;
			$state = implode(",",$state);
			$where[] = array("state_id"=>array("in",$state),"id"=>$_GET['id']);
			$where[] = $kun;
			$srt = $this->model->where($where)->find();//未完成数据是否能搜索的到
			
			$states = M("state")->where(array('type'=>array("in","2")))->getField('id', true);//公海状态类型
			$states = implode(",",$states);
			$end_time = strtotime(date('Y-m-d', strtotime('-15 day'))); //15天不联系投入公海数据
			$wheres = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time),"id"=>$_GET['id']);
			$wheres[] = $kun;
			$srts = $this->model->where($wheres)->find();//7天不联系投入公海数据
			/* if(!$srt && !$srts){
				$this->redirect("/Admin/uclient/index");
			} */
             $data['member_username'] = M("Member")->where(array("id"=>$data['member_id']))->getField("username");//添加人
			 $member_ids = M("admin")->where(array("id"=>$data['admin_id']))->getField("member_id");//跟进人
			 $data['admin_username'] = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
            
			// print_r($qut);exit;
             $data['rank'] = $this->model->where(array("rank_id"=>$data['rank_id']))->field("id,member_id,rank_admin_id")->select();//跟进状态	
			
            //查找渠道部成员
            $rank_id = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'渠道部')))->getField("id");           
			$data['rank'] = M("Admin")->where(array("level_id"=>$rank_id))->select();
			foreach($data['rank'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['rank'][$k]['username'] = $srt['username'];
				$data['rank'][$k]['admin_id'] = $srt['id'];
			}
			//print_r($data);exit;
			 //查找财务部成员
            $finance_id = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'财务部')))->getField("id");           
			$data['finance'] = M("Admin")->where(array("level_id"=>$finance_id))->select();
			foreach($data['finance'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['finance'][$k]['username'] = $srt['username'];
				$data['finance'][$k]['admin_id'] = $srt['id'];
			}
			 
			 $data['state_type'] =  M("state")->where(array("id"=>$data['state_id']))->getField("state");
		     $data["images"] = explode(",", $data["images"]);
			 
			 //左侧显示记录
			 $this->state_name = M("state")->where(array("id"=>$data['state_id']))->getField("name");
			 
			 //留言记录
			 $record = M("record")->where(array("u_id"=>$data['id']))->order("addtime desc")->select();
			 $count=count($record);//得到数组元素个数
			 $Page= $this->getPages($count,5);// 实例化分页类 传入总记录数和每页显示的记录数
			 $record = array_slice($record,$Page->firstRow,$Page->listRows);
			 $this->show= $Page->show();// 分页显示输出?
			 $this->assign("record",$record);
			}

			$this->assign("data", $data);
		    $this->columns = M("type")->select();//客户类型
			$this->columnss = M("state")->order("sort asc")->select();//跟进状态
		    $admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
			$admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
			if($data['admin_id']){
			$genjin = M("rank")->where(array("pid"=>$admin_name))->select();
			
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}

		    $genjins = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
			$genjin = array_merge($genjin, $genjins);
			$genjin[] = $admin_names;
			$genjin = Data::tree($genjin, "name", "id", "pid");
	
			$this->assign("genjin", $genjin);
			
			}else{
			$genjin = M("rank")->where(array("pid"=>$admin_name))->select();
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}
			$genjins = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
			$genjin = array_merge($genjin, $genjins);
			$genjin[] = $admin_names;
			$genjin = Data::tree($genjin, "name", "id", "pid");
			$this->assign("genjin", $genjin);
			}
           //跟进状态
            $columnsss = M("Admin")->where(array("level_id"=>$data['admin_ids']))->select();	
			foreach($columnsss as $k=>$v){
				$columnsss[$k]['username'] = M("Member")->where(array("id"=>$v['member_id']))->getField('username');	
			}


			//渠道或财务跟近
			$this->rank = M("rank")->where(array("id"=>$data['rank_id']))->select();
			$this->finance = M("rank")->where(array("id"=>$data['finance_id']))->select();
			
			$this->columnsss = $columnsss;
			$this->display();
	}

	// 添加公海
	public function addindex()
	{   
		if(IS_POST)
		{
			$data = I("post.");
			$data["content"] = $_POST["content"];
			$data["lxtime"] = time();
			$data["images"] = implode(",", $data["images"]);
			$iid = I("post.iid", 0, "intval");
			if($arr=$this->model->find($iid))
			{   if($arr['admin_id'] != $data['admin_id']){
		        $data['state'] = 0;
				$data['middle_admin_id'] = $data['admin_id'];
				$data['admin_id'] = $arr['admin_id'];
			    }
				//判断取出公海数据 1.类型是否是15天有所联系的 2.时间需要改当天联系
				if(M("state")->where(array("type"=>array("in","2"),"id"=>$data['state_id']))->find()){
					 $end_time = strtotime(date('Y-m-d h:i', strtotime('-15 day')));
				
					if($data['lxtime'] > $end_time){
						$data["su_state"] = 1;
					}
				}
				
				$arr=$this->model->find($iid);
				if($data['state_id'] != $arr['state_id']){
						$post['time'] = time();
						$post['u_id'] = $arr['id'];
						$post['state_id'] = $data['state_id'];
						$post['admin_id'] = $data['admin_id'];
						M("uclienttime")->add($post);
				}
				
				$this->model->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{    $data['state'] = 1;
		         $data['middle_admin_id'] = $data['admin_id'];
		         $data['addtime'] = time();
		         $data['member_id'] = $_SESSION['aid'];
				 $data['sources'] = '公海';
				 $id=$this->model->add($data);
				echo 1;
			}
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
             $data['member_username'] = M("Member")->where(array("id"=>$data['member_id']))->getField("username");//添加人
			 $data['admin_username'] = M("Member")->where(array("id"=>$data['admin_id']))->getField("username");//跟进人
            
			// print_r($qut);exit;
             $data['rank'] = $this->model->where(array("rank_id"=>$data['rank_id']))->field("id,member_id,rank_admin_id")->select();//跟进状态	
			
           
			 
			 $data['state_type'] =  M("state")->where(array("id"=>$data['state_id']))->getField("state");
		     $data["images"] = explode(",", $data["images"]);
			}
			//print_r($data);exit;
			$this->assign("data", $data);
		    $this->columns = M("type")->select();//客户类型
			$this->columnss = M("state")->where(array("type"=>array("in","1,2")))->order("sort asc")->select();//跟进状态
		    $admin_name = M("rank")->where(array("name"=>'业务员'))->getField("id");
			if($data['admin_id']){
			$columnsss = M("Admin")->where(array("id"=>array("neq",$data['admin_id']),"level_id"=>$admin_name))->select();
			}else{
			$columnsss = M("Admin")->where(array("level_id"=>$admin_name))->select();	
			}
			
			$admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
			$admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
			$genjin = M("rank")->where(array("pid"=>$admin_name))->select();
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}
			//print_r($genjins);exit;
		    $genjins = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
			$genjin = array_merge($genjin, $genjins);
			$genjin[] = $admin_names;
			$genjin = Data::tree($genjin, "name", "id", "pid");
	
			$this->assign("genjin", $genjin);
			
           //跟进状态
			//echo M("Admin")->getLastSql();exit;
			//print_r($columnsss);exit;
			foreach($columnsss as $k=>$v){
				$columnsss[$k]['username'] = M("Member")->where(array("id"=>$v['member_id']))->getField('username');	
			}
			//print_r($columnsss);exit;
			//渠道或财务跟近
			$this->rank = M("rank")->where(array("id"=>$data['rank_id']))->select();
			$this->finance = M("rank")->where(array("id"=>$data['finance_id']))->select();
			
			$this->columnsss = $columnsss;
			$this->display();
		}
	}
	
	public function admin_ids(){
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
	
	//提前数据
	public function editindex(){
		
		if(IS_POST){
			$iid = I("post.iid", 0, "intval");
		 	$data = I("post.");	
            //$data["su_state"] = 1;	
            if(M("state")->where(array("type"=>array("in","2"),"id"=>$data['state_id']))->find()){
					 $end_time = strtotime(date('Y-m-d h:i', strtotime('-15 day')));
				
					if(strtotime($data['lxtime']) > $end_time){
						$data["su_state"] = 1;
					}
			}
            
			//$data['admin_id'] = session("aid");
            $arr=$this->model->find($iid);
			if($data['state_id'] != $arr['state_id']){
					$post['time'] = time();
					$post['u_id'] = $arr['id'];
					$post['state_id'] = $data['state_id'];
					$post['admin_id'] = $data['admin_id'];
					M("uclienttime")->add($post);
			}
            $data['sources'] = '公海'; 			
			$data["lxtime"] = I('post.lxtime', '') ? strtotime(I('post.lxtime', '')) : time();
			$this->model->where(array("id" => $iid))->save($data);
			echo 1;
		}else{
		$id = I("get.id", 0, "intval");	
		if($data = $this->model->find($id))
		{
		 $this->columnss = M("state")->where(array("type"=>array("in","1,2")))->select();//跟进状态
		  $data['member_username'] = M("Member")->where(array("id"=>$data['member_id']))->getField("username");//添加人
		  $member_ids = M("admin")->where(array("id"=>$data['admin_id']))->getField("member_id");//跟进人
		  
		  $data['admin_username'] = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
		  $admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
		$admin_names = M("rank")->where(array("name"=>'业务总监'))->find(); 
	    $genjin = M("rank")->where(array("pid"=>$admin_name))->select();
		foreach($genjin as $kb=>$vb){
			$genjins[$kb] =$vb['id'];
		}
		//print_r($genjins);exit;
		$genjins = implode(",",$genjins);
		$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
		$genjin = array_merge($genjin, $genjins);
		$genjin[] = $admin_names;
		$genjin = Data::tree($genjin, "name", "id", "pid");

		$this->assign("genjin", $genjin);
		 $this->assign("data", $data);
		 //所属
		$admin_ads = M("admin")->where(array("id"=>session("aid")))->getField("level_id");
		$admin_ads = M("rank")->where(array("id"=>$admin_ads))->Field("id,name")->find();
		$this->admin_ads = $admin_ads; 
		}
		$this->display();	
		}
	
	}
	
	 //批量删除
	public function del(){
		$id = I("post.id");
		if($id){
		$ids = implode(",",$id);
		$this->model->where(array("id"=>array("in",$ids)))->delete();
		}
		$this->redirect('index');
		
	}


}


 ?>