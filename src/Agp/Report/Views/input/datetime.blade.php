<div class="input-daterange input-group datepicker">
    <input type="text" class="form-control" name="{{ $inputName }}[start]"
           value="{{ $inputValue['start']?$inputValue['start']:'' }}">
    <div class="input-group-append">
        <span class="input-group-text">
            <i class="la la-ellipsis-h"></i>
        </span>
    </div>
    <input type="text" class="form-control" name="{{ $inputName }}[end]"
           value="{{ $inputValue['end']?$inputValue['end']:'' }}">
</div>
