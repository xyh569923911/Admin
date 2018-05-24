<?php 
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller{

	public function index()
	{
		$type=$_GET['t'];
		if($type){
			$this->assign('types',$type);
			$this->display("Login/indexs");
		}else{
			$this->display();
		}
		
	}

	// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
	protected function check_verify($code, $id = ''){
	    $verify = new \Think\Verify();
	    return $verify->check($code, $id);
	}

	public function doLogin()
	{
		if(IS_POST)
		{  // echo 1;exit;
			$username = I("post.username");
			$password = I("post.password");
			 $code = I("post.code");
			 //平板不验证验证码
			 if(!I("post.types")){
					if(!$this->check_verify($code))
					{
						$this->ajaxReturn(array("state" => 0, "msg" => "验证码错误"));
						exit;
					} 
				}

			if($username == ''){
				$this->ajaxReturn(array("state" => 0, "msg" => "用户名不能为空"));
				exit;
			}
			if($password == ''){
				$this->ajaxReturn(array("state" => 0, "msg" => "密码不能为空"));
				exit;
			}

			$model = M("Admin");
			$username_id = M("member")->where(array("username"=>$username))->getField('id');	
			if($info = $model->where(array("member_id" => $username_id, "password" => md5($password)))->find())
			{   
		        //记住登录时间
				$model-> where(array("id"=>$info['id']))->setField('dladdtime',time());
				// session("aid", $info["id"]);
				$_SESSION["aid"] = $info["id"];
				$_SESSION["aname"] = $username;
				//权限
				$level = M("rank")->where(array("id"=>$info["level_id"]))->find();
				$level["level"] = explode(",",$level["level"]);
				//审批权限
				$sp["sp_level"] = explode(",",$info["sp_level"]);
				if($sp["sp_level"]){
					$_SESSION["sp_level"] = $sp["sp_level"];
				}
				if(in_array('1', $sp["sp_level"]) || in_array('2', $sp["sp_level"]) || in_array('3', $sp["sp_level"]) || in_array('4', $sp["sp_level"]) || in_array('5', $sp["sp_level"]) || in_array('6', $sp["sp_level"]) || in_array('7', $sp["sp_level"])){
					array_push($level["level"],'81');
					array_push($level["level"],'86');
					array_push($level["level"],'1002');
				}
				
				$_SESSION["level"] = $level["level"];
				$_SESSION["rank_name"] = $level["name"];
				
				if(I("post.types")=='1'){
					$this->ajaxReturn(array("state" => 1,"url"=>"/Admin/Monetary/ipadSystem"));
				}else if(I("post.types")=='2'){
					$this->ajaxReturn(array("state" => 1,"url"=>"/Admin/Intermediary/ipadSystem"));
				}else{
					$this->ajaxReturn(array("state" => 1,"url"=>"/Admin/Base/index"));
				}
				
				
				exit;
			}
			else
			{
				$this->ajaxReturn(array("state" => 0, "msg" => "用户名或密码错误"));
				exit;
			}

		}
	}


	public function loginOut()
	{
		session(null);
		if($_GET['t']){
			$this->success("退出成功", "http://47.92.119.237:8888/Signing/");
		}else{
			$this->success("退出成功", "/Admin/Login/index");
		}
		
	}


}


 ?>