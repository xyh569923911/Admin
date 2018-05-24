<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class ColumnController extends CommonController{

	protected $model;

	public function _initialize()
	{
		parent::_initialize();
		//$this->isColumn = true;
		$this->isNavs = 'Download';
		$this->model = M("Columns");
	}

	public function index()
	{
		$ch_datas = $this->model->where(array("lang" => 0))->order("sort asc")->select();
		$this->ch_data = Data::tree($ch_datas, "name", "id", "pid");


		$en_datas = $this->model->where(array("lang" => 1))->order("sort asc")->select();
		$this->en_data = Data::tree($en_datas, "name", "id", "pid");

		$this->display();
	}

	public function getLang()
	{
		$lang = I("get.lang", 0, "intval");
		$data = $this->model->where(array("lang" => $lang))->order("sort asc")->select();
		$columns = Data::tree($data, "name", "id", "pid");
		$this->ajaxReturn($columns);
	}

	// 添加栏目
	public function addindex()
	{
		if(IS_POST)
		{
			$iid = I("post.iid", 0, "intval");
			$data = I("post.");
			$data["content"] = $_POST["content"];
            $data["images"] = implode(",", $data["images"]);
			if($arr=$this->model->find($iid))
			{   
		        //调用删除图片文件
		        $this->DelImage($arr,$data);
				
				$this->model->where(array("id" => $iid))->save($data);
			}else
			{   
		        $data['addtime'] = time();
				$this->model->add($data);
			}
			echo 1;die;
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
				// 编辑
				$data["images"] = explode(",", $data["images"]);
				$columns = $this->model->where(array("lang" => $data["lang"]))->order("sort asc")->select();
				$this->columns = $this->get_choose($this->model, $columns, $id, $data["lang"]);
				$this->assign("data", $data);

			}else
			{
				// 添加
				// 默认获取中文栏目
				$columns = $this->model->where(array("lang" => 0))->order("sort asc")->select();
				$this->columns = Data::tree($columns, "name", "id", "pid");
			}
			
			//获取模板文件
			$this->html = $this->getFiles();

			$this->display();
		}

	}

	// 获取模板文件
	protected function getFiles()
	{
		$temp_path = APP_PATH . "Home/View/pc/";
		$files = array_map("basename", glob($temp_path."*.html"));
		return $files;
	}

	

	// 修改排序
	public function editSort()
	{
		if(IS_POST)
		{
			$id = I("post.id", 0, "intval");
			$newSort = I("post.newSort", 0, "intval");
			$this->model->save(array("id" => $id, "sort" => $newSort));
			echo 1;die;
		}
	}

	// 删除
	public function delById()
	{
		$id = I("post.id");
		if($m = ucfirst(I("post.m", "")))
		{
			$model = M("$m");

			if($model->find($id))
			{
				$model->where(array("id" => $id))->delete();
				echo 1;
				exit;
			}
		}
	}


}



 ?>