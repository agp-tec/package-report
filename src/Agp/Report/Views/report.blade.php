<div class="card card-custom gutter-b">
    <div class="card-header border-0 py-5">
        <div class="card-toolbar">
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseOpcoes"
                    aria-expanded="false" aria-controls="collapseOpcoes">
                Opções
            </button>
        </div>
    </div>
    <div class="card-body py-0" id="transacoes">
        <div class="collapse mb-5" id="collapseOpcoes">
            <div class="card card-body">
                <form method="get">
                    <div class="row border">
                        @foreach($report->columns as $column)
                            @if($column->filter->tipo != '')
                                <div class="col-md-3 mr-2">
                                    {!! $column->title !!}
                                    {!! $column->filter->renderInput() !!}
                                </div>
                            @endif
                        @endforeach
                        <div class="col-md-1 border align-items-center">
                            <button class="btn btn-primary font-weight-bolder font-size-sm" type="submit">Filtrar
                            </button>
                        </div>

                        <div class="col-md-1">
                            <form method="get">
                                <input class="form-control" type="hidden" name="export" value="1">
                                <button class="btn btn-success font-weight-bolder font-size-sm" type="submit">Download
                                </button>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
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
    </div>
</div>
