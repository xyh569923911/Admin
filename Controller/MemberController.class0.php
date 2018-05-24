<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class MemberController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isMember = true;
		$this->model = M("Member");
	}

	public function index()
	{   
	   
		 $data = $this->model->select();
		 foreach($data as $key=>$val){
			    $data[$key]['cname'] = M("branch")->where(array("id"=>$val['branch_id']))->getField('name');	
		 }
		 $this->data =$data;
		$this->display();
	}

	public function getLang()
	{
		$lang = I("get.lang", 0, "intval");
		$data = M("Columns")->where(array("lang" => $lang, "type" => "admin"))->order("sort asc")->select();
		
		$columns = Data::tree($data, "name", "id", "pid");
	
		$this->ajaxReturn($columns);
	}


		// 添加
	public function addindex()
	{
		if(IS_POST)
		{
			$data = I("post.");
			$iid = I("post.iid", 0, "intval");
			$data["addtime"] = I('post.addtime', '') ? strtotime(I('post.addtime', '')) : time();
			if($arr=$this->model->find($iid))
			{   
		       
				$this->model->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{   $data['password'] = MD5($data['password']);
				$this->model->add($data);
				echo 1;
			}
		}else
		{   
	        
			$id = I("get.id", 0, "intval");
			//echo $_SESSION['aid'];exit;
			
			if($data = $this->model->find($id))
			{
				$this->assign("data", $data);
			}
			//print_r($data);exit;
			$columns = M("branch")->select();	
			$this->assign("columns", $columns);
			$this->display();
			
		}
	}
    
	
	public function perm(){
		if(IS_POST)
		{
			$data = I("post.");
			
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
				$this->assign("data", $data);
			}
			$lang = isset($data['lang']) ? $data['lang'] : 0;
			$columns = $this->getColumn("admin", $lang);
			$this->assign("columns", $columns);
			$this->display();
		}
	}
	
	//部门列表
	public function branch(){
	  $this->data = M("branch")->select();	
	 $this->display();	
	}
    
	//部门添加
    public function addbranch(){
		$branch = M("branch");
		
		if(IS_POST)
		{
			$data = I("post.");

			$iid = I("post.iid", 0, "intval");
			$data["addtime"] = time();
			if($arr=$branch->find($iid))
			{   
		       
				$branch->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{   
				$branch->add($data);
				echo 1;
			}
		}else
		{   
			$id = I("get.id", 0, "intval");
			if($data = $branch->find($id))
			{
				$this->assign("data", $data);
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
         		 $srt = M("admin")->where(array("member_id"=>$id))->find();
				if(!$srt){
				$model->where(array("id" => $id))->delete();
				echo 1;
				}else{
				echo 2;	
				}
				exit;
			}
		}
	}
	
		// 删除
	public function delByIds()
	{
		$id = I("post.id");
		if($m = ucfirst(I("post.m", "")))
		{
			$model = M("$m");

			if($arr=$model->find($id))
			{   
				//查找管理员有没有此人
         		 $srt = M("member")->where(array("branch_id"=>$id))->find();
				if(!$srt){
				$model->where(array("id" => $id))->delete();
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