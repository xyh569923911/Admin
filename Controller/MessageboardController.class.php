<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;

class MessageboardController extends CommonController{


	protected $model;

	public function _initialize()
	{
    	parent::_initialize();
		$this->model = M("Messageboard");
		$this->isMessageboard = true;
		$this->aid = session("aid");
		
	}
	public function index(){
		$levels = $this->levels;
		if(in_array("63",$levels)){
		$key=I('get.key');//;搜索内容
		if($key){
			//分页
			$map['username|phone'] =array('like',"%$key%");
			$count = $this->model->where($map)->count();// 查询满足要求的总记录数
			$Page = new \Think\Page($count,200);// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();// 分页显示输出
			$ch_data = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id asc')->select();
			
		}else{
			//分页
			$count = $this->model->count();// 查询满足要求的总记录数
			$Page = new \Think\Page($count,200);// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();// 分页显示输出
			$ch_data = $this->model->limit($Page->firstRow.','.$Page->listRows)->order('id asc')->select();
		}
		if($_GET['exc'] == 1){
			$ch_data = $this->model->select();
		}
		}else if(in_array("65",$levels)){
		  $admin = M("admin")->where(array("id"=>session("aid")))->find();
		  $level = M("rank")->where(array("id"=>$admin['level_id']))->find();
		  if($cids = M("rank")->where(array("pid" => $level['id']))->getField("id", true)){
			  $cids[] = $level["id"];
			  $cids = implode(",", $cids);
			  $all_id = M("admin")->where(array('level_id'=>array("in",$cids)))->getField('member_id', true);
		      $all_id = implode(",", $all_id);
		  }else{
			 $all_id = $admin['member_id'];
		  }
		  if($all_id){
		  $where = array("admin_id"=>array("in",$all_id));
		  }else{
		  $where = array("admin_id"=>array("in",0));	  
		  }
		  //分页
		  $count = $this->model->where($where)->count();// 查询满足要求的总记录数
		  $Page = new \Think\Page($count,200);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		  $show = $Page->show();// 分页显示输出
		  $ch_data = $this->model->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('id asc')->select();
		  //print_r($all_id);exit;
		}
		 if($_GET['exc'] == 1){
			 if($ch_data){
				$this->messageboard_excel($ch_data);exit;//导出 
			 }else{
				echo "<script>alert('没有资料,无法下载！');history.go(-1)</script>";
			 }
			 
		 }
		
		$this->assign('show',$show);
		$this->assign('ch_data',$ch_data);
		$this->display();
	}

	// 添加
		public function addindex(){

		if(IS_POST)
		{	
			 $filename=i('post.filename');
            // print_r($filename);exit;
             vendor("PHPExcel.PHPExcel");
           	 $file_name="./Public".$filename;

			$PHPExcel = new \PHPExcel();// 实例化PHPExcel工具类
			//分析文件获取后缀判断是2007版本还是2003
			$extend = pathinfo($file_name);
			$extend = strtolower($extend["extension"]); 
			 
			// 判断xlsx版本，如果是xlsx的就是2007版本的，否则就是2003
			 if ($extend=="xlsx") {
				 //echo 1;exit;
				$objReader = \PHPExcel_IOFactory::createReader('Excel2007');
			}else{
				$objReader = \PHPExcel_IOFactory::createReader('Excel5');
			} 
            
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            $this->model=M('Messageboard');
            for($i=1;$i<=$highestRow;$i++)
            {   
                $data['username'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();//姓名
                $data['phone'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();//手机
                $data['price'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();//金额
                $data['content'] = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();//备注
				
                //$data['addtime'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();//时间
                //$data['f'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
              	$this->model->add($data);   	
            } 
            unlink( $file_name);
           	echo 1;
		}else{
			$this->display();
		}

	}

  //修改
  public function editindexs(){
	  $id = I("id");
	  $data = I("post.");
	  $data["content"] = $_POST["content"];
	  $iid = I("post.iid", 0, "intval");
	 
	if(IS_POST){
		 //echo $iid;exit;
        if($arr=$this->model->find($iid))
		{  
			
			$this->model->where(array("id" => $iid))->save($data);
			echo 1;
		}
		
	  }else{
	  if($data = $this->model->find($id))
	  {
		 $this->data = $data; 
	  }	  
	  $this->display(); 
	 }
  }
  
  
   //messageboard_excel
    public function messageboard_excel($xlsData){
        ini_set("memory_limit", "512M");
        if($xlsData){
         
        $post = I('get.'); 
        $xlsName  = "留言列表";
        $xlsCell  = array(
        array('id','序列'),
        array('username','姓名'),
        array('phone','手机'),
        array('price','金额（万元）'),
        array('content','备注'),
        array('admin_id','分配人'),
        array('addtime','分配时间'),

        );
        if(!$xlsData){ echo "<script>alert('没有资料,无法下载！');history.go(-1)</script>";}
        $this->member=M('member');
        foreach($xlsData as $key=>$val){
            $xlsData[$key]['id'] =  $key;
            $xlsData[$key]['username'] =  $val['username'];
            $xlsData[$key]['phone'] =  $val['phone'];
            $xlsData[$key]['price'] =  $val['price'];
            $xlsData[$key]['content'] =  $val['content'];
            $xlsData[$key]['addtime'] =  date("Y-m-d H:i",$val['addtime']);
            if ($val['admin_id']) {
                 $xlsData[$key]['admin_id'] =  $this->member->where('id='.$val['admin_id'])->getField('username');
            }else{
                 $xlsData[$key]['admin_id']='';
            }
        }
		/* $count=count($xlsData);
        $xlsData[$key+1]['id'] = '总计:'.$count;  */
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
        }
    }
    

    public function exportExcel($expTitle,$expCellName,$expTableData){

        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $xlsTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);//合并单元格
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }  
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   
    }

}


 ?>