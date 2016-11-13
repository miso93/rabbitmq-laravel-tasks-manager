<?php

namespace App\Http\Controllers;

use App\Task;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TasksController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Task::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $message = [
            "action"  => "create-task",
            "success" => false
        ];

        try {

            $task = Task::create($request->input());
            $message['success'] = true;
            $message['data'] = Task::find($task->id);

            $this->rabbit_mq_send($message);

        } catch (Exception $e) {
            $message['error'] = $e->getMessage();
        }

        return $message;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Task::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $message = [
            "action"  => "update-task",
            "success" => false
        ];

        try {
            $task = Task::find($id);

            $message['success'] = $task->update($request->input());
            $message['data'] = Task::find($id);

            $this->rabbit_mq_send($message);

        } catch (Exception $e) {
            $message['error'] = $e->getMessage();
        }

        return $message;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = [
            "action"  => "delete-task",
            "success" => false
        ];

        try {
            $task = Task::find($id);

            $message['data'] = $id;
            $message['success'] = $task->delete();

            $this->rabbit_mq_send($message);

            return $message;
        } catch (Exception $e) {
            $message['error'] = $e->getMessage();
        }

        return $message;
    }

    public function rabbit_mq_send($message = [])
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

//        $channel->queue_declare('tasks', false, true, false, false);

//        $channel->basic_qos(null, 1, null);
//        $channel->basic_consume('task_queue', '', false, true, false, false, $callback);


        $routing_key  = $message['action'];
        $message = json_encode($message);
        $msg = new AMQPMessage($message);

        $channel->exchange_declare('tasks', 'topic', false, false, false);
        $channel->basic_publish($msg, 'tasks', $routing_key);

        $channel->close();
        $connection->close();

    }
}
