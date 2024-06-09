<!DOCTYPE html>
<html>
<head>
    <title>To-Do List</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('package/bootstrap.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        #hideCompleteTask{
            display:none;
        }
        .container h1{
            color:#0b5ed7;
        }
        .center-form {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .center-form .form-group {
            margin-bottom: 0;
        }
        #task {
            width: 400px; 
            margin: 0 auto; 
        }

        
    </style>
</head>
<body>
<div class="container">
    <h1>PHP - Simple To Do List App</h1>
    <hr>
    <form id="add_task_form" class="center-form">
        <div class="row">
            <div class="col lg-6">
                <div class="form-group mb-2">
                    <input type="text" id="task" name="task" class="form-control" placeholder="Enter task">
                </div>
            </div>
            <div class="col lg-6">
                <button type="submit" id="addTask" class="btn btn-primary mb-2 ml-2">Add Task</button>
            </div>
        </div>
    </form>
    <button id="showAllTasks" class="btn btn-secondary my-2">Show All Tasks</button>
    <button id="hideCompleteTask" class="btn btn-secondary my-2">Hide Complete Tasks</button>
</div>
<div class="toDoTable container">
    <table id="taskTable" class="display">
        <thead>
            <tr>
                <th>#</th>
                <th>Task</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<script src="{{ asset('package/jquery-3.5.1.min.js')}}"></script>
<script src="{{ asset('package/jquery.validate.min.js')}}"></script>
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function(){

        let showAll = false;

        // get task for table
        var cookieVal = "{{ $cookieVal }}";
        var table = $('#taskTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/tasks/datatable',
                data: function(d) {
                    d.cookieVal = cookieVal;
                    d.showAll = showAll;
                }
            },
            columns: [
                { data: 'sno', name: 'sno' },
                { data: 'task', name: 'task' },
                { data: 'completed', name: 'completed' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            lengthMenu: false,
            dom: '<"top"l<"clear">>rtip',
            ordering: false
        });

        $('#taskTable_wrapper .dataTables_length').hide();

        $.validator.addMethod("noScriptTags", function(value, element) {
            return this.optional(element) || !/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi.test(value);
        }, "Script tags are not allowed.");

        $("#add_task_form").on("submit",function(e){
            e.preventDefault();
        })

        // added few validation on add task
        $("#add_task_form").validate({
            rules: {
                task: {
                    noScriptTags: true,
                    maxlength: 100
                }
            },
            messages: {
                task: {
                    maxlength: "Task length should not be greater than 100 characters."
                }
            },
            submitHandler: function(form) {
                let task = $('#task').val();
                if (task && $.trim(task) != '') {
                    $.ajax({
                        url: '/store',
                        method: 'POST',
                        data: { task: task },
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(data) {
                            table.ajax.reload();
                            $('#task').val('');
                        },
                        error: function(){
                            alert("To-Do task in not created, please try again");
                        }
                    });
                }
            },
        });

        // show all task fucntion
        $(document).on("click","#showAllTasks",function(){
            showAll = true;
            table.ajax.reload();
            $(this).hide();
            $("#hideCompleteTask").show();
        });

        // hide complete to-do
        $(document).on("click","#hideCompleteTask",function(){
            showAll = false;
            table.ajax.reload();
            $(this).hide();
            $("#showAllTasks").show();
        });

        // mark to-do task complete
        $(document).on("click",".mark-complete",function(e){
            e.preventDefault();
            let th = $(this);
            let id = th.attr("data-id");
            $.ajax({
                url: '/task',
                data:{taskId:id,cookieVal:cookieVal},
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(data) {
                    if(data.status){
                        table.ajax.reload();
                        Swal.fire("Success", data.msg, "success");

                    }else{
                        Swal.fire("Error", data.msg, "error");
                    }
                },
                error:function(){
                    Swal.fire("Error", data.msg, "error");
                }
            });
        })

        // delete to-do task 
        $(document).on("click",".delete-task",function(e){
            e.preventDefault();
            let th = $(this);
            let id = th.attr("data-id");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/deleteTask',
                        method: 'DELETE',
                        data:{taskId:id,cookieVal:cookieVal},
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(data) {
                            table.ajax.reload();
                            Swal.fire("Deleted!", "Your task has been deleted.", "success");
                        },
                        error: function() {
                            Swal.fire("Error", "To-Do task is not deleted, please try again", "error");
                        }
                    });
                }
            });
        })
    });
</script>
</body>
</html>
