<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;

class DownloadController extends CommonController{


	protected $model;
	public function _initialize()
	{
		parent::_initialize();
		$this->model = M("Download");
		$this->isNavs = 'Download';
	}
	public function index()
	{   $this->level = M("admin")->where(array("id"=>session("aid")))->getField("dow");
		$this->ch_data = $this->model->alias("m")->join("__COLUMNS__ as c ON m.cid=c.id")->where(array("m.lang" => 0))->order("c.name asc, m.sort")->field("m.id, m.name, c.name as cname, m.sort, m.filename,m.addtime")->select();
		$this->en_data = $this->model->alias("m")->join("__COLUMNS__ as c ON m.cid=c.id")->where(array("m.lang" => 1))->order("c.name asc, m.sort")->field("m.id, m.name, c.name as cname, m.sort, m.filename")->select();
		$this->display();
	}

	public function getLang()
	{
		$lang = I("get.lang", 0, "intval");
		$data = M("Columns")->where(array("lang" => $lang, "type" => "download"))->order("sort asc")->select();
		$columns = Data::tree($data, "name", "id", "pid");
		$this->ajaxReturn($columns);
	}


	public function addindex()
	{
		if(IS_POST)
		{
			$data = I("post.");
			$data["url"] = I("post.url", "");
			$data["filename"] = I("post.filename", "");
			$data["image"] = I("post.image", "");
			$iid = I("post.iid", 0, "intval");
			if($this->model->find($iid))
			{
				$this->model->where(array("id" => $iid))->save($data);
				echo 1;
			}else
			{   $data["addtime"] = time();
				$this->model->add($data);
				echo 1;
			}
		}else
		{
			$id = I("get.id", 0, "intval");
			if($data = $this->model->find($id))
			{
				$this->assign("data", $data);
			}
			$lang = isset($data['lang']) ? $data['lang'] : 0;
		//	print_r($lang);exit;
			$columns = $this->getColumn("Download", $lang);
			//print_r($columns);exit;
			$this->assign("columns", $columns);
			$this->display();
		}
	}

}


 ?>