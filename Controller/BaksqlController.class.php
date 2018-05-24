<?php 
namespace Admin\Controller;
use Admin\Controller\CommonController;
use \Org\Util\Data;
use Think\Db\Driver;

class BaksqlController extends CommonController{
    
	public function _initialize()
	{
		parent::_initialize();
		$this->isNavs = 'Baksql';
		
	}
	
    public function index() {
		$levels = $this->levels;
		if(!in_array('1007',$levels)){
			echo "<script>history.go(-1)</script>";
		}
		/* $a = filemtime('databak/daxxt_20170712115200_359728081.sql');
		$time = date("Y-m-d H:i:s", $a);
		echo "文件最后访问的时间是".date("Y-m-d H:i:s",$a)."<br/>";   
        echo "文件最后改变的时间是".date("Y-m-d H:i:s",$a)."<br/>";   
        echo "文件最后修改的时间是".date("Y-m-d H:i:s",$a)."<br/>";   
		echo $time;exit; */
        $DataDir = "databak/";
        mkdir($DataDir);
	
        if (!empty($_GET['action'])) {
			
            //import("Common.Org.MySQLReback");
            $config = array(
                'host' => C('DB_HOST'),
                'port' => C('DB_PORT'),
                'userName' => C('DB_USER'),
                'userPassword' => C('DB_PWD'),
                'dbprefix' => C('DB_PREFIX'),
                'charset' => 'UTF8',
                'path' => $DataDir,
                'isCompress' => 0, //是否开启gzip压缩
                'isDownload' => 0
            );
			//	print_r($config);exit;
			$mr= new MySQLReback($config);
			//print_r($mr);exit;
            //$cate=$Category::unLimitedForLevel($cate);
           // $mr = new \MySQLReback($config);
            $mr->setDBName(C('DB_NAME'));
            if ($_GET['action'] == 'backup') {
				//print_r($mr);exit;
                $mr->backup();
                echo "<script>document.location.href='" . "/Admin/Baksql/index" . "'</script>";
//                $this->success( '数据库备份成功！');
            } elseif ($_GET['action'] == 'RL') {
                $mr->recover($_GET['file']);
                echo "<script>document.location.href='" . "/Admin/Baksql/index" . "'</script>";
//                $this->success( '数据库还原成功！');
            } elseif ($_GET['action'] == 'Del') {
				//print_r($DataDir . $_GET['file']);exit;
                if (@unlink($DataDir . $_GET['file'])) {
                    // $this->success('删除成功！');
                    echo "<script>document.location.href='" . "/Admin/Baksql/index" . "'</script>";
                } else {
                    $this->error('删除失败！');
                }
            }
            if ($_GET['action'] == 'download') {
            //  echo 1;exit;
                function DownloadFile($fileName) {
					//print_r($fileName);exit;
                    ob_end_clean();
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Length: ' . filesize($fileName));
                    header('Content-Disposition: attachment; filename=' . basename($fileName));
                    readfile($fileName);
                }
                DownloadFile($DataDir . $_GET['file']);
                exit();
            }
        }
        $lists = $this->MyScandir('databak/');
        $this->assign("datadir",$DataDir);
		//print_r($this->getfiletime('daxxt_20170712115200_359728081.sql','/databak/'));exit;
        $this->assign("lists", $lists);
        $this->display();
    }

    private function MyScandir($FilePath = './', $Order = 0) {
        $FilePath = opendir($FilePath);
        while (false !== ($filename = readdir($FilePath))) {
            $FileAndFolderAyy[] = $filename;
        }
        $Order == 0 ? sort($FileAndFolderAyy) : rsort($FileAndFolderAyy);
        return $FileAndFolderAyy;
    }
	

  
}
