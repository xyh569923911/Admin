<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class CoopeController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isCoope = "Coope";
		$this->model = M("Uclient");
		
	}
	
	public function adminMember(){
		
		$state = M("state")->where(array('type'=>array("in","3")))->getField('id', true);
		$state = implode(",",$state);
		//print_r($state);exit;
		if(in_array('1001',$_SESSION['level'])){
		  $kun =  array("state_id"=>array("in",$state),'admin_id'=>array('gt',0));	
		}else if(in_array('1002',$_SESSION['level'])){
		  $admin = M("admin")->where(array("id"=>session("aid")))->find();
		  $level = M("rank")->where(array("id"=>$admin['level_id']))->find();
		  $cids = M("rank")->where(array("pid" => $level['id']))->getField("id", true);
		  $cids[] = $level["id"];
		  $cids = implode(",", $cids);
		  $all_id = M("admin")->where(array('level_id'=>array("in",$cids)))->getField('id', true);
		  $all_id = implode(",", $all_id);
		  $kun = array("state_id"=>array("in",$state),'admin_id'=>array('in',$all_id));	
		//  print_r($kun);exit;
		}else if(in_array('1004',$_SESSION['level'])){
		  $tab_rank = M("state")->where(array("state"=>array("in","2")))->getField("id",true);
		  $tab_rank = implode(",", $tab_rank);
		  $where = array("state_id"=>array("in",$tab_rank));	
		  $kun = array("state_id"=>array("in",$tab_rank),"finance_admin_id"=>session('aid'));
		}else{
			
		  $kun = array(array('admin_id'=>session('aid'),'_logic'=>'or',"finance_admin_id"=>session('aid'),"rank_admin_id"=>session('aid')),"state"=>1);	
		}
		return $kun;
	}
	
    //成功数据
	public function index()
	{
		$kun = $this->adminMember();
        $state = M("state")->where(array('type'=>array("in","3")))->getField('id', true);
		$state = implode(",",$state);
		$post = I('get.');
		//print_r($post);exit;
		if(IS_GET){
			$stime = strtotime($post['stime']." 00:00:00");//开始时间
		    $endtime = strtotime($post['endtime']." 24:00:00");//开始时间
			if($post['stime'] && $post['endtime']){
			 if($post['types']){
			 $where = array("state_id"=>array("in",$state),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array('elt',$endtime)));	 
			 $where[] = $kun;
			 }else{	
			 $where = array("state_id"=>array("in",$state),"lxtime"=>array(array("gt",$stime),array('elt',$endtime)));
			 $where[] = $kun;
			 }
			 }else{
				if($post['stime']){
					if($post['types']){
					$where = array("state_id"=>array("in",$state),"types"=>$post['types'],"lxtime"=>array("gt",$stime));
					$where[] = $kun;
					}else{
					$where = array("state_id"=>array("in",$state),"lxtime"=>array("gt",$stime));	
					$where[] = $kun;
					}
				}else if($post['endtime']){
					if($post['types']){
					$where = array("state_id"=>array("in",$state),"types"=>$post['types'],"lxtime"=>array("elt",$endtime));
					$where[] = $kun;
					}else{
					$where = array("state_id"=>array("in",$state),"lxtime"=>array("elt",$endtime));
					$where[] = $kun;
					}
				}else{
				  if($post['types']){
					$where = array("state_id"=>array("in",$state),"types"=>$post['types']);  
					$where[] = $kun;
				  }else{
				  $where[] = array("state_id"=>array("in",$state));
				  $where[] = $kun;
				  }
				}	  
			 }
			 
		}else{
			$where = array("state_id"=>array("in",$state).$kun);
		}
	    
		$ch_datats = $this->model->where($where)->order("fitime desc")->field("id,username,sex,phone,lxtime,type_id,state_id,admin_id,types,state,rank_id,rank_admin_id,finance_admin_id,finance_id,finance_state,allot,content,source,sources,promotion")->select();
        //echo $this->model->getLastSql();exit;
        foreach($ch_datats as $key=>$val){
			$ch_datat[$key] = $val;
			$ch_datat[$key]['type_id'] = M("type")->where(array('id'=>$val['type_id']))->find();
			$ch_datat[$key]['state_id'] = M("state")->where(array('id'=>$val['state_id']))->find();
			$admin_id = M("admin")->where(array('id'=>$val['admin_id']))->getField('member_id'); //跟进业务员
			$ch_datat[$key]['admin_id'] = M("Member")->where(array('id'=>$admin_id))->getField('username');
			
			$rank_admin_id = M("admin")->where(array('id'=>$val['rank_admin_id']))->getField('member_id'); //跟进渠道部人员
			$ch_datat[$key]['rank_admin_id'] = M("Member")->where(array('id'=>$rank_admin_id))->getField('username');
			
			$finance_admin_id = M("admin")->where(array('id'=>$val['finance_admin_id']))->getField('member_id'); //跟进财务部人员
			$ch_datat[$key]['finance_admin_id'] = M("Member")->where(array('id'=>$finance_admin_id))->getField('username');
			
			if($val['state'] == 0){
			$middle_admin_id = M("admin")->where(array('id'=>$val['middle_admin_id']))->getField('member_id');
			$ch_datat[$key]['middle_admin_username'] = M("Member")->where(array('id'=>$middle_admin_id))->getField('username');
			}
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
		}
		//print_r($ch_datat);exit;
		//$this->ch_data = $ch_datat;
		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,30);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		//print_r($newarray_1);
		$this->assign("ch_data",$ch_datat);
		$this->display();
	}
    
	//预览
	public function viewindex(){
		$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
             $data['member_username'] = M("Member")->where(array("id"=>$data['member_id']))->getField("username");//添加人
			// $data['admin_username'] = M("Member")->where(array("id"=>$data['admin_id']))->getField("username");//跟进人
            $member_ids = M("admin")->where(array("id"=>$data['admin_id']))->getField("member_id");//跟进人
			 $data['admin_username'] = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
			// print_r($qut);exit;
             $data['rank'] = $this->model->where(array("rank_id"=>$data['rank_id']))->field("id,member_id,rank_admin_id")->select();//跟进状态	
			
            //查找渠道部成员
            $rank_id = M("rank")->where(array("name"=>'渠道部'))->getField("id");           
			$data['rank'] = M("Admin")->where(array("level_id"=>$rank_id))->select();
			foreach($data['rank'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['rank'][$k]['username'] = $srt['username'];
				$data['rank'][$k]['admin_id'] = $srt['id'];
			}
			//print_r($data);exit;
			 //查找财务部成员
            $finance_id = M("rank")->where(array("name"=>'财务部'))->getField("id");           
			$data['finance'] = M("Admin")->where(array("level_id"=>$finance_id))->select();
			foreach($data['finance'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['finance'][$k]['username'] = $srt['username'];
				$data['finance'][$k]['admin_id'] = $srt['id'];
			}
			 
			 $data['state_type'] =  M("state")->where(array("id"=>$data['state_id']))->getField("state");
		     $data["images"] = explode(",", $data["images"]);
			}
			$data['phones'] = preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$data['phone']);
			//print_r($data);exit;
			$this->assign("data", $data);
		    $this->columns = M("type")->select();//客户类型
			$this->columnss = M("state")->where(array("type"=>3))->select();//跟进状态
			
		    $admin_name = M("rank")->where(array("name"=>'业务员'))->getField("id");
			if($data['admin_id']){
			$columnsss = M("Admin")->where(array("id"=>array("neq",$data['admin_id']),"level_id"=>$admin_name))->select();
			}else{
			$columnsss = M("Admin")->where(array("level_id"=>$admin_name))->select();	
			}
			
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

	// 添加已合作客户
	public function addindex()
	{  
		if(IS_POST)
		{
			$data = I("post.");
			$data["content"] = $_POST["content"];
			$data["lxtime"] = time();
			$data["images"] = implode(",", $data["images"]);
			$iid = I("post.iid", 0, "intval");

			if($data['finance_state'] == 1){
				$data['ctime'] = time();
			}else{
				$data['ctime'] = '';
			}
			if($arr=$this->model->find($iid))
			{  
				//业务员转状态到渠道部
				if($data['rank_id']){
					$data['rank_state'] = 0;
					if($data['rank_admin_id'] != $arr['rank_admin_id']){
					$data['sign_rank_state'] = $arr['rank_admin_id'];
					$data['sign_state'] = 1;	
					}else{
					 $data['sign_rank_state'] = $data['rank_admin_id'];	
					 $data['sign_state'] = 0;
					}
				}
				
				$this->model->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{   
				 $data['middle_admin_id'] = $data['admin_id'];
		         $data['sign_rank_state'] = $data['rank_admin_id'];
				 $data['state'] = 1;
				 $data['types'] = 1;
		         $data['addtime'] = time();
				 $data['fitime'] = time();
		         $data['member_id'] = $_SESSION['aid'];
				 $id=$this->model->add($data);
				echo $id;
			}
		}else
	 	{   

			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
			 $kun = $this->adminMember();
			 $state = M("state")->where(array('type'=>array("in","3")))->getField('id', true);
			 $state = implode(",",$state);
			 $where[] = array("state_id"=>array("in",$state),"id"=>$_GET['id']);
			 $where[] = $kun;
			 $srt = $this->model->where($where)->find();//未完成数据是否能搜索的到
			 if(!$srt){
				$this->redirect("/Admin/Coope/index");exit;
			 }
				
             $data['member_username'] = M("Member")->where(array("id"=>$data['member_id']))->getField("username");//添加人
			 $member_ids = M("admin")->where(array("id"=>$data['admin_id']))->getField("member_id");//跟进人
			 $data['admin_username'] = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
 
             $data['rank'] = $this->model->where(array("rank_id"=>$data['rank_id']))->field("id,member_id,rank_admin_id")->select();//跟进状态	
			 
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
			 
			 $rank_admin_id = M("admin")->where(array("id"=>$data['rank_admin_id']))->getField("member_id");//渠道跟进人
			 $this->rank_admin_username = M("Member")->where(array("id"=>$rank_admin_id))->getField("username");//跟进人
			 
			 $finance_admin_id = M("admin")->where(array("id"=>$data['finance_admin_id']))->getField("member_id");//财务跟进人
			 $this->finance_admin_username = M("Member")->where(array("id"=>$finance_admin_id))->getField("username");//财务跟进人
			}
			
			 //查找渠道部成员
            $rank_id = M("rank")->where(array("name"=>'渠道部'))->getField("id");		
			$data['rank'] = M("Admin")->where(array("level_id"=>$rank_id))->select();
			foreach($data['rank'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['rank'][$k]['username'] = $srt['username'];
				$data['rank'][$k]['admin_id'] = $srt['id'];
			}
			
			 //查找财务部成员
            $finance_id = M("rank")->where(array("name"=>'财务部'))->getField("id");           
			$data['finance'] = M("Admin")->where(array("level_id"=>$finance_id))->select();
			foreach($data['finance'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['finance'][$k]['username'] = $srt['username'];
				$data['finance'][$k]['admin_id'] = $srt['id'];
			}
			
			//print_r($data);exit;
			$this->assign("data", $data);
		    $this->columns = M("type")->select();//客户类型
			$this->columnss = M("state")->where(array("type"=>3))->select();//跟进状态
		    $admin_name = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'业务员')))->getField("id");

			
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
			$this->rank = M("rank")->where(array("name"=>'渠道部'))->select();
			$this->finance = M("rank")->where(array("name"=>'财务部'))->select();
			
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