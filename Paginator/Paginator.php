<?php

namespace Morki\BounceBundle\Paginator;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\QueryBuilder;

class Paginator extends DoctrinePaginator
{
    protected $page;
    protected $limit;


    public function __construct(QueryBuilder $qb, $limit, $page = 1)
    {
        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        $this->page = $page;
        $this->limit = $limit;

        parent::__construct($qb);
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getPages()
    {
        return ceil(count($this) / $this->limit);
    }
}