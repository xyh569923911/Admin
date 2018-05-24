<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;

class TypeController extends CommonController{


	protected $type;
    protected $state;
	
	public function _initialize()
	{
    	parent::_initialize();
		$this->type = M("Type");
		$this->state = M("State");
		$this->isNavs = "Type";
	}
	
	//类型
	public function index()
	{   
        /*  $arr = M("uclienttime")->Field("id,u_id")->select();
		foreach($arr as $key=>$val){
			$user = M("uclient")->where(array("id"=>$val["u_id"]))->getField("admin_id");
			M("uclienttime")->where(array("id"=>$val['id']))->save(array("admin_id"=>$user));
		}  */
		
		$this->data = $this->type->order("id asc")->select();
		
		$this->display();
	}

	// 类型添加
	public function addindex()
	{
		if(IS_POST)
		{
			$data = I("post.");
			$iid = I("post.iid", 0, "intval");
			$data['addtime'] = time();
			if($this->type->find($iid))
			{
				$this->type->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{
				$this->type->add($data);
				echo 1;
			}
			
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->type->find($id))
			{
				$this->assign("data", $data);
			}
			$this->display();
		}
	}
	
	//状态
    public function state()
	{
		$this->data = $this->state->order("sort asc")->select();
		
		$this->display();
	}
    
	// 状态添加
	public function addstate()
	{
		if(IS_POST)
		{
			$data = I("post.");
			$iid = I("post.iid", 0, "intval");
			$data['addtime'] = time();
			if($this->state->find($iid))
			{
				$this->state->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{
				$this->state->add($data);
				echo 1;
			}
			
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->state->find($id))
			{
				$this->assign("data", $data);
			}
			$this->display();
		}
	}

}


 ?>