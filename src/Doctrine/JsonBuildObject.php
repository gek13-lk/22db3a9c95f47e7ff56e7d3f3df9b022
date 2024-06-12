<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class JsonBuildObject extends FunctionNode
{
    public $values = [];

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        while (!$parser->getLexer()->isNextToken(TokenType::T_CLOSE_PARENTHESIS)) {
            $this->values[] = $parser->StringPrimary();
            $parser->match(TokenType::T_COMMA);
            $this->values[] = $parser->ArithmeticPrimary();
            if ($parser->getLexer()->isNextToken(TokenType::T_COMMA)) {
                $parser->match(TokenType::T_COMMA);
            }
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $elements = [];
        for ($i = 0; $i < count($this->values); $i += 2) {
            $key = $this->values[$i]->dispatch($sqlWalker);
            $value = $this->values[$i + 1]->dispatch($sqlWalker);
            $elements[] = $key . ', ' . $value;
        }

        return 'JSON_BUILD_OBJECT(' . implode(', ', $elements) . ')';
    }
}
