<tr class="time_row empty_data">
    <td>
        <select name="timesheet_new[{{$i}}][activity_type]" class="form-control project_number project_number_field"
                autocomplete="off">
            <option></option>
            @foreach ($timesheet_types as $type_id => $type)
                <option value="{{$type_id}}">{{$type}}</option>
            @endforeach
        </select>
    </td>
    <td><input type="text" class="form-control job_type" name="timesheet_new[{{$i}}][project_code]"></td>
    <td><input type="text" class="form-control" name="timesheet_new[{{$i}}][comment]"></td>

    @for($y = 1 ; $y < 8 ; $y++)
        <td>
            <input
                type="text" class="form-control work_hours item-nt p-2"
                name="timesheet_new[{{$i}}][hour_{{$y}}]"
                data-index="{{$y}}"
                data-id="{{$i}}"
                data-item="{{$y}}"
                min="0"
            >
        </td>
    @endfor

    <td><a href="#" class='d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm btnDelete' data-id=""><i
                class='fa fa-trash'></i></a></td>
</tr>