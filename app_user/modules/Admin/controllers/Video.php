<?php
use Admin as Controller;
use Illuminate\Database\Capsule\Manager as DB;
use traits\PaginatorInit;
use models\Video;

class VideoController extends Controller {
    use PaginatorInit;
    public $menu = 'article';
    var $key = 0;

    function listAction() {
        $this->submenu = 'video';
        $queries = $this->queries;
        $re = DB::table('system_video')->leftjoin('system_video_area','system_video_area.id','=','system_video.area_id')->orderBy('system_video.id','desc');
        if (!empty($_GET['startTime'])) {
            $re = $re->where('system_video.addtime',' >= ', $_GET['startTime'] . " 00:00:01");
        }
        if (!empty($_GET['endTime'])) {
            $re = $re->where('system_video.addtime',' <= ', $_GET['endTime'] . " 23:59:59");
        }
        if (!empty($_GET['title'])) {
            $re = $re->where('system_video.title',$_GET['title']);
        }
        if (!empty($_GET['area'])) {
           $re = $re->where('system_video.area_id',$_GET['area']);
        }
        $fields = $re->select('system_video.id','title','area_id','link','cover','addtime','status','name','lookStatus')->paginate(15);
        $re = DB::table('system_video_area')->get();
        $fields->appends($queries->all());
        $this->display('list',['list' => $fields, 'queries'=>$queries, 'area'=>$re]);
    }

    function addAction() {
        $this->submenu = 'video';
        $re = DB::table('system_video_area')->orderBy('id','asc');
        $fields = $re->select('*')->paginate(15);
        $Video = new Video();
        $this->display('add',['areas' => $fields ,'list' => $Video]);
    }

    function saveAction() {
        $queries = $this->getAllPost();
        $id = isset($queries['id']) ? trim($queries['id']) : '';    
        $title = isset($queries['title']) ? trim($queries['title']) : '';
        $link = isset($queries['link']) ? trim($queries['link']) : '';
        $area = isset($queries['area']) ? intval($queries['area']) : 0;
        $status = isset($queries['status']) ? intval($queries['status']) : 0;
        $file = isset($queries['cover']) ? trim($queries['cover']) : '';
        $time = isset($queries['addtime'])?trim($queries['addtime']):date("Y-m-d");
        if ($status == 1) {
            $status = 0;
        } else {
            $status = 1;
        }

        if ($title == '') {
            Flash::success('标题不能为空');
            $this->redirect('/admin/video/list');
        }
        if ($area == '') {
            Flash::success('请选择地区');
            $this->redirect('/admin/video/list');
        }
        if ($file == '') {
            Flash::success('上传图片失败');
            $this->redirect('/admin/video/list');
        }
        $data = array();
        $data['title'] = $title;
        $data['link'] = $link;
        $data['status'] = $status;
        $data['cover'] = $file;
        $data['area_id'] = $area;
        $data['addtime'] = $time;

        if(!empty($id)){
            $re = DB::table('system_video')->where('id',$id)->update($data);
        }else{
            $re = DB::table('system_video')->insert($data);
        }
        if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/video/list');
        } else {
            Flash::error('未知错误');
            $this->redirect('/admin/video/list');
        }
    }

    function editAction() {
        if (is_numeric($_GET['id']) AND ! empty($_GET['id'])) {
            $id = $_GET['id'];
            $row = DB::table('system_video')->where('id','=',$id)->first();
            $re = DB::table('system_video_area')->get();
            $this->display('add',['list'=>$row,'areas'=>$re]);
        } else {
            Flash::error('未知错误');
            $this->redirect('/admin/video/list');
        }
    }

    function delAction() {
        $queries = $this->getAllPost();
        if (is_numeric($queries['id'])) {
            $db = DB::table('system_video')->where('id','=',$queries['id'])->delete();
            $rdata = [];
            if($db) {
                $rdata['status'] = 1;
                $rdata['info'] = '删除成功！';
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '删除失败！';
                $this->backJson($rdata);
            }
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '参数错误';
            $this->backJson($rdata);
        }
    }


    function areaListAction() {
        $this->submenu = 'arealist';
		$re = DB::table('system_video_area')->orderBy('id','asc');
        if (!empty($_GET['name'])) {
            $re = $re->where('system_video_area.name','=',$_GET['name']);
        }
        $fields = $re->paginate(15);
        $this->display('area',['list' => $fields]);
    }

    function addAreaAction() {
        $this->submenu = 'arealist';
        $area = new Video; 
        $this->display('addarea',['area' => $area]);
    }

    function saveAreaAction() {
        $post = $this->getAllPost();
        $name = isset($post['name']) ? trim($post['name']) : '';
        $lookStatus = (isset($post["lookStatus"]) && is_numeric($post["lookStatus"])) ? trim($post["lookStatus"]) : '1';
        if ($name == '') {
            return back()->with('mes','标题不能为空');
        }
        $data = array();
        $data['name'] = $name;
        $data["lookStatus"] = $lookStatus;
        if(!empty($post['id'])){
            $re = DB::table('system_video_area')->where('id',$post['id'])->update($data);
        }else{
            $re = DB::table('system_video_area')->insert($data);
        }
        if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/video/arealist');
        } else {
            Flash::error('未知错误');
            $this->redirect('/admin/video/arealist');
        }
    }

    function editAreaAction() {
        $get = $this->getAllQuery();
        if (is_numeric($get['id']) AND ! empty($get['id'])) {
            $id = $get['id'];
            $row = DB::table('system_video_area')->where('id','=',$id)->first();
            $this->display('addarea',['area' => $row]);
        } else {
            Flash::error('未知错误');
            $this->redirect('/admin/video/arealist');
        }
    }


    function delAreaAction() {
        $queries = $this->getAllPost();
        if (is_numeric($queries['id'])) {
            $db = DB::table('system_video_area')->where('id','=',$queries['id'])->delete();
            $rdata = [];
            if($db) {
                $rdata['status'] = 1;
                $rdata['info'] = '删除成功！';
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '删除失败！';
                $this->backJson($rdata);
            }
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '参数错误';
            $this->backJson($rdata);
        }
    }

    function getAreaList() {
		$re = DB::table('system_video_area')->orderBy('id','asc');
        $fields = &$re;
        $re = $this->perpage($re, $fields, 20);
        foreach($re as $key=>$row) {
			foreach($row as $k => $v)
            $types[$row->id][$k] = $v;
        } 
        return $types;
    }

/*    function uploadImage($file) {
        $this->publicCheckLogin();
        if (!isset($_FILES[$file])) {
            return '';
        }
        if ((($_FILES[$file]["type"] == "image/gif") || ($_FILES[$file]["type"] == "image/jpeg") || ($_FILES[$file]["type"] == "image/pjpeg") || ($_FILES[$file]["type"] == "image/png")) && ($_FILES[$file]["size"] < 500000)) {
            if ($_FILES[$file]["error"] > 0) {
                return '';
            } else {
                $suffix = substr(strrchr($_FILES[$file]["name"], '.'), 1);
                $fileName = time() . rand(10000, 99999) . '.' . $suffix;
                if (move_uploaded_file($_FILES[$file]["tmp_name"], "data/upload/" . $fileName)) {
                    return $fileName;
                } else {
                    return '';
                }
            }
        } else {
            '';
        }
    }*/

}

?>