<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class RecordController extends CommonController{

	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->model = M("uclient");
	}

	public function index(){

		if(in_array('1001',$_SESSION['level'])){
		  $kun =  array('type'=>1);

		}else if(in_array('1002',$_SESSION['level'])){

		  $id=M("rank")->where("name='{$_SESSION['rank_name']}'")->getField("id");
		  $ids=M("rank")->where("pid={$id}")->getField("id",true);
		  $ids[]=$id;
		  $u_id=M("uclient")->where(array("admin_ids"=>array("in",$ids)))->getField("id",true);
		  $kun = array("type"=>1,"u_id"=>array("in",$u_id));
		}else{
			$kun= array("type"=>1,"admin_id"=>$_SESSION['aid']);
		}

		$post = I('get.');
		$stime = strtotime($post['stime']." 00:00:00");
		$endtime = strtotime($post['endtime']." 24:00:00");
		if(IS_GET){
			if($post['stime'] && $post['endtime'] && $post['sou']){

				$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>array("like","%{$post['sou']}%")))->select();
				foreach ($aid as $value) {
					$aids[]=$value["id"];
				}
				$uid=M("uclient")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
				if(count($aids)){
					$where['admin_id']=array("in",$aids);
				}else{
					$where["u_id"]=array("in",$uid);
				}
				$where[]=array("type"=>1,"ids"=>array(array("gt",$stime),array('elt',$endtime)));
				$where[]=$kun;
			  }elseif($post['stime'] && $post['endtime']){
			  	$where[]=array("type"=>1,"ids"=>array(array("gt",$stime),array('elt',$endtime)));
				$where[]=$kun;
			  }else{
				  	$aid=M("member")->alias("m")->join("admin a on a.member_id=m.id")->Field("a.id")->where(array("m.username"=>array("like","%{$post['sou']}%")))->select();
					foreach ($aid as $value) {
						$aids[]=$value["id"];
					}
					$uid=M("uclient")->where(array("username"=>array("like","%{$post['sou']}%")))->getField("id",true);
					if(count($aids)){
						$where['admin_id']=array("in",$aids);
					}else{
						$where["u_id"]=array("in",$uid);
					}
					$where["type"]=1;
					$where[]=$kun;
			  }
		}

        $data=M("voice")->where($where)->order("ids desc")->select();

        foreach ($data as $key => $value) {
            $data[$key]=$value;
            $data[$key]["start_time"]=date("Y-m-d H:i:s" , $data[$key]["ids"]);

            if($value['u_id']){
                $data[$key]["u_id"]=$this->model->where("id={$value['u_id']}")->getField("username");
            }
            if($value['admin_id']){
                $data[$key]["admin_id"]=M("member")->alias('m')->join("admin a ON m.id=a.member_id")->where("a.id={$value['admin_id']}")->getField("m.username");
            }

        }
        $count=count($data);
        $Page= $this->getPage($count,20);
        $this->show= $Page->show();
        $datas = array_slice($data,$Page->firstRow,$Page->listRows);
        $this->assign('data',$datas);
        $this->display();
	}

}
