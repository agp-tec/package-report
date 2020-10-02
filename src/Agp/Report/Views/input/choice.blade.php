<select class="form-control" name="{{ $inputName }}">
    <option value>Selecione</option>
    @foreach($options as $key => $value)
        <option value="{{ $key }}" {{ $inputValue == $key?'selected':'' }}>{{ $value }}</option>
    @endforeach
</select>
