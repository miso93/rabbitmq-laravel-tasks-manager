/**
 * Created by Michal on 10.10.2016.
 */
require('net');
var Stomp = require('stompjs');

var config = {
    resource: "api/tasks{/id}"
};

Vue.component('tasks', {
    template: '#tasks-template',
    props: ['list'],
    data: function () {
        return {
            list: [],
            newTask: ''
        };
    },
    created: function () {
        this.fetchTaskList();
    },
    computed: {
        doneTasksList: function () {
            return this.list.filter(this.isDone);
        },
        openTasksList: function () {
            return this.list.filter(this.isOpen);
        }
    },
    methods: {
        handleError: function (error) {
            alert(error);
        },
        isDone: function (task) {
            return task.done;
        },
        isOpen: function (task) {
            return !this.isDone(task);
        },
        doneTask: function (task) {
            task.done = 1;
            this.editTask(task);
        },
        upPriority: function (task) {
            task.priority += 1;
            this.editTask(task);
        },
        downPriority: function (task) {
            task.priority -= 1;
            this.editTask(task);
        },
        backOpen: function (task) {
            task.done = 0;
            this.editTask(task);
        },
        editTask: function (task) {
            var resource = this.$resource(config.resource);

            resource.update({id: task.id}, task).then(function (response) {
                if (!response.body.success) {
                    this.handleError(response.body.error);
                }
            });
        }
        ,
        fetchTaskList: function () {
            var vm = this;
            var resource = this.$resource(config.resource);

            resource.get().then(function (response) {
                vm.list = response.body;
            });
        }
        ,
        deleteTask: function (task) {
            var resource = this.$resource(config.resource);
            var vm = this;
            resource.delete({id: task.id}).then(function (response) {
                if (!response.body.success) {
                    this.handleError(response.body.error);
                }
            });

            // this.list.$remove(task);
        }
        ,
        createTask: function () {
            var resource = this.$resource(config.resource);
            var vm = this;
            resource.save({body: vm.newTask}).then(function (response) {

                if (!response.body.success) {
                    this.handleError(response.body.error);
                } else {
                    vm.newTask = '';
                }

            });

        },
        listen: function () {
            var vm = this;
            var echange = "/exchange/tasks/";
            var ws = new WebSocket('ws://127.0.0.1:15674/ws');

            var client = Stomp.over(ws);

            client.connect('guest', 'guest',
                function(x) {
                    client.subscribe(echange + 'delete-task', function(m) {

                        var response = JSON.parse(m.body);

                        var id = parseInt(response.data);
                        vm.list = vm.list.filter(function (item){
                            console.log(id);
                           return item.id != id;
                        });
                    });

                    client.subscribe(echange + 'create-task', function(m) {

                        var response = JSON.parse(m.body);
                        vm.list.push(response.data);

                    });

                    client.subscribe(echange + 'update-task', function(m) {

                        var response = JSON.parse(m.body);
                        vm.list = vm.list.map(function (item){
                            if(item.id == response.data.id){
                                item = response.data;
                            }
                            return item;
                        });

                    });

                }, function(){
                    console.log('error');
                }, '/');

            // var channel = pusher.subscribe('tasks');

            // channel.bind('new-task', function (data) {
            //     vm.list.push(data);
            // });
            //
            // channel.bind('edit-task', function (data) {
            //     vm.list = vm.list.filter(function (value) {
            //         return data.id != value.id;
            //     });
            //     vm.list.push(data);
            // });
            //
            // channel.bind('delete-task', function (data) {
            //     // console.log('data id', data.id);
            //     vm.list = vm.list.filter(function (value) {
            //         return data.id != value.id;
            //     });
            // });
        }
    }
    ,
    ready: function () {
        this.listen();
    }
})
;

new Vue({
    el: 'body'
});