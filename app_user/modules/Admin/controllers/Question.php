<?php
use Admin as Controller;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;
/**
 * ArticleController
 * 问答管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class QuestionController extends Controller {
    use PaginatorInit;
    public $ceoName = "CEO";
    public $userName = "平台客服";
    /*
     * 查看待审核的问题
     */
    function  questionsAction(){
		$re = DB::table('user_question')->where('status','=','0');
        $fields = $re->paginate(15);
        $this->display('list', ['list'=>$fields]);
    }
    
    /*
     * 审核提问
     */
    function trialQuestionsAction(){
        $id = $this->getQuery('id');
        $status = $this->getQuery('status');
        if($id && $status){
            $re = DB::table('user_question')->where('id',$id)->update(['status'=>$status]);
            if($re) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/questions');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        }else{
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    /*
     * 全部审核通过
     */
    function trialAllQuestionsAction(){
        $status = $this->getQuery('status');
        $re = DB::table('user_question')->where('status','=','0')->update(['status'=>$status]);
        if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/question/questions');
        } else {
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    /*
     * 查看所有待审核的回答
     */
    function answerAction(){
		$re = DB::table('user_question_answer AS answer1')->leftjoin('user_question_answer AS answer2','answer1.parentId','=','answer2.id')
			->leftjoin('user_question AS question','answer1.questionId','=','question.id')
			->where('answer1.status','=',0);
        $re = $re->select('answer1.id','answer1.username','answer1.answerTime','answer1.content','answer2.content AS answerContent','question.content AS questionContent')->paginate(15);
        $list = array();
        $i = 0;
		foreach($re as $key => $row) {
			foreach($row as $k => $v){
				if(empty($row->answerContent)){	
					$row->answerContent = $row->questionContent;
				}
				unset($row->questionContent);
			}
        }
        $this->display('answer',['list' => $re]);
    }
    /*
     * 审核回复
     */
    function trialAnswerAction(){
        $id = $this->getPost('id');
        $status = $this->getPost('status');
        if($id && $status){
            $res = DB::table('user_question_answer')->select('username','answerTime','parentId','questionId')->where('id','=',$id)->first();
            if($res->parentId == '0'){
                $answerCount = DB::table('user_question_answer')->where('parentId','=','0')->where('status','=','1')->where('questionId','=',$res->questionId)->count();
                $answerCount = (isset($answerCount) && is_numeric($answerCount)) ? $answerCount+1 : 1;
                $data['answerCount'] = $answerCount;
                $data['lastAnswerUser'] = $res->username;
                $data['lastAnswerTime'] = $res->answerTime;
                $db = DB::table('user_question')->where('id','=',"$res->questionId")->update($data);
            }else{
                //更新回答的回复数
                $replyCount = DB::table('user_question_answer')->where('status','=','1')->where('parentId','=',"$res->parentId")->count();
                $replyCount = (isset($replyCount) && is_numeric($replyCount)) ? $replyCount+1 : 1;
                $db = DB::table('user_question_answer')->where('id','=',"$res->parentId")->update(['replyCount'=>$replyCount]);
            }
            $re = DB::table('user_question_answer')->where('id','=',$id)->update(['status'=>$status]);
            if($re) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/questions');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        }else{
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    /*
     * 审核所有回复
     */
     function trialAllAnswerAction(){
        $re = DB::table('user_question_answer')->where('status','0')->update(['status'=>'1']);
        if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/article/list');
        } else {
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    /*
     * 查看问题
     */
    function listAction(){
        $queries = $this->queries->defaults(['title'=>'', 'startTime'=>'', 'endTime'=>'', 'type'=>'all']);
		$re = DB::table('user_question')->where('status','1')->orderBy('addTime','desc');
        if ($queries->startTime != '') {
            $re = $re->where('addTime','>=',$queries->startTime . " 00:00:01");
        }
        if ($queries->endTime != '') {
            $re = $re->where('addTime','<=',$queries->endTime . " 23:59:59");
        }
        if($queries->type != 'all'){
            $re = $re->where('type','=',$queries->type);
        }
        $fields = $re->paginate(15);
        $fields->appends($queries->all());
        $this->display('listQuestion',['list' => $fields,'queries'=>$queries]);     
    }
            
     /*
     * 查看问题详情
     */
    function questionDetailsAction(){
        $id = $this->getPost('id');
        if(!$id){
            Flash::error('操作失败');
            $this->goBack();
        }
        $row = DB::table('user_question')->where('id','=',$id)->first();
        $answer = DB::table('user_question_answer')->where('parentId','=','0')->where('status','=','1')->where('questionId','=',$id)->get();
        if ($answer) {
            //$list[$i]['answer'] = $answer;
            foreach ($answer as $key => $val) {
                $db = DB::table('user_question_answer')->where('status','=','1')->where('parentId','=',$id)->get();
                $answer[$key]->answer2 = $db;
            }
            if (!empty($answer)) {
                $row->answer1 = $answer;
            }
        }
        $this->display('questionDetails',['question'=>$row]);
    }
    
    /*
     * 回复列表
     */
    
    function showListReply(){
        $id = $this->getQuery('id');
        $re = DB::table('user_question_answer')->where('status','=','1')->orderBy('answerTime','desc');
        if(!empty($_GET["type"]) && $_GET["type"]=="new"){
            $re = $re->where('lookStatus','=',0)->where('replyRole','=',user);
        }
        if (!empty($_GET['startTime'])) {
            $re = $re->where('answerTime','>=',$_GET['startTime'] . " 00:00:01");
        }
        if (!empty($_GET['endTime'])) {
            $re = $re->where('answerTime','<=',$_GET['endTime'] . " 23:59:59");
        }
        $re = $this->perpage($re,$re,10);
        $list = array();
        foreach($re as $key=>$row) {
			foreach($row as $k => $v)
            $list[$key][$k] = $v;
        } 
        //设置回复已经看过
		DB::table('user_question_answer')->where('lookStatus','=',0)->update(['lookStatus' => 1]);

		$show_pages = $this->Tmpl['show_pages'];
        return view('question.ListReply',['list' => $list])->with(['show_pages' => $show_pages]);
    }

    /*
     * 删除回复
     */
    
    function delReply(){
        $id = $this->getPost('id');
        if(!$id){
            $res = DB::table('user_question_answer')->where('id',$id)->select('questionId','parentId')->first();
            $db = DB::table('user_question_answer')->where('parentId','=',$id)->delete();
            $db = DB::table('user_question_answer')->where('id',$id)->delete();
            if('0' == $res->parentId){
                $answerCount = DB::table('user_question_answer')->where('parentId','0')->where('status','1')->where('questionId',$res->questionId)->count();
                $answerCount = (isset($answerCount) && is_numeric($answerCount)) ? $answerCount : 0;
                $data['answerCount'] = $answerCount;
                /*
                 * 在此处更新最后回答用户和回答时间
                 */
                $row = DB::table('user_question_answer')->select('username','answerTime')->where('parentId','0')->where('status','0')->where('questionId',$res->questionId)->orderBy('answerTime','desc')->first();
                if(empty($row)){
                    $db = DB::table('user_question')->where('id',$res->questionId)->update(['lastAnswerUser' => '','lastAnswerTime' =>null,'answerCount' => $answerCount]);
                }else{
                    $data['lastAnswerUser'] = $res->username;
                    $data['lastAnswerTime'] = $res->answerTime;
                    DB::table('user_question')->where('id',$res->questionId)->update($data);
                }
            }else{
                //更新回答的回复数
                $replyCount = DB::table('user_question_answer')->where('status','=','1')->where('parentId','=',"$res->parentId")->count();
                $replyCount = (isset($replyCount) && is_numeric($replyCount)) ? $replyCount : 0;
                DB::table('user_question_answer')->where('id',$res->parentId)->update(['replyCount'=>$replyCount]);
            }
            $status = 1;
        }else{
            $status = 0;
        }   
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/question/list');
        } else {
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    /*
     * 删除问题
     */
    function DelQuestion(){
        $id = $this->getPost('id');
        if($id){
            $id = $_GET['id'];
            $db = DB::table('user_question')->where('id',$id)->delete();
            if($db){
                $db = DB::table('user_question_answer')->where('questionId',$id)->delete();
                if($db){
                    $status = 1;
                }else{
                    $status = 0;
                }
            }else{
                $status = 0;
            }
        }else{
            $status = 0;
        }
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/question/list');
        } else {
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    /*
     * 编辑问题
     */
    function EditQuestion(){
        $id = $this->getQuery('id');
        if($id){
            $re = DB::table('user_question')->where('id','=',$id)->first();
            $this->display('editQuestion')->with('list',$re);
        }
    }
    /*
     * 编辑问题
     */
    function  doEditQuestion(){
        $id = $this->getPost('id');
        $sort = $this->getPost('sort');
        if($id & $sort){
            //开始更新是否热门
            //$sql_str = "UPDATE user_question SET isHot = '".$_POST['info']['isHot']."' WHERE id = ".$_POST['id'];
            $db = DB::table('user_question')->where('id',$id)->update(['sort' => $sort]);
            if($status) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/list');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        }else{
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    /*
     * 删除回答或者回复
     */
    function showDelAnswer(){
        $id = $this->getQuery('id');
        if($id){
            $re = DB::table('user_question_answer')->delete($id);
            if($re){
                $re = DB::table('user_question_answer')->where('parentId',$id)->delete();
                if($re) {
                    Flash::success('操作成功！');
                    $this->redirect('/admin/question/list');
                } else {
                    Flash::error('操作失败');
                    $this->goBack();
                }
            }else{
                Flash::error('操作失败');
                $this->goBack();
            }
        }else{
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    /*
     * 显示热门标签添加
     */
    function showAddQuestionTab(){
		$re = DB::table('user_question_tab')->get();
		foreach($re as $key=>$row) {
			foreach($row as $k => $v)
            $hotTab[$key][$k] = $v;
        } 
        $this->display('addQuestionTab',['list' => $hotTab]);
    }
    
    /*
     * 添加获编辑热门标签
     */
    
    function doAddTabAction(){
        $data = $this->getAllPost();
        if($data->tabType == 'add'){
            $data->info['addTime'] = date("Y-m-d H:i:s");
            $re = DB::table('user_question_tab')->insert($data->info);
            if($re) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/list');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        }elseif($data->tabType == 'edit'){
            $re = DB::table('user_question_tab')->where('id',$data->tabId)->update($data->info);
            if($re) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/list');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        }
    }
    /*
     * 删除热门标签
     */
    function deleteTabAction(){
        $id = $this->getPost('id');
        if ($id) {
            $re = DB::table('user_question_tab')->delete($id);
            if($re) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/list');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        } else {
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    /*
     * 查看热门标签
     */
    
    function tabDetailsAction(){
        $id = $this->getQuery('id');
        if($id){
            $questionStr = DB::table('user_question_tab')->where('id',$id)->first('questionId','content');
            $questionArr = explode(",",$questionStr->questionId);
            $idStr = "(0";
            foreach ($questionArr as $key=>$val){
                if(empty($val)){
                    unset($questionArr[$key]);
                    continue;
                }
                $idStr = $idStr . "," .$val;
            }
            $idStr = $idStr . ")";
            $res = DB::table('user_question')->whereRaw('id IN'.$idStr)->paginate(15);
            $this->display('tabDetail', ['content'=> $questionStr->content, 'list'=> $list, 'tabId'=> $id]);
        }else{
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    //将问题从标签中移除
    function showDelQuestionOnTab(){
        $tabId = $this->getQuery('tabId');
        if ($id) {
            $questionId = DB::table('user_question_tab')->where('id',$tabId)->first('questionId');
            $questionIdArr = explode(",", $questionId);
            foreach ($questionIdArr as $key => $values) {
                if ($values == $_GET['id'] || empty($questionIdArr[$key])) {
                    unset($questionIdArr[$key]);
                }
            }
            $questionNum = count($questionIdArr);
            $questionStr = "," . implode(",", $questionIdArr) . ",";
            $re = DB::table('user_question_tab')->where('id',$tabId)->update(['questionId'=>$questionStr,'questionNum'=>$questionNum]);
            if($re) {
                Flash::success('操作成功！');
                $this->redirect('/admin/question/list');
            } else {
                Flash::error('操作失败');
                $this->goBack();
            }
        } else {
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    public function ceoQuestionAction(){
		$ceoAnswer = array();
		$ceoQuestion = array();
		//获取ceo问题
		$ceoQuestion = DB::table('user_question')->select('id','title AS content','username','addTime')->where('status','=',1)->where('type','=',ceo)->where('answerCount','=',0)->get();
		foreach($ceoQuestion as $key => &$val){
			$val = (array)$val;
			$ceoQuestion[$key]['type'] = 'question';
			$ceoQuestion[$key]['questionId'] = $ceoQuestion['id'];
		}
		//获取ceo回复
		$ceoAnswer = DB::table('user_question_answer AS answer')->select('answer.id','answer.username','answer.content','answer.answerTime as addTime','questionId')
					->leftjoin('user_question','answer.questionId','=','user_question.id')
					->where('answer.replyRole','=',user)->where('answer.status','=',1)->where('user_question.type','=',ceo)->where('answer.replyCount','=',0)->get();
		foreach($ceoAnswer as $key => &$value){
			$value = (array)$value;
			$ceoAnswer[$key]['type'] = 'answer';
		}
		$questionList = array_merge($ceoQuestion,$ceoAnswer);
		$addTime = array();
		foreach ($questionList as $values){
           $addTime[] = $values['addTime'];
		}
		array_multisort($addTime, SORT_DESC,$questionList);
        $this->display('ceoQuestion',['questionList' => $questionList]);
    }
    
    /*
     * ceo问题回复
     */
    public function replyAction(){
        $id = $this->getQuery('id');
        $type = $this->getQuery('type');
        if(!$id || !$type){
            Flash::error('操作失败');
            $this->goBack();
        }
        switch($type){
            case "question": 
                $res = DB::table('user_question')->select('id','title','content')->where('id',$id)->first();
                $replayList = DB::table('user_question_answer')->select('id','content')->where('replyRole','admin')->where('questionId',$id)->where('parentId','0')->where('status','1')->first();
                break;
            case "answer":
                $res = DB::table('user_question_answer')->select('id','content')->where('id',$id)->first();
                if(!empty($res) && is_array($res)){
                    $res->title = '';
                }
                $replayList = array();
                break;
            default :
                Flash::error('操作失败');
                $this->goBack();
                break;
        }
        if(empty($res)){
            Flash::error('操作失败');
            $this->goBack();
        }
        $this->display('question.reply',['questionDetails'=>$res,'replayList'=>$replayList]);
    }
    
    public function doreplyAction(){
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        if(!$id || !$type){
            Flash::error('操作失败');
            $this->goBack();
        }
        date_default_timezone_set('Asia/Shanghai');
        switch($type){
            case "question": 
                //将回答加入表中
                if(isset($_POST['answerId']) && is_numeric($_POST['answerId'])){
                    $re = DB::table('user_question_answer')->where('id',$_POST['answerId'])->update(['conten'=>$_POST['replayContent']]);
                    if($db){
                        Flash::success('操作成功！');
                        $this->redirect('/admin/question/reply');
                    }
                }else{
                    $questionMsg = DB::table('user_question')->where('id',$id)->select('id','type','username','title')->first();
                    $answer = array();
                    $answer['content'] = addslashes(trim($_POST['replayContent']));
                    if($questionMsg->type == 'ceo'){
                        $answer['username'] = $this->ceoName;
                    }else{
                        $answer['username'] = $this->userName;
                    }
                    $answer['questionId'] = $id;
                    $answer['parentId'] = '0';
                    $answer['replyRole'] = 'admin'; 
                    $answer['answerTime'] = date("Y-m-d H:m:s");
                    $answer['status'] = '1';
                    $db = DB::table('user_question_answer')->insert($answer);
                    //修改问题的回复数
                    $answerCount = DB::table('user_question_answer')->where('parentId','0')->where('status','1')->where('questionId',$id)->count();
                    $answerCount = (isset($answerCount) && is_numeric($answerCount)) ? $answerCount : 0;
                    $data['lastAnswerTime'] = date("Y-m-d H:m:s");
                    $data['answerCount'] = $answerCount;
                    $data['lastAnswerUser'] = $this->ceoName;
                    $db = DB::table('user_question')->where('id',$id)->update($data);
		    //发送站内信
                    $emailData['title'] = '您的问题有新的回复！';
                    $emailData['content'] = '您的问题【<a href="http://www.hcjrfw.com/question/view?id='.$questionMsg->id.'" target="_blank">'.$questionMsg->title.'</a>】有新的回答';
                    $emailData['addTime'] = date("Y-m-d H:m:s");
                    $ip = getClientIp();
                    $emailData['addIp'] = $ip['REMOTE_ADDR'][0];
                    $emailData['status'] = '1';
                    $emailData['sendUser'] = 'system';
                    $emailData['sendType'] = '0';
                    $emailData['sendUserType'] = '0';
                    $emailData['receiveUser'] = $questionMsg->username;
                    $db = DB::table('system_webmail')->insert($emailData);
                    Flash::success('操作成功！');
                    $this->redirect('/admin/question/reply');
                }
                break;
            case "answer":
                $res = DB::table('user_question_answer')->select('questionId','parentId','content','username')->where('id',$id)->first();
                $answer = array();
                $answer['questionId'] = $res->questionId;
                $answer['content'] = addslashes(trim($_POST['replayContent']));
                $answer['username'] = $this->ceoName;
                $answer['parentId'] = $res->parentId;
                $answer['answerTime'] = date("Y-m-d H:m:s");
                $answer['replyRole'] = 'admin';
                $answer['status'] = '1';
                DB::table('user_question_answer')->insert($answer);
                //更新回复数
                //获取追问父id
                $replyCount = DB::table('user_question_answer')->where('status','1')->where('parentId',$res->parentId)->first();
                $replyCount = (isset($replyCount) && is_numeric($replyCount)) ? $replyCount : 0;
                DB::table('user_question_answer')->where('id',$res->parentId)->update(['replyCount' => $replyCount]);
                DB::table('user_question_answer')->where('id',$id)->update(['replyCount' =>1]);
                //发送站内信                
                
                $emailData['title'] = '您的回答问有新的回复！';
                $emailData['content'] = '您的回答【<a href="http://www.hcjrfw.com/question/view?id=' . $res['questionId'] . '" target="_blank">' . $res['content'] . '</a>】有新的回复';
                $emailData['addTime'] = date("Y-m-d H:m:s");
                $ip = getClientIp();
                $emailData['addIp'] = $ip['REMOTE_ADDR'][0];
                $emailData['status'] = '1';
                $emailData['sendUser'] = 'system';
                $emailData['sendType'] = '0';
                $emailData['sendUserType'] = '0';
                $emailData['receiveUser'] = $res->username;
                DB::table('system_webmail')->insert($emailData);
                Flash::success('操作成功！');
                $this->redirect('/admin/question/reply');
                break;
            default :
                Flash::error('操作失败');
                $this->goBack();
                break;
        }
        if(empty($res)){
            Flash::error('操作失败');
            $this->goBack();
        }
    }
    
    public function showSetReply(){
        $id = $this->getQuery('id');
        if(is_numeric($id)){
            $re = DB::table('user_question_answer')->where('id',$id)->update(['replyCount'=>'1']);
            if($re){
                Flash::success('操作成功！');
                $this->redirect('/admin/question/reply');
            }
        }
        Flash::error('操作失败');
        $this->goBack();
    }
}

?>