<?php
use Admin as Controller;
use Illuminate\Database\Capsule\Manager as DB;
use traits\PaginatorInit;
use models\Banner;
use helpers\NetworkHelper;

class BannerController extends Controller{
    use PaginatorInit;
    public $menu = 'article';
    public $key = 0;
    function listAction() {
        $this->submenu = 'banner';
        $queries = $this->queries;
        $re = DB::table("system_banner")->orderBy('system_banner.id','desc');
        if (!empty($queries->startTime) AND ! empty($queries->endTime)) {
            $re = $re->where('system_banner.addtime','>=',$queries->startTime . '00:00:01')->where('system_banner.addtime','<=',$queries->endTime." 23:59:59");
        }
        if (!empty($queries->title)) {
            $re = $re->where('system_banner.title','=','%'. $queries->title .'%');
        }
        if (!empty($queries->type)) {
            $re = $re->where('system_banner.type_id','=',$queries->type);
        }
        $types = $this->getTypeList();
        $fields = $re->select('system_banner.*')->paginate(15);
        $fields->appends($queries->all());
        $this->display('list',['list' => $fields, 'types' => $types, 'queries'=>$queries]);
    }

    function addAction() {
        $this->submenu = 'banner';
        $types = $this->getTypeList();
        $banner = new Banner();
        $this->display('add',['types' => $types , 'list' => $banner]);
    }

    function doAddAction() {
        $queries = $this->getAllPost(false);
        $id = $this->getPost('id', '');
        $title = $this->getPost('title', '');
        $link = $this->getPost('link', '',false);
        $type = $this->getPost('type', '');
        $status = $this->getPost('status', '');
        $url = $this->getPost('imageUrl','');
        if(strstr($url,WEB_ASSET)){
            $imageUrl = $url;
        }else{
            $imageUrl = WEB_ASSET.'/uploads/images/'.$url;
        }
        $banner_order = isset($queries['banner_order']) ? trim($queries['banner_order']) : '';
        if ($title == '') {
            Flash::success('标题不能为空');
            $this->redirect('/admin/banner/list');
        }
        if ($type == '') {
            Flash::success('请选择类型');
            $this->redirect('/admin/banner/list');
        }

        $data = array();
        $data['title'] = $title;
        $data['link'] = $link;
        $data['status'] = $status;
        $data['banner'] = $imageUrl;
        $data['type_id'] = $type;
        $data['addtime'] = date('Y-m-d H:i:s', time());
        $data['banner_order'] = $banner_order;
        if(!empty($id)){
            $re = DB::table('system_banner')->where('id',$id)->update($data);
        }else{
            $re = DB::table('system_banner')->insert($data);
        }
        if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/banner/list');
        } else {
            Flash::error('未知错误');
            $this->redirect('/admin/banner/list');
        }
    }

    function editAction() {
        $this->submenu = 'banner';
        $queries =  $this->getAllQuery();
        if (is_numeric($queries['id']) AND ! empty($queries['id'])) {
            $row = DB::table('system_banner')->where('id','=',$queries['id'])->first();
            $types = $this->getTypeList();
            $this->display('add',['types' => $types,'list' => $row]);
        } else {
            Flash::error('未知错误');
            $this->redirect('/admin/banner/list');
        }
    }

    function delAction() {
        $queries = $this->getAllPost();
        if (is_numeric($queries['id'])) {
            $db = DB::table('system_banner')->where('id','=',$queries['id'])->delete();
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


    function typesAction() {
        $this->submenu = 'banner-type';
        $queries = $this->getAllQuery();
        $queries['name'] = '';
        $re = DB::table('system_banner_type')->orderBy('id','asc');
        if(!empty($queries['id'])){
            $name = $re->where('id',$queries['id'])->first();
            $queries['name'] = $name->name;
        }
        $fields = $re->paginate(15);
        $this->display('types',['list' => $fields , 'queries' => $queries]);  
    }


    function typeAddAction() {
        $queries = $this->getAllPost();
        $name = isset($queries['name']) ? trim($queries['name']) : '';
        if ($name == '') {
            Flash::error('标题不能为空');
            $this->redirect('/admin/banner/types');
        }
        $data = array();
        $data['name'] = $name;
        if(empty($queries['id'])){
            $re = DB::table('system_banner_type')->insert($data);
        }else{
            $re = DB::table('system_banner_type')->where('id',$queries['id'])->update($data);
        }
        if ($re){
            Flash::success('操作成功');
            $this->redirect('/admin/banner/types');
        }
        else{
            Flash::error('操作失败');
            $this->redirect('/admin/banner/types');
        }
    }

    function TypeEdit() {
        if (is_numeric($queries['id']) AND ! empty($queries['id'])) {
            $id = $queries['id'];
            $row = DB::table('system_banner_type')->where('id','=',$id)->first();
            return view('banner/typeedit')->with('bannerType',$row);
        } else {
            return back()->with('mes','参数错误');
        }
    }

    function doTypeEdit() {
        $name = isset($queries['name']) ? trim($queries['name']) : '';

        if ($name == '') {
            return back()->with('mes','标题不能为空');
        }

        $data = array();
        $data['name'] = $name;

        if (is_numeric($queries['id']) AND ! empty($queries['id'])) {
            $re = DB::table('system_banner_type')->where('id','=',$queries['id'])->update($data);
            if ($re)
                return back()->with('mes','修改成功');
            else
                return back()->with('mes','修改失败');
        }else {
            return back()->with('mes','参数错误');
        }
    }

    function deleteTypeAction() {
        $queries = $this->getAllPost();
        if (is_numeric($queries['id'])) {
            $db = DB::table('system_banner_type')->where('id','=',$queries['id'])->delete();
            $rdata = array();
            if($db){
                DB::table('system_banner')->where('type_id','=',$queries['id'])->delete();
                $rdata['status'] = 1;
                $rdata['info'] = '删除成功';
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '删除失败';
                $this->backJson($rdata);
            }
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '参数有误';
            $this->backJson($rdata);
        }
    }

    function getTypeList() {
        $types = array();
        $types = DB::table('system_banner_type')->get();
         foreach($types as $key => $val){
             $type[$val->id] = (array)$val;
         } 
        return $type;
    }

    function uploadImage($file) {
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
    }

}
?>
