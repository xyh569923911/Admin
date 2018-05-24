<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;

class UploadController extends CommonController{

	// 图片上传
	// protected function uploadImg(){
	// 	$upload = new \Think\Upload();
	// 	$upload->maxSize = 3145728;
	// 	$upload->exts = array("jpg", 'gif', 'png', 'jpeg');
	// 	$upload->rootPath = './Public/';
	// 	$upload->savePath = '/upload/images/';
	// 	$info = $upload->upload();
	// 	if(!$info){
	// 		$this->error($upload->getError());
	// 	}else{
	// 		return $info;
	// 	}
	// }

	// 图片上传
	protected function uploadImg($savePath, $exts = '', $maxSize = 5242880){
		$upload = new \Think\Upload();
		$upload->maxSize = $maxSize;
		$upload->exts = $exts;
		$upload->rootPath = './Public/';
		$upload->savePath = $savePath;
		$info = $upload->upload();
		if(!$info){
			return array("state" => 0, "msg" => $upload->getError());
		}else{
			return array("state" => 1, "info" => $info);
		}
	}

	// 图片缩略
	protected function thumbImg($file ,$width = 200, $height = 150){
		$img = new \Think\Image();
		$img->open("./Public/". $file);
		$thumbPath = dirname($file) . "/thumb_" . basename($file);
		$img->thumb($width, $height, \Think\Image::IMAGE_THUMB_FIXED)->save("./Public" . $thumbPath);
		return $thumbPath;

	}

	public function upload()
	{
		if(IS_POST)
		{
			$exts = array("jpg", 'gif', 'png', 'jpeg','doc','docx');
			$savePath = '/upload/images/';
			$result = $this->uploadImg($savePath, $exts);
			if($result["state"] == 1)
			{
				$info = $result["info"];
				$image = $info['file']['savepath'] . $info['file']['savename'];
				$this->ajaxReturn(array("state" => 1, "image" => $image));
			}else
			{
				$this->ajaxReturn(array("state" => 0, "msg" => $result["msg"]));
			}
			
		}
	}

	public function fileUpload()
	{
		if(IS_POST)
		{	
			//$model = M("Downloadfile");
			$savePath = '/upload/files/';
			$result = $this->uploadImg($savePath, '', 52428800);
			if($result["state"] == 1)
			{
				$info = $result["info"];
				$filePath = $info['file']['savepath'] . $info['file']['savename'];
				$filename = $info['file']['name'];
				//$fid = $model->add(array("url" => $filePath, "name" => $filename));
				//$this->ajaxReturn(array("state" => 1, "fid" => $fid, "filename" => $filePath));
				$this->ajaxReturn(array("state" => 1, "filePath" => $filePath, "filename" => $filePath));
			}else
			{
				$this->ajaxReturn(array("state" => 0, "msg" => $result["msg"]));
			}
		}
	}


	// public function upload()
	// {
	// 	if(IS_POST)
	// 	{
	// 		// P($_FILES);die;
	// 		$info = $this->uploadImg();
	// 		$image = $info['file']['savepath'] . $info['file']['savename'];
	// 		$this->ajaxReturn(array("image" => $image));
	// 	}
	// }
}


 ?>