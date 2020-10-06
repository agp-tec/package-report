<?php


namespace Agp\Report;


use Closure;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Report
{

    /** Colunas do relatório
     * @var Collection
     */
    public $columns;
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
     * @var Closure|Builder
     */
    protected $queryBuilder;

    /** Nome do arquivo blade
     * @var string
     */
    private $view;

    /**
     * @var ReportExport
     */
    private $reportExport;

    /**
     * Report constructor.
     */
    public function __construct()
    {
        $this->view = config('report.view');
        if (!$this->view)
            $this->view = 'Report::report';
        $this->columns = new Collection();
        $this->reportExport = new ReportExport($this);
        $this->queryBuilder = null;
    }

    /** Adiciona uma coluna
     * @param ReportColumn|string $data
     * @return ReportColumn
     */
    public function addColumn($data)
    {
        if ($data instanceof ReportColumn) {
            $this->columns->add($data);
            $data->alias = pow($this->columns->count(), 3);
            return $data;
        }
        if (is_string($data)) {
            $column = new ReportColumn($data);
            $this->columns->add($column);
            $column->alias = pow($this->columns->count(), 3);
            return $column;
        }
        throw new \Exception('Invalid type');
    }

    /** Executa query do relatorio
     * @param Builder|null $builder
     * @param bool $export Indica se query deve ser paginada para web ou sem paginacao para export
     */
    public function executaQuery($builder = null, $export = false)
    {
        $query = $this->queryBuilder;
        $builder = $query();
        if (request()->get('clear'))
            request()->merge([
                'clear' => null,
                'query' => null,
                'order' => null,
            ]);
        $builder = $this->montaSelects($builder);
        $builder = $this->montaWhere($builder);
        $builder = $this->montaOrder($builder);
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
        throw new \Exception('Unknow column ' . $name);
    }

    /** Retorna a coluna identificada pelo alias
     * @param $alias
     * @return ReportColumn|null
     */
    public function getColumnByAlias($alias)
    {
        foreach ($this->columns as $item)
            if ($item->alias == $alias)
                return $item;
        throw new \Exception('Unknow column ' . $alias);
    }

    /** Cria colunas com base no atributo fillables da entidade
     * @param Model|array $model Entidade
     */
    public function setModel($model)
    {
        if ($model instanceof Model) {
            foreach ($model->getFillable() as $item) {
                $this->columns->add(new ReportColumn([
                    'name' => $model->getTable() . '.' . $item,
                    'title' => ucwords(str_replace('_', ' ', strtolower($item))),
                ]));
            }
        } elseif (is_array($model)) {
            foreach ($model as $item) {
                $this->setModel($item);
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
                $column->totalizador->append($item);

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
    protected function view()
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
        return $this->reportExport->doExport();
    }

    /**
     * @param Builder $builder
     * @return Builder
     * @throws \Exception
     */
    private function montaWhere($builder)
    {
        $query = request()->get('query');
        if ($query) {
            foreach ($query as $key => $value) {
                if ($value) {
                    $column = $this->getColumnByAlias($key);
                    $column->filter->data = $value;
                    if ($column->filter->tipo == 'choice') {
                        $builder = $builder->where(DB::raw($column->name), '=', $value);
                        continue;
                    }
                    switch ($column->filter->metodo) {
                        case '=':
                        case '>':
                        case '<':
                        case '<=':
                        case '>=':
                        case '!=':
                        case '<>':
                            $builder = $builder->where(DB::raw($column->name), $column->filter->metodo, $value);
                            break;
                        case 'like':
                            $builder = $builder->where(DB::raw($column->name), 'LIKE', '%' . $value . '%');
                            break;
                        case 'between':
                            if (array_key_exists('start', $value) && array_key_exists('end', $value) &&
                                $value['start'] && $value['end']) {
                                $format = $column->filter->tipo == 'date' ? 'd/m/Y' : 'd/m/Y H:i:s';
                                $value['start'] = DateTime::createFromFormat($format, $value['start']);
                                $value['end'] = DateTime::createFromFormat($format, $value['end']);
                                $builder = $builder->whereBetween(DB::raw($column->name), $value);
                            } else
                                unset($query[$key]);
                            break;
                    }
                } else
                    unset($query[$key]);
            }
            $this->httpParams['query'] = $query;
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    private function montaSelects($builder)
    {
        $selects = array();
        foreach ($this->columns as $column)
            if ($column->field->getActions() == null)
                $selects[] = $column->name . ' as ' . $column->name;
        return $builder->select($selects);
    }

    /**
     * @param Builder $builder
     * @return Builder
     * @throws \Exception
     */
    private function montaOrder($builder)
    {
        $order = request()->get('order');
        if ($order) {
            foreach ($order as $key => $value) {
                if ($value) {
                    $column = $this->getColumnByAlias($key);
                    $column->filter->order = $value;
                    $builder = $builder->orderBy($column->name, $value);
                } else
                    unset($order[$key]);
            }
            $this->httpParams['order'] = $order;
        }
        return $builder;
    }

}
