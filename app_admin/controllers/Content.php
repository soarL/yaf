<?php
use Admin as Controller;
use models\History;
use models\Filiale;
use models\Activity;
use models\Job;
use models\Department;
use models\Staff;
use models\UserVip;
use forms\admin\HistoryForm;
use helpers\StringHelper;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * ContentController
 * 内容管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ContentController extends Controller {
    use PaginatorInit;

    public $menu = 'article';

    /**
     * 公司历程
     * @return mixed
     */
    public function historiesAction() {
        $this->submenu = 'history';
        $queries = $this->queries->defaults(['name'=>'', 'beginTime'=>'', 'endTime'=>'']);
        $name = $queries->name;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $builder = History::whereRaw('1=1');

        if($name!='') {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        if($beginTime!='') {
            $builder->where('happened_at', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('happened_at', '<=', $endTime);
        }

        $histories = $builder->orderBy('happened_at', 'desc')->paginate(15);
        $histories->appends($queries->all());

        $this->display('histories', ['histories'=>$histories, 'queries'=>$queries]);
    }

    /**
     * 添加历程
     * @return mixed
     */
    public function addHistoryAction() {
        $this->submenu = 'history';
        $history = new History();
        $this->display('historyForm', ['history'=>$history]);
    }

    /**
     * 修改历程
     * @return mixed
     */
    public function updateHistoryAction() {
        $this->submenu = 'history';
        $id = $this->getQuery('id');
        $history = History::find($id);
        if(!$history) {
            Flash::error('历程不存在！');
            $this->redirect('/admin/content/histories');
        }
        $this->display('historyForm', ['history'=>$history]);
    }

    /**
     * 保存历程
     * @return mixed
     */
    public function saveHistoryAction() {
        $params = $this->getAllPost();
        $form = new HistoryForm($params);
        if($form->save()) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/histories');
        } else {
            Flash::error($form->posError());
            $this->goBack();
        }
    }

    /**
     * 删除历程
     * @return mixed
     */
    public function deleteHistoryAction() {
        $id = $this->getPost('id');
        $history = History::find($id);
        $status = false;
        if($history) {
            $status = $history->delete();
        }
        $rdata = [];
        if($status) {
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 公司网点
     * @return mixed
     */
    public function filialesAction() {
        $this->submenu = 'filiale';
        $filiales = Filiale::get();
        $this->display('filiales', ['filiales'=>$filiales]);
    }

    /**
     * 添加公司网点
     * @return mixed
     */
    public function addFilialeAction() {
        $this->submenu = 'filiale';
        $filiale = new Filiale();
        $filiale->phone = '400-186-9996';
        $filiale->email = 'email@hcjrfw.com';
        $filiale->qq = '319554771';
        $filiale->status = 1;
        $this->display('filialeForm', ['filiale'=>$filiale]);
    }

    /**
     * 修改公司网点
     * @return mixed
     */
    public function updateFilialeAction() {
        $this->submenu = 'filiale';
        $id = $this->getQuery('id');
        $filiale = Filiale::find($id);
        if(!$filiale) {
            Flash::error('网点不存在！');
            $this->redirect('/admin/content/filiales');
        }
        $this->display('filialeForm', ['filiale'=>$filiale]);
    }

    /**
     * 保存公司网点
     * @return mixed
     */
    public function saveFilialeAction() {
        $params = $this->getAllPost(true);
        $filiale = null;
        if($params['id']) {
            $filiale = Filiale::find($params['id']);
        } else {
            $filiale = new Filiale();
        }

        $data = [];
        $data['name'] = $params['name'];
        $data['status'] = $params['status'];
        $data['link'] = $params['link'];
        $data['address'] = $params['address'];
        $data['phone'] = $params['phone'];
        $data['email'] = $params['email'];
        $data['qq'] = $params['qq'];
        $data['type'] = $params['type'];
        $data['postcode'] = $params['postcode'];
        $data['photos'] = $params['photos'];

        foreach ($data as $key => $value) {
            $filiale->$key = $value;
        }
        $status = $filiale->save();
        
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/filiales');
        } else {
            Flash::error('操作失败！');
            $this->goBack();
        }
    }

    /**
     * 删除公司网点
     * @return mixed
     */
    public function deleteFilialeAction() {
        $id = $this->getPost('id');
        $filiale = Filiale::find($id);
        $status = false;
        if($filiale) {
            $status = $filiale->delete();
        }
        $rdata = [];
        if($status) {
            $images = StringHelper::decodeImages($filiale->photos, 'all');
            foreach ($images as $image) {
                $max = $image['max'];
                if(file_exists($max)) {
                    unlink($max);
                }
                $min = $image['min'];
                if(file_exists($min)) {
                    unlink($min);
                }
            }
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 公司活动
     * @return mixed
     */
    public function activitiesAction() {
        $this->submenu = 'activity';

        $queries = $this->queries->defaults(['title'=>'', 'beginTime'=>'', 'endTime'=>'', 'type'=>'all']);
        $title = $queries->title;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;
        $type = $queries->type;

        $builder = Activity::whereRaw('1=1');

        if($title!='') {
            $builder->where('title', 'like', '%'.$title.'%');
        }

        if($beginTime!='') {
            $builder->where('addtime', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('addtime', '<=', $endTime);
        }

        if($type!='all') {
            $builder->where('type', '<=', $type);
        }

        $activities = $builder->orderBy('addtime', 'desc')->paginate(15);
        $activities->appends($queries->all());
        $this->display('activities', ['activities'=>$activities, 'queries'=>$queries]);
    }

    /**
     * 添加公司活动
     * @return mixed
     */
    public function addActivityAction() {
        $this->submenu = 'activity';
        $activity = new Activity();
        $this->display('activityForm', ['activity'=>$activity]);
    }

    /**
     * 更新公司活动
     * @return mixed
     */
    public function updateActivityAction() {
        $this->submenu = 'activity';
        $id = $this->getQuery('id');
        $activity = Activity::find($id);
        if(!$activity) {
            Flash::error('活动不存在！');
            $this->redirect('/admin/content/activities');
        }

        $this->display('activityForm', ['activity'=>$activity]);
    }

    /**
     * 保存公司活动
     * @return mixed
     */
    public function saveActivityAction() {
        $params = $this->getAllPost(true);
        $activity = null;
        if($params['id']) {
            $activity = Activity::find($params['id']);
        } else {
            $activity = new Activity();
        }

        $startDate = null;
        $endDate = null;
        if($params['startDate']!='') {
            $startDate = $params['startDate'];
        }
        if($params['endDate']!='') {
            $endDate = $params['endDate'];
        }

        $data = [];
        $data['title'] = $params['title'];
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['content'] = $params['content'];
        $data['imageUrl'] = $params['imageUrl'];
        $data['linkUrl'] = $params['linkUrl'];
        $data['type'] = $params['type'];
        $data['lookStatus'] = $params['lookStatus'];
        $data['photos'] = $params['photos'];

        $status = false;
        if(!$params['id']) {
            $data['addtime'] = date('Y-m-d H:i:s');
        }
        foreach ($data as $key => $value) {
            $activity->$key = $value;
        }
        $status = $activity->save();
        
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/activities');
        } else {
            Flash::error('操作失败！');
            $this->goBack();
        }
    }

    /**
     * 删除公司活动
     * @return mixed
     */
    public function deleteActivityAction() {
        $id = $this->getPost('id');
        $activity = Activity::find($id);
        $status = false;
        if($activity) {
            $status = $activity->delete();
        }
        $rdata = [];
        if($status) {
            $images = StringHelper::decodeImages($activity['photos'], 'all');
            foreach ($images as $image) {
                $max = $image['max'];
                if(file_exists($max)) {
                    unlink($max);
                }
                $min = $image['min'];
                if(file_exists($min)) {
                    unlink($min);
                }
            }
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 公司招聘
     * @return mixed
     */
    public function jobsAction() {
        $this->submenu = 'job';
        $queries = $this->queries->defaults(['name'=>'', 'beginTime'=>'', 'endTime'=>'']);
        $name = $queries->name;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $builder = Job::whereRaw('1=1');

        if($name!='') {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        if($beginTime!='') {
            $builder->where('created_at', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('created_at', '<=', $endTime);
        }

        $jobs = $builder->with('department')->orderBy('created_at', 'desc')->paginate(15);
        $jobs->appends($queries->all());

        $this->display('jobs', ['jobs'=>$jobs, 'queries'=>$queries]);
    }

    /**
     * 添加职位
     * @return mixed
     */
    public function addJobAction() {
        $this->submenu = 'job';
        $job = new Job();
        $departments = Department::all();
        $this->display('jobForm', ['job'=>$job, 'departments'=>$departments]);
    }

    /**
     * 更新职位
     * @return mixed
     */
    public function updateJobAction() {
        $this->submenu = 'job';
        $id = $this->getQuery('id');
        $job = Job::find($id);
        if(!$job) {
            Flash::error('职位不存在！');
            $this->redirect('/admin/content/jobs');
        }
        $departments = Department::all();
        $this->display('jobForm', ['job'=>$job, 'departments'=>$departments]);
    }

    /**
     * 保存职位
     * @return mixed
     */
    public function saveJobAction() {
        $params = $this->getAllPost(true);
        $job = null;
        if($params['id']) {
            $job = Job::find($params['id']);
        } else {
            $job = new Job();
        }
        $data = [];
        $data['name'] = $params['name'];
        $data['dp_id'] = $params['dp_id'];
        $data['experience'] = $params['experience'];
        $data['education'] = $params['education'];
        $data['work_time'] = $params['work_time'];
        $data['address'] = $params['address'];
        $data['salary'] = $params['salary'];
        $data['duty'] = $params['duty'];
        $data['requirement'] = $params['requirement'];
        $data['welfare'] = $params['welfare'];
        $data['status'] = $params['status'];

        $status = false;
        foreach ($data as $key => $value) {
            $job->$key = $value;
        }
        $status = $job->save();
        
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/jobs');
        } else {
            Flash::error('操作失败！');
            $this->goBack();
        }
    }

    /**
     * 删除招聘职位
     * @return mixed
     */
    public function deleteJobAction() {
        $id = $this->getPost('id');
        $job = Job::find($id);
        $status = false;
        if($job) {
            $status = $job->delete();
        }
        $rdata = [];
        if($status) {
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 公司部门
     * @return mixed
     */
    public function departmentsAction() {
        $this->submenu = 'department';
        $queries = $this->queries->defaults(['name'=>'']);
        $name = $queries->name;

        $builder = Department::whereRaw('1=1');

        if($name!='') {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        $departments = $builder->paginate(15);
        $departments->appends($queries->all());

        $this->display('departments', ['departments'=>$departments, 'queries'=>$queries]);
    }

    /**
     * 添加部门
     * @return mixed
     */
    public function addDepartmentAction() {
        $this->submenu = 'department';
        $department = new Department();
        $this->display('departmentForm', ['department'=>$department]);
    }

    /**
     * 更新部门
     * @return mixed
     */
    public function updateDepartmentAction() {
        $this->submenu = 'department';
        $id = $this->getQuery('id');
        $department = Department::find($id);
        if(!$department) {
            Flash::error('部门不存在！');
            $this->redirect('/admin/content/departments');
        }

        $this->display('departmentForm', ['department'=>$department]);
    }

    /**
     * 保存部门
     * @return mixed
     */
    public function saveDepartmentAction() {
        $params = $this->getAllPost(true);
        $department = null;
        if($params['id']) {
            $department = Department::find($params['id']);
        } else {
            $department = new Department();
        }
        if($params['name']=='') {
            Flash::error('名称不能为空！');
            $this->goBack();
        }
        $data = [];
        $data['name'] = $params['name'];
        if(!$department->id) {
            if($params['identifier']=='') {
                Flash::error('标识符不能为空！');
                $this->goBack();
            }
            $data['identifier'] = $params['identifier'];
        }
        
        $status = false;
        foreach ($data as $key => $value) {
            $department->$key = $value;
        }
        $status = $department->save();
        
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/departments');
        } else {
            Flash::error('操作失败！');
            $this->goBack();
        }
    }

    /**
     * 删除部门
     * @return mixed
     */
    public function deleteDepartmentAction() {
        $id = $this->getPost('id');
        $department = Department::find($id);
        $status = false;
        if($department) {
            $status = $department->delete();
        }
        $rdata = [];
        if($status) {
            Job::where('dp_id', $department->id)->delete();
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 客服人员
     * @return mixed
     */
    public function staffsAction() {
        $this->submenu = 'staff';
        $queries = $this->queries->defaults(['name'=>'']);
        $name = $queries->name;

        $builder = Staff::whereRaw('1=1');

        if($name!='') {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        $staffs = $builder->paginate(15);
        $staffs->appends($queries->all());

        $this->display('staffs', ['staffs'=>$staffs, 'queries'=>$queries]);
    }

    /**
     * 添加客服
     * @return mixed
     */
    public function addStaffAction() {
        $this->submenu = 'staff';
        $staff = new Staff();
        $this->display('staffForm', ['staff'=>$staff]);
    }

    /**
     * 更新客服
     * @return mixed
     */
    public function updateStaffAction() {
        $this->submenu = 'staff';
        $id = $this->getQuery('id');
        $staff = Staff::find($id);
        if(!$staff) {
            Flash::error('客服不存在！');
            $this->redirect('/admin/content/staffs');
        }

        $this->display('staffForm', ['staff'=>$staff]);
    }

    /**
     * 保存客服
     * @return mixed
     */
    public function saveStaffAction() {
        $params = $this->getAllPost(true);
        $staff = null;
        if($params['id']) {
            $staff = Staff::find($params['id']);
        } else {
            $staff = new Staff();
        }
        if($params['name']=='') {
            Flash::error('名称不能为空！');
            $this->goBack();
        }
        if($params['nick_name']=='') {
            Flash::error('昵称不能为空！');
            $this->goBack();
        }
        
        $staff->name = $params['name'];
        $staff->qq = $params['qq'];
        $staff->nick_name = $params['nick_name'];
        
        if($staff->save()) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/staffs');
        } else {
            Flash::error('操作失败！');
            $this->goBack();
        }
    }

    /**
     * 删除客服
     * @return mixed
     */
    public function deleteStaffAction() {
        $id = $this->getPost('id');
        $staff = Staff::find($id);
        $status = false;
        if($staff) {
            $status = $staff->delete();
        }
        $rdata = [];
        if($status) {
            $staffs = Staff::whereRaw('1=1')->get(['id']);
            $count = 0;
            $list = [];
            foreach ($staffs as $staff) {
                $list[] = $staff->id;
                $count++;
            }
            $userVips = UserVip::where('customService', $id)->get();
            foreach ($userVips as $userVip) {
                $key = rand(1, $count);
                $userVip->customService = $list[$key];
                $userVip->save();
            }

            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 网站设置
     */
    public function setAction(){
    	$this->submenu = 'set';
    	$list = DB::table('work_info')->get();
    	$this->display('set', ['list'=>$list]);
    }

    /**
     * 保存设置
     */
    public function saveSetAction(){
    	$oddremark = $this->getPost('oddremark'); 
    	$oddvalue = $this->getPost('oddvalue'); 
    	$re = DB::table('work_info')->where('oddremark',$oddremark)->update(['oddvalue'=>$oddvalue]);
    	if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/content/set');
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/content/set');
        }
    }
}
