import 'bootstrap';

let requestTable = $('#dataTable_request').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": "/requests/list",
        "dataType": "json",
        "type": "POST",
        "data": {
            _token: $('meta[name="csrf-token"]').attr('content'),
        }
    },
    "columns": [
        { "data": 'id' },
        { "data": 'time_start' },
        { "data": 'time_end' },
        { "data": 'hour' },
        { "data": 'user' },
        { "data": 'request_type' },
        { "data": 'created_at' },
        { "data": 'status' },
        { "data": 'comment' },
        { "data": 'action' },
    ],
    "order": [[0, "desc"]],
    "columnDefs": [
        {
            "targets": 8,
            "orderable": false
        }, {
            'targets': 7,
            'className': 'text-right',
        }
    ],
    drawCallback: function(settings) {
        $('[data-toggle="tooltip"]').tooltip();
    },
});

$(document).ready(function () {
    
});

$(document).on('click', '.btnEditRequest', function() {
	showEditRequest($(this).data('id'));
});

$(document).on('click', '.btnAddRequest', function() {
	resetEditRequestForm();
	showEditRequest();
});

$(document).on('submit', '.frmTaskType', function() {
	$('#btnSubmitRequest').trigger('click');
});

$(document).on('click', '#btnSubmitRequest', function() {
	submitEditRequest();
});

function resetEditRequestForm() {
	$('#frmTaskType').trigger("reset");
    $('#txtComment').text('')
}

function showEditRequest(id) {
	console.log(id);
	if(id) {
		$('#requestModalTitle').text('Edit Request');
	} else {
		$('#requestModalTitle').text('Add Request');
	}

	let modal = $('#editRequestModal');	
	// new
	$.ajax({
    	type: "POST",
      	url: "/requests/edit",
      	data: {
      		_token: $('meta[name="csrf-token"]').attr('content'),
      		id: id
      	},
      	complete: function(data) {
        	if (data.status == 200) {
          		console.log(data);
          		let responseData = data.responseJSON;
          		$('#selectUser').html(responseData.user_select_html);
          		$('#selectUser').val(responseData.curr_request.user_id);
          		$('#selectType').val(responseData.curr_request.request_type);
          		$('#txtLeaveRequestId').val(responseData.curr_request.id);
          		$('#txtDateStart').val(responseData.curr_request.date_start)
          		$('#txtDateEnd').val(responseData.curr_request.date_end)
          		$('#txtTimeStart').val(responseData.curr_request.time_start)
          		$('#txtTimeEnd').val(responseData.curr_request.time_end)
          		$('#txtHour').val(responseData.curr_request.hour)
          		$('#txtComment').text(responseData.curr_request.comment)
          		modal.modal("show");
        	} else {
          		alert("An error occured. Please refresh the page");
        	}
      	},
    });
}

function submitEditRequest() {
	let inputData = $('#frmTaskType').serializeArray();

	let modal = $('#editRequestModal');	
	// new
	$.ajax({
    	type: "POST",
      	url: "/requests/update",
      	data: {
      		_token: $('meta[name="csrf-token"]').attr('content'),
      		input: inputData
      	},
      	complete: function(data) {
        	if (data.status == 200) {
          		console.log(data);
          		// let responseData = data.responseJSON;

          		requestTable.ajax.reload();
          		resetEditRequestForm();
          		modal.modal("hide");
        	} else {
          		alert("An error occured. Please refresh the page");
        	}
      	},
    });
}