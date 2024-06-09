<?php

declare(strict_types=1);

namespace App\Doctrine;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;
class JsonbExists extends FunctionNode
{
    public $key;
    public $value;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->key = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->value = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'JSONB_EXISTS('. $this->key->dispatch($sqlWalker) .'::jsonb, '.$this->value->dispatch($sqlWalker).')';
    }
}
