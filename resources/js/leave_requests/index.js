import 'bootstrap/dist/js/bootstrap.bundle.js';

import Inputmask from 'inputmask';
import AirDatepicker from 'air-datepicker';
import 'air-datepicker/air-datepicker.css';
import moment from 'moment';
window.moment = moment;

moment.updateLocale('en', {
  workinghours: {
    0: null, // Sunday - closed
    1: ['09:00:00', '17:00:00'], // Monday
    2: ['09:00:00', '17:00:00'], // Tuesday
    3: ['09:00:00', '17:00:00'], // Wednesday
    4: ['09:00:00', '17:00:00'], // Thursday
    5: ['09:00:00', '17:00:00'], // Friday
    6: null // Saturday - closed
  }
});

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
    Inputmask('99:99').mask('.clockpicker');

    $('.date-picker').each(function () {
        new AirDatepicker(this, {
            autoClose: true,
            dateFormat: 'dd/MM/yyyy',
            onShow: () => console.log('Calendar is showing'),
        });
    });
});

function updateTimeDiff() {
    const startTime = $('#txtDateStart').val() + ' ' + $('#txtTimeStart').val();
    const endTime = $('#txtDateEnd').val() + ' ' + $('#txtTimeEnd').val();

    const format = 'dd/MM/yyyy HH:mm';

    if (moment(startTime, format).isValid() && moment(endTime, format).isValid()) {
        let start = moment(startTime, format);
        let end = moment(endTime, format);

        const diffHours = end.diff(start, 'minutes') / 60;
        $('#txtHour').val(diffHours.toFixed(1));
    } else {
        $('#txtHour').val(0);
    }
}

$(document).on('change', '.clockpicker', function() {
    updateTimeDiff();
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

$(document).on('click', '.btnApprove', function() {
    var id = $(this).data('id');
    showEditRequest(id);
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
                console.log(responseData.readonly);
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

                $('#selectUser').prop("disabled", responseData.readonly);
                $('#selectType').prop("disabled", responseData.readonly);
                $('#txtDateStart').prop("disabled", responseData.readonly);
                $('#txtDateEnd').prop("disabled", responseData.readonly);
                $('#txtTimeStart').prop("disabled", responseData.readonly);
                $('#txtTimeEnd').prop("disabled", responseData.readonly);
                $('#txtHour').prop("disabled", responseData.readonly);
                $('#txtComment').prop("readonly", responseData.readonly);
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