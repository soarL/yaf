<?php
// This controller use illuminate/database.
use models\Video;
use models\VideoArea;
use traits\PaginatorInit;
use tools\Queries;

/**
 * VideoController
 * 视频控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class VideoController extends Controller {
    use PaginatorInit;

    public $menu = 'video';
    public $submenu = 'video';

    public function listAction() {
        $queries = $this->queries->defaults(['area'=>0]);

        $areas = VideoArea::where('lookStatus', 1)->get();
        $area = null;
        if($queries->area) {
            $area = VideoArea::where('lookStatus', 1)->where('id', $queries->area)->first();
        } else if(count($areas)>0) {
            $area = $areas[0];
            $queries->area = $area->id;
        }

        $videos = [];
        if($area) {
            $videos = Video::where('status', 1)->where('area_id', $area->id)->orderBy('addtime', 'desc')->paginate(9);
            $videos->appends($queries->all());
        }

        $this->display('list', ['videos'=>$videos, 'areas'=>$areas, 'queries'=>$queries]);
    }
	
}