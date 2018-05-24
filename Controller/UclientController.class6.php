<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class UclientController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->isUclient = "Uclient";
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
	{   //header("Content-type: text/html; charset=utf-8"); 
		$kun = $this->adminMember();
		$state = M("state")->where(array('type'=>array("in","0")))->getField('id', true);
		$state[] = 0;
		$state = implode(",",$state);
		$post = I('get.');
		$stime = strtotime($post['stime']." 00:00:00");//开始时间
		$endtime = strtotime($post['endtime']." 24:00:00");//开始时间
		if(!in_array('1004',$_SESSION['level']) || in_array('1001',$_SESSION['level']) || in_array('1002',$_SESSION['level'])){
        //未完成客户数据
		if(IS_GET){
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
				   if($post['sou']){
					 
					 $sou_member=M("member")->where(array("username"=>$post['sou']))->find();
					 if($sou_member){
					 $sou_member=M("admin")->where(array("member_id"=>$sou_member['id']))->getField("id");	 
					 }
					 if($sou_member){
					 $where[] = array("state_id"=>array("in",$state));	 
					 $where[] = array("admin_id"=>$sou_member);  			 
					 }else{
					 $sou_state = M("state")->where(array("name"=>$post['sou']))->getField("id");					 
					 if($sou_state || $post['sou'] == iconv("GB2312","UTF-8","未联系")){
					 if($post['sou'] == iconv("GB2312","UTF-8","未联系")){
					 $where[] = array("state_id"=>""); 	  	 
					 }else{
					 $arr_state = explode(",",$state);	
                     if(in_array($sou_state,$arr_state)){
						 $where[] = array("state_id"=>$sou_state); 	  
					 }else{
						 $where[] = array("state_id"=>100);  
					 }					 
					 }	 
					 }else{	 
					 $where[]["username|phone"] = $post['sou'];
					 $where[] = array("state_id"=>array("in",$state));
					 }
					 $where[] = $kun;
					 }
				   }else{	  
				    $where[] = array("state_id"=>array("in",$state));
				    $where[] = $kun;
				   }
				  
				  }
				}	  
			 }
			 
		}else{
			$where = array("state_id"=>array("in",$state).$kun);
			
		}
		}else{
			$where = $kun;
		}
	    
		$ch_datats = $this->model->where($where)->order("addtime desc")->field("id,username,sex,phone,lxtime,type_id,state_id,admin_id,types,state,sign_state,rank_admin_id,su_state,met,content,allot,source,sources,promotion")->select();
        /* if(in_array('1001',$_SESSION['level'])){
			 echo $this->model->getLastSql();exit;
		 }   */
		 
		 
		//15天不联系投入公海数据
        $end_time = strtotime(date('Y-m-d', strtotime('-15 day')));
		$states = M("state")->where(array('type'=>array("in","2")))->getField('id', true);
		$states = implode(",",$states);

		if(IS_GET){
			
			if($post['stime'] && $post['endtime']){
				if($post['types']){
				 $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array('elt',$endtime),array("gt",$end_time)));
				 $wheres[] = $kun;
				}else{
				 $wheres = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array('elt',$endtime),array("gt",$end_time)));	
				 $wheres[] = $kun;
				}
			 }else{
				if($post['stime']){
					if($post['types']){
					$wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));
					$wheres[] = $kun;
					}else{
					$wheres = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));	
					$wheres[] = $kun;
					}
				}else if($post['endtime']){
					if($post['types']){
					$wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));
					$wheres[] = $kun;
					}else{
					$wheres = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));		
					$wheres[] = $kun;
					}
				}else{
				  if($post['types']){	
				  $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array("gt",$end_time));
                  $wheres[] = $kun;				  
				  }else{
				  if($post['sou']){

					 $sou_member=M("member")->where(array("username"=>$post['sou']))->find();
					 if($sou_member){
					 $sou_member=M("admin")->where(array("member_id"=>$sou_member['id']))->getField("id");	 
					 }
					 if($sou_member){
					 $wheres = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time));	 
					 $wheres[] = array("admin_id"=>$sou_member);  			 
					 }else{  

					 $sou_state = M("state")->where(array("name"=>$post['sou']))->getField("id");					 
					 if($sou_state || $post['sou'] == iconv("GB2312","UTF-8","未联系")){
					 if($post['sou'] == iconv("GB2312","UTF-8","未联系")){
					 $wheres[] = array("state_id"=>100);	 
					 $wheres[] = array("lxtime"=>array("gt",$end_time));	
					 }else{
                     $arr_state = explode(",",$states);	
					 //print_r($sou_state);exit;
                     if(in_array($sou_state,$arr_state)){
						 $wheres[] = array("state_id"=>$sou_state,"lxtime"=>array("gt",$end_time));   
					 }else{
						 $wheres[] = array("state_id"=>100,"lxtime"=>array("gt",$end_time));  
					 }					  
					  
					 						 
					 }	 
					 }else{	 
//					 $wheres["username|phone"] = $post['sou'];
                     $wheres["username|phone"] = array('like','%'.$post['sou'].'%');
					 $wheres[] = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time));	 
					 }
					 $wheres[] = $kun;
					 	
					 }
				   }else{  
				     $wheres = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time));
				     $wheres[] = $kun;
				   }
				  
				  }
				}	  
			 }
		}else{
			$wheres = array("state_id"=>array("in",$states));
			$wheres[] = $kun;
			
		}
		$ch_datatss = $this->model->where($wheres)->order("addtime desc")->field("id,username,sex,phone,lxtime,type_id,state_id,admin_id,types,state,sign_state,rank_admin_id,su_state,met,content,allot,source,sources,promotion")->select();
		
		/* if(in_array('1001',$_SESSION['level'])){
			 echo $this->model->getLastSql();exit;
		 }    */
		$ch_datats = array_merge_recursive($ch_datatss,$ch_datats);//合并数组 rank_admin_username
		$ch_datatss = $ch_datats;
		foreach($ch_datatss as $key=>$v){
		$ch_datatss[$key]['lxtime'] = $v['lxtime'];
		} 
		$datetime = array();
		foreach ($ch_datatss as $user) {
		$datetime[] = $user['lxtime'];
		} 
		array_multisort($datetime,SORT_DESC,$ch_datats);//时间排序
		//业务部 渠道部 财务部
		
		$name_r = M("Admin")->where(array("id"=>session("aid")))->getField("level_id");
		$name_r = M("rank")->where(array("id"=>$name_r))->Field("name,id")->find();
		
		$admin_names = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'业务总监')))->find();
		$genjin = M("rank")->where(array("pid"=>$admin_names['id']))->select();

		foreach($genjin as $kb=>$vb){
			$genjins[$kb] =$vb['id'];
		}
		$genjins = implode(",",$genjins);
		$genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
		foreach($genjins as $k=>$v){
			$ayp[$k] = $v['id'];
		}

		if(in_array($name_r['id'],$ayp)){
			$kuns['bus_name'] = '业务员';
			$kuns['bus'] = M("state")->where(array("state"=>0))->getField("id",true);
			$this->kuns = $kuns;
		}else if($name_r['name'] == iconv("GB2312","UTF-8",'渠道部')){
			$kuns['bus_name'] = '渠道部';
			$kuns['bus'] = M("state")->where(array("state"=>1))->getField("id",true);
			$this->kuns = $kuns;
		}else if($name_r['name'] == iconv("GB2312","UTF-8",'财务部')){
			$kuns['bus_name'] = '财务部';
			$kuns['bus'] = M("state")->where(array("state"=>2))->getField("id",true);
			$this->kuns = $kuns;
		}

        /*foreach($ch_datats as $key=>$val){
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
			//手机号加密****
			$ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);	
            
			
			
		}

		$count=count($ch_datat);//得到数组元素个数
		$Page= $this->getPage($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
		$ch_datat = array_slice($ch_datat,$Page->firstRow,$Page->listRows);
		$this->show= $Page->show();// 分页显示输出?
		$this->assign("ch_data",$ch_datat);
		$this->display();0921删除*/
        $count=count($ch_datats);//得到数组元素个数
        $Page= $this->getPage($count,20);  //   传入总记录数和每页显示的记录数

        $ch_datats = array_slice($ch_datats,$Page->firstRow,$Page->listRows);

        foreach($ch_datats as $key=>$val){
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
            //手机号加密****
            // $ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);
            $ch_datat[$key]['phone'] =$val['phone'];

        }

        $this->show= $Page->show();// 分页显示输出?
        $this->assign("ch_data",$ch_datat);
        $this->display();



	}

//返回接口数据
    public function calls(){

        $cid=intval($_POST['id']);

        $arr=M("uclient")->alias('ut')->field('m.phone,ut.phone as uphone')->join('__ADMIN__ a ON a.id=ut.admin_id')
            ->join('__MEMBER__ m ON m.id=a.member_id')->where("ut.id={$cid}")->find();

        $data['id']=time();
        $data['account']=$arr['phone'];
        $data['number']=$arr['uphone'];
        $data['type']=1;
        // echo json_encode($data);
        $url="http://localhost/Admin/uclient/index?id={$data['id']}&account={$data['account']}&number={$data['uphone']}&type=1";
        $ch=curl_init((string)$url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch,CURLOPT_POST, true); //启用POST提交
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
        // echo json_encode($data);
        // $this->ajaxReturn($data);
    }

    public function Ticket($id,$acount,$number,$type,$start_time,$duration,$record){

        $arr=M("admin")->alias('ad')->field('ad.id')->join("__MEMBER__ m ON m.id=ad.member_id")->where("m.phone={$acount}")->find();
        $arrs=M('uclient')->field("id")->where("phone={$number}")->find();
        $data['ids']=$id;
        $data['admin_id']=$arr['id'];
        $data['u_id']=$arrs['id'];
        $data['type']=$type;
        $data['start_time']=$start_time;
        $data['duration']=$duration;
        $data['url']=$record;
        M('voice')->add($data);
    }

//排序
    public function px(){

        $kun = $this->adminMember();
        $state = M("state")->where(array('type'=>array("in","0")))->getField('id', true);
        $state[] = 0;
        $state = implode(",",$state);
        $post = I('get.');
        $stime = strtotime($post['stime']." 00:00:00");//开始时间
        $endtime = strtotime($post['endtime']." 24:00:00");//开始时间
        if(!in_array('1004',$_SESSION['level']) || in_array('1001',$_SESSION['level']) || in_array('1002',$_SESSION['level'])){
            //未完成客户数据
            if(IS_GET){
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
                            if($post['sou']){

                                $sou_member=M("member")->where(array("username"=>$post['sou']))->find();
                                if($sou_member){
                                    $sou_member=M("admin")->where(array("member_id"=>$sou_member['id']))->getField("id");
                                }
                                if($sou_member){
                                    $where[] = array("state_id"=>array("in",$state));
                                    $where[] = array("admin_id"=>$sou_member);
                                }else{
                                    $sou_state = M("state")->where(array("name"=>$post['sou']))->getField("id");
                                    if($sou_state || $post['sou'] == iconv("GB2312","UTF-8","未联系")){
                                        if($post['sou'] == iconv("GB2312","UTF-8","未联系")){
                                            $where[] = array("state_id"=>"");
                                        }else{
                                            $arr_state = explode(",",$state);
                                            if(in_array($sou_state,$arr_state)){
                                                $where[] = array("state_id"=>$sou_state);
                                            }else{
                                                $where[] = array("state_id"=>100);
                                            }
                                        }
                                    }else{
                                        $where[]["username|phone"] = $post['sou'];
                                        $where[] = array("state_id"=>array("in",$state));
                                    }
                                    $where[] = $kun;
                                }
                            }else{
                                $where[] = array("state_id"=>array("in",$state));
                                $where[] = $kun;
                            }

                        }
                    }
                }

            }else{
                $where = array("state_id"=>array("in",$state).$kun);

            }
        }else{
            $where = $kun;
        }

        $ch_datats = $this->model->where($where)->order("addtime asc")->field("id,username,sex,phone,lxtime,type_id,state_id,admin_id,types,state,sign_state,rank_admin_id,su_state,met,content,allot,source,sources,promotion")->select();
        /* if(in_array('1001',$_SESSION['level'])){
			 echo $this->model->getLastSql();exit;
		 }   */


        //15天不联系投入公海数据
        $end_time = strtotime(date('Y-m-d', strtotime('-15 day')));
        $states = M("state")->where(array('type'=>array("in","2")))->getField('id', true);
        $states = implode(",",$states);

        if(IS_GET){

            if($post['stime'] && $post['endtime']){
                if($post['types']){
                    $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array('elt',$endtime),array("gt",$end_time)));
                    $wheres[] = $kun;
                }else{
                    $wheres = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array('elt',$endtime),array("gt",$end_time)));
                    $wheres[] = $kun;
                }
            }else{
                if($post['stime']){
                    if($post['types']){
                        $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));
                        $wheres[] = $kun;
                    }else{
                        $wheres = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));
                        $wheres[] = $kun;
                    }
                }else if($post['endtime']){
                    if($post['types']){
                        $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));
                        $wheres[] = $kun;
                    }else{
                        $wheres = array("state_id"=>array("in",$states),"lxtime"=>array(array("gt",$stime),array("gt",$end_time)));
                        $wheres[] = $kun;
                    }
                }else{
                    if($post['types']){
                        $wheres = array("state_id"=>array("in",$states),"types"=>$post['types'],"lxtime"=>array("gt",$end_time));
                        $wheres[] = $kun;
                    }else{
                        if($post['sou']){

                            $sou_member=M("member")->where(array("username"=>$post['sou']))->find();
                            if($sou_member){
                                $sou_member=M("admin")->where(array("member_id"=>$sou_member['id']))->getField("id");
                            }
                            if($sou_member){
                                $wheres = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time));
                                $wheres[] = array("admin_id"=>$sou_member);
                            }else{

                                $sou_state = M("state")->where(array("name"=>$post['sou']))->getField("id");
                                if($sou_state || $post['sou'] == iconv("GB2312","UTF-8","未联系")){
                                    if($post['sou'] == iconv("GB2312","UTF-8","未联系")){
                                        $wheres[] = array("state_id"=>100);
                                        $wheres[] = array("lxtime"=>array("gt",$end_time));
                                    }else{
                                        $arr_state = explode(",",$states);
                                        //print_r($sou_state);exit;
                                        if(in_array($sou_state,$arr_state)){
                                            $wheres[] = array("state_id"=>$sou_state,"lxtime"=>array("gt",$end_time));
                                        }else{
                                            $wheres[] = array("state_id"=>100,"lxtime"=>array("gt",$end_time));
                                        }


                                    }
                                }else{
                                    $wheres["username|phone"] = $post['sou'];
                                    $wheres[] = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time));
                                }
                                $wheres[] = $kun;

                            }
                        }else{
                            $wheres = array("state_id"=>array("in",$states),"lxtime"=>array("gt",$end_time));
                            $wheres[] = $kun;
                        }

                    }
                }
            }
        }else{
            $wheres = array("state_id"=>array("in",$states));
            $wheres[] = $kun;

        }
        $ch_datatss = $this->model->where($wheres)->order("addtime asc")->field("id,username,sex,phone,lxtime,type_id,state_id,admin_id,types,state,sign_state,rank_admin_id,su_state,met,content,allot,source,sources,promotion")->select();

        /* if(in_array('1001',$_SESSION['level'])){
             echo $this->model->getLastSql();exit;
         }    */
        $ch_datats = array_merge_recursive($ch_datatss,$ch_datats);//合并数组 rank_admin_username
        $ch_datatss = $ch_datats;
        foreach($ch_datatss as $key=>$v){
            $ch_datatss[$key]['lxtime'] = $v['lxtime'];
        }
        $datetime = array();
        foreach ($ch_datatss as $user) {
            $datetime[] = $user['lxtime'];
        }
        array_multisort($datetime,SORT_ASC,$ch_datats);//时间排序

        //业务部 渠道部 财务部

        $name_r = M("Admin")->where(array("id"=>session("aid")))->getField("level_id");
        $name_r = M("rank")->where(array("id"=>$name_r))->Field("name,id")->find();

        $admin_names = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'业务总监')))->find();
        $genjin = M("rank")->where(array("pid"=>$admin_names['id']))->select();

        foreach($genjin as $kb=>$vb){
            $genjins[$kb] =$vb['id'];
        }
        $genjins = implode(",",$genjins);
        $genjins = M("rank")->where(array("pid"=>array("in",$genjins)))->select();
        foreach($genjins as $k=>$v){
            $ayp[$k] = $v['id'];
        }

        if(in_array($name_r['id'],$ayp)){
            $kuns['bus_name'] = '业务员';
            $kuns['bus'] = M("state")->where(array("state"=>0))->getField("id",true);
            $this->kuns = $kuns;
        }else if($name_r['name'] == iconv("GB2312","UTF-8",'渠道部')){
            $kuns['bus_name'] = '渠道部';
            $kuns['bus'] = M("state")->where(array("state"=>1))->getField("id",true);
            $this->kuns = $kuns;
        }else if($name_r['name'] == iconv("GB2312","UTF-8",'财务部')){
            $kuns['bus_name'] = '财务部';
            $kuns['bus'] = M("state")->where(array("state"=>2))->getField("id",true);
            $this->kuns = $kuns;
        }


        $count=count($ch_datats);//得到数组元素个数
        // var_dump($count);
        $Page= $this->getPage($count,20);  //   传入总记录数和每页显示的记录数

        $ch_datats = array_slice($ch_datats,$Page->firstRow,$Page->listRows);

        foreach($ch_datats as $key=>$val){
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
            //手机号加密****
            // $ch_datat[$key]['phone'] = preg_replace('/(1[34587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$val['phone']);
            $ch_datat[$key]['phone'] =$val['phone'];

        }
        $this->show= $Page->show();// 分页显示输出?
        $this->assign("ch_data",$ch_datat);

        $this->display('Uclient/indexpx');

    }

	// 添加未合作客户
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
		        $data['state'] = 1;
				$data['middle_admin_id'] = $data['admin_id'];
				$data['admin_id'] = $data['admin_id'];
			    }
				
				//业务员转状态到渠道部
				if($data['rank_admin_id']){
					$data['rank_state'] = 0;
					if($data['rank_admin_id'] != $arr['rank_admin_id']){
					$data['sign_rank_state'] = $arr['rank_admin_id'];
					$data['sign_state'] = 1;	
					}else{
					 $data['sign_rank_state'] = $data['rank_admin_id'];	
					 $data['sign_state'] = 0;
					}
				}
				
				if($data['finance_id']){
					$data['fitime'] = time();
				}
				
				
				if($data['phone'] != $arr['phone']){
				if($data['state_id']<10){
                 if($zong = $this->model->where(array("phone"=>$data['phone']))->find()){
					 if($zong['state_id']<10){
						echo 3;exit;  
					 }
				 }
				 }
				 }
				
                if($arr["state_id"] !=8 ){				
				if($data['state_id'] == 8){
					$data['met'] = 1;
				}
				}
				
				if($data['state_id'] != $arr['state_id']){
					$post['time'] = time();
					$post['u_id'] = $arr['id'];
					$post['state_id'] = $data['state_id'];
					$post['admin_id'] = $data['admin_id'];
					M("uclienttime")->add($post);
		     	} 
				$this->model->where(array("id" => $iid))->save($data);

				echo 4;
			}else
			{   
                 
				 if($data['state_id']<10){
                 if($zong = $this->model->where(array("phone"=>$data['phone']))->field("state_id")->find()){
					 if($zong['state_id']<10){
						echo 3;exit;  
					 }
				 }
				 }
            	 $data['state'] = 1;
		         $data['addtime'] = time();
		         $data['member_id'] = $_SESSION['aid'];
                $ss=M("rank")->where(array("name"=>$_SESSION['rank_name']))->field('id')->find();
                if(!in_array('1008',$_SESSION['level'])){
                    $data['admin_ids']=intval($ss['id']);
                }
				 $id=$this->model->add($data);
				 $post['time'] = time();
				 $post['u_id'] = $id;
				 $post['state_id'] = $data['state_id'];
				 $post['admin_id'] = $data['admin_id'];
				 M("uclienttime")->add($post);
				
				echo $id;
			}
		}else
		{
			
			
			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
			$this->model->where(array("id"=>$id))->save(array("zd_state"=>1));
			$kun = $this->adminMember();
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
			if(!$srt && !$srts){
				$this->redirect("/Admin/uclient/index");
			}
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
		    $admin_name = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'业务总监')))->getField("id");
			$admin_names = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'业务总监')))->find();
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
	}
	
	//留言记录
	public function record_desc(){
		$get['u_id'] = I("id");
		$get['content'] = I("content");
		$get['addtime'] = time();
		$get['admin_id'] = session("aname");
		$arr = M("record")->add($get);
		if($arr){
			$data['lxtime'] = time();
			$this->model->where(array("id" => $get['u_id']))->save($data);
			$arr = M("record")->where(array("id"=>$arr))->find();
			$arr['addtime'] = date("Y-m-d H:i",$arr['addtime']);
			$this->ajaxReturn($arr);
			//echo 1;
		}else{
			echo 2;
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
	
	//跟进记录
	public function record(){
		$id = I("id");
		$this->arr = M("record")->where(array("u_id"=>$id))->order("addtime desc")->select();
		$this->display();
	}

    //录音资料

    public function lyzls(){

        $aid=$_POST['aid'];

        $uid=$_POST['uid'];
        $arr=M("voice")->where(array('admin_id'=>$aid,'u_id'=>$uid))->select();
        if($arr){
            $this->ajaxReturn($arr);
        }else{
            echo 1;
        }
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
			 
             if($data['rank_admin_id'] == 0){
				 $qut[] = array("rank_admin_id"=>'');
			 }else{
				 $qut[] = array("rank_admin_id"=>$data["rank_admin_id"]);
			 }
			 if($data['rank_id'] == 0){
				 $qut[] = array("admin_id"=>'');
			 }else{
				 $qut[] = array("admin_id"=>$data["admin_id"]);
			 }
			// print_r($qut);exit;
            /*   $data['rank'] = $this->model->where($qut)->field("id,member_id,rank_admin_id")->select();//跟进状态	
			//print_r($data['rank']);exit;
             foreach($data['rank'] as $k=>$v){
				$admin_id = M("Admin")->where(array("id"=>$v['rank_admin_id']))->getField("id");//跟进状态 
				$srt = M("Member")->where(array("id"=>$admin_id))->Field('username,id')->find();	
				$data['rank'][$k]['username'] = $srt['username'];
				$data['rank'][$k]['admin_id'] = $srt['id'];
			}	
			 */
			$yun = M("admin")->where(array("id"=>$data['rank_admin_id']))->Field("member_id,id")->find();
			
			$data['rank'][0] = M("member")->where(array("id"=>$yun['member_id']))->find();
			$data['rank'][0]['rand_id'] = $yun['id'];
			//print_r($data);exit;
			//$rank_id = M("rank")->where(array("name"=>'渠道部'))->getField("id");   
			//print_r($data);exit;
			/* $data['rank'] = M("Admin")->where(array("level_id"=>$rank_id))->select();
			foreach($data['rank'] as $k=>$v){
				$srt = M("Member")->where(array("id"=>$v['member_id']))->Field('username,id')->find();	
				$data['rank'][$k]['username'] = $srt['username'];
				$data['rank'][$k]['admin_id'] = $srt['id'];
			} */
			
			
	        $this->columns = M("type")->select();//客户类型
			$this->columnss = M("state")->select();//跟进状态
		    $admin_name = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'业务员')))->getField("id");
            $columnsss = M("Admin")->where(array("id"=>array("neq",$data['admin_id']),"level_id"=>$admin_name))->select();//跟进状态
			foreach($columnsss as $k=>$v){
				$columnsss[$k]['username'] = M("Member")->where(array("id"=>$v['member_id']))->getField('username');	
			}
			
			//渠道或财务跟近
			$this->rank = M("rank")->select();
			 $data['phones'] = preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$data['phone']);
			$this->columnsss = $columnsss;
	        $data["images"] = explode(",", $data["images"]);
			$this->assign("data", $data);
			
		}
		$this->display();
	}
	
	//渠道或财务跟近
	public function getrank_admin(){
		$id = I("id");
		//查找属于渠道人员
		/* $state = M("state")->where(array("id"=>$id))->getField("state");
		if($arr["state"] == 1){ //渠道
			$name = '渠道部';
		}else if($arr["state"] == 2){//财务
			$name = '财务部';
		}else{
			$name = '业务员';
		} */
		$rank_id = M("rank")->where(array("name"=>$name))->getField("id");
		
		$datas = M("Admin")->where(array("level_id"=>$id))->select();//跟进状态
		foreach($datas as $key=>$val){
			$datas[$key]['username'] = M("Member")->where(array("id" => $val['member_id']))->getField('username');	
		}
		$this->ajaxReturn($datas);
	}
	
	//业务员跟进人审核
	public function getcheck(){
		$id = I("id");
		$sun = I("sun");
		$arr = $this->model->where(array("id" => $id))->field("admin_id,id,middle_admin_id")->find();
		$data['state'] = 1;
		if($sun == 1){ //通过审核
			$data['admin_id'] = $arr['middle_admin_id'];
		}else{ //不通过审核
			$data['middle_admin_id'] = $arr['admin_id'];
		}
		$this->model->where(array("id" => $id))->save($data);
		echo 1;exit;
	}
	
	//业务员转交渠道部审核
	public function getrank(){
		$id = I("id");
		$sun = I("sun");
		$arr = $this->model->where(array("id" => $id))->field("rank_admin_id,id,sign_rank_state,rank_id")->find();
		$data['sign_state'] = 0;
		$data['rank_state'] = 0;
		if($sun == 1){ //通过审核
		    if($arr['rank_admin_id'] != $arr['sign_rank_state']){
				$data['sign_rank_state'] = $arr['rank_admin_id'];
			}
			$this->model->where(array("id" => $id))->save($data);
			echo 1;exit;
		}else{ //不通过审核
		    
			if($arr['rank_admin_id'] != $arr['sign_rank_state']){//判断渠道部更换其他渠道部人员
			    if($arr['sign_rank_state']){
				$data['rank_admin_id'] = $arr['sign_rank_state'];
				}else{
					$data['rank_id'] = '';
					$data['rank_admin_id'] = '';	
					$data['sign_rank_state'] = '';
					$data['rank_state'] = 1;
					$data['state_id'] = 9;
				}
			}else{ //业务员转交渠道部审核没有通过
			    $data['rank_id'] = '';
			    $data['rank_admin_id'] = '';	
				$data['sign_rank_state'] = '';
				$data['rank_state'] = 1;
				$data['state_id'] = 9;
			}
			$this->model->where(array("id" => $id))->save($data);
			echo 2;exit;
		}
		
		
	}
	
	//约见审核
	public function getmet(){
		$id = I("id");
		$data['met'] = 0;
		$this->model->where(array("id" => $id))->save($data);
		echo 1;exit;
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
	
	//等于渠道或财务的
	public function channel(){
		$id = I("id");
		$cid = I("cid");
		$data = $this->model->where(array("id"=>$cid))->field("rank_id")->find();
		$arr = M("state")->where(array("id"=>$id))->field("state")->find();
		//print_r($arr);exit;
		if($arr["state"] == 1){ //渠道
		$name = iconv("GB2312","UTF-8",'渠道部');
		$datas['son'] = M("rank")->where(array("name"=>$name))->select();
		//echo M("rank")->getLastSql();exit;
		//print_r($datas['son']);exit;
		$datas['son'][0]['sun'] = 1;
		$datas['sons'] = M("Admin")->where(array("level_id"=>$datas['son'][0]['id']))->select();//跟进状态
		foreach($datas['sons'] as $key=>$val){
			$datas['sons'][$key]['username'] = M("Member")->where(array("id" => $val['member_id']))->getField('username');	
		}
		$this->ajaxReturn($datas);
			
		}else if($arr["state"] == 2){//财务
		    if($data['rank_id']=='' || $data['rank_id']==0){
				
				$datas['sonp'] = M("rank")->where(array("name"=>iconv("GB2312","UTF-8",'渠道部')))->select();
				//$datas['son'][0]['sun'] = 1;
				$datas['sonp'][0]['ps'] = 1;
				$datas['sonsp'] = M("Admin")->where(array("level_id"=>$datas['sonp'][0]['id']))->select();//跟进状态
				foreach($datas['sonsp'] as $key=>$val){
					$datas['sonsp'][$key]['username'] = M("Member")->where(array("id" => $val['member_id']))->getField('username');	
				}
			}
			$name = iconv("GB2312","UTF-8",'财务部');
			$datas['son'] = M("rank")->where(array("name"=>$name))->select();
			$datas['son'][0]['sun'] = 2;
			$datas['sons'] = M("Admin")->where(array("level_id"=>$datas['son'][0]['id']))->select();//跟进状态
			foreach($datas['sons'] as $key=>$val){
				$datas['sons'][$key]['username'] = M("Member")->where(array("id" => $val['member_id']))->getField('username');	
			}
			//print_r($datas);exit;
			$this->ajaxReturn($datas);
		}else if($arr["state"] == 0){
			$datas['son'][0]['sun'] = 3;
			//echo 3;
			$this->ajaxReturn($datas);
		}
	}
	
	public function zdtime(){
		date_default_timezone_set('PRC');
		//echo $zdtime= date("Y-m-d H:i:s",1502068811);exit;
		$time = $this->base;
		$times = $time['time']*60;
		$arrall = M("Uclient")->where(array("state_id"=>0,"zdtime"=>array('lt',time()-$times),"zd_state"=>0))->Field("admin_id,id,zdtime")->select();
		//print_r(M("Uclient")->getLastSql());exit;
		if($arrall){
			foreach($arrall as $ks=>$vs){
				$member_ids = M("admin")->where(array("id"=>$vs['admin_id']))->getField("member_id");//跟进人
			    $menadm = M("Member")->where(array("id"=>$member_ids))->Field("username,phone")->find();//跟进人
				$admin_sm = M("admin_sms")->order("sun asc,member_id asc")->find();
				$admin_sms = M("member")->where(array("id"=>$admin_sm['member_id']))->find();
				M("admin_sms")->where(array("id"=>$admin_sm['id']))->setInc('sun');
				$arra_admin = array("zdtime"=>time(),'admin_id'=>$admin_sm['admin_id'],'lxtime'=>time());
				//print_r($arra_admin);exit;
				M("Uclient")->where(array("id"=>$vs['id']))->save($arra_admin);
				//exit;
				//print_r($admin_sms);exit;
				$this->codes($admin_sms['phone']);
			}
		}else{
			print_r(2);exit;
		}
	}
	
	public function onoff(){
		$id = I("id");
		
		if($id == 1){ //可分配
		//echo session("aid");exit;
		if(!$srt=M("admin_sms")->where(array("admin_id"=>session("aid")))->find()){	
		$kun['admin_id'] = session("aid");
		$data = M("admin")->where(array("id"=>$kun['admin_id']))->find();
		$member = M("member")->where(array("id"=>$data['member_id']))->field("id,phone")->find();
		$kun['phone'] = $member['phone'];
		$kun['member_id'] = $member['id'];
		$sun = M("admin_sms")->field("sun")->order("sun desc")->find();
		$kun['sun'] = $sun['sun'];
		M("admin_sms")->add($kun);
        M("admin")->where(array("id"=>session("aid")))->save(array("type"=>0));		
		} 
		}else{
		//echo 44;exit;	
		M("admin")->where(array("id"=>session("aid")))->save(array("type"=>1));			
		M("admin_sms")->where(array("admin_id" => session("aid")))->delete();	
		}
	}

}


 ?>