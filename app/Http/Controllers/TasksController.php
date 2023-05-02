<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TaskResource;
use Carbon\Carbon;
use Auth;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function profile()
    {
        $user = Auth::user();
        return response()->json(['user' => $user]);
    }

    public function tasksAll(){
        $tasks =Tasks::latest()->get();
        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }

    public function tasksOverdue(){
        $tasks = Tasks::where('end_date', '<', Carbon::now())->get();
        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }

    public function tasksTodo()
    {
        $status = "not_complete";
        $tasks = Tasks::where('status', $status)
        ->where('user_id',Auth::user()->id)
        ->orderBy('id', 'DESC')
        ->get();
        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }

    public function tasksComplete()
    {
        $status = "complete";
        $tasks = Tasks::where('status', $status)
        ->where('user_id',Auth::user()->id)
        ->orderBy('id', 'DESC')
        ->get();
        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }

    public function index()
    {
        $now = Carbon::now();
        $tasks = Tasks::whereDate('start_date',$now)
        ->where('user_id',Auth::user()->id)
        ->orderBy('id', 'DESC')
        ->get();
        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }

    public function tasksWeek()
    {
        $now = Carbon::now();
        $from = $now->startOfWeek()->format('Y-m-d');
        $to =   $now->endOfWeek()->format('Y-m-d');

        $tasks = Tasks::whereBetween('start_date', [$from,$to])
        ->where('user_id',Auth::user()->id)
        ->orderBy('id', 'DESC')
        ->get();

        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }

    public function tasksMonth()
    {
        $now = Carbon::now();

        $from = $now->startOfMonth()->format('Y-m-d');
        $to =   $now->endOfMonth()->format('Y-m-d');

        $tasks = Tasks::whereBetween('start_date', [$from,$to])
        ->where('user_id',Auth::user()->id)
        ->orderBy('id', 'DESC')
        ->get();

        return response()->json(['tasks' => TaskResource::collection($tasks)]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'task_name' => 'required|max:255',
            'task_level' => 'required',
            'categories' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $tasks = Tasks::create([
            'user_id' => Auth::user()->id,
            'task_name' => $request->task_name,
            'task_level' => $request->task_level,
            'categories' =>  $request->categories,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        return response()->json([
            "code" => 200,
            "message" =>
            "added successfully" ,
            "data" =>$tasks->refresh()
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tasks =  Tasks::find($id);
        return response()->json(['tasks' => new TaskResource($tasks)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $vallidation = $this->validate($request, [
            'task_name' => 'required|max:255',
            'task_level' => 'required',
            'categories' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);


        $tasks =  Tasks::find($id);

        $tasks->update([
            'user_id' => Auth::user()->id,
            'task_name' => $request->task_name,
            'task_level' => $request->task_level,
            'categories' => $request->categories,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json(['code' => 200, "message" => "updated successfully" , "data" =>$tasks->refresh() ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tasks =  Tasks::find($id);
        $tasks->delete();
        return response()->json(["code" => 200,"message" => "Tasks Deleted successfully"]);
    }

    public function updateStatus($id){
        $tasks = Tasks::find($id);
        if($tasks->status == "complete"){
            $tasks->status = "not_complete";
            $tasks->save();
        }else{
            $tasks->status = "complete";
            $tasks->save();
        }
        return response()->json(["code" => 200,"message" => "Tasks Update successfully" , "data" =>$tasks->refresh()]);
    }
}
