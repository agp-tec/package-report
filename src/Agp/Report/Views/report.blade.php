<div class="d-flex mt-5">
    <form method="get">
        <div class="form-row">
            @foreach($report->columns as $column)
                @if($column->filter->tipo != '')
                    <div class="col-md-3">
                        {!! $column->title !!}
                        {!! $column->filter->renderInput() !!}
                    </div>
                @endif
            @endforeach
            <div class="col-md-3">
                <button class="form-control" type="submit">Filter</button>
            </div>
        </div>
    </form>
    <form method="get">
        <div class="col-md-3">
            <label>Download
                <input class="form-control" type="hidden" name="export" value="1">
                <button class="form-control" type="submit">Download</button>
            </label>
        </div>
    </form>
</div>

<table class='table table-head-custom table-head-bg table-borderless table-vertical-center'>
    <thead>
    <tr class="text-left text-uppercase">
        @foreach($report->columns as $column)
            <th
            @foreach($column->attr as $attr => $value)
                {{ $attr }}='{{ $value }}'
            @endforeach
            >
            <a href="?{{ $column->filter->getOrderByUrl($report->httpParams) }}">{{ $column->title }}</a>
            </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($report->items as $item)
        <tr
        >
            @foreach($report->fields as $field)
                <td
                @foreach($field->attr as $attr => $value)
                    {{ $attr }}='{{ $value }}'
                @endforeach
                >
                {!! $field->renderField($item) !!}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center mt-5">
    <table class='table table-bordered'>
        <thead>
        <tr>
            @foreach($report->columns as $column)
                <th>
                    @if($column->totalizador)
                        {{ $column->totalizador->getValor() }}
                    @endif
                </th>
            @endforeach
        </tr>
        </thead>
    </table>
</div>
<div class="d-flex justify-content-center mt-5">
    {{ $report->links() }}
</div>
