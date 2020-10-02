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
            <div class="card card-body d-flex">
                <span class="text-secondary">Filtros</span>
                <div class="row d-flex flex-column">
                    <div class="form-group">
                        <form method="get">
                            @foreach($report->columns as $column)
                                @if($column->filter->tipo != '')
                                    <div class="form-group">
                                        {!! $column->header->title !!}
                                        {!! $column->filter->renderInput() !!}
                                    </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                <div class="row d-flex flex-column">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Filtrar
                        </button>
                        </form>
                        <a class="btn btn-warning" href="?clear=1">Limpar</a>
                    </div>
                </div>
            </div>
        </div>
        <table class='table table-head-custom table-head-bg table-borderless table-vertical-center'>
            <thead>
            <tr class="text-left text-uppercase">
                @foreach($report->columns as $column)
                    <th {{ $column->header->getAttrs() }}>
                        <a href="?{{ $column->filter->getOrderByUrl($report->httpParams) }}">{{ $column->header->title }}</a>
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($report->items as $item)
                <tr>
                    @foreach($report->columns as $column)
                        <td {{ $column->field->getAttrs() }}>
                            {!! $column->field->renderField($item) !!}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-5">
            <table class='table table-borderless'>
                <thead>
                <tr>
                    @foreach($report->columns as $column)
                        <th>
                            @if($column->totalizador->metodo != '')
                                {{ $column->totalizador->getValor() }}
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
            </table>
            <div class="row">
                <div class="col-md-2 text-right align-content-end justify-content-end">
                    <form method="get">
                        <input class="form-control" type="hidden" name="export" value="1">
                        <button class="btn btn-success font-weight-bolder font-size-sm" type="submit">Download
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-5">
            {{ $report->links() }}
        </div>
    </div>
</div>
