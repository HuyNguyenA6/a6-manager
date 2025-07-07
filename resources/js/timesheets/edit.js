import '../app.js';

$(document).ready(function() {
  $('#dataTable_reportDate').DataTable( {      
      scrollY: 500,
      scrollX: true,
      searching: false,
      ordering: false,
      paging: false,
      bPaginate: false,
      bInfo : false,
  } );


  showDates();
  calculatorAll()
  console.log("Datepicker?", $.fn.datepicker);
  console.log("jQuery version:", $.fn.jquery);
  // var moment = require("moment");
  var start = new Date();
  start.setHours(9);
  start.setMinutes(0);
  var prevDay;
  if($("#start_date").val()) {
    showDates();    
  }

  var disabledDays = [0, 2, 3, 4, 5, 6];
  $("#start_date").datepicker({
    dateFormat: "dd/mm/yy",
    firstDay: 1, 
    beforeShowDay: function(date) {
      const disabledDays = [0, 2, 3, 4, 5, 6]; // Only allow Mondays
      return [disabledDays.indexOf(date.getDay()) === -1];
    },
    onSelect: function (fd, date) {
      //let selected = moment(date);
      showDates();
    }
  });
});

//========================================================================
// BUTTON CLICK
//========================================================================

$(document).on('click', '.btnAdd', function (event) {
  event.preventDefault();
  // var index = $('#index').val();
  // $('#index').val(index + 1);
  let index = $('.empty_data').length;
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $.ajax({
      type: 'POST',
      url: $('#add_work_hour_link').val(),
      data: {
          index: index
      }
  }).done(function (data) {
    console.log(data);
    if (event.target.closest('tr')){
      console.log('a');
      // $('#add_row').before(data);
      $('#dataTable_reportDate').DataTable().row.add($(data)).draw();
      // $('#dataTable_reportDate').DataTable().draw();
    }
    else if (event.target.closest('div')){
      console.log('b');
      $(".data_reportDate .report-body").find("#add_row").before(data);
    }
    console.log('c');
    // refreshFields();
  });
});

$(document).on('click', '.btnDelete', function (event) {
  event.preventDefault();
  var id = $(this).data('id')
  console.log(id);
  if(id == '') {
    if (event.target.closest('tr')){
      $(this).closest('tr').remove();
    }
    else if(event.target.closest('div')){
      $(this).parents(".time_row").remove();
    }
  } else {
    $('#' + id).val('1');
    if (event.target.closest('tr')){
      $(this).closest('tr').hide();
    }
    else if(event.target.closest('div')){
      $(this).parents(".time_row").hide();
      $(this).parents(".time_row").next("hr").remove();
    }
  }
  calculatorAll()
});

//========================================================================
// INPUT CHANGE
//========================================================================

$(document).on('change', '.work_hours', function(event) {
  calculatorAll();
})

//========================================================================
// FUNCTIONS
//========================================================================

function showDates() {
  if($("#start_date").val()) {
    var date = moment($("#start_date").val(), 'DD/MM/YYYY');
    $('#original_date').val($("#start_date").val());
    $('#monday_field').html(date.format('DD/MM'));
    $('#monday_input').val(date.format('DD/MM/YYYY'));
    $('#tuesday_field').html(date.add(1,'days').format('DD/MM'));
    $('#tuesday_input').val(date.format('DD/MM/YYYY'));
    $('#wednesday_field').html(date.add(1,'days').format('DD/MM'));
    $('#wednesday_input').val(date.format('DD/MM/YYYY'));
    $('#thursday_field').html(date.add(1,'days').format('DD/MM'));
    $('#thursday_input').val(date.format('DD/MM/YYYY'));
    $('#friday_field').html(date.add(1,'days').format('DD/MM'));
    $('#friday_input').val(date.format('DD/MM/YYYY'));
    $('#saturday_field').html(date.add(1,'days').format('DD/MM'));
    $('#saturday_input').val(date.format('DD/MM/YYYY'));
    $('#sunday_field').html(date.add(1,'days').format('DD/MM'));
    $('#sunday_input').val(date.format('DD/MM/YYYY'));
  }  
}

function calculatorAll() {
  for (let x = 0 ; x < 8 ; x++) {
    handleCalculatorReport(x, 'nt')
  }
}

function handleCalculatorReport(index, type) {

  let subtotal = 0;
  $(`.data_reportDate .item-${type}[data-index="${index}"]:visible`).each((index, item) => {
    subtotal += parseFloat($(item).val() || "0");
  })

  // As the footer will be duplicated
  // We will deduct the expenses from the BODY table
  $(`.dataTables_scrollBody .data_reportDate tfoot .item-${type}[data-index="${index}"]`).each((index, item) => {
    subtotal -= parseFloat($(item).val() || "0");
  })

  $(`.subtotal_${type}_${index}`).val(subtotal.toFixed(2))

  //Calculator Total By Type
  let total = 0;
  for (let i = 1; i < 8 ; i++) {
    let value = $(`.data_reportDate .subtotal_${type}_${i}`).val();
    if (value && !isNaN(value)) {
      total += parseFloat(value)
    }
  }

  // $(`.total_${type}`).val(total.toFixed(2));
  $(`.total_count`).val(total.toFixed(2));
  $('#hours_worked').val(total.toFixed(2));
}