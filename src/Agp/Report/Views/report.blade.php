<div class="table-responsive">
    <table class='table table-head-custom table-head-bg table-borderless table-vertical-center'>
        <thead>
        <tr class="text-left text-uppercase">
            @foreach($report->columns as $column)
                @if(!$column->visible)
                    @continue
                @endif
                <th {!! $column->header->getAttrs() !!}>
                    <a href="?{{ $column->filter->getOrderByUrl($report->httpParams) }}">{{ $column->header->title }}</a>
                </th>
            @endforeach
        </tr>
        </thead>
        @if($report->items->count() == 0)
            <div class="row d-flex">
                <div class="col-md-12">
                    Nenhum registro
                    encontrado {{ \App\Helper\Theme\Metronic::getSVG('media/svg/icons/Weather/Wind.svg','svg-icon-warning svg-icon-lg-4x') }}
                </div>
            </div>
        @else
            <tbody>
            @foreach ($report->items as $item)
                <tr>
                    @foreach($report->columns as $column)
                        @if(!$column->visible)
                            @continue
                        @endif
                        <td class="text-muted">
                            {!! $column->field->renderField($item) !!}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            @endif
            </tbody>
    </table>
    @if(!isset($report->notDownload))
        <div class="row d-flex justify-content-end mt-5">
            <div class="col-2 text-right">
                <form method="get">
                    <input class="form-control" type="hidden" name="export" value="1">
                    <button class="btn btn-success font-weight-bolder font-size-sm" type="submit">Download
                    </button>
                </form>
            </div>
        </div>
    @endif
    <div class="d-flex justify-content-center mt-5">
        {{ $report->links() }}
    </div>
</div>
