<?php


namespace Agp\Report;


use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class Report implements FromCollection
{
    use Exportable;

    /** Colunas do relatório
     * @var Collection
     */
    public $columns;
    /** Campos do relatório
     * @var Collection
     */
    public $fields;
    /** Resultado da query
     * @var LengthAwarePaginator
     */
    public $items;
    /** Parametros GET de filtro e ordenacao
     * @var array
     */
    public $httpParams;
    /** Metodo que executa o select
     * @return Builder
     * @var Closure
     */
    protected $queryBuilder;
    /** Nome do arquivo blade
     * @var string
     */
    private $view;

    /**
     * Report constructor.
     */
    public function __construct()
    {
        $this->view = config('report.view');
        if (!$this->view)
            $this->view = 'Report::report';
        $this->columns = new Collection();
        $this->fields = new Collection();

        $this->queryBuilder = null;
    }

    /**
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->executaQuery(null, true);
        return $this->items;
    }

    /** Executa query do relatorio
     * @param Builder|null $builder
     * @param bool $export Indica se query deve ser paginada para web ou sem paginacao para export
     */
    private function executaQuery($builder = null, $export = false)
    {
        $query = $this->queryBuilder;
        $builder = $query();

        $query = request()->get('query');
        if ($query) {
            foreach ($query as $key => $value) {
                if ($value) {
                    $column = $this->getColumnByName($key);
                    switch ($column->filter->metodo) {
                        case '=':
                        case '>':
                        case '<':
                        case '<=':
                        case '>=':
                        case '!=':
                        case '<>':
                            $builder = $builder->where($key, $column->filter->metodo, $value);
                            break;
                        case 'like':
                            $builder = $builder->where($key, 'LIKE', '%' . $value . '%');
                            break;
                        case 'between':
                            if (array_key_exists('start', $value) && array_key_exists('end', $value) &&
                                $value['start'] && $value['end'])
                                $builder = $builder->whereBetween($key, $value);
                            else
                                unset($query[$key]);
                            break;
                    }
                } else
                    unset($query[$key]);
            }
        }

        $order = request()->get('order');
        if ($order) {
            foreach ($order as $key => $value) {
                if ($value) {
                    $column = $this->getColumnByName($key);
                    $column->filter->order = $value;
                    $builder = $builder->orderBy($key, $value);
                } else
                    unset($order[$key]);
            }
        }
        $this->httpParams = [
            'order' => $order,
            'query' => $query,
        ];
        if ($export)
            $this->items = $builder->get();
        else
            $this->items = $builder->paginate(10)->appends($this->httpParams);
        return $this->items;
    }

    /** Retorna a coluna identificada pelo nome
     * @param $name
     * @return ReportColumn|null
     */
    public function getColumnByName($name)
    {
        foreach ($this->columns as $item)
            if ($item->name == $name)
                return $item;
        return null;
    }

    /** Retorna o campo identificada pelo nome
     * @param $name
     * @return ReportField|null
     */
    public function getFieldByName($name)
    {
        foreach ($this->fields as $item)
            if ($item->column->name == $name)
                return $item;
        return null;
    }

    /** Cria colunas com base no atributo fillables da entidade
     * @param Model $model Entidade
     */
    public function setModel($model)
    {
        if ($model instanceof Model) {
            foreach ($model->getFillable() as $item) {
                $column = new ReportColumn([
                    'name' => $item,
                    'title' => ucwords(str_replace('_', ' ', strtolower($item))),
                    'headerClass' => '',
                    'rowClass' => '',
                ]);
                $this->columns->add($column);
                $this->fields->add(new ReportField($column, [
                    'attr' => [
                        'calss' => '',
                    ],
                    'callback' => null
                ]));
            }
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function build()
    {
        if (!$this->queryBuilder)
            throw new \Exception('Method queryBuilder not implemented.');

        $this->clearTotalizadores();

        $this->executaQuery();

        foreach ($this->items as $item)
            foreach ($this->columns as $column)
                if ($column->totalizador)
                    $column->totalizador->append($item->{$column->name});

        return $this->view();
    }

    /**
     * Limpa os totalizadores
     */
    private function clearTotalizadores()
    {
        foreach ($this->columns as $column)
            if ($column->totalizador)
                $column->totalizador->clear();
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function view()
    {
        $report = $this;
        return view($this->view, compact('report'));
    }

    /** Retorna os links do paginator
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function links()
    {
        return $this->items->links();
    }

    /** Retorna se possui parametro indicando que deve fazer download do arquivo
     * @return bool
     */
    public function toExport()
    {
        return (request()->query('export') != null);
    }

    /** Retorna resposta com arquivo para download
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return Excel::download($this, 'users.xlsx');
    }
}
