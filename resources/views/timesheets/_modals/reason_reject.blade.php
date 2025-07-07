<div class="modal" id="reason_reject_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reason Reject</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formReject" action="{{ $action }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <lable class="required">Reject Reason</lable>
                        <textarea
                            class="form-control"
                            name="reject_reason"
                            rows="3"
                            required
                            placeholder="Please enter the reason why timesheet is reject"
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" onclick="var e=this;setTimeout(function(){e.disabled=true;},0);return true;">Submit</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>