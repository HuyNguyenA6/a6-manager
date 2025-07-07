import 'bootstrap';

let timesheetTable = $('#dataTable_timesheet').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": "/timesheets/list",
        "dataType": "json",
        "type": "POST",
        "data": {
            _token: $('meta[name="csrf-token"]').attr('content'),
        }
    },
    "columns": [
        { "data": 'id' },
        { "data": 'week_start' },
        { "data": 'user' },
        { "data": 'profile' },
        { "data": 'submitted_at' },
        { "data": 'work_hours' },
        { "data": 'approver' },
        { "data": 'status' },
        { "data": 'comments' },
        { "data": 'action' },
    ],
    "order": [[0, "desc"]],
    "columnDefs": [
        {
            'targets': 0,
            'className': 'd-none'
        },
        {
            "targets": 9,
            "orderable": false
        }
    ],
    drawCallback: function(settings) {
        $('[data-toggle="tooltip"]').tooltip();
    },
});

$(document).ready(function () {
    
});

$(document).on('click', '.btnEditRequest', function() {
	
});

$(document).on('click', '.btnDelete', function () {
    $('#deleteModal').modal('show');
    var id = $(this).data('id');
    $('#formDelete').attr('action', `/timesheets/${id}/delete`);
});

$(document).on('submit', '#formDelete', function(event) {
    event.preventDefault();
    var url = $('#formDelete').attr('action');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'DELETE',
        url: url,
    }).done(function (data) {
        if (data == 100) {
            $('#deleteModal').modal('hide');
            timesheetTable.ajax.reload();
        } else {
            alert("An error occured");
        }
    });
});