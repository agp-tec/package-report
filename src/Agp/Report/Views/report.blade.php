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
        <tbody>
        @if($report->items->count() == 0)
            <tr>
                <td colspan="{{ $report->columns->count() }}">
                    <div class="row d-flex">
                        <div class="col-md-12">
                    <span class="text-warning">Nenhum registro
                        encontrado {{ \App\Helper\Theme\Metronic::getSVG('media/svg/icons/Weather/Wind.svg','svg-icon-warning svg-icon-lg-4x') }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        @else
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
                <a href="?{{ $report->getDownloadLink() }}" class="btn btn-success font-weight-bolder font-size-sm">Download</a>
            </div>
        </div>
    @endif
    <div class="d-flex justify-content-center mt-5">
        {{ $report->links() }}
    </div>
</div>
