<?php 
namespace Admin\Controller;
use Think\Controller;
use \Org\Util\Data;
use Think\Db\Driver;

class LuyinController extends Controller{
    
	
    public function index() {

		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 3*1024*1024 ;// 设置附件上传大小
		$upload->exts = array('mp3','mp4','jpg','zip','amr','3gp');// 设置附件上传类型
		$upload->savePath = '/Uploads/'; // 设置附件上传根目录
		// 上传文件 
		$info = $upload->upload();
		ini_set('post_max_size', '2000M');
		if($info) {// 上传错误提示错误信息
		$data = array("a"=>1);
		echo json_encode($data);
		exit; 
		}

		exit; 
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
		//print_r($newArr);exit;
		foreach($newArr as $kuy=>$vul){
			if($vul['hprice'] == $b[count($b)-1]){
				 $srt[] = $vul;
			}
		}
		
		$member_ids = M("admin")->where(array("id"=>$srt[0]['admin_id']))->getField("member_id");//跟进人
		$sun_admin = M("Member")->where(array("id"=>$member_ids))->getField("username");//跟进人
		$arr['admin_member_id'] = $sun_admin;
		$arr['hprice'] = $srt[0]['hprice'];
		return $arr;
	}
   
    public function cesi() {
		
	   //货款额最高的		
	  $lohb = array("ctime"=>array(array('gt',strtotime(date('Y-m-01 0:00:01'))),array("elt",mktime(23,59,59,date('m'),date('t'),date('Y')))));
	  $lohb[] = array("state_id"=>15,"finance_state"=>1);
	  $lohbss = M("uclient")->where($lohb)->field("id,hprice,admin_id")->select();//约见客户
	  
	  $this->admin_member_danss = $this->sunprice($lohbss);  	
		print_r($this->admin_member_danss ) ;exit;
	 $this->display();	
    }
  
}
