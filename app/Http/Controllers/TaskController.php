<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Cookie;
use Exception;
use DataTables;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class TaskController extends Controller
{
    /**
    * @author Himanshu
    * @method index
    * @description function to show to do main page
    */
    public function index(Request $request, $cookieVal = null)
    { 
        $cookieVal = $cookieVal ?: $request->get('todolist');
        return view('task', ['cookieVal' => $cookieVal]);
    }

    /**
    * @author Himanshu
    * @method store
    * @description Function to store the to-do task
    */
    public function store(Request $request){
        try {
            $cookieVal = $request->cookie('todolist') && $request->cookie('todolist') != null ? $request->cookie('todolist') : $request->get('todolist') ;
            $cookie = Cookie::where('cookie_val', $cookieVal)->firstOrFail();

            if($cookie->id){
                $task = new Task;
                $task->task = $request->task;
                $task->cookie_id = $cookie->id;
                $task->save();
            }else{
                throw new Exception("An error occurred");
            }
            return response()->json($task);
        } catch (\Exception $e) {
            return response()->json(array("error" => true,"msg" => "Error while saving to-do task"));
        }
        
    }

    /**
    * @author Himanshu
    * @method getTasksDataList
    * @description fucntion to get to-do list
    */
    public function getTasksDataList(Request $request)
    {
        $cookieVal = $request->get('cookieVal') ?: $request->get('todolist');
        $cookie = Cookie::where('cookie_val', $cookieVal)->firstOrFail();
        $tasks = Task::where('cookie_id', $cookie->id);
        if ($request->get('showAll') !== 'true') {
            $tasks->where('completed', false);
        }
        $tasks->orderBy('created_at', 'desc');

        return DataTables::of($tasks)
            ->editColumn('completed', function($task) {
                return $task->completed ? 'Done' : '';
            })
            ->addColumn('sno', function ($row) {
                static $i = 1;
                return $i++;
            })
            ->addColumn('action', function($task) {
                $action = '';
                if(!$task->completed){
                    $action .= '<button class="btn btn-success btn-sm mark-complete" title="Mark Complete" data-id="' . Crypt::encrypt($task->id) . '"><i class="fas fa-check-square"></i></button><span> | </span>';
                }
                return $action.'<button class="btn btn-danger btn-sm delete-task" title="Delete To-Do Task" data-id="' . Crypt::encrypt($task->id) . '"><i class="fas fa-times-square"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
    * @author Himanshu
    * @method completeTask
    * @description mark task as complete
    */
    public function completeTask(Request $request){
        try {
            $id = Crypt::decrypt($request->get('taskId'));
            $cookieVal = $request->get('cookieVal') ?: $request->get('todolist');
            $cookieId = Cookie::where('cookie_val', $cookieVal)->firstOrFail();
            $task = Task::where('id', $id)->where('cookie_id', $cookieId->id)->firstOrFail();
            $task->completed = !$task->completed;
            $task->save();
            return response()->json(array('status'=>true,"msg"=>"To-Do task completed successfully"));
        } catch (\Exception $th) {
            return response()->json(array('status'=>false,"msg"=>"Error while marking to-do task complete. Try again later"));
        }
    }

    /**
    * @author Himanshu
    * @method destroy
    * @description function for deleting to do task
    */
    public function destroy(Request $request){
        try {
            $id = Crypt::decrypt($request->get('taskId'));
            Task::destroy($id);
            return response()->json(array('status'=>true,"msg"=>"To-Do task deleted successfully"));
        } catch (\Exception $th) {
            return response()->json(array('status'=>false,"msg"=>"Error while deleting to-do task. Try agin later"));
        }
    }
}
