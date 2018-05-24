<?php 
namespace Admin\Controller;
use Think\Controller;
use \Org\Util\Data;
class CommonController extends Controller{

	public function _initialize()
	{   $this->huanc = $this->getRealSize($this->getDirSize('./Application/Runtime'));
		if(!isset($_SESSION["aid"]) || !isset($_SESSION["aname"]))
		{
			$this->redirect("Login/index");
		}
		$level = M("admin")->where(array("id"=>session("aid")))->find();
		$this->admin_level = $level;
		$level = M("rank")->where(array("id"=>$level['level_id']))->getField("level");
		$this->levels = explode(",",$level);
		$this->level_y = $this->admin_arr();
		$this->base = M("base")->where(array("id"=>3))->field("time")->find();
	}

	public function admin_arr(){
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
		$ayp = M("admin")->where(array("level_id"=>array("in",$ayp)))->getField("id",true);
		 return $ayp;
		
	}
	
	public function times(){
		$id = I("id");
	    if($id){
		  $id = ceil($id);
		  M("base")->where(array("id"=>3))->save(array("time"=>$id));
		  echo 1;
	    }
	}
	
	public function codes($mobile){
		            //$this->error(I("post.phone"));
		require_once(VENDOR_PATH.'Send/Send.php');
		header('Content-Type:text/html; charset=utf-8');				   
	   //以下三条读取配置文件中的内容即可
		$http = C('message.http');
		$uid = C('message.uid');
		$pwd = C('message.pwd');
		$time=date('m月d日 H:i:s',time());
		$content = '【大象金服】您有新客户,请登录大象CRM系统查看！'.$time;							
		$send = new \Send;
		$res = $send->sendSMS($http,$uid,$pwd,$mobile,$content);
	}
	
	protected function thumbImg($file ,$width = 220, $height = 120){
		$img = new \Think\Image();
		$img->open("./Public/". $file);
		$thumbPath = dirname($file) . "/thumb_" . basename($file);
		$img->thumb($width, $height, \Think\Image::IMAGE_THUMB_FIXED)->save("./Public" . $thumbPath);
		return $thumbPath;
	}

	protected function get_choose($model, $data, $id, $lang){
		$ids = $this->get_son($data, $id);
		$ids[] = $id;
		$strId = implode(',', $ids);
		$choose = $model->where(array("lang" => $lang, "id" => array("not in", "{$strId}")))->select();
		$final = Data::tree($choose, "name", "id", "pid");
		return $final;
	}


	protected function get_son($cate, $id){
		$temp = array();
		foreach ($cate as $k => $v) {
			if($v['pid'] == $id){
				$temp[] = $v["id"];
				$temp = array_merge($temp, $this->get_son($cate, $v['id']));
			}
		}

		return $temp;
	}

	// 获得所属栏目列表
	protected function getColumn($type, $lang = 0)
	{
		$columns = M("Columns")->where(array("lang" => $lang))->select();
		$columns = Data::tree($columns, "name", "id", "pid");
		foreach ($columns as $key => $value) {
			if($value["type"] != $type)
			{
				unset($columns[$key]);
			}
		}
		return $columns;
	}


	// 修改排序
	public function editSort()
	{
		$id = I("post.id");
		$newSort = I("post.newSort", 0, "intval");
		if($m = ucfirst(I("post.m", "")))
		{

			$model = M("$m");
			if($model->find($id))
			{
				$model->where(array("id" => $id))->save(array("sort" => $newSort));
			}
			echo 1;
		}
	}
	
	// 删除文件图片
	protected function DelImage($arr, $data)
	{ 
		if($arr['image'] != $data['image']){
		 if(!empty($arr['image'])){
		   unlink('./Public'.$arr['image']);
		 };
		};

        if($arr['images'] != $data['images']){
		 //循环判断
		 foreach($arr['images'] as $k=>$val){
         $pos = strpos($data['images'], $val);
		
         if($pos!==false){
             unlink('./Public'.$val);
           
         }
		 }
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
         		//调用删除图片文件
		        $this->DelImage($arr);
				if($model == "Uclient"){
					unlink('./Public'.$arr['filename']);
				}
				
				$model->where(array("id" => $id))->delete();
				echo 1;
				exit;
			}
		}
	}
	
	//获取缓存大小
	public function getDirSize($dir)  {  
		 $handle = opendir($dir); 
		 while (false!==($FolderOrFile = readdir($handle)))   {  
		 if($FolderOrFile != "." && $FolderOrFile != "..")     {   
		 if(is_dir("$dir/$FolderOrFile"))     { 
		 $sizeResult += $this->getDirSize("$dir/$FolderOrFile");  
		 }     else    {    
		 $sizeResult += filesize("$dir/$FolderOrFile");    
		 }    }    } 
		 closedir($handle); 
		 return $sizeResult; 
    }  
   // 单位自动转换函数 
     public function getRealSize($size)  {    $kb = 1024;   // Kilobyte   
		 $mb = 1024 * $kb; // Megabyte   
		 $gb = 1024 * $mb; // Gigabyte   
		 $tb = 1024 * $gb; // Terabyte  
		 if($size < $kb)   {     return $size." B";   
		 }   else if($size < $mb)   {
			 return round($size/$kb,2)." KB";   
			 }   else if($size < $gb)   {   
			 return round($size/$mb,2)." MB"; 
			 }   else if($size < $tb)   {
				 return round($size/$gb,2)." GB";  
				 }   else  {   
				 return round($size/$tb,2)." TB";  
				 } 
	 }
	 
	 protected function getPage($count, $page =10, $parameter)
	{
		$p = new \Think\Page($count, $page, $parameter);

		$p->setConfig('prev', '上一页');
	    $p->setConfig('next', '下一页');
	    $p->setConfig('last', '末页');
	    $p->setConfig('first', '首页');
		$p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
	    $p->lastSuffix = false;//最后一页不显示为总页数
	    return $p;
	}
	
	protected function getPages($count, $page =10, $parameter)
	{
		$p = new \Think\Pages($count, $page, $parameter);

		$p->setConfig('prev', '上一页');
	    $p->setConfig('next', '下一页');
	    $p->setConfig('last', '末页');
	    $p->setConfig('first', '首页');
		$p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
	    $p->lastSuffix = false;//最后一页不显示为总页数
	    return $p;
	}
	
	//查找共享人
	public function qxshai($ch_datat,$id,$model,$duaz){		
		foreach($ch_datat as $key=>$val){
			//判断权限
			 $arr = M("Admin")->where(array("id"=>$val['member_id']))->find();
			 $val['username'] = $arr['username'];
			if($id == 1 || $_SESSION['level']==3){
			   $ch_datat[$key]['username'] = $val['username'];
			   $ch_datas = $ch_datat;
			}else{
				 if( $_SESSION['level']==2 && $model=="supplyLevel"){
					 $ch_datat[$key]['username'] = $val['username'];
			         $ch_datas = $ch_datat;
				 }else{
				 if($val['level'] == 0){
					 if($val['member_id'] == $id){
						 $ch_datas[$key] = $val;
					 } 
				 }else{
				   //查找共享人
				   $srt = M($model)->where(array($duaz=>$val['id']))->find();
				   $srt = explode(",",$srt['member_id']);
				   if(in_array($id,$srt)){
					   $ch_datas[$key] = $val;
				   }
				 }
				 }
			}
		}
		return $ch_datas;
	}
	
	//显示会员
	public function getaction_lists(){
	
            $admin_name = M("rank")->where(array("name"=>'业务总监'))->getField("id");
				//echo $admin_name;exit;
			//$admin_names = M("rank")->where(array("name"=>'业务总监'))->find();
			
			$genjin = M("rank")->where(array("pid"=>$admin_name))->select();
			
			foreach($genjin as $kb=>$vb){
				$genjins[$kb] =$vb['id'];
			}
			$gen = $genjins;
		
		    $genjinss = implode(",",$genjins);
			$genjins = M("rank")->where(array("pid"=>array("in",$genjinss)))->getField("id",true);
				
			$genjin = array_merge($gen, $genjins);
		
			$degen = implode(",",$genjin);
			$srt_arr = M("admin")->where(array("level_id"=>array("in",$degen)))->field("id,member_id")->select();
			foreach($srt_arr as $k=>$v){
				$srt_arr[$k]['username'] = M("member")->where(array("id"=>$v['member_id']))->getField("username");
			}
			$data=$srt_arr;
		   
		$arr='';
		foreach ($data as $key => $value) {
			$arr .='<label class="checkbox-inline">';
			$arr .='<input type="radio" name="admin_id" value="'.$value['member_id'].'">'.$value['username'].'';
			$arr .='</label>';
		}
		$this->ajaxReturn($arr);exit;
	}
	
	//分配会员权限
	public function setaction_lists(){
		$admin_id=I('post.admin_id');//会员id
		$messageboard_str=I('post.messageboard_str');//资料id

		$messageboard_id_Array=explode(',',$messageboard_str);

		$this->messageboard=M('messageboard');

		foreach ($messageboard_id_Array as $key => $value) {
			 $post['admin_id'] = $admin_id;
			 $post['addtime'] = time();
		 	 $this->messageboard->where(array('id'=>$value))->save($post);
		} 
		 
		$this->ajaxReturn(1);exit; 
		 
	}

	//不计算公海的
	public function statewc(){
		/* $state = M("state")->where(array('type'=>array("in","0,3")))->getField('id', true);
		$state[] = 0;
		$state = implode(",",$state);
		
		$end_time = strtotime(date('Y-m-d', strtotime('-7 day'))); 
		$states = M("state")->where(array('type'=>array("in","2")))->getField('id', true);
		$states = implode(",",$states);
		$data = array("state_id"=>array("in",$state),'_logic'=>'or',array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time))); */
		$data = array("sources"=>array("neq",'公海'));
		return $data;
	}
}



 ?>