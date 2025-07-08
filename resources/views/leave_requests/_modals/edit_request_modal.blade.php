<div class="modal fade" id="editRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="requestModalTitle" class="modal-title">New/Edit Request</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frmTaskType">
                    <input type="hidden" id="txtLeaveRequestId" name="id">
                    <div class="form-group">
                        <label>User</label>
                        <select id="selectUser" name="user_id" class="form-control">
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="txtTimeStart" class="required" >Time Start</label>
                        <div class="input-group">
                            <input type="text" class="date-picker form-control" id="txtDateStart" name="date_start" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy">

                            <div class="input-group-append show-datepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                            <input type="text" class="clockpicker form-control" id="txtTimeStart" name="time_start" value="" data-placement="left" data-align="top" data-autoclose="true" required style="background-color: white">
                            <div class="input-group-append">
                                <div class="input-group-text"><i class="fa fa-clock"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="txtTimeEnd" class="required" >Time End</label>
                        <div class="input-group">
                            <input type="text" class="date-picker form-control" id="txtDateEnd" name="date_end" value="" data-language="en" data-date-format="dd/mm/yyyy" data-time-format='hh:ii' required readonly style="background-color: white">
                            <div class="input-group-append show-datepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                            <input type="text" class="clockpicker form-control" id="txtTimeEnd" name="time_end" value="" data-placement="left" data-align="top" data-autoclose="true" required style="background-color: white">
                            <div class="input-group-append">
                                <div class="input-group-text"><i class="fa fa-clock"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="txtHour">Hours</label>
                            <input type="text" class="form-control" id="txtHour" name="hour" value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="type">Type</label>
                            <select id="selectType" name="request_type" class="form-control">
                                <option value="">= Select Type =</option>
                                @foreach ($request_types as $key => $type)
                                    <option value="{{ $key }}">
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="txtComment">Comment</label>
                        <textarea class="form-control" id="txtComment" name="comment" rows="5"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <a id="btnSubmitRequest" class="btn btn-primary" href="#">Submit</a>
            </div>
        </div>
    </div>
</div>