<?php

namespace App\Util\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class DistinctOn extends FunctionNode
{
    private $expressions = null;

    private $right = null;

    public function parse(Parser $parser)
    {
        $lexer = $parser->getLexer();
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expressions[] = $parser->SingleValuedPathExpression();
        while ($lexer->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->expressions[] = $parser->SingleValuedPathExpression();
            if ($lexer->isNextToken(Lexer::T_AS)) {
                $parser->match(Lexer::T_AS);
                $this->alias = $parser->StringPrimary();
            }
        }
        $this->right = array_pop($this->expressions);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    public function getSql(SqlWalker $walker)
    {
        return sprintf(
            "DISTINCT ON (%s) %s",
            implode(', ', array_map(function ($expression) use ($walker) {
                return $expression->dispatch($walker);
            }, $this->expressions)),
            $this->right->dispatch($walker)
        );
    }
}