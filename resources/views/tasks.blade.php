<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Michal Tasks</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{elixir('css/app.css')}}">
    <script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
</head>
<body>
<div class="container">

    <tasks></tasks>
</div>

<template id="tasks-template">
    <h1>My Tasks</h1>
    <ul class="list-group">
        <li class="list-group-item" v-for="task in openTasksList | orderBy 'priority' -1">
            <div class="priority">
                @{{task.priority}}
            </div>
            <div class="actions">
                <button @click="upPriority(task)">UP</button>
                <button @click="downPriority(task)">DOWN</button>
            </div>
            <div class="actions">
                <button @click="doneTask(task)">Done task</button>
            </div>
            <div class="body">@{{task.body}}</div>
            <strong class="delete" @click="deleteTask(task)">X</strong>
        </li>
    </ul>
    <div class="form-group">
        <input placeholder="New task" class="form-control" type="text" @keyup.enter="createTask" v-model="newTask">
    </div>

    <h1>Done Tasks</h1>
    <ul class="list-group">
        <li class="tasks-done list-group-item" v-for="task in doneTasksList | orderBy 'priority' -1">
            <div class="priority">
                @{{task.priority}}
            </div>
            <div class="actions">
                <button @click="backOpen(task)">Back open task</button>
            </div>
            <div class="body">@{{task.body}}</div>
            <strong class="delete" @click="deleteTask(task)">X</strong>
        </li>
    </ul>

</template>

{{--<script src="http://code.jquery.com/jquery.js"></script>--}}

<script src="http://cdnjs.cloudflare.com/ajax/libs/vue/1.0.8/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/1.0.3/vue-resource.min.js"></script>


<script src="{{elixir("js/main.js")}}">

</script>

</body>
</html>
